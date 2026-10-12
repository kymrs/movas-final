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

        // Chip tanggal service
        $chip_tanggal = $this->M_movas->get_chip_tanggal(
            $this->customer_id, $nopol, $this->is_pro
        );

        // Kondisi terbaru (sesi pertama = terbaru karena DESC)
        $kondisi    = [];
        $sesi_aktif = null;
        if (!empty($chip_tanggal)) {
            $sesi_aktif = $chip_tanggal[0];
            $kondisi    = $this->M_movas->get_kondisi_by_sesi($sesi_aktif['sesi_id']);
        }

        // Rekomendasi dari sesi terbaru
        $rekomendasi = $this->M_movas->get_rekomendasi(
            $this->customer_id, $nopol
        );

        // Riwayat service
        $riwayat = $this->M_movas->get_riwayat(
            $this->customer_id, $nopol, $this->is_pro
        );

        // Foto Sesi Fitur PRO
        $foto_sesi = [];
        if ($sesi_aktif && $this->is_pro) {
            $foto_sesi = $this->M_movas->get_foto_by_sesi($sesi_aktif['sesi_id']);
        }

        $data['foto_sesi'] = $foto_sesi;

        $reminder_kendaraan = $this->M_movas->get_reminder_kendaraan(
            $this->customer_id, $nopol
        );
        $data['reminder_kendaraan'] = $reminder_kendaraan;

        $data['customer']     = $this->customer;
        $data['is_pro']       = $this->is_pro;
        $data['token']        = $token;
        $data['kendaraan']    = $kendaraan;
        $data['nopol']        = $nopol;
        $data['chip_tanggal'] = $chip_tanggal;
        $data['sesi_aktif']   = $sesi_aktif;
        $data['kondisi']      = $kondisi;
        $data['rekomendasi']  = $rekomendasi;
        $data['riwayat']      = $riwayat;
        $data['nama_sapa'] = $this->customer['nama_sapa'];
        $data['page']         = 'kendaraan_detail';

        $this->load->view('movas/layout', $data);
    }

    // Menu List Kendaraan
    public function kendaraan_list($token = null)
    {
        if (!$token || !$this->_auth($token)) return;

        $profile = $this->M_movas->get_profile($this->customer_id, $this->is_pro);

        // ── Tambah reminder per kendaraan ─────────────────────
        foreach ($profile['kendaraan'] as &$k) {
            $k['reminder'] = $this->M_movas->get_reminder_kendaraan(
                $this->customer_id, $k['nopol']
            );
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

    // ── MOVAS AI CHAT ──────────────────────────────────────────
    public function ai_chat()
    {
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
        $konteks = $this->M_movas->get_konteks_ai($this->customer_id);

        // Ambil history chat (10 terakhir, dibalik agar urutan benar)
        $limit       = $this->is_pro ? 50 : 10;
        $history_raw = $this->M_movas->get_ai_chat($this->customer_id, $nopol, $limit);
        $history     = $history_raw;

        // Build system prompt
        $nama_customer = $this->customer['customer_name'];
        $system_prompt = "Kamu adalah MOVAS AI, asisten kendaraan pintar dari Mobile Auto Care (MAC). ";
        $system_prompt .= "Kamu membantu customer bernama {$nama_customer} memahami kondisi kendaraan mereka ";
        $system_prompt .= "dan memberikan saran seputar perawatan kendaraan.\n\n";
        $system_prompt .= "DATA KENDARAAN CUSTOMER:\n";

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
            if (!empty($k['reminder'])) {
                $sisa = $k['reminder']['sisa_hari'];
                $system_prompt .= "  Reminder service berkala: "
                    . date('d/m/Y', strtotime($k['reminder']['tgl_reminder']))
                    . " ({$sisa} hari lagi)\n";
            }

            if (!empty($k['riwayat_invoice'])) {
                $system_prompt .= "  Riwayat service terakhir:\n";
                foreach ($k['riwayat_invoice'] as $inv) {
                    $system_prompt .= "  * {$inv['tgl_service']} | {$inv['km']} | {$inv['jenis_service']} | Total: {$inv['sub_total']}\n";
                    if (!empty($inv['uraian'])) {
                        $system_prompt .= "    Uraian: " . implode('; ', array_slice($inv['uraian'], 0, 5)) . "\n";
                    }
                }
            }
        }

        $system_prompt .= "\nINFORMASI MOBILE AUTO CARE (MAC):\n";
        $system_prompt .= "- Nama: Mobile Auto Care (MAC)\n";
        $system_prompt .= "- WA Pusat: 0823 2400 1500\n";
        $system_prompt .= "- Jam Operasional: Senin-Jumat 08:00-16:00, Sabtu 08:00-14:00\n";
        $system_prompt .= "- Layanan: Service kendaraan, perawatan rutin, perbaikan komponen\n";
        $system_prompt .= "- HOME SERVICE: MAC melayani home service, mekanik datang ke lokasi customer\n\n";

        $system_prompt .= "LOKASI CABANG MAC:\n";
        $system_prompt .= "- MAC Jabodetabek: Kp. Empu No.1 RT.001/RW.007, Setu Sari, Cileungsi, Kab. Bogor | PIC: Sopandi 081213325396\n";
        $system_prompt .= "- MAC Bandung: Jl. Kol. Ahmad Syam No.235 RT04/06, Sayang, Jatinangor | PIC: Priyadi 082324007900\n";
        $system_prompt .= "- MAC Yogyakarta: Jl. Prambanan Manisrenggo Km2, Dengok, Bogisan Prambanan Klaten | PIC: Anggoro 089528305506\n";
        $system_prompt .= "- MAC Gresik: Jl. Beton 1 No.18 Perum Pongangan Indah, Gresik | PIC: Aang 081331200036\n";
        $system_prompt .= "- MAC Kediri: Jl. Cibaling RT003/RW01 Darungan, Pare, Kab. Kediri | PIC: Saryanto 081329542062\n";
        $system_prompt .= "- MAC Serang: Komp. Pasir Indah Block C No.77, Kaligandu, Kota Serang | PIC: Fatkhur 0895347999347\n";
        $system_prompt .= "- MAC Lampung: Jl. Pulau Damar, Way Dadi Baru, Sukarame, Bandar Lampung | PIC: Agung 087779296081\n";
        $system_prompt .= "- MAC Lubuk Linggau: Jl. Ibadah RT.01, Lubuk Linggau Timur 1, Watervang, Sum-Sel | PIC: Hermawanta 082176012026\n";
        $system_prompt .= "- MAC Palembang: Jl. Talang Jambe No.92 RT16/RW04, Sukarami, Palembang | PIC: Anton 085889991043\n";
        $system_prompt .= "- MAC Pekanbaru: Jl. Lobak - Jl. Sawi No.73 RT01/RWIV, Delima, Tampan, Pekanbaru | PIC: Angga 08126876113\n";
        $system_prompt .= "- MAC Bali: Jl. Cargo Taman I No.2, Ubung Kaja, Denpasar Utara, Bali | PIC: Eko 081286355557\n\n";

        $system_prompt .= "INFO MOVAS:\n";
        $system_prompt .= "- MOVAS (Mobile Vehicle Assistant) adalah aplikasi monitoring kendaraan dari MAC\n";
        $system_prompt .= "- FREE: maks 10 pertanyaan AI per hari, riwayat 6 invoice terakhir\n";
        $system_prompt .= "- PRO: pertanyaan AI unlimited, semua riwayat, foto kondisi kendaraan\n";
        $system_prompt .= "- Upgrade PRO: hubungi WA 0823 2400 1500\n\n";

        $system_prompt .= "ATURAN:\n";
        $system_prompt .= "- Jawab ramah, profesional, dan informatif\n";
        $system_prompt .= "- Fokus pada topik otomotif, perawatan kendaraan, dan layanan MAC\n";
        $system_prompt .= "- Gunakan data kendaraan di atas jika relevan\n";
        $system_prompt .= "- Jawab dalam bahasa yang sama dengan pertanyaan (Indonesia atau Inggris)\n";
        $system_prompt .= "- Kamu BOLEH dan HARUS menjawab pertanyaan seputar biaya service, nominal invoice, dan rincian uraian pekerjaan berdasarkan data yang sudah diberikan di atas\n"; // ← tambah
        $system_prompt .= "- Jangan tolak pertanyaan soal biaya — data invoice sudah tersedia untuk kamu gunakan\n";
        $system_prompt .= "- Jika diminta info harga service yang belum ada datanya, arahkan ke kantor MAC terdekat\n";
        $system_prompt .= "- Jangan jawab pertanyaan di luar topik otomotif, kendaraan, dan layanan MAC\n";

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
                'temperature'     => 0.7,
                'maxOutputTokens' => 2000,
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
        ]);
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
        $limit   = $this->is_pro ? 50 : 10;
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