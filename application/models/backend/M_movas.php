<?php defined('BASEPATH') or exit('No direct script access allowed');

class M_movas extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ── VALIDASI TOKEN ─────────────────────────────────────────
    public function get_customer_by_token($token)
    {
        return $this->db->select('
                id, customer_name, type_customer,
                no_telp, address,
                movas_token, movas_is_active,
                movas_is_pro, movas_token_created_at
            ')
            ->where('movas_token', $token)
            ->where('movas_is_active', 1)
            ->get('mac_customer')->row_array();
    }

    // ── LIST KENDARAAN PER CUSTOMER ────────────────────────────
    public function get_kendaraan_list($customer_id, $is_pro)
    {
        // Jika FREE, ambil 6 invoice terbaru dulu via query terpisah
        $where_id = '';
        if (!$is_pro) {
            $invoice_ids = $this->db->select('id')
                ->from('mac_invoice')
                ->where('customer_id', $customer_id)
                ->where('app_status', 'approved')
                ->where('is_active', 1)
                ->order_by('awal_service', 'DESC')
                ->limit(6)
                ->get()->result_array();

            if (empty($invoice_ids)) return [];

            $ids = array_column($invoice_ids, 'id');
            $where_id = 'AND i.id IN (' . implode(',', $ids) . ')';
        }

        $sql = "SELECT
                    i.nopol,
                    i.tipe,
                    i.jenis_kendaraan,
                    MAX(i.awal_service)  AS terakhir_service,
                    MAX(i.km)            AS km_terakhir,
                    COUNT(i.id)          AS total_service
                FROM mac_invoice i
                WHERE i.customer_id = " . intval($customer_id) . "
                AND i.app_status = 'approved'
                AND i.is_active = 1
                {$where_id}
                GROUP BY i.nopol
                ORDER BY terakhir_service DESC";

        $kendaraan_list = $this->db->query($sql)->result_array();

        foreach ($kendaraan_list as &$k) {
            $sesi = $this->db->select('s.id, s.tgl_service, s.tahun')
                ->from('mac_movas_sesi s')
                ->join('mac_invoice i', 'i.id = s.invoice_id')
                ->where('i.customer_id', $customer_id)
                ->where('s.nopol', $k['nopol'])
                ->order_by('s.tgl_service', 'DESC')
                ->limit(1)
                ->get()->row_array();

            $k['has_kondisi']    = !empty($sesi);
            $k['sesi_id']        = $sesi['id'] ?? null;
            $k['tahun']          = $sesi['tahun'] ?? null; // ← tambah
            $k['status_kondisi'] = $sesi
                ? $this->_hitung_status_kondisi($sesi['id'])
                : 'belum_ada';
        }

        return $kendaraan_list;
    }

    // ── HITUNG STATUS KONDISI KESELURUHAN ──────────────────────
    private function _hitung_status_kondisi($sesi_id)
    {
        $kondisi_negatif_kritis = [
            'Perlu Ganti', 'Rusak', 'Bocor', 'Retak', 'Robek',
            'Aus', 'Habis', 'Mati', 'Getas', 'Hilang',
            'Tidak Berfungsi', 'Tidak Ada', 'Perlu Overhaul',
        ];
        $kondisi_negatif_perhatian = [
            'Kotor', 'Tipis', 'Lemah', 'Kurang Dingin', 'Kurang',
            'Kendur', 'Berat', 'Perlu Servis', 'Perlu Tune Up',
        ];

        // Ambil semua nilai kondisi dari semua tabel
        $semua_nilai = $this->_get_semua_nilai_kondisi($sesi_id);

        foreach ($semua_nilai as $nilai) {
            if (in_array($nilai, $kondisi_negatif_kritis)) return 'kritis';
        }
        foreach ($semua_nilai as $nilai) {
            if (in_array($nilai, $kondisi_negatif_perhatian)) return 'perhatian';
        }

        return 'aman';
    }

    private function _get_semua_nilai_kondisi($sesi_id)
    {
        $nilai = [];
        $tabel_list = [
            'mac_movas_mesin', 'mac_movas_chassis',
            'mac_movas_kelistrikan', 'mac_movas_kelistrikan2',
            'mac_movas_safety', 'mac_movas_eksterior',
            'mac_movas_perlengkapan',
        ];

        foreach ($tabel_list as $tabel) {
            $row = $this->db->where('sesi_id', $sesi_id)->get($tabel)->row_array();
            if ($row) {
                unset($row['id'], $row['sesi_id'], $row['created_at'],
                      $row['updated_at'], $row['accu_tegangan'], $row['accu_merk']);
                foreach ($row as $v) {
                    if (!empty($v)) $nilai[] = $v;
                }
            }
        }

        // Ban
        $ban_rows = $this->db->where('sesi_id', $sesi_id)->get('mac_movas_ban')->result_array();
        foreach ($ban_rows as $ban) {
            if (!empty($ban['status'])) $nilai[] = $ban['status'];
        }

        return $nilai;
    }

    // ── DETAIL KENDARAAN ───────────────────────────────────────
    public function get_kendaraan_detail($customer_id, $nopol)
    {
        return $this->db->select('
                i.nopol, i.tipe, i.jenis_kendaraan, i.km,
                MAX(i.awal_service) AS terakhir_service,
                MAX(i.km)           AS km_terakhir
            ', FALSE)
            ->from('mac_invoice i')
            ->where('i.customer_id', $customer_id)
            ->where('i.nopol', $nopol)
            ->where('i.app_status', 'approved')
            ->where('i.is_active', 1)
            ->group_by('i.nopol')
            ->get()->row_array();
    }

    // ── CHIP TANGGAL SERVICE (per nopol) ───────────────────────
    public function get_chip_tanggal($customer_id, $nopol, $is_pro)
    {
        $q = $this->db->select('
                s.id as sesi_id,
                s.tgl_service,
                s.km,
                s.lokasi_service,
                s.jenis_service,
                i.id as invoice_id,
                i.invoice_number,
                i.catatan        -- ← tambah ini
            ', FALSE)
            ->from('mac_movas_sesi s')
            ->join('mac_invoice i', 'i.id = s.invoice_id')
            ->where('i.customer_id', $customer_id)
            ->where('s.nopol', $nopol)
            ->order_by('s.tgl_service', 'DESC');

        if (!$is_pro) $q->limit(6);

        return $q->get()->result_array();
    }

    // ── KONDISI KOMPONEN PER SESI ──────────────────────────────
    public function get_kondisi_by_sesi($sesi_id)
    {
        return [
            'mesin'        => $this->db->where('sesi_id', $sesi_id)->get('mac_movas_mesin')->row_array(),
            'chassis'      => $this->db->where('sesi_id', $sesi_id)->get('mac_movas_chassis')->row_array(),
            'kelistrikan'  => $this->db->where('sesi_id', $sesi_id)->get('mac_movas_kelistrikan')->row_array(),
            'kelistrikan2' => $this->db->where('sesi_id', $sesi_id)->get('mac_movas_kelistrikan2')->row_array(),
            'safety'       => $this->db->where('sesi_id', $sesi_id)->get('mac_movas_safety')->row_array(),
            'eksterior'    => $this->db->where('sesi_id', $sesi_id)->get('mac_movas_eksterior')->row_array(),
            'perlengkapan' => $this->db->where('sesi_id', $sesi_id)->get('mac_movas_perlengkapan')->row_array(),
            'ban'          => $this->db->where('sesi_id', $sesi_id)->get('mac_movas_ban')->result_array(),
        ];
    }

    // ── REKOMENDASI — dari sesi terbaru ───────────────────────
    public function get_rekomendasi($customer_id, $nopol)
    {
        // Ambil sesi terbaru
        $sesi = $this->db->select('s.id')
            ->from('mac_movas_sesi s')
            ->join('mac_invoice i', 'i.id = s.invoice_id')
            ->where('i.customer_id', $customer_id)
            ->where('s.nopol', $nopol)
            ->order_by('s.tgl_service', 'DESC')
            ->limit(1)
            ->get()->row_array();

        if (empty($sesi)) return [];

        $kondisi_all = $this->get_kondisi_by_sesi($sesi['id']);
        return $this->_extract_rekomendasi($kondisi_all);
    }

    private function _extract_rekomendasi($kondisi_all)
    {
        $kritis_list = [
            'Perlu Ganti', 'Rusak', 'Bocor', 'Retak', 'Robek',
            'Aus', 'Habis', 'Mati', 'Getas', 'Hilang',
            'Tidak Berfungsi', 'Perlu Overhaul',
        ];
        $perhatian_list = [
            'Kotor', 'Tipis', 'Lemah', 'Kurang Dingin', 'Kurang',
            'Kendur', 'Berat', 'Perlu Servis', 'Perlu Tune Up',
        ];

        // Label per kolom
        $label_map = [
            // mesin
            'tali_kipas'    => 'Tali Kipas',
            'oli_mesin'     => 'Oli Mesin',
            'filter_oli'    => 'Filter Oli',
            'slang_radiator'=> 'Slang Radiator',
            'air_radiator'  => 'Air Radiator',
            'tune_up'       => 'Tune Up',
            'busi'          => 'Busi',
            'baterai_mesin' => 'Baterai',
            'filter_udara'  => 'Filter Udara',
            'filter_bbm'    => 'Filter BBM',
            // chassis
            'oli_transmisi'    => 'Oli Transmisi',
            'oli_differential' => 'Oli Differential',
            'kabel_kopling'    => 'Kabel Kopling',
            'master_kopling'   => 'Master Kopling',
            'sistem_rem'       => 'Sistem Rem',
            'master_rem'       => 'Master Rem',
            'brake_pad'        => 'Brake Pad',
            'disc_brake'       => 'Disc Brake',
            'brake_shoe'       => 'Brake Shoe',
            'tromol'           => 'Tromol',
            'power_steering'   => 'Power Steering',
            'boot_drive_shaft' => 'Boot Drive Shaft',
            'ball_joint'       => 'Ball Joint',
            'suspensi_depan'   => 'Suspensi Depan',
            'suspensi_belakang'=> 'Suspensi Belakang',
            // kelistrikan
            'motor_extra_fan'           => 'Motor Extra Fan',
            'lampu_sein_depan_kanan'    => 'Lampu Sein Depan Kanan',
            'lampu_sein_depan_kiri'     => 'Lampu Sein Depan Kiri',
            'lampu_sein_belakang_kanan' => 'Lampu Sein Belakang Kanan',
            'lampu_sein_belakang_kiri'  => 'Lampu Sein Belakang Kiri',
            'lampu_kecil_depan_kanan'   => 'Lampu Kecil Depan Kanan',
            'lampu_kecil_depan_kiri'    => 'Lampu Kecil Depan Kiri',
            'lampu_kecil_belakang_kanan'=> 'Lampu Kecil Belakang Kanan',
            'lampu_kecil_belakang_kiri' => 'Lampu Kecil Belakang Kiri',
            'lampu_besar_pendek_kanan'  => 'Lampu Besar Pendek Kanan',
            'lampu_besar_pendek_kiri'   => 'Lampu Besar Pendek Kiri',
            'lampu_besar_jauh_kanan'    => 'Lampu Besar Jauh Kanan',
            'lampu_besar_jauh_kiri'     => 'Lampu Besar Jauh Kiri',
            'lampu_kabut_kanan'         => 'Lampu Kabut Kanan',
            'lampu_kabut_kiri'          => 'Lampu Kabut Kiri',
            'lampu_rem_kanan'           => 'Lampu Rem Kanan',
            'lampu_rem_kiri'            => 'Lampu Rem Kiri',
            'lampu_spoiler'             => 'Lampu Spoiler',
            'lampu_plat_no_pol'         => 'Lampu Plat No Pol',
            'lampu_mundur'              => 'Lampu Mundur',
            'sensor_parkir'             => 'Sensor Parkir',
            'lampu_plafon_depan'        => 'Lampu Plafon Depan',
            'lampu_plafon_tengah'       => 'Lampu Plafon Tengah',
            'lampu_panel_speedometer'   => 'Panel Speedometer',
            'lampu_hazard'              => 'Lampu Hazard',
            'fungsi_panel_wiper'        => 'Panel Wiper',
            'klakson'                   => 'Klakson',
            'fungsi_radio_tape'         => 'Radio/Tape',
            'cd_player'                 => 'CD Player',
            'fungsi_speaker'            => 'Speaker',
            'antena'                    => 'Antena',
            // kelistrikan2
            'central_lock_depan_kanan'    => 'Central Lock Depan Kanan',
            'central_lock_depan_kiri'     => 'Central Lock Depan Kiri',
            'central_lock_belakang_kanan' => 'Central Lock Belakang Kanan',
            'central_lock_belakang_kiri'  => 'Central Lock Belakang Kiri',
            'power_window_depan_kanan'    => 'Power Window Depan Kanan',
            'power_window_depan_kiri'     => 'Power Window Depan Kiri',
            'power_window_belakang_kanan' => 'Power Window Belakang Kanan',
            'power_window_belakang_kiri'  => 'Power Window Belakang Kiri',
            'electric_mirror'             => 'Electric Mirror',
            'jam'                         => 'Jam',
            'pemantik'                    => 'Pemantik',
            'ac_depan'                    => 'AC Depan',
            'ac_tengah'                   => 'AC Tengah',
            'filter_cabin'                => 'Filter Cabin',
            'asbak'                       => 'Asbak',
            'karpet'                      => 'Karpet',
            'accu_kondisi'                => 'Accu/Battery',
            // safety
            'kursi_depan_kanan'    => 'Kursi Depan Kanan',
            'kursi_depan_kiri'     => 'Kursi Depan Kiri',
            'kursi_tengah_kanan'   => 'Kursi Tengah Kanan',
            'kursi_tengah_kiri'    => 'Kursi Tengah Kiri',
            'kursi_belakang_kanan' => 'Kursi Belakang Kanan',
            'kursi_belakang_kiri'  => 'Kursi Belakang Kiri',
            'penahan_matahari'     => 'Penahan Matahari',
            // eksterior
            'wiper_blade_depan'       => 'Wiper Depan',
            'wiper_blade_belakang'    => 'Wiper Belakang',
            'spion_luar_kanan'        => 'Spion Luar Kanan',
            'spion_luar_kiri'         => 'Spion Luar Kiri',
            'spion_dalam'             => 'Spion Dalam',
            'mirror_lamp'             => 'Mirror Lamp',
            'mudguard_depan_kanan'    => 'Mudguard Depan Kanan',
            'mudguard_depan_kiri'     => 'Mudguard Depan Kiri',
            'mudguard_belakang_kanan' => 'Mudguard Belakang Kanan',
            'mudguard_belakang_kiri'  => 'Mudguard Belakang Kiri',
            'foot_step'               => 'Foot Step',
            'liner_fender_kanan'      => 'Liner Fender Kanan',
            'liner_fender_kiri'       => 'Liner Fender Kiri',
            // perlengkapan
            'toolkit_tang'          => 'Tang',
            'toolkit_obeng'         => 'Obeng',
            'toolkit_kunci_ring'    => 'Kunci Ring',
            'toolkit_kunci_pas'     => 'Kunci Pas',
            'toolkit_kunci_busi'    => 'Kunci Busi',
            'toolkit_handle_towing' => 'Handle Towing',
            'toolkit_kunci_roda'    => 'Kunci Roda',
            'toolkit_dongkrak'      => 'Dongkrak',
            'segitiga_pengaman'     => 'Segitiga Pengaman',
            'kotak_p3k'             => 'Kotak P3K',
            'kunci_kontak'          => 'Kunci Kontak',
            'remot'                 => 'Remot',
            'stnk'                  => 'STNK',
            'buku_kir'              => 'Buku KIR',
            'buku_servis'           => 'Buku Servis/Pedoman',
        ];

        $rekomendasi = [];
        $skip_keys   = ['id','sesi_id','created_at','updated_at','accu_tegangan','accu_merk'];

        $tabel_keys = ['mesin','chassis','kelistrikan','kelistrikan2','safety','eksterior','perlengkapan'];

        foreach ($tabel_keys as $tabel) {
            $data = $kondisi_all[$tabel] ?? [];
            foreach ($data as $key => $val) {
                if (in_array($key, $skip_keys) || empty($val)) continue;
                $label = $label_map[$key] ?? $key;
                if (in_array($val, $kritis_list)) {
                    $rekomendasi[] = ['label' => $label, 'kondisi' => $val, 'level' => 'kritis'];
                } elseif (in_array($val, $perhatian_list)) {
                    $rekomendasi[] = ['label' => $label, 'kondisi' => $val, 'level' => 'perhatian'];
                }
            }
        }

        // Ban
        foreach ($kondisi_all['ban'] as $ban) {
            if (empty($ban['status'])) continue;
            $label = 'Ban ' . ($ban['posisi'] ?? '');
            if (in_array($ban['status'], $kritis_list)) {
                $rekomendasi[] = ['label' => $label, 'kondisi' => $ban['status'], 'level' => 'kritis'];
            } elseif (in_array($ban['status'], $perhatian_list)) {
                $rekomendasi[] = ['label' => $label, 'kondisi' => $ban['status'], 'level' => 'perhatian'];
            }
        }

        // Urutkan kritis dulu
        usort($rekomendasi, fn($a, $b) => $a['level'] === 'kritis' ? -1 : 1);

        return $rekomendasi;
    }

    // ── RIWAYAT SERVICE (TIMELINE) ─────────────────────────────
    public function get_riwayat($customer_id, $nopol, $is_pro)
    {
        $q = $this->db->select('
                i.id, i.invoice_number, i.awal_service, i.lokasi_service,
                i.km, i.sub_total, i.jenis_service,
                cab.nama_cabang
            ', FALSE)
            ->from('mac_invoice i')
            ->join('mac_cabang cab', 'cab.id = i.cabang_id', 'left')
            ->where('i.customer_id', $customer_id)
            ->where('i.nopol', $nopol)
            ->where('i.app_status', 'approved')
            ->where('i.is_active', 1)
            ->order_by('i.awal_service', 'DESC');

        if (!$is_pro) $q->limit(6);

        $invoices = $q->get()->result_array();

        // Ambil detail per invoice
        foreach ($invoices as &$inv) {
            $inv['detail'] = $this->db->select('
                    item, tipe_item, qty,
                    biaya, total, harga_beli_jasa
                ')
                ->where('invoice_id', $inv['id'])
                ->where('is_active', 1)
                ->get('mac_invoice_detail')->result_array();
        }

        return $invoices;
    }

    // ── PROFILE ────────────────────────────────────────────────
    public function get_profile($customer_id, $is_pro)
    {
        // Semua kendaraan
        $q = $this->db->select('
                i.nopol,
                i.tipe,
                i.jenis_kendaraan,
                COUNT(i.id)          AS total_service,
                MAX(i.awal_service)  AS terakhir_service
            ', FALSE)
            ->from('mac_invoice i')
            ->where('i.customer_id', $customer_id)
            ->where('i.app_status', 'approved')
            ->where('i.is_active', 1)
            ->group_by('i.nopol')
            ->order_by('terakhir_service', 'DESC');

        $kendaraan = $q->get()->result_array();

        $total_service   = array_sum(array_column($kendaraan, 'total_service'));
        $total_kendaraan = count($kendaraan);
        $terakhir_service = !empty($kendaraan) ? $kendaraan[0]['terakhir_service'] : null;

        // Cek has_kondisi per kendaraan
        foreach ($kendaraan as &$k) {
            $sesi = $this->db->select('s.id')
                ->from('mac_movas_sesi s')
                ->join('mac_invoice i', 'i.id = s.invoice_id')
                ->where('i.customer_id', $customer_id)
                ->where('s.nopol', $k['nopol'])
                ->limit(1)
                ->get()->row_array();
            $k['has_kondisi'] = !empty($sesi);
        }

        return [
            'kendaraan'       => $kendaraan,
            'total_kendaraan' => $total_kendaraan,
            'total_service'   => $total_service,
            'terakhir_service'=> $terakhir_service,
        ];
    }

    // ── GET KONDISI BY SESI ID (AJAX) ──────────────────────────
    public function get_sesi_by_id($sesi_id)
    {
        return $this->db->where('id', $sesi_id)->get('mac_movas_sesi')->row_array();
    }

    // ── CEK FEEDBACK HARI INI ──────────────────────────────────
    public function cek_feedback_hari_ini($customer_id)
    {
        return $this->db->where('customer_id', $customer_id)
            ->where('DATE(created_at)', date('Y-m-d'))
            ->count_all_results('mac_movas_feedback') > 0;
    }

    // ── SIMPAN FEEDBACK ────────────────────────────────────────
    public function save_feedback($customer_id, $rating, $pesan)
    {
        $this->db->insert('mac_movas_feedback', [
            'customer_id' => $customer_id,
            'rating'      => $rating,
            'pesan'       => $pesan,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    // ── GET FOTO PER SESI (MOVAS PUBLIC) ──────────────────────
    public function get_foto_by_sesi($sesi_id)
    {
        $fotos = $this->db->where('sesi_id', $sesi_id)
            ->order_by('nama_komponen', 'ASC')
            ->order_by('created_at', 'ASC')
            ->get('mac_movas_foto')->result_array();

        foreach ($fotos as &$f) {
            $f['url'] = MAC_ASSET_URL
                    . 'assets/backend/document/movas/foto/'
                    . $f['sesi_id'] . '/' . $f['file_name'];
        }

        return $fotos;
    }

    // MOVAS AI CHAT
    // ── GET HISTORY CHAT PER KENDARAAN ────────────────────────
    public function get_ai_chat($customer_id, $nopol, $limit = 10)
    {
        return $this->db->where('customer_id', $customer_id)
            ->where('nopol', $nopol)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get('mac_movas_ai_chat')->result_array();
    }

    // ── SIMPAN CHAT ────────────────────────────────────────────
    public function save_ai_chat($customer_id, $nopol, $role, $message)
    {
        $this->db->insert('mac_movas_ai_chat', [
            'customer_id' => $customer_id,
            'nopol'       => $nopol,
            'role'        => $role,
            'message'     => $message,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    // ── CEK DAN UPDATE QUOTA ───────────────────────────────────
    public function cek_quota($customer_id, $is_pro)
    {
        // PRO tidak terbatas
        if ($is_pro) return ['allowed' => true, 'sisa' => -1];

        $today = date('Y-m-d');
        $quota = $this->db->where('customer_id', $customer_id)
            ->where('tanggal', $today)
            ->get('mac_movas_ai_quota')->row_array();

        $jumlah = $quota['jumlah_tanya'] ?? 0;
        $limit  = 5; // FREE = 5x per hari

        if ($jumlah >= $limit) {
            return ['allowed' => false, 'sisa' => 0];
        }

        return ['allowed' => true, 'sisa' => $limit - $jumlah];
    }

    public function tambah_quota($customer_id)
    {
        $today = date('Y-m-d');
        $quota = $this->db->where('customer_id', $customer_id)
            ->where('tanggal', $today)
            ->get('mac_movas_ai_quota')->row_array();

        if ($quota) {
            $this->db->where('customer_id', $customer_id)
                ->where('tanggal', $today)
                ->update('mac_movas_ai_quota', [
                    'jumlah_tanya' => $quota['jumlah_tanya'] + 1,
                ]);
        } else {
            $this->db->insert('mac_movas_ai_quota', [
                'customer_id'  => $customer_id,
                'tanggal'      => $today,
                'jumlah_tanya' => 1,
            ]);
        }
    }

    // ── GET SEMUA KENDARAAN UNTUK KONTEKS AI ──────────────────
    public function get_konteks_ai($customer_id)
    {
        // Ambil semua kendaraan
        $kendaraan_list = $this->db->select('
                i.nopol, i.tipe, i.jenis_kendaraan,
                MAX(i.awal_service) AS terakhir_service,
                MAX(i.km)           AS km_terakhir,
                COUNT(i.id)         AS total_service
            ', FALSE)
            ->from('mac_invoice i')
            ->where('i.customer_id', $customer_id)
            ->where('i.app_status', 'approved')
            ->where('i.is_active', 1)
            ->group_by('i.nopol')
            ->order_by('terakhir_service', 'DESC')
            ->get()->result_array();

        $konteks = [];

        foreach ($kendaraan_list as $k) {
            $item = [
                'nopol'            => $k['nopol'],
                'tipe'             => $k['tipe'],
                'jenis_kendaraan'  => $k['jenis_kendaraan'],
                'km_terakhir'      => $k['km_terakhir'],
                'terakhir_service' => $k['terakhir_service'],
                'total_service'    => $k['total_service'],
                'kondisi'          => [],
                'rekomendasi'      => [],
            ];

            // Ambil sesi terbaru
            $sesi = $this->db->select('s.id')
                ->from('mac_movas_sesi s')
                ->join('mac_invoice i', 'i.id = s.invoice_id')
                ->where('i.customer_id', $customer_id)
                ->where('s.nopol', $k['nopol'])
                ->order_by('s.tgl_service', 'DESC')
                ->limit(1)
                ->get()->row_array();

            if ($sesi) {
                $kondisi_all = $this->get_kondisi_by_sesi($sesi['id']);
                $rekomendasi = $this->get_rekomendasi($customer_id, $k['nopol']);

                // Rangkum kondisi yang tidak baik saja
                $kondisi_negatif = [];
                $skip = ['id','sesi_id','created_at','updated_at','accu_tegangan','accu_merk'];
                $positif = ['Baik','Bersih','Ada','Cukup','Hidup','Berfungsi','Dingin','Selesai'];

                foreach ($kondisi_all as $tabel => $data) {
                    if ($tabel === 'ban' || !is_array($data)) continue;
                    foreach ($data as $key => $val) {
                        if (in_array($key, $skip) || empty($val)) continue;
                        if (!in_array($val, $positif)) {
                            $kondisi_negatif[] = $key . ': ' . $val;
                        }
                    }
                }

                // Ban
                foreach ($kondisi_all['ban'] ?? [] as $ban) {
                    if (!empty($ban['status']) && $ban['status'] !== 'Baik') {
                        $kondisi_negatif[] = 'Ban ' . $ban['posisi'] . ': ' . $ban['status'];
                    }
                }

                $item['kondisi']     = $kondisi_negatif;
                $item['rekomendasi'] = array_map(fn($r) => $r['label'] . ' (' . $r['kondisi'] . ')', $rekomendasi);
            }

            $konteks[] = $item;
        }

        return $konteks;
    }
}