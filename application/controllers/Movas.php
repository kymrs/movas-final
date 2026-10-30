<?php defined('BASEPATH') or exit('No direct script access allowed');

class Movas extends CI_Controller
{
    protected $customer    = [];
    protected $is_pro      = false;
    protected $customer_id = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('backend/M_movas');
        $this->load->model('backend/M_movas_booking');
        date_default_timezone_set('Asia/Jakarta');
    }

    // ── VALIDASI TOKEN (dipanggil di setiap method) ────────────
    private function _auth($token)
    {
        $customer = $this->M_movas->get_customer_by_token($token);
        if (empty($customer)) {
            $this->_show_error('Akses Tidak Valid', '...');
            return false;
        }

        // ── Cek expired PRO customer ───────────────────────────
        if ($customer['movas_is_pro'] && !empty($customer['movas_pro_expired_at'])) {
            if (strtotime($customer['movas_pro_expired_at']) < time()) {
                // Expired — set FREE
                $this->db->where('id', $customer['id'])->update('mac_customer', [
                    'movas_is_pro' => 0,
                ]);
                // Nonaktifkan semua unit PRO
                $this->db->where('customer_id', $customer['id'])
                    ->update('mac_movas_pro_unit', ['is_pro' => 0]);
                $customer['movas_is_pro'] = 0;
            }
        }

        $this->customer    = $customer;
        $this->customer_id = $customer['id'];
        $this->is_pro      = (bool) $customer['movas_is_pro'];

        // Hapus teks dalam kurung
        $this->customer['customer_name'] = trim(
            preg_replace('/\(.*?\)/', '', $customer['customer_name'])
        );

        // ── Tentukan nama sapa ─────────────────────────────────
        $nama_sapa = $this->customer['customer_name'];
        if (strtolower($customer['type_customer']) === 'perusahaan') {
            $invoice_pic = $this->db->select('pic')
                ->where('customer_id', $customer['id'])
                ->where('app_status', 'approved')
                ->where('is_active', 1)
                ->order_by('created_at', 'DESC')
                ->limit(1)
                ->get('mac_invoice')->row_array();

            if (!empty($invoice_pic['pic'])) {
                $nama_sapa = trim(preg_replace('/\(.*?\)/', '', $invoice_pic['pic']));
            }
        }

        $this->customer['nama_sapa'] = $nama_sapa;
        return true;
    }

    // ── HALAMAN ERROR ──────────────────────────────────────────
    private function _show_error($title, $message)
    {
        $data['title']   = $title;
        $data['message'] = $message;
        $this->load->view('movas/error', $data);
    }

    // ── HELPER: format tanggal Indonesia ──────────────────────
    private function _tgl_indo($date)
    {
        if (empty($date)) return '—';
        $bulan = [
            '01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr',
            '05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu',
            '09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des',
        ];
        $d = date('d', strtotime($date));
        $m = $bulan[date('m', strtotime($date))];
        $y = date('Y', strtotime($date));
        return "$d $m $y";
    }

    // ══════════════════════════════════════════════════════════
    // HALAMAN 1 — VEHICLE LIST
    // URL: movas/{token}
    // ══════════════════════════════════════════════════════════
    public function index($token = null)
    {
        if (!$token || !$this->_auth($token)) return;

        $kendaraan_list = $this->M_movas->get_kendaraan_list(
            $this->customer_id, $this->is_pro
        );

        // ── Ambil PIC dari invoice terbaru jika perusahaan ──
        $nama_sapa = $this->customer['customer_name'];
        if (strtolower($this->customer['type_customer']) === 'perusahaan') {
            $invoice_pic = $this->db->select('pic')
                ->where('customer_id', $this->customer_id)
                ->where('app_status', 'approved')
                ->where('is_active', 1)
                ->order_by('created_at', 'DESC')
                ->limit(1)
                ->get('mac_invoice')->row_array();

            if (!empty($invoice_pic['pic'])) {
                $nama_sapa = $invoice_pic['pic'];
            }
        }

        $nama_sapa = trim(preg_replace('/\(.*?\)/', '', $nama_sapa));

        // ── Salam berdasarkan jam ──────────────────────────
        $jam = (int) date('H');
        if ($jam >= 3 && $jam < 11) {
            $salam = 'Selamat Pagi';
        } elseif ($jam >= 11 && $jam < 15) {
            $salam = 'Selamat Siang';
        } elseif ($jam >= 15 && $jam < 19) {
            $salam = 'Selamat Sore';
        } else {
            $salam = 'Selamat Malam';
        }

        $data['customer']       = $this->customer;
        $data['is_pro']         = $this->is_pro;
        $data['token']          = $token;
        foreach ($kendaraan_list as &$k) {
            $reminder = $this->M_movas->get_reminder_kendaraan(
                $this->customer_id, $k['nopol']
            );
            $k['reminder'] = $reminder;

            // PRO unit hanya berlaku jika customer juga PRO
            if (!$this->is_pro) $k['is_pro_unit'] = false;
        }

        $data['kendaraan_list'] = $kendaraan_list;
        $data['nama_sapa']      = $nama_sapa;
        $data['salam']          = $salam;
        $data['page']           = 'vehicle_list';

        $this->load->view('movas/layout', $data);
    }

    // ══════════════════════════════════════════════════════════
    // HALAMAN 2 — DETAIL KENDARAAN
    // URL: movas/{token}/kendaraan/{nopol}
    // ══════════════════════════════════════════════════════════
    public function kendaraan($token = null, $nopol_encoded = null)
    {
        if (!$token || !$this->_auth($token)) return;

        $nopol = str_replace('-', ' ', $nopol_encoded);

        // Validasi nopol milik customer ini
        $kendaraan = $this->M_movas->get_kendaraan_detail(
            $this->customer_id, $nopol
        );

        if (empty($kendaraan)) {
            $this->_show_error(
                'Kendaraan Tidak Ditemukan',
                'Data kendaraan dengan nopol ' . $nopol . ' tidak ditemukan.'
            );
            return;
        }

        // ── Cek PRO unit ───────────────────────────────────────
        $pro_unit    = $this->db->where('customer_id', $this->customer_id)
            ->where('nopol', $nopol)
            ->where('is_pro', 1)
            ->get('mac_movas_pro_unit')->row_array();

        $is_pro_unit = false;
        if (!empty($pro_unit)) {
            if (!empty($pro_unit['expired_at']) && strtotime($pro_unit['expired_at']) < time()) {
                // Expired — nonaktifkan
                $this->db->where('customer_id', $this->customer_id)
                    ->where('nopol', $nopol)
                    ->update('mac_movas_pro_unit', ['is_pro' => 0]);
            } else {
                $is_pro_unit = true;
            }
        }

        // PRO unit hanya berlaku jika customer juga PRO
        if (!$this->is_pro) $is_pro_unit = false;

        // ── Chip tanggal service ───────────────────────────────
        $chip_tanggal_all = $this->M_movas->get_chip_tanggal(
            $this->customer_id, $nopol, $this->is_pro
        );

        // FREE unit → hanya 1 tanggal terakhir
        $chip_tanggal = $is_pro_unit
            ? $chip_tanggal_all
            : array_slice($chip_tanggal_all, 0, 1);

        // ── Kondisi terbaru ────────────────────────────────────────
        $kondisi    = [];
        $sesi_aktif = null;
        if (!empty($chip_tanggal)) {
            $sesi_aktif = $chip_tanggal[0];
            $raw_kondisi = $this->M_movas->get_kondisi_by_sesi($sesi_aktif['sesi_id']);

            // ── Filter field kosong ────────────────────────────────
            $skip_fields = ['id', 'sesi_id', 'created_at', 'updated_at'];

            foreach ($raw_kondisi as $key => &$val) {
                if ($key === 'ban') {
                    // Ban adalah array — filter per item
                    $val = array_filter($val, function($ban) use ($skip_fields) {
                        foreach ($ban as $field => $v) {
                            if (in_array($field, $skip_fields)) continue;
                            if (!empty($v)) return true;
                        }
                        return false;
                    });
                    $val = array_values($val);
                } else {
                    // Non-ban — cek apakah ada field yang terisi
                    if (!empty($val)) {
                        $has_data = false;
                        foreach ($val as $field => $v) {
                            if (in_array($field, $skip_fields)) continue;
                            if (!empty($v)) { $has_data = true; break; }
                        }
                        // Jika semua kosong — set empty array
                        if (!$has_data) $val = [];
                    }
                }
            }

            $kondisi = $raw_kondisi;
        }

        // ── Rekomendasi ────────────────────────────────────────
        $rekomendasi = $this->M_movas->get_rekomendasi(
            $this->customer_id, $nopol
        );

        // ── Riwayat service ────────────────────────────────────
        $riwayat_all   = $this->M_movas->get_riwayat(
            $this->customer_id, $nopol, TRUE // ambil semua dulu
        );
        $total_riwayat = count($riwayat_all);

        // FREE unit → hanya 1 terakhir
        $riwayat = $is_pro_unit
            ? $riwayat_all
            : array_slice($riwayat_all, 0, 1);

        // ── Foto kondisi — hanya PRO unit ─────────────────────
        $foto_sesi = [];
        if ($sesi_aktif && $is_pro_unit) {
            $foto_sesi = $this->M_movas->get_foto_by_sesi($sesi_aktif['sesi_id']);
        }

        // ── Reminder ───────────────────────────────────────────
        $reminder_kendaraan = $this->M_movas->get_reminder_kendaraan(
            $this->customer_id, $nopol
        );

        $data['customer']          = $this->customer;
        $data['is_pro']            = $this->is_pro;
        $data['is_pro_unit']       = $is_pro_unit; // ← tambah
        $data['token']             = $token;
        $data['kendaraan']         = $kendaraan;
        $data['nopol']             = $nopol;
        if (!$is_pro_unit && !empty($chip_tanggal)) {
            $chip_tanggal = array_slice($chip_tanggal, 0, 1);
        }
        $data['chip_tanggal'] = $chip_tanggal;
        $data['chip_tanggal_all']  = $chip_tanggal_all; // ← untuk hitung total
        $data['sesi_aktif']        = $sesi_aktif;
        $data['kondisi']           = $kondisi;
        $data['rekomendasi']       = $rekomendasi;

        // Ambil semua riwayat dulu untuk hitung total
        $riwayat_all   = $this->M_movas->get_riwayat($this->customer_id, $nopol);
        $total_riwayat = count($riwayat_all);

        // FREE → hanya 1 terakhir
        $riwayat = $is_pro_unit ? $riwayat_all : array_slice($riwayat_all, 0, 1);

        $data['riwayat']       = $riwayat;

        $data['total_riwayat'] = $total_riwayat;
        $data['total_riwayat']     = $total_riwayat; // ← untuk blur banner
        $data['foto_sesi']         = $foto_sesi;
        $data['reminder_kendaraan']= $reminder_kendaraan;
        $data['nama_sapa']         = $this->customer['nama_sapa'];
        $data['page']              = 'kendaraan_detail';

        $this->load->view('movas/layout', $data);
    }

    // Menu List Kendaraan
    public function kendaraan_list($token = null)
    {
        if (!$token || !$this->_auth($token)) return;

        $profile = $this->M_movas->get_profile($this->customer_id, $this->is_pro);

        // ── Tambah reminder + is_pro_unit per kendaraan ────────
        foreach ($profile['kendaraan'] as &$k) {
            $k['reminder'] = $this->M_movas->get_reminder_kendaraan(
                $this->customer_id, $k['nopol']
            );

            // ── Cek PRO unit ───────────────────────────────────
            $pro_unit = $this->db->where('customer_id', $this->customer_id)
                ->where('nopol', $k['nopol'])
                ->where('is_pro', 1)
                ->get('mac_movas_pro_unit')->row_array();

            $is_pro_unit = false;
            if (!empty($pro_unit)) {
                if (!empty($pro_unit['expired_at']) && strtotime($pro_unit['expired_at']) < time()) {
                    $is_pro_unit = false;
                } else {
                    $is_pro_unit = true;
                }
            }

            // PRO unit hanya berlaku jika customer juga PRO
            $k['is_pro_unit'] = $this->is_pro ? $is_pro_unit : false;
        }

        $data['customer']  = $this->customer;
        $data['is_pro']    = $this->is_pro;
        $data['token']     = $token;
        $data['profile']   = $profile;
        $data['nama_sapa'] = $this->customer['nama_sapa'];
        $data['page']      = 'kendaraan_list';

        $this->load->view('movas/layout', $data);
    }

    // ══════════════════════════════════════════════════════════
    // AJAX — SWITCH KONDISI PER CHIP TANGGAL
    // ══════════════════════════════════════════════════════════
    public function get_kondisi_ajax()
    {
        $token   = $this->input->post('token');
        $sesi_id = intval($this->input->post('sesi_id'));

        if (!$token || !$this->_auth($token)) {
            echo json_encode(['status' => FALSE, 'error' => 'Akses ditolak.']);
            return;
        }

        // Validasi sesi milik customer ini
        $sesi = $this->M_movas->get_sesi_by_id($sesi_id);
        if (empty($sesi)) {
            echo json_encode(['status' => FALSE, 'error' => 'Sesi tidak ditemukan.']);
            return;
        }

        // Cek invoice milik customer
        $invoice = $this->db->select('customer_id')
            ->where('id', $sesi['invoice_id'])
            ->get('mac_invoice')->row_array();

        if (empty($invoice) || $invoice['customer_id'] != $this->customer_id) {
            echo json_encode(['status' => FALSE, 'error' => 'Akses ditolak.']);
            return;
        }

        $kondisi = $this->M_movas->get_kondisi_by_sesi($sesi_id);

        $foto_sesi = [];
        if ($this->is_pro) {
            $foto_sesi = $this->M_movas->get_foto_by_sesi($sesi_id);
        }

        echo json_encode([
            'status'    => TRUE,
            'kondisi'   => $kondisi,
            'sesi'      => $sesi,
            'catatan'   => $invoice['catatan'] ?? '',
            'foto_sesi' => $foto_sesi, // ← tambah
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // HALAMAN 3 — PROFILE
    // URL: movas/{token}/profile
    // ══════════════════════════════════════════════════════════
    public function profile($token = null)
    {
        if (!$token || !$this->_auth($token)) return;

        $profile = $this->M_movas->get_profile(
            $this->customer_id, $this->is_pro
        );

        $data['customer'] = $this->customer;
        $data['is_pro']   = $this->is_pro;
        $data['token']    = $token;
        $data['profile']  = $profile;
        $data['nama_sapa'] = $this->customer['nama_sapa'];
        $data['page']     = 'profile';

        $this->load->view('movas/layout', $data);
    }

    // ══════════════════════════════════════════════════════════
    // DOWNLOAD PDF — SEMUA RIWAYAT
    // URL: movas/{token}/download/{nopol}
    // ══════════════════════════════════════════════════════════
    public function download($token = null, $nopol_encoded = null)
    {
        if (!$token || !$this->_auth($token)) return;

        // ── SEBELUM
        $nopol = str_replace('-', ' ', $nopol_encoded);

        // ── SESUDAH — coba kedua cara decode
        $nopol = urldecode($nopol_encoded);
        $nopol = str_replace('-', ' ', $nopol);

        $riwayat   = $this->M_movas->get_riwayat($this->customer_id, $nopol, $this->is_pro);
        $kendaraan = $this->M_movas->get_kendaraan_detail($this->customer_id, $nopol);

        // ── Tambah debug sementara
        if (empty($kendaraan)) {
            echo 'kendaraan kosong, nopol: ' . $nopol;
            die;
        }

        $data['customer']  = $this->customer;
        $data['kendaraan'] = $kendaraan;
        $data['riwayat']   = $riwayat;
        $data['nopol']     = $nopol;
        $data['is_pro']    = $this->is_pro;

        // Untuk mPDF
        require APPPATH . '../vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf([
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'margin_left'   => 15,
            'margin_right'  => 15,
        ]);

        $html = $this->load->view('movas/pdf_riwayat', $data, TRUE);
        $mpdf->WriteHTML($html);

        $filename = 'MOVAS_Riwayat_' . str_replace(' ', '_', $nopol) . '_' . date('Ymd') . '.pdf';
        $mpdf->Output($filename, 'D');
        exit;
    }

    // ══════════════════════════════════════════════════════════
    // DOWNLOAD PDF — PER INVOICE
    // URL: movas/{token}/download_invoice/{invoice_id}
    // ══════════════════════════════════════════════════════════
    public function download_invoice($token = null, $invoice_id = null)
    {
        if (!$token || !$this->_auth($token)) return;

        $invoice_id = intval($invoice_id);

        // Validasi invoice milik customer
        $invoice = $this->db->select('
                i.id, i.invoice_number, i.nopol,
                i.awal_service, i.km, i.sub_total,
                i.jenis_service, cab.nama_cabang
            ', FALSE)
            ->from('mac_invoice i')
            ->join('mac_cabang cab', 'cab.id = i.cabang_id', 'left')
            ->where('i.id', $invoice_id)
            ->where('i.customer_id', $this->customer_id)
            ->where('i.app_status', 'approved')
            ->get()->row_array();

        if (empty($invoice)) {
            $this->_show_error('Invoice Tidak Ditemukan', 'Data invoice tidak ditemukan.');
            return;
        }

        $detail = $this->db->select('item, tipe_item, qty, biaya, total')
            ->where('invoice_id', $invoice_id)
            ->where('is_active', 1)
            ->get('mac_invoice_detail')->result_array();

        require APPPATH . '../vendor/autoload.php';

        $mpdf = new \Mpdf\Mpdf([
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'margin_left'   => 15,
            'margin_right'  => 15,
        ]);

        $html = $this->load->view('movas/pdf_invoice', [
            'customer' => $this->customer,
            'invoice'  => $invoice,
            'detail'   => $detail,
        ], TRUE);

        $mpdf->WriteHTML($html);

        $filename = 'MOVAS_Invoice_' . $invoice['invoice_number'] . '_' . date('Ymd') . '.pdf';
        $mpdf->Output($filename, 'D');
        exit;
    }

    // ── SAVE FEEDBACK ──────────────────────────────────────────
    public function save_feedback()
    {
        $token = $this->input->post('token');
        if (!$token || !$this->_auth($token)) {
            echo json_encode(['status' => FALSE, 'error' => 'Akses ditolak.']);
            return;
        }

        $rating = intval($this->input->post('rating'));
        $pesan  = trim($this->input->post('pesan'));

        if ($rating < 1 || $rating > 5) {
            echo json_encode(['status' => FALSE, 'error' => 'Rating tidak valid.']);
            return;
        }

        // Cek apakah sudah kirim feedback hari ini
        if ($this->M_movas->cek_feedback_hari_ini($this->customer_id)) {
            echo json_encode([
                'status'  => FALSE,
                'error'   => 'Anda sudah mengirim masukan hari ini. Silakan coba lagi besok.'
            ]);
            return;
        }

        $this->M_movas->save_feedback($this->customer_id, $rating, $pesan);

        echo json_encode([
            'status'  => TRUE,
            'message' => 'Terima kasih atas masukan Anda!'
        ]);
    }

    // ── HALAMAN BOOKING ────────────────────────────────────────
    public function booking($token = null)
    {
        if (!$token || !$this->_auth($token)) return;

        // Ambil semua kendaraan customer
        $kendaraan_list = $this->M_movas->get_kendaraan_list(
            $this->customer_id, $this->is_pro
        );

        // Ambil booking aktif
        $booking_aktif = $this->M_movas_booking->get_booking_aktif($this->customer_id);
        foreach ($booking_aktif as &$b) {
            $b['detail'] = $this->M_movas_booking->get_detail_booking($b['id']);
        }

        // Ambil riwayat booking
        $riwayat_booking = $this->M_movas_booking->get_riwayat_booking($this->customer_id);
        foreach ($riwayat_booking as &$b) {
            $b['detail'] = $this->M_movas_booking->get_detail_booking($b['id']);
        }

        // Ganti dengan hardcode alamat service
        $cabang_list = [
            ['id' => 2,  'nama' => 'Yogyakarta',    'alamat' => 'Jl. Prambanan Manisrenggo Km 2 Dengok, Bogisan Prambanan Klaten'],
            ['id' => 3,  'nama' => 'Gresik',        'alamat' => 'Jl. Beton 1 no. 18 Perum Pongangan Indah, Gresik'],
            ['id' => 4,  'nama' => 'Jabodetabek',   'alamat' => 'Kp. Empu No.1, RT.001/RW.007, Setu Sari, Kec. Cileungsi, Kabupaten Bogor'],
            ['id' => 5,  'nama' => 'Bandung',       'alamat' => 'Jl. Kol. Ahmad Syam No.235 RT 04/06 Desa Sayang Kecamatan Jatinangor'],
            ['id' => 6, 'nama' => 'Pekanbaru',     'alamat' => 'Jl. Lobak - Jl. Sawi No. 73 RT 01 RW IV Kel. Delima Kec. Tampan Pekanbaru, Riau'],
            ['id' => 7,  'nama' => 'Lampung',       'alamat' => 'Jl. Pulau Damar, Way Dadi Baru Kec. Sukarame Bandar Lampung'],
            ['id' => 8,  'nama' => 'Serang',        'alamat' => 'Komp. Pasir Indah Block C No. 77, Kaligandu, Kec. Serang Kota Serang'],
            ['id' => 9, 'nama' => 'Palembang',     'alamat' => 'Jln. Talang Jambe No. 92 RT. 16 RW 04 Kel. Talang Jambe Kec. Sukarami Palembang'],
            ['id' => 10, 'nama' => 'Lubuk Linggau', 'alamat' => 'Jl. Ibadah RT.01 Kec. Lubuk Linggau Timur 1 Watervang Lubuk Linggau, Sumatera Selatan'],
            ['id' => 11, 'nama' => 'Bali',          'alamat' => 'Jl. Cargo Taman I No. 2 Ubung Kaja, Kec. Denpasar Utara, Kota Denpasar, Bali'],
            ['id' => 12,  'nama' => 'Kediri',        'alamat' => 'Jl. Cibaling RT 003 RW 01 Darungan, Pare Kabupaten Kediri'],
        ];

        // Ambil cabang default dari invoice terakhir
        $invoice_terakhir = $this->db->select('cabang_id')
            ->where('customer_id', $this->customer_id)
            ->where('app_status', 'approved')
            ->where('is_active', 1)
            ->order_by('created_at', 'DESC')
            ->limit(1)
            ->get('mac_invoice')->row_array();

        $cabang_default_id = $invoice_terakhir['cabang_id'] ?? null;

        // Ambil cabang default dari invoice terakhir
        $invoice_terakhir = $this->db->select('cabang_id')
            ->where('customer_id', $this->customer_id)
            ->where('app_status', 'approved')
            ->where('is_active', 1)
            ->order_by('created_at', 'DESC')
            ->limit(1)
            ->get('mac_invoice')->row_array();

        $cabang_default = $invoice_terakhir['cabang_id'] ?? null;

        // Ambil alamat default dari mac_customer
        $customer_address = $this->customer['address'] ?? '';

        $data['customer']         = $this->customer;
        $data['is_pro']           = $this->is_pro;
        $data['token']            = $token;
        $data['kendaraan_list']   = $kendaraan_list;
        $data['booking_aktif']    = $booking_aktif;
        $data['riwayat_booking']  = $riwayat_booking;
        $data['cabang_list']      = $cabang_list;
        $data['cabang_default_id'] = $cabang_default_id;
        $data['cabang_default']   = $cabang_default;
        $data['customer_address'] = $customer_address;
        $data['nama_sapa']        = $this->customer['nama_sapa'];
        $data['page']             = 'booking';

        $this->load->view('movas/layout', $data);
    }

    // ── SUBMIT BOOKING ─────────────────────────────────────────
    public function booking_submit($token = null)
    {
        if (!$token || !$this->_auth($token)) {
            echo json_encode(['status' => FALSE, 'error' => 'Akses ditolak.']);
            return;
        }

        $nama_cabang    = $this->input->post('nama_cabang');
        $tgl_booking    = $this->input->post('tgl_booking');
        $waktu_catatan  = $this->input->post('waktu_catatan');
        $lokasi_service = $this->input->post('lokasi_service');
        $alamat_service = $this->input->post('alamat_service');
        $catatan        = $this->input->post('catatan');
        $kendaraan      = $this->input->post('kendaraan'); // array

        // Validasi
        if (!$nama_cabang || !$tgl_booking || empty($kendaraan)) {
            echo json_encode(['status' => FALSE, 'error' => 'Data tidak lengkap.']);
            return;
        }

        if ($lokasi_service === 'home_service' && empty($alamat_service)) {
            echo json_encode(['status' => FALSE, 'error' => 'Alamat service wajib diisi untuk Home Service.']);
            return;
        }

        // Cek booking aktif per nopol
        $nopol_konflik = [];
        foreach ($kendaraan as $k) {
            $cek = $this->M_movas_booking->cek_booking_aktif_nopol(
                $this->customer_id, $k['nopol']
            );
            if ($cek) $nopol_konflik[] = $k['nopol'];
        }

        if (!empty($nopol_konflik)) {
            echo json_encode([
                'status' => FALSE,
                'error'  => 'Kendaraan berikut sudah memiliki booking aktif: '
                        . implode(', ', $nopol_konflik)
                        . '. Selesaikan atau batalkan booking sebelumnya.',
            ]);
            return;
        }

        // ── Validasi nopol unit baru — cek apakah sudah ada di invoice ──
        foreach ($kendaraan as $k) {
            if (!empty($k['is_new_unit']) && intval($k['is_new_unit']) === 1) {
                $cek = $this->db->select('id')
                    ->where('customer_id', $this->customer_id)
                    ->where('nopol', strtoupper($k['nopol']))
                    ->where('app_status', 'approved')
                    ->where('is_active', 1)
                    ->limit(1)
                    ->get('mac_invoice')->row_array();

                if (!empty($cek)) {
                    echo json_encode([
                        'status' => FALSE,
                        'error'  => 'Kendaraan ' . strtoupper($k['nopol']) . ' sudah terdaftar di sistem. Silakan pilih dari daftar kendaraan yang tersedia.',
                    ]);
                    return;
                }
            }
        }

        // Simpan booking
        $booking_id = $this->M_movas_booking->save_booking([
            'customer_id'    => $this->customer_id,
            'nama_cabang'    => $nama_cabang,
            'tgl_booking'    => $tgl_booking,
            'waktu_catatan'  => $waktu_catatan ?: null,
            'lokasi_service' => $lokasi_service,
            'alamat_service' => $lokasi_service === 'home_service' ? $alamat_service : null,
            'catatan'        => $catatan ?: null,
            'status'         => 'pending',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        // ── Ambil tipe & jenis untuk unit existing ─────────────────
        foreach ($kendaraan as &$k) {
            if (empty($k['is_new_unit'])) {
                $last = $this->db->select('tipe, jenis_kendaraan')
                    ->where('nopol', $k['nopol'])
                    ->where('customer_id', $this->customer_id)
                    ->where('app_status', 'approved')
                    ->order_by('created_at', 'DESC')
                    ->limit(1)
                    ->get('mac_invoice')->row_array();

                $k['tipe']            = $last['tipe'] ?? null;
                $k['jenis_kendaraan'] = $last['jenis_kendaraan'] ?? null;
            }
        }
        unset($k);

        // Simpan detail kendaraan
        $this->M_movas_booking->save_booking_detail($booking_id, $kendaraan);

        // Kirim WA ke admin via Fonnte
        $this->_kirim_wa_booking_admin($booking_id);

        echo json_encode([
            'status'  => TRUE,
            'message' => 'Booking service berhasil dikirim! Tim MAC akan segera menghubungi Anda.',
        ]);
    }

    // ── CANCEL BOOKING ─────────────────────────────────────────
    public function booking_cancel($token = null, $booking_id = null)
    {
        if (!$token || !$this->_auth($token)) {
            echo json_encode(['status' => FALSE, 'error' => 'Akses ditolak.']);
            return;
        }

        $booking_id = intval($booking_id);
        $result     = $this->M_movas_booking->cancel_booking($booking_id, $this->customer_id);

        if ($result) {
            echo json_encode(['status' => TRUE, 'message' => 'Booking berhasil dibatalkan.']);
        } else {
            echo json_encode(['status' => FALSE, 'error' => 'Booking tidak dapat dibatalkan.']);
        }
    }

    // ── HELPER: DAFTAR NOMOR WA PENERIMA BOOKING ──────────────
    private function _target_wa_booking($nama_cabang)
    {
        // Nomor pusat — selalu menerima semua booking
        $target = ['082324001500'];

        // PIC per cabang
        $pic_cabang = [
            'jabodetabek'   => '081213325396', // Sopandi
            'bandung'       => '082324007900', // Priyadi
            'yogyakarta'    => '089528305506', // Anggoro
            'gresik'        => '081331200036', // Aang
            'kediri'        => '081329542062', // Saryanto
            'serang'        => '0895347999347', // Fatkhur
            'lampung'       => '087779296081', // Agung
            'lubuk linggau' => '082176012026', // Hermawanta
            'palembang'     => '085889991043', // Anton
            'pekanbaru'     => '08126876113',  // Angga
            'bali'          => '081286355557', // Eko
        ];

        $key = strtolower(trim(str_replace('MAC ', '', (string) $nama_cabang)));

        if (isset($pic_cabang[$key])) {
            $target[] = $pic_cabang[$key];
        }

        // Hindari nomor ganda
        return array_values(array_unique($target));
    }

    // ── HELPER: KIRIM WA KE ADMIN ─────────────────────────────
    private function _kirim_wa_booking_admin($booking_id)
    {
        $booking = $this->M_movas_booking->get_booking_by_id($booking_id);
        $detail  = $this->M_movas_booking->get_detail_booking($booking_id);

        if (empty($booking)) return;

        // Nama customer
        if (strtolower($booking['type_customer']) === 'perusahaan' && !empty($booking['pic'])) {
            $nama = trim(preg_replace('/\(.*?\)/', '', $booking['pic']));
        } else {
            $nama = trim(preg_replace('/\(.*?\)/', '', $booking['customer_name']));
        }

        // Format tanggal
        $bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
        $tgl = date('d', strtotime($booking['tgl_booking'])) . ' '
            . $bulan[date('m', strtotime($booking['tgl_booking']))] . ' '
            . date('Y', strtotime($booking['tgl_booking']));

        // Lokasi
        $lokasi = $booking['lokasi_service'] === 'home_service'
            ? 'Home Service — ' . $booking['alamat_service']
            : 'Ke Bengkel';

        // Cabang
        $nama_cabang = str_replace('MAC ', '', $booking['nama_cabang'] ?? 'MAC');

        // Detail kendaraan
        $list_kendaraan = '';
        foreach ($detail as $idx => $d) {
            $no = $idx + 1;
            $list_kendaraan .= "{$no}. *{$d['nopol']}*";
            if (!empty($d['tipe']))    $list_kendaraan .= " — {$d['tipe']}";
            $list_kendaraan .= "\n";
            if (!empty($d['km']))      $list_kendaraan .= "      KM          : " . number_format($d['km'], 0, ',', '.') . "\n";
            if (!empty($d['keluhan'])) $list_kendaraan .= "      Keluhan  : {$d['keluhan']}\n";
            $list_kendaraan .= "\n";
        }

        $pesan  = "🔔 *Booking Service Baru!*\n\n";
        $pesan .= "Ada permintaan booking service masuk dari MOVAS.\n\n";
        $pesan .= "*• Detail Customer*\n";

        if (strtolower($booking['type_customer']) === 'perusahaan') {
            $nama = trim(preg_replace('/\(.*?\)/', '', $booking['customer_name']));
            $pic  = !empty($booking['pic']) ? trim(preg_replace('/\(.*?\)/', '', $booking['pic'])) : '-';
            $pesan .= "🏢 Customer    : {$nama}\n";
            $pesan .= "👤 PIC               : {$pic}\n";
        } else {
            $nama = trim(preg_replace('/\(.*?\)/', '', $booking['customer_name']));
            $pesan .= "👤 Nama           : {$nama}\n";
        }

        $pesan .= "📱 No. Telp       : {$booking['no_telp']}\n\n";

        $pesan .= "*• Detail Booking*\n";
        $pesan .= "📅 Tanggal    : {$tgl}\n";
        if (!empty($booking['waktu_catatan'])) {
            $pesan .= "⏰ Waktu       : {$booking['waktu_catatan']}\n";
        }
        $pesan .= "📍 Lokasi      : {$lokasi}\n";
        $pesan .= "🏠 Cabang    : {$nama_cabang}\n";
        if (!empty($booking['catatan'])) {
            $pesan .= "📝 Catatan    : {$booking['catatan']}\n\n";
        }
        $pesan .= "*• Kendaraan yang akan di-service*\n";
        $pesan .= $list_kendaraan;
        $pesan .= "⚡ Segera konfirmasi melalui sistem MAC.\n\n";
        $mac_url = 'https://app.mobileautocare.co.id/';
        $pesan .= "🔗 Detail Booking :\n      {$mac_url}mac_booking/detail/{$booking_id}\n\n";
        $pesan .= "*MOVAS — Mobile Vehicle Assistant*";

        // ── Daftar nomor admin penerima ────────────────────────
        $target_admin = $this->_target_wa_booking($booking['nama_cabang'] ?? '');

        // Kirim via Fonnte (beberapa nomor dipisah koma)
        $fonnte_token = $this->config->item('fonnte_token');
        $ch = curl_init('https://api.fonnte.com/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'target'  => implode(',', $target_admin),
            'message' => $pesan,
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $fonnte_token]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }

    // ── BOOKING PUBLIK ─────────────────────────────────────────
    public function booking_publik()
    {
        // Ambil semua cabang
        $cabang_list = [
            ['id' => 4,  'nama' => 'Jabodetabek',   'alamat' => 'Kp. Empu No.1, RT.001/RW.007, Setu Sari, Kec. Cileungsi, Kabupaten Bogor'],
            ['id' => 2,  'nama' => 'Yogyakarta',    'alamat' => 'Jl. Prambanan Manisrenggo Km 2 Dengok, Bogisan Prambanan Klaten'],
            ['id' => 3,  'nama' => 'Gresik',        'alamat' => 'Jl. Beton 1 no. 18 Perum Pongangan Indah, Gresik'],
            ['id' => 5,  'nama' => 'Bandung',       'alamat' => 'Jl. Kol. Ahmad Syam No.235 RT 04/06 Desa Sayang Kecamatan Jatinangor'],
            ['id' => 6,  'nama' => 'Pekanbaru',     'alamat' => 'Jl. Lobak - Jl. Sawi No. 73 RT 01 RW IV Kel. Delima Kec. Tampan Pekanbaru, Riau'],
            ['id' => 7,  'nama' => 'Lampung',       'alamat' => 'Jl. Pulau Damar, Way Dadi Baru Kec. Sukarame Bandar Lampung'],
            ['id' => 8,  'nama' => 'Serang',        'alamat' => 'Komp. Pasir Indah Block C No. 77, Kaligandu, Kec. Serang Kota Serang'],
            ['id' => 9,  'nama' => 'Palembang',     'alamat' => 'Jln. Talang Jambe No. 92 RT. 16 RW 04 Kel. Talang Jambe Kec. Sukarami Palembang'],
            ['id' => 10, 'nama' => 'Lubuk Linggau', 'alamat' => 'Jl. Ibadah RT.01 Kec. Lubuk Linggau Timur 1 Watervang Lubuk Linggau, Sumatera Selatan'],
            ['id' => 11, 'nama' => 'Bali',          'alamat' => 'Jl. Cargo Taman I No. 2 Ubung Kaja, Kec. Denpasar Utara, Kota Denpasar, Bali'],
            ['id' => 12, 'nama' => 'Kediri',        'alamat' => 'Jl. Cibaling RT 003 RW 01 Darungan, Pare Kabupaten Kediri'],
        ];

        $data['cabang_list'] = $cabang_list;
        $data['page']        = 'booking_publik';
        $this->load->view('movas/booking_publik', $data);
    }

    // ── SUBMIT BOOKING PUBLIK ──────────────────────────────────
    public function booking_publik_submit()
    {
        $nama_publik    = ucwords(strtolower(trim($this->input->post('nama_publik'))));
        $no_telp_publik = trim($this->input->post('no_telp_publik'));
        $tgl_booking    = $this->input->post('tgl_booking');
        $waktu_catatan  = $this->input->post('waktu_catatan');
        $lokasi_service = $this->input->post('lokasi_service');
        $alamat_service = $this->input->post('alamat_service');
        $nama_cabang    = $this->input->post('nama_cabang');
        $catatan        = $this->input->post('catatan');
        $kendaraan      = $this->input->post('kendaraan');

        // Validasi
        if (empty($nama_publik)) {
            echo json_encode(['status' => FALSE, 'error' => 'Nama wajib diisi.']);
            return;
        }
        if (empty($no_telp_publik)) {
            echo json_encode(['status' => FALSE, 'error' => 'No. Telepon wajib diisi.']);
            return;
        }
        if (empty($tgl_booking)) {
            echo json_encode(['status' => FALSE, 'error' => 'Tanggal service wajib dipilih.']);
            return;
        }
        if (empty($waktu_catatan)) {
            echo json_encode(['status' => FALSE, 'error' => 'Waktu service wajib dipilih.']);
            return;
        }
        if (empty($nama_cabang)) {
            echo json_encode(['status' => FALSE, 'error' => 'Cabang terdekat wajib dipilih.']);
            return;
        }
        if ($lokasi_service === 'home_service' && empty($alamat_service)) {
            echo json_encode(['status' => FALSE, 'error' => 'Alamat service wajib diisi untuk Home Service.']);
            return;
        }
        if (empty($kendaraan)) {
            echo json_encode(['status' => FALSE, 'error' => 'Tambahkan minimal 1 kendaraan.']);
            return;
        }

        // Validasi format nopol
        $kendaraan_normalized = [];
        foreach ($kendaraan as $k) {
            $nopol = strtoupper(trim($k['nopol']));
            $nopol = preg_replace('/^([A-Z]{1,2})(\d{1,5})([A-Z]{1,3})$/', '$1 $2 $3', $nopol);
            if (!preg_match('/^[A-Z]{1,2}\s\d{1,5}\s[A-Z]{1,3}$/', $nopol)) {
                echo json_encode([
                    'status' => FALSE,
                    'error'  => 'Format nopol "' . $k['nopol'] . '" tidak valid. Contoh: B 1234 ABC',
                ]);
                return;
            }
            $k['nopol'] = $nopol;
            $kendaraan_normalized[] = $k;
        }
        $kendaraan = $kendaraan_normalized;

        // Rate limit — 1x per no telp per hari
        $no_bersih = preg_replace('/[^0-9]/', '', $no_telp_publik);
        // $cek_rate  = $this->db->where('no_telp_publik', $no_bersih)
        //     ->where('is_public', 1)
        //     ->where('DATE(created_at)', date('Y-m-d'))
        //     ->get('mac_booking_service')->row_array();

        // if (!empty($cek_rate)) {
        //     echo json_encode([
        //         'status' => FALSE,
        //         'error'  => 'Nomor telepon ini sudah melakukan booking hari ini. Silakan hubungi ' .
        //                     '0823 2400 1500 jika ada pertanyaan.',
        //     ]);
        //     return;
        // }

        // Simpan booking
        $booking_id = $this->M_movas_booking->save_booking([
            'customer_id'     => null,
            'nama_cabang'     => $nama_cabang,
            'tgl_booking'     => $tgl_booking,
            'waktu_catatan'   => $waktu_catatan ?: null,
            'lokasi_service'  => $lokasi_service,
            'alamat_service'  => $lokasi_service === 'home_service' ? $alamat_service : null,
            'catatan'         => $catatan ?: null,
            'status'          => 'pending',
            'is_public'       => 1,
            'nama_publik'     => $nama_publik,
            'no_telp_publik'  => $no_bersih,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        // Simpan detail kendaraan
        $this->M_movas_booking->save_booking_detail($booking_id, $kendaraan);

        // Kirim WA ke admin
        $this->_kirim_wa_booking_publik_admin($booking_id);

        echo json_encode([
            'status'  => TRUE,
            'message' => 'Booking berhasil dikirim!',
        ]);
    }

    // ── HAPUS 1 UNIT DARI BOOKING (hanya saat pending) ─────────
    public function booking_hapus_unit($token = null)
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$token || !$this->_auth($token)) {
            echo json_encode(['status' => FALSE, 'error' => 'Akses ditolak.']);
            return;
        }

        $booking_id = intval($this->input->post('booking_id'));
        $nopol      = trim($this->input->post('nopol'));

        if (!$booking_id || $nopol === '') {
            echo json_encode(['status' => FALSE, 'error' => 'Data tidak lengkap.']);
            return;
        }

        $hasil = $this->M_movas_booking->hapus_unit_booking(
            $booking_id, $nopol, $this->customer_id
        );

        echo json_encode($hasil);
    }

    // ── HELPER: KIRIM WA BOOKING PUBLIK KE ADMIN ──────────────
    private function _kirim_wa_booking_publik_admin($booking_id)
    {
        $booking = $this->M_movas_booking->get_booking_by_id($booking_id);
        $detail  = $this->M_movas_booking->get_detail_booking($booking_id);

        if (empty($booking)) return;

        $bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];

        $tgl = date('d', strtotime($booking['tgl_booking'])) . ' '
            . $bulan[date('m', strtotime($booking['tgl_booking']))] . ' '
            . date('Y', strtotime($booking['tgl_booking']));

        $lokasi = $booking['lokasi_service'] === 'home_service'
            ? 'Home Service — ' . $booking['alamat_service']
            : 'Ke Bengkel';

        $list_kendaraan = '';
        foreach ($detail as $idx => $d) {
            $no = $idx + 1;
            $list_kendaraan .= "{$no}. *{$d['nopol']}*";
            if (!empty($d['tipe_baru'])) $list_kendaraan .= " — {$d['tipe_baru']}";
            $list_kendaraan .= "\n";
            if (!empty($d['km']))      $list_kendaraan .= "    KM         : " . number_format($d['km'], 0, ',', '.') . "\n";
            if (!empty($d['keluhan'])) $list_kendaraan .= "    Keluhan : {$d['keluhan']}\n";
            $list_kendaraan .= "\n";
        }

        $pesan  = "🔔 *Booking Service Baru! (Publik)*\n\n";
        $pesan .= "Ada permintaan booking service dari website.\n\n";
        $pesan .= "*Detail Customer :*\n";
        $pesan .= "👤 Nama     : {$booking['nama_publik']}\n";
        $pesan .= "📱 No. Telp : {$booking['no_telp_publik']}\n\n";
        $pesan .= "*Detail Booking :*\n";
        $pesan .= "📅 Tanggal  : {$tgl}\n";
        if (!empty($booking['waktu_catatan'])) {
            $pesan .= "⏰ Waktu    : {$booking['waktu_catatan']}\n";
        }
        $pesan .= "📍 Lokasi    : {$lokasi}\n";
        $pesan .= "🏠 Cabang   : {$booking['nama_cabang']}\n\n";
        $pesan .= "*Kendaraan yang akan di-service :*\n";
        $pesan .= $list_kendaraan;
        $pesan .= "\n⚡ Segera konfirmasi melalui sistem MAC.\n\n";
        $pesan .= "*MOVAS — Mobile Vehicle Assistant*";

        $fonnte_token = $this->config->item('fonnte_token');
        $ch = curl_init('https://api.fonnte.com/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        $target_admin = $this->_target_wa_booking($booking['nama_cabang'] ?? '');

        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'target'  => implode(',', $target_admin),
            'message' => $pesan,
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $fonnte_token]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }

    public function cek_closed_date()
    {
        $tgl         = $this->input->post('tgl');
        $nama_cabang = $this->input->post('nama_cabang');

        $cabang_map = [
            'Yogyakarta'    => 2, 'Gresik'        => 3,
            'Jabodetabek'   => 4, 'Bandung'       => 5,
            'Pekanbaru'     => 6, 'Lampung'       => 7,
            'Serang'        => 8, 'Palembang'     => 9,
            'Lubuk Linggau' => 10,'Bali'          => 11,
            'Kediri'        => 12,
        ];

        $cabang_id = $cabang_map[$nama_cabang] ?? null;

        if (!$tgl || !$cabang_id) {
            echo json_encode(['status' => TRUE, 'closed' => FALSE]);
            return;
        }

        $cek = $this->db->where('tgl', $tgl)
            ->where('cabang_id', $cabang_id)
            ->get('mac_booking_closed_date')->row_array();

        echo json_encode([
            'status' => TRUE,
            'closed' => !empty($cek),
            'alasan' => $cek['alasan'] ?? null,
        ]);
    }

    public function booking_publik_cek_closed()
    {
        $tgl         = $this->input->post('tgl');
        $nama_cabang = $this->input->post('nama_cabang');

        $cabang_map = [
            'Yogyakarta'    => 2, 'Gresik'        => 3,
            'Jabodetabek'   => 4, 'Bandung'       => 5,
            'Pekanbaru'     => 6, 'Lampung'       => 7,
            'Serang'        => 8, 'Palembang'     => 9,
            'Lubuk Linggau' => 10,'Bali'          => 11,
            'Kediri'        => 12,
        ];

        $cabang_id = $cabang_map[$nama_cabang] ?? null;

        if (!$tgl || !$cabang_id) {
            echo json_encode(['status' => TRUE, 'closed' => FALSE]);
            return;
        }

        $cek = $this->db->where('tgl', $tgl)
            ->where('cabang_id', $cabang_id)
            ->get('mac_booking_closed_date')->row_array();

        echo json_encode([
            'status' => TRUE,
            'closed' => !empty($cek),
            'alasan' => $cek['alasan'] ?? null,
        ]);
    }

    // ── MOVAS AI CHAT ──────────────────────────────────────────
    public function ai_chat()
    {
        header('Content-Type: application/json; charset=utf-8');

        $token = $this->input->post('token');
        if (!$token || !$this->_auth($token)) {
            echo json_encode(['status' => FALSE, 'error' => 'Akses ditolak.']);
            return;
        }

        $nopol   = $this->input->post('nopol') ?: 'umum';
        $message = trim($this->input->post('message'));

        if (empty($message)) {
            echo json_encode(['status' => FALSE, 'error' => 'Pesan tidak boleh kosong.']);
            return;
        }

        // Cek quota
        $quota = $this->M_movas->cek_quota($this->customer_id, $this->is_pro);
        if (!$quota['allowed']) {
            echo json_encode([
                'status' => FALSE,
                'error'  => 'Kuota pertanyaan hari ini sudah habis. ' .
                            ($this->is_pro ? '' : 'Upgrade ke PRO untuk tanya tanpa batas.'),
            ]);
            return;
        }

        // Ambil konteks kendaraan
        $konteks_data  = $this->M_movas->get_konteks_ai($this->customer_id, $this->is_pro);
        $konteks       = $konteks_data['kendaraan'];
        $booking_aktif = $konteks_data['booking_aktif'];

        // Ambil history chat (10 terakhir, dibalik agar urutan benar)
        $limit       = $this->is_pro ? 50 : 5;
        $history_raw = $this->M_movas->get_ai_chat($this->customer_id, $nopol, $limit);
        $history     = $history_raw;

        // Build system prompt
        $nama_customer   = $this->customer['customer_name'];
        $alamat_customer = $this->customer['address'];

        // ── Ambil nama sapa ────────────────────────────────────────
        $nama_sapa       = $nama_customer;
        $info_perusahaan = '';

        if (strtolower($this->customer['type_customer']) === 'perusahaan') {
            $last_invoice = $this->db->select('pic')
                ->where('customer_id', $this->customer_id)
                ->where('app_status', 'approved')
                ->order_by('created_at', 'DESC')
                ->limit(1)
                ->get('mac_invoice')->row_array();

            if (!empty($last_invoice['pic'])) {
                $nama_sapa = trim(preg_replace('/\(.*?\)/', '', $last_invoice['pic']));
            }

            $info_perusahaan = "- Perusahaan: {$nama_customer}\n";
        }

        $system_prompt  = "Kamu adalah MOVAS AI, asisten kendaraan dari Mobile Auto Care (MAC). ";
        $system_prompt .= "Bantu {$nama_sapa} dengan gaya santai, friendly, peduli, tapi tetap profesional.\n\n";

        $system_prompt .= "CUSTOMER: {$nama_sapa}";
        if (!empty($info_perusahaan)) {
            $system_prompt .= " (PIC dari {$nama_customer})";
        }
        if (!empty($alamat_customer)) {
            $system_prompt .= " | Alamat: {$alamat_customer}";
        }
        $system_prompt .= "\n\n";

        $system_prompt .= "DATA KENDARAAN CUSTOMER:\n";

        // ── Info paket ─────────────────────────────────────────────
        // $system_prompt .= "PAKET AKUN CUSTOMER:\n";
        // $system_prompt .= $this->is_pro
        //     ? "- Customer ini menggunakan paket PRO — dapat melihat semua kendaraan dan riwayat lengkap.\n\n"
        //     : "- Customer ini menggunakan paket FREE — hanya dapat melihat maksimal 6 kendaraan.\n\n";

        foreach ($konteks as $k) {
            $system_prompt .= "- {$k['nopol']} ({$k['tipe']}, {$k['jenis_kendaraan']})";
            $system_prompt .= ", KM: " . number_format($k['km_terakhir'], 0, ',', '.') . " KM";
            $system_prompt .= ", Service: {$k['total_service']}x\n";

            if (!empty($k['kondisi'])) {
                $system_prompt .= "  Kondisi bermasalah: " . implode(', ', array_slice($k['kondisi'], 0, 5)) . "\n";
            }
            if (!empty($k['rekomendasi'])) {
                $system_prompt .= "  Rekomendasi: " . implode(', ', array_slice($k['rekomendasi'], 0, 3)) . "\n";
            }

            // ── Tambah reminder ────────────────────────────────────
            // if (!empty($k['reminder'])) {
            //     $sisa = $k['reminder']['sisa_hari'];
            //     $system_prompt .= "  Reminder service berkala: "
            //         . date('d/m/Y', strtotime($k['reminder']['tgl_reminder']))
            //         . " ({$sisa} hari lagi)\n";
            // }

            if (!empty($k['riwayat_invoice'])) {
                $inv = $k['riwayat_invoice'][0]; // ← hanya 1 terakhir
                    $system_prompt .= "  Service terakhir: {$inv['tgl_service']} | KM: {$inv['km']} | Total: Rp " . number_format((float)$inv['sub_total'], 0, ',', '.') . "\n";
                if (!empty($inv['uraian'])) {
                    $system_prompt .= "  Uraian: " . implode('; ', array_slice($inv['uraian'], 0, 3)) . "\n";
                }
            }
        }

        // ── Info booking aktif ─────────────────────────────────────
        // $system_prompt .= "\nBOOKING SERVICE AKTIF:\n";
        // if (!empty($booking_aktif)) {
        //     foreach ($booking_aktif as $b) {
        //         $tgl    = date('d/m/Y', strtotime($b['tgl_booking']));
        //         $status = $b['status'] === 'pending' ? 'Menunggu Konfirmasi' : 'Dikonfirmasi';
        //         $lokasi = $b['lokasi_service'] === 'home_service'
        //             ? 'Home Service — ' . ($b['alamat_service'] ?? '')
        //             : 'Ke Bengkel';

        //         $system_prompt .= "- Booking #{$b['id']} | Tgl: {$tgl} | Status: {$status}\n";
        //         $system_prompt .= "  Lokasi: {$lokasi} | Cabang: {$b['nama_cabang']}\n";
        //         if (!empty($b['waktu_catatan'])) {
        //             $system_prompt .= "  Waktu: {$b['waktu_catatan']}\n";
        //         }
        //         foreach ($b['detail'] as $d) {
        //             $system_prompt .= "  - {$d['nopol']}";
        //             if (!empty($d['km']))      $system_prompt .= " | KM: " . number_format($d['km'], 0, ',', '.');
        //             if (!empty($d['keluhan'])) $system_prompt .= " | Keluhan: {$d['keluhan']}";
        //             $system_prompt .= "\n";
        //         }
        //     }
        // } else {
        //     $system_prompt .= "- Tidak ada booking aktif saat ini.\n";
        // }
        // $system_prompt .= "\n";

        $system_prompt .= "\nINFO MAC:\n";
        $system_prompt .= "Mobile Auto Care (MAC) | WA: 0823 2400 1500 | Ops: Sen-Jum 08-16, Sab 08-14\n";
        $system_prompt .= "Layanan: service, perawatan, perbaikan komponen, HOME SERVICE (mekanik ke lokasi)\n\n";

        $system_prompt .= "CABANG:\n";
        $system_prompt .= "Jabodetabek: Cileungsi Kab.Bogor | Sopandi 081213325396\n";
        $system_prompt .= "Bandung: Jatinangor | Priyadi 082324007900\n";
        $system_prompt .= "Yogyakarta: Prambanan Klaten | Anggoro 089528305506\n";
        $system_prompt .= "Gresik: Pongangan Indah | Aang 081331200036\n";
        $system_prompt .= "Kediri: Pare Kab.Kediri | Saryanto 081329542062\n";
        $system_prompt .= "Serang: Kaligandu Kota Serang | Fatkhur 0895347999347\n";
        $system_prompt .= "Lampung: Sukarame Bandar Lampung | Agung 087779296081\n";
        $system_prompt .= "Lubuk Linggau: Watervang Sum-Sel | Hermawanta 082176012026\n";
        $system_prompt .= "Palembang: Sukarami | Anton 085889991043\n";
        $system_prompt .= "Pekanbaru: Tampan | Angga 08126876113\n";
        $system_prompt .= "Bali: Denpasar Utara | Eko 081286355557\n\n";

        // $system_prompt .= "INFO MOVAS:\n";
        // $system_prompt .= "- MOVAS (Mobile Vehicle Assistant) adalah aplikasi monitoring kendaraan dari MAC\n";
        // $system_prompt .= "- FREE: maks 10 pertanyaan AI per hari, riwayat 6 invoice terakhir\n";
        // $system_prompt .= "- PRO: pertanyaan AI unlimited, semua riwayat, foto kondisi kendaraan\n";
        // $system_prompt .= "- Upgrade PRO: hubungi WA 0823 2400 1500\n\n";

        $system_prompt .= "\nCARA BOOKING SERVICE:\n";
        $system_prompt .= "1. Via MOVAS: Menu Booking Service → pilih kendaraan → isi KM & keluhan → pilih tanggal/waktu/lokasi/cabang → kirim\n";
        $system_prompt .= "2. Via Web: https://movas.mobileautocare.co.id/booking → isi data diri & kendaraan → kirim\n";
        $system_prompt .= "Booking Sen-Sab (Minggu libur) | Sen-Jum 08-16, Sab 08-14 | Konfirmasi via WA | Bisa dibatalkan jika masih Menunggu Konfirmasi\n\n";

        $system_prompt .= "\nCATATAN: Kamu hanya diberikan data service terakhir per kendaraan untuk menjaga performa. ";
        $system_prompt .= "Jika customer tanya riwayat lengkap, sampaikan dengan ramah bahwa detail lengkap bisa dilihat langsung di halaman kendaraan MOVAS.\n\n";

        $system_prompt .= "ATURAN:\n";

        // ── Gaya komunikasi ────────────────────────────────────────
        // $system_prompt .= "- Jawab dengan gaya friendly, hangat, dan santai tapi tetap profesional\n";
        // $system_prompt .= "- Boleh pakai sapaan seperti 'Halo!', 'Wah', 'Oke siap!', 'Tenang aja' dll\n";
        // $system_prompt .= "- Sesekali pakai emoji yang relevan (🚗 🔧 ✅ ⚠️ dll) tapi jangan berlebihan\n";
        // $system_prompt .= "- Hindari emoji yang kompleks atau jarang digunakan — cukup pakai emoji umum seperti 😊 👍 🚗 🔧 ✅ ⚠️ 💡 📋 saja\n";
        // $system_prompt .= "- Jika tidak yakin emoji akan terbaca, lebih baik tidak pakai emoji sama sekali\n";
        // $system_prompt .= "- Sesekali sapa customer dengan nama mereka agar lebih personal, tapi jangan setiap kalimat\n";
        // $system_prompt .= "- Untuk pertanyaan singkat, jawab singkat dan padat\n";
        // $system_prompt .= "- Untuk pertanyaan kompleks, jawab detail dan terstruktur\n";
        // $system_prompt .= "- Sapa customer dengan nama hanya di pesan PERTAMA saja, setelah itu langsung jawab tanpa sapaan nama\n";
        // $system_prompt .= "- Jangan ulangi sapaan 'Halo', 'Hi', atau nama customer di setiap respon\n";
        // $system_prompt .= "- Format jawaban dengan rapi dan mudah dibaca\n";
        // $system_prompt .= "- Gunakan baris baru untuk memisahkan poin-poin penting\n";
        // $system_prompt .= "- Jika ada list, gunakan tanda '-' atau angka yang konsisten\n";
        // $system_prompt .= "- Hindari paragraf yang terlalu panjang — pecah jadi beberapa paragraf pendek\n";
        // $system_prompt .= "- Gunakan *teks tebal* untuk highlight informasi penting\n";
        // $system_prompt .= "- Jangan gunakan format markdown yang berlebihan seperti ### atau --- \n";

        $system_prompt .= "- Gaya friendly, hangat, santai tapi profesional. Boleh pakai 'Wah', 'Oke siap!', 'Tenang aja'\n";
        $system_prompt .= "- Emoji secukupnya (🚗 🔧 ✅ ⚠️ 😊), hindari emoji kompleks\n";
        $system_prompt .= "- Sapa nama customer hanya di pesan PERTAMA, tidak perlu diulang\n";
        $system_prompt .= "- Jawab singkat jika pertanyaan singkat, detail jika kompleks\n";
        $system_prompt .= "- Format rapi: baris baru antar poin, *tebal* untuk info penting, hindari ###\n";
        
        // // ── Data & informasi ───────────────────────────────────────
        // $system_prompt .= "- Gunakan data kendaraan di atas jika relevan\n";
        // $system_prompt .= "- Jawab dalam bahasa yang sama dengan pertanyaan (Indonesia atau Inggris)\n";
        // $system_prompt .= "- Kamu BOLEH dan HARUS menjawab pertanyaan seputar biaya service, nominal invoice, dan rincian uraian pekerjaan berdasarkan data yang sudah diberikan\n";
        // $system_prompt .= "- Jangan pernah mengarang data kendaraan atau invoice yang tidak ada di konteks\n";
        // $system_prompt .= "- Jika data tidak tersedia, jujur bilang tidak ada datanya\n";

        // // ── Proaktif & saran ───────────────────────────────────────
        // $system_prompt .= "- Jika melihat kondisi kendaraan bermasalah dari data, proaktif kasih peringatan dengan nada peduli\n";
        // $system_prompt .= "- Sesekali kasih tips perawatan kendaraan yang relevan dengan kondisi kendaraan customer\n";
        // $system_prompt .= "- Jika customer tanya cabang terdekat, tanya dulu lokasinya baru rekomendasikan\n";
        // $system_prompt .= "- Jika diminta info harga yang belum ada datanya, arahkan ke kantor MAC terdekat dengan ramah\n";

        // // ── Batasan topik ──────────────────────────────────────────
        // $system_prompt .= "- Fokus pada topik otomotif, perawatan kendaraan, dan layanan MAC\n";
        // $system_prompt .= "- Kalau customer nanya hal random di luar topik, tolak dengan cara yang lucu dan tetap ramah\n";

        // // ── Promosi PRO ────────────────────────────────────────────
        // $system_prompt .= "- Jika customer FREE tanya fitur PRO, kasih tahu dengan santai dan ajak upgrade 1x saja\n";
        // $system_prompt .= "- Jangan promosi PRO berulang-ulang dalam satu percakapan\n";

        // // ── Aturan System ──────────────────────────────────────────
        // $system_prompt .= "- PENTING: Jangan pernah menyebut, menampilkan, atau mengacu pada 'aturan', 'instruksi', atau 'system prompt' dalam jawabanmu\n";
        // $system_prompt .= "- Jawab selalu dalam bentuk percakapan natural, bukan format list instruksi\n";
        // $system_prompt .= "- Jika kamu merasa perlu mengikuti aturan tertentu, lakukan saja tanpa menyebutkan aturannya\n";

        // ── Data & informasi ───────────────────────────────────────
        $system_prompt .= "- Gunakan data kendaraan jika relevan, jawab dalam bahasa yang sama dengan pertanyaan\n";
        $system_prompt .= "- Boleh jawab soal biaya/invoice dari data yang ada, jangan karang data yang tidak ada\n";
        $system_prompt .= "- Jika data tidak tersedia, jujur bilang tidak ada\n";

        // ── Proaktif & batasan ─────────────────────────────────────
        $system_prompt .= "- Proaktif kasih peringatan jika ada kondisi kendaraan bermasalah\n";
        $system_prompt .= "- Jika tanya cabang terdekat, tanya lokasi dulu. Jika tanya harga yang tidak ada, arahkan ke MAC terdekat\n";
        $system_prompt .= "- Fokus otomotif & layanan MAC, tolak topik lain dengan ramah dan lucu\n";

        // ── PRO & System ───────────────────────────────────────────
        $system_prompt .= "- Jika customer FREE tanya PRO, ajak upgrade 1x saja, tidak perlu berulang\n";
        $system_prompt .= "- Jangan sebut 'aturan', 'instruksi', atau 'system prompt' dalam jawaban\n";
        $system_prompt .= "- Jawab natural seperti percakapan biasa\n";

        // Build messages untuk Gemini
        $contents = [];

        // System prompt sebagai pesan pertama
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $system_prompt]]
        ];
        $contents[] = [
            'role'  => 'model',
            'parts' => [['text' => 'Siap! Saya MOVAS AI, asisten kendaraan Anda. Ada yang bisa saya bantu?']]
        ];

        // History chat
        foreach ($history as $h) {
            $contents[] = [
                'role'  => $h['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $h['message']]]
            ];
        }

        // Pesan user sekarang
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $message]]
        ];

        // Call Gemini API
        $api_key = GEMINI_API_KEY;

        $payload = json_encode([
            'contents'         => $contents,
            'generationConfig' => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 1500,
            ],
        ]);

        $models = [
            'gemini-flash-latest',
            'gemini-pro-latest',
            'gemini-3.5-flash-lite',
            'gemma-4-31b-it',
            'gemma-4-26b-a4b-it',
        ];

        $response = null;
        $result   = null;
        $found    = false;

        foreach ($models as $model) {
            $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/'
                    . $model . ':generateContent?key=' . $api_key;

            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'User-Agent: Mozilla/5.0',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

            $resp_raw = curl_exec($ch);
            curl_close($ch);

            if ($resp_raw === false) continue;

            $resp_decoded = json_decode($resp_raw, true);
            if (empty($resp_decoded)) continue;

            // Jika bukan 503, pakai response ini
            $error_code = $resp_decoded['error']['code'] ?? 0;
            if (empty($resp_decoded['error']) || !in_array($error_code, [429, 503, 404])) {
                $result = $resp_decoded;
                $found  = true;
                break;
            }
        }

        if (!$found || empty($result['candidates'][0]['content']['parts'][0]['text'])) {
            echo json_encode([
                'status'       => FALSE,
                'error'        => 'AI sedang sibuk.',
                'debug_found'  => $found,
                'debug_result' => $result,
                'debug_keys'   => array_keys($result ?? []),
            ]);
            return;
        }

        $ai_reply = $result['candidates'][0]['content']['parts'][0]['text'];

        $ai_reply = mb_convert_encoding($ai_reply, 'UTF-8', 'UTF-8');
        $ai_reply = preg_replace('/[^\x{0000}-\x{FFFF}]/u', '', $ai_reply);

        // Simpan ke history
        $this->M_movas->save_ai_chat($this->customer_id, $nopol, 'user', $message);
        $this->M_movas->save_ai_chat($this->customer_id, $nopol, 'assistant', $ai_reply);

        // Update quota
        $this->M_movas->tambah_quota($this->customer_id);

        // Hitung sisa quota
        $quota_baru = $this->M_movas->cek_quota($this->customer_id, $this->is_pro);

        echo json_encode([
            'status'  => TRUE,
            'reply'   => $ai_reply,
            'sisa'    => $quota_baru['sisa'],
            'is_pro'  => $this->is_pro,
        ], JSON_UNESCAPED_UNICODE);
    }

    // ── GET HISTORY CHAT ───────────────────────────────────────
    public function get_ai_chat_history()
    {
        $token = $this->input->post('token');
        if (!$token || !$this->_auth($token)) {
            echo json_encode(['status' => FALSE, 'error' => 'Akses ditolak.']);
            return;
        }

        $nopol       = $this->input->post('nopol') ?: '';
        $limit   = $this->is_pro ? 50 : 5;
        $history = $this->M_movas->get_ai_chat($this->customer_id, $nopol, $limit);

        // Hitung sisa quota
        $quota = $this->M_movas->cek_quota($this->customer_id, $this->is_pro);

        echo json_encode([
            'status'  => TRUE,
            'history' => $history,
            'sisa'    => $quota['sisa'],
            'is_pro'  => $this->is_pro,
        ]);
    }
}