<?php defined('BASEPATH') OR exit('No direct script access allowed');

class M_movas_booking extends CI_Model
{
    // ── CEK BOOKING AKTIF PER NOPOL ───────────────────────
    public function cek_booking_aktif_nopol($customer_id, $nopol)
    {
        return $this->db->select('bd.id')
            ->from('mac_booking_detail bd')
            ->join('mac_booking_service bs', 'bs.id = bd.booking_id')
            ->where('bs.customer_id', $customer_id)
            ->where('bd.nopol', $nopol)
            ->where_in('bs.status', ['pending', 'dikonfirmasi'])
            ->limit(1)
            ->get()->row_array();
    }

    // ── CEK BOOKING AKTIF CUSTOMER ─────────────────────────
    public function get_booking_aktif($customer_id)
    {
        return $this->db->select('
                bs.id, bs.tgl_booking, bs.waktu_catatan,
                bs.lokasi_service, bs.alamat_service,
                bs.catatan, bs.status, bs.created_at,
                bs.nama_cabang  -- ← tambah ini
            ', FALSE)
            ->from('mac_booking_service bs')
            ->where('bs.customer_id', $customer_id)
            ->where_in('bs.status', ['pending', 'dikonfirmasi'])
            ->order_by('bs.created_at', 'DESC')
            ->get()->result_array();
    }

    // ── GET DETAIL BOOKING ─────────────────────────────────
    public function get_detail_booking($booking_id)
    {
        return $this->db->select('
                bd.nopol, bd.km, bd.keluhan,
                bd.is_new_unit, bd.tipe AS tipe_baru, bd.jenis_kendaraan AS jenis_baru,
                (SELECT i.tipe FROM mac_invoice i
                WHERE i.nopol = bd.nopol
                AND i.app_status = "approved"
                ORDER BY i.created_at DESC LIMIT 1) AS tipe
            ', FALSE)
            ->from('mac_booking_detail bd')
            ->where('bd.booking_id', $booking_id)
            ->get()->result_array();
    }

    // ── GET RIWAYAT BOOKING ────────────────────────────────
    public function get_riwayat_booking($customer_id)
    {
        return $this->db->select('
                bs.id, bs.tgl_booking, bs.waktu_catatan,
                bs.lokasi_service, bs.alamat_service,
                bs.status, bs.dibatalkan_oleh, bs.alasan_batal,
                bs.created_at, bs.nama_cabang
            ', FALSE)
            ->from('mac_booking_service bs')
            ->where('bs.customer_id', $customer_id)
            ->where_in('bs.status', ['selesai', 'dibatalkan'])
            ->order_by('bs.created_at', 'DESC')
            ->limit(10)
            ->get()->result_array();
    }

    // ── SAVE BOOKING ───────────────────────────────────────
    public function save_booking($data)
    {
        $this->db->insert('mac_booking_service', $data);
        return $this->db->insert_id();
    }

    // ── SAVE BOOKING DETAIL ────────────────────────────────
    public function save_booking_detail($booking_id, $details)
    {
        foreach ($details as $d) {
            $this->db->insert('mac_booking_detail', [
                'booking_id'      => $booking_id,
                'nopol'           => $d['nopol'],
                'km'              => $d['km'] ?: null,
                'keluhan'         => $d['keluhan'] ?: null,
                'is_new_unit'     => $d['is_new_unit'] ?? 0,   // ← tambah
                'tipe'            => $d['tipe'] ?? null,         // ← tambah
                'jenis_kendaraan' => $d['jenis_kendaraan'] ?? null, // ← tambah
            ]);
        }
    }

    // ── CANCEL BOOKING OLEH CUSTOMER ──────────────────────
    public function cancel_booking($booking_id, $customer_id)
    {
        // Pastikan hanya bisa cancel jika pending dan milik customer ini
        $booking = $this->db->where('id', $booking_id)
            ->where('customer_id', $customer_id)
            ->where('status', 'pending')
            ->get('mac_booking_service')->row_array();

        if (empty($booking)) return FALSE;

        $this->db->where('id', $booking_id)
            ->update('mac_booking_service', [
                'status'          => 'dibatalkan',
                'dibatalkan_oleh' => 'customer',
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

        return TRUE;
    }

    // ── GET BOOKING BY ID ──────────────────────────────────
    public function get_booking_by_id($booking_id)
    {
        $booking = $this->db->select('
                bs.*, c.customer_name, c.type_customer, c.no_telp
            ', FALSE)
            ->from('mac_booking_service bs')
            ->join('mac_customer c', 'c.id = bs.customer_id', 'left')
            ->where('bs.id', $booking_id)
            ->get()->row_array();

        if (empty($booking)) return null;

        // Ambil PIC dari invoice terakhir secara terpisah
        $invoice = $this->db->select('pic')
            ->where('customer_id', $booking['customer_id'])
            ->where('app_status', 'approved')
            ->order_by('created_at', 'DESC')
            ->limit(1)
            ->get('mac_invoice')->row_array();

        $booking['pic'] = $invoice['pic'] ?? '';

        return $booking;
    }

    // ── GET LIST BOOKING UNTUK ADMIN ──────────────────────
    public function get_list_admin($cabang_id = null, $is_nasional = false, $status = null)
    {
        $this->db->select('
                bs.id, bs.tgl_booking, bs.waktu_catatan,
                bs.lokasi_service, bs.alamat_service,
                bs.status, bs.created_at, bs.confirmed_at,
                c.customer_name, c.no_telp, c.type_customer,
                i.pic
            ', FALSE)
            ->from('mac_booking_service bs')
            ->join('mac_customer c', 'c.id = bs.customer_id', 'left')
            ->join('mac_invoice i',  'i.customer_id = bs.customer_id
                AND i.id = (
                    SELECT id FROM mac_invoice
                    WHERE customer_id = bs.customer_id
                    AND app_status = "approved"
                    ORDER BY created_at DESC LIMIT 1
                )', 'left');

        if (!$is_nasional && $cabang_id) {
            $this->db->where('bs.cabang_id', $cabang_id);
        } elseif ($is_nasional && $cabang_id) {
            $this->db->where('bs.cabang_id', $cabang_id);
        }

        if ($status) {
            $this->db->where('bs.status', $status);
        }

        $this->db->order_by('bs.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    // ── COUNT BOOKING BARU (UNTUK BADGE) ──────────────────
    public function count_booking_baru($cabang_id = null, $is_nasional = false)
    {
        $this->db->from('mac_booking_service');

        if (!$is_nasional && $cabang_id) {
            $this->db->where('cabang_id', $cabang_id);
        }

        $this->db->where('status', 'pending');

        return $this->db->count_all_results();
    }
}