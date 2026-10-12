<?php
// Helper badge kondisi
function kondisi_badge($val) {
    $kritis = ['Perlu Ganti','Rusak','Bocor','Retak','Robek','Aus','Habis',
                'Mati','Getas','Hilang','Tidak Berfungsi','Perlu Overhaul', 'Segera Ganti', 'Tidak Ada'];
    $perhatian = ['Kotor','Tipis','Lemah','Kurang Dingin','Kurang',
                  'Kendur','Berat','Perlu Servis','Perlu Tune Up', 'Perlu Perhatian'];
    if (empty($val)) return '<span style="color:var(--movas-text3)">—</span>';
    if (in_array($val, $kritis))    return '<span class="kondisi-badge critical">' . $val . '</span>';
    if (in_array($val, $perhatian)) return '<span class="kondisi-badge attention">' . $val . '</span>';
    return '<span class="kondisi-badge safe">' . $val . '</span>';
}

function accu_color($tegangan) {
    if (empty($tegangan)) return null;
    $val = floatval(str_replace(',', '.', $tegangan));
    if ($val >= 12.6)                  return 'var(--movas-safe)';   // hijau
    if ($val >= 12.0 && $val < 12.6)  return 'var(--movas-warn)';   // kuning
    return 'var(--movas-danger)';                                     // merah
}

function renderFotoGallery($fotos) {
    if (empty($fotos)) return '';

    $label_map = [
        'tali_kipas'=>'Tali Kipas','oli_mesin'=>'Oli Mesin',
        'filter_oli'=>'Filter Oli','slang_radiator'=>'Slang Radiator',
        'air_radiator'=>'Air Radiator','tune_up'=>'Tune Up',
        'busi'=>'Busi','baterai_mesin'=>'Baterai',
        'filter_udara'=>'Filter Udara','filter_bbm'=>'Filter BBM',
        'oli_transmisi'=>'Oli Transmisi','oli_differential'=>'Oli Differential',
        'kabel_kopling'=>'Kabel Kopling','master_kopling'=>'Master Kopling',
        'sistem_rem'=>'Sistem Rem','master_rem'=>'Master Rem',
        'brake_pad'=>'Brake Pad','disc_brake'=>'Disc Brake',
        'brake_shoe'=>'Brake Shoe','tromol'=>'Tromol',
        'power_steering'=>'Power Steering','boot_drive_shaft'=>'Boot Drive Shaft',
        'ball_joint'=>'Ball Joint','suspensi_depan'=>'Suspensi Depan',
        'suspensi_belakang'=>'Suspensi Belakang',
        'motor_extra_fan'=>'Motor Extra Fan',
        'lampu_sein_depan_kanan'=>'Sein Depan Kanan',
        'lampu_sein_depan_kiri'=>'Sein Depan Kiri',
        'lampu_besar_pendek_kanan'=>'Lampu Besar Pendek Kanan',
        'lampu_besar_pendek_kiri'=>'Lampu Besar Pendek Kiri',
        'lampu_kabut_kanan'=>'Lampu Kabut Kanan',
        'lampu_kabut_kiri'=>'Lampu Kabut Kiri',
        'lampu_rem_kanan'=>'Lampu Rem Kanan',
        'lampu_rem_kiri'=>'Lampu Rem Kiri',
        'klakson'=>'Klakson','ac_depan'=>'AC Depan',
        'ac_tengah'=>'AC Tengah','filter_cabin'=>'Filter Cabin',
        'accu_kondisi'=>'Accu/Battery',
        'wiper_blade_depan'=>'Wiper Depan',
        'wiper_blade_belakang'=>'Wiper Belakang',
        'spion_luar_kanan'=>'Spion Luar Kanan',
        'spion_luar_kiri'=>'Spion Luar Kiri',
        'mudguard_depan_kanan'=>'Mudguard Depan Kanan',
        'mudguard_depan_kiri'=>'Mudguard Depan Kiri',
        'mudguard_belakang_kanan'=>'Mudguard Belakang Kanan',
        'mudguard_belakang_kiri'=>'Mudguard Belakang Kiri',
        'foot_step'=>'Foot Step',
        'liner_fender_kanan'=>'Liner Fender Kanan',
        'liner_fender_kiri'=>'Liner Fender Kiri',
        'ban_depan_kanan'=>'Ban Depan Kanan',
        'ban_depan_kiri'=>'Ban Depan Kiri',
        'ban_belakang_kanan'=>'Ban Belakang Kanan',
        'ban_belakang_kiri'=>'Ban Belakang Kiri',
        'ban_cadangan'=>'Ban Cadangan',
    ];

    $grouped = [];
    foreach ($fotos as $f) {
        $grouped[$f['nama_komponen']][] = $f;
    }

    $html = '<div id="foto-gallery-inner">';

    foreach ($grouped as $komponen => $items) {
        $label     = $label_map[$komponen] ?? $komponen;
        $total     = count($items);
        $show      = array_slice($items, 0, 3);
        $remaining = $total - 3;

        $html .= '<div style="margin-bottom:16px;">';
        $html .= '<div style="font-size:11px; color:var(--movas-text3);
                              margin-bottom:8px; font-weight:600;
                              text-transform:uppercase; letter-spacing:0.5px;">'
               . $label . '</div>';
        $html .= '<div style="display:flex; gap:8px; flex-wrap:wrap; position:relative;">';

        foreach ($show as $idx => $foto) {
            $caption = htmlspecialchars($label);
            if (!empty($foto['keterangan'])) {
                $caption .= ' — ' . htmlspecialchars($foto['keterangan']);
            }

            $html .= '<a href="' . $foto['url'] . '"
                         class="glightbox"
                         data-fancybox="gallery-' . $komponen . '"
                         data-caption="' . $caption . '"
                         style="display:block; width:90px; height:90px;
                                flex-shrink:0; border-radius:10px; overflow:hidden;
                                border:1px solid var(--movas-border); position:relative;">';

            $html .= '<img src="' . $foto['url'] . '"
                          style="width:100%; height:100%; object-fit:cover;"
                          loading="lazy">';

            if ($idx === 2 && $remaining > 0) {
                $html .= '<div style="position:absolute; inset:0;
                                      background:rgba(0,0,0,0.55);
                                      display:flex; align-items:center;
                                      justify-content:center;
                                      color:white; font-size:14px; font-weight:700;">
                              +' . $remaining . '
                          </div>';
            }

            $html .= '</a>';
        }

        // Foto hidden untuk navigasi
        $hidden = array_slice($items, 3);
        foreach ($hidden as $foto) {
            $caption = htmlspecialchars($label);
            if (!empty($foto['keterangan'])) {
                $caption .= ' — ' . htmlspecialchars($foto['keterangan']);
            }
            $html .= '<a href="' . $foto['url'] . '"
                         class="glightbox"
                         data-fancybox="gallery-' . $komponen . '"
                         data-caption="' . $caption . '"
                         style="position:absolute; width:0; height:0;
                                overflow:hidden; opacity:0; pointer-events:none;"></a>';
        }

        $html .= '</div></div>';
    }

    $html .= '</div>';
    return $html;
}
?>

<!-- Info Kendaraan -->
<div class="movas-card mb-4">
    <!-- Sticky header saat scroll -->
    <div class="vehicle-sticky-header" id="vehicle-sticky">
        <div class="sticky-left">
            <div class="sticky-icon">
                <?php if (strtolower($kendaraan['jenis_kendaraan']) === 'motor'): ?>
                    <i class="fas fa-motorcycle" style="color:#00b4d8;"></i>
                <?php else: ?>
                    <i class="fas fa-car" style="color:#00b4d8;"></i>
                <?php endif; ?>
            </div>
            <div>
                <div class="sticky-nopol"><?= htmlspecialchars($nopol) ?></div>
                <div class="sticky-sub">
                    <?= htmlspecialchars($kendaraan['tipe'] ?: '—') ?>
                    &nbsp;·&nbsp; <?= $kendaraan['jenis_kendaraan'] ?>
                    <?php if ($kendaraan['km_terakhir']): ?>
                    &nbsp;·&nbsp; <?= number_format($kendaraan['km_terakhir'], 0, ',', '.') ?> KM
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="sticky-right">
            <a href="<?= site_url($token . '/download/' . urlencode($nopol)) ?>"
            style="display:inline-flex; align-items:center; gap:6px;
                    background:var(--movas-surface2); border:1px solid var(--movas-border);
                    color:var(--movas-text); text-decoration:none;
                    padding:6px 12px; border-radius:8px; font-size:11px; font-weight:500;">
                <i class="fas fa-file-pdf" style="color:#e74a3b;"></i>
                <span class="sticky-download-text">Download Riwayat</span>
            </a>
        </div>
    </div>
    <!-- Spacer agar konten tidak tertutup sticky header -->
</div>
<!-- Spacer -->
<div style="height: 35px;"></div>

<!-- Back + Header -->
<div style="display:flex; justify-content:flex-end; margin-bottom:20px;">
    <a href="<?= site_url($token) ?>"
       style="display:inline-flex; align-items:center; gap:6px;
              color:var(--movas-text2); text-decoration:none; font-size:12px;
              background:var(--movas-surface); border:1px solid var(--movas-border);
              padding:6px 12px; border-radius:8px; transition:all 0.15s;"
       onmouseover="this.style.borderColor='var(--movas-cyan)'; this.style.color='var(--movas-cyan)'"
       onmouseout="this.style.borderColor='var(--movas-border)'; this.style.color='var(--movas-text2)'">
        <i class="fas fa-arrow-left" style="font-size:11px;"></i> Kembali
    </a>
</div>

<!-- SECTION 1: KONDISI TERKINI -->
<div class="movas-card mb-4">
    <div class="movas-card-header">
        <span><i class="fas fa-clipboard-check" style="color:var(--movas-cyan);"></i> Kondisi Kendaraan</span>
        <?php if (!empty($chip_tanggal)): ?>
        <span style="font-size:11px; color:var(--movas-text3);">
            <?= count($chip_tanggal) ?> sesi service
        </span>
        <?php endif; ?>
    </div>

    <?php if (!empty($chip_tanggal)): ?>
    <!-- GANTI chip scroll dengan dropdown -->
    <div style="padding:12px 16px; border-bottom:1px solid var(--movas-border);">
        <!-- Label -->
        <div style="font-size:11px; color:var(--movas-text3); margin-bottom:8px;">
            <i class="fas fa-calendar-alt" style="margin-right:5px; color:var(--movas-cyan);"></i>
            Pilih Tanggal Service
        </div>
        <div class="movas-select-wrapper" id="tanggal-dropdown">
                <!-- Trigger -->
                <div class="movas-select-trigger" onclick="toggleDropdown()">
                    <?php
                    $chip    = $chip_tanggal[0];
                    $bulan   = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr',
                                '05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu',
                                '09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
                    $tgl_sel = date('d', strtotime($chip['tgl_service'])) . ' '
                            . $bulan[date('m', strtotime($chip['tgl_service']))] . ' '
                            . date('Y', strtotime($chip['tgl_service']));
                    ?>
                    <div class="movas-select-selected">
                        <div class="movas-select-dot"></div>
                        <div>
                            <div class="movas-select-tgl"><?= $tgl_sel ?></div>
                            <?php if (!empty($chip['jenis_service'])): ?>
                            <div class="movas-select-sub"><?= $chip['jenis_service'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down movas-select-arrow"></i>
                </div>

                <!-- Options -->
                <div class="movas-select-options" id="dropdown-options">
                    <?php foreach ($chip_tanggal as $idx => $chip):
                        $tgl_opt = date('d', strtotime($chip['tgl_service'])) . ' '
                                . $bulan[date('m', strtotime($chip['tgl_service']))] . ' '
                                . date('Y', strtotime($chip['tgl_service']));
                    ?>
                    <div class="movas-select-option <?= $idx === 0 ? 'active' : '' ?>"
                        data-sesi-id="<?= $chip['sesi_id'] ?>"
                        data-catatan="<?= htmlspecialchars($chip['catatan'] ?? '') ?>"
                        data-tgl="<?= $tgl_opt ?>"
                        data-jenis="<?= htmlspecialchars($chip['jenis_service'] ?? '') ?>"
                        onclick="selectTanggal(this)">
                        <div class="movas-option-dot <?= $idx === 0 ? 'active' : '' ?>"></div>
                        <div>
                            <div class="movas-option-tgl"><?= $tgl_opt ?></div>
                            <?php if (!empty($chip['jenis_service'])): ?>
                            <div class="movas-option-sub"><?= $chip['jenis_service'] ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($idx === 0): ?>
                        <span class="movas-option-badge">Terkini</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <!-- Catatan — tampil di bawah chip -->
        <?php
        $catatan_awal = $chip_tanggal[0]['catatan'] ?? '';
        $catatan_awal = trim($catatan_awal);
        $show_catatan = !empty($catatan_awal) && $catatan_awal !== '-';
        ?>
        <div id="catatan-wrapper" style="margin-top:10px; <?= !$show_catatan ? 'display:none;' : '' ?>">
            <div style="display:flex; align-items:flex-start; gap:8px;
                        background:rgba(0,180,216,0.07);
                        border-left:3px solid var(--movas-cyan);
                        border-radius:0 8px 8px 0;
                        padding:10px 12px;">
                <i class="fas fa-comment-alt"
                style="color:var(--movas-cyan); font-size:12px; margin-top:2px; flex-shrink:0;"></i>
                <div>
                    <div style="font-size:10px; color:var(--movas-text3);
                                margin-bottom:3px; text-transform:uppercase;
                                letter-spacing:0.5px;">Catatan Service</div>
                    <div id="catatan-text" style="font-size:12px; color:var(--movas-text2); line-height:1.5;">
                        <?= htmlspecialchars($catatan_awal) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="movas-card-body" id="kondisi-wrapper">
        <?php if (empty($kondisi) || !$sesi_aktif): ?>
        <div style="text-align:center; padding:30px; color:var(--movas-text3);">
            <i class="fas fa-clipboard" style="font-size:32px; opacity:0.3; margin-bottom:10px; display:block;"></i>
            Data kondisi kendaraan belum diisi.
        </div>
        <?php else: ?>
        <?php
        $kategori_config = [
            'mesin'        => ['Mesin',             'fa-cogs',       $kondisi['mesin']],
            'chassis'      => ['Chassis & Bodi',    'fa-car',        $kondisi['chassis']],
            'kelistrikan'  => ['Kelistrikan',       'fa-bolt',       $kondisi['kelistrikan']],
            'kelistrikan2' => ['AC, Central Lock & Accu','fa-snowflake',$kondisi['kelistrikan2']],
            'safety'       => ['Safety Belt & Jok', 'fa-shield-alt', $kondisi['safety']],
            'eksterior'    => ['Eksterior',         'fa-car-side',   $kondisi['eksterior']],
            'perlengkapan' => ['Perlengkapan',      'fa-toolbox',    $kondisi['perlengkapan']],
        ];

        $label_map = [
            'tali_kipas'=>'Tali Kipas','oli_mesin'=>'Oli Mesin','filter_oli'=>'Filter Oli',
            'slang_radiator'=>'Slang Radiator','air_radiator'=>'Air Radiator','tune_up'=>'Tune Up',
            'busi'=>'Busi','baterai_mesin'=>'Baterai','filter_udara'=>'Filter Udara','filter_bbm'=>'Filter BBM',
            'oli_transmisi'=>'Oli Transmisi','oli_differential'=>'Oli Differential','kabel_kopling'=>'Kabel Kopling',
            'master_kopling'=>'Master Kopling','sistem_rem'=>'Sistem Rem','master_rem'=>'Master Rem',
            'brake_pad'=>'Brake Pad','disc_brake'=>'Disc Brake','brake_shoe'=>'Brake Shoe','tromol'=>'Tromol',
            'power_steering'=>'Power Steering','boot_drive_shaft'=>'Boot Drive Shaft','ball_joint'=>'Ball Joint',
            'suspensi_depan'=>'Suspensi Depan','suspensi_belakang'=>'Suspensi Belakang',
            'motor_extra_fan'=>'Motor Extra Fan','lampu_sein_depan_kanan'=>'Lampu Sein Depan Kanan',
            'lampu_sein_depan_kiri'=>'Lampu Sein Depan Kiri','lampu_sein_belakang_kanan'=>'Lampu Sein Belakang Kanan',
            'lampu_sein_belakang_kiri'=>'Lampu Sein Belakang Kiri','lampu_kecil_depan_kanan'=>'Lampu Kecil Depan Kanan',
            'lampu_kecil_depan_kiri'=>'Lampu Kecil Depan Kiri','lampu_kecil_belakang_kanan'=>'Lampu Kecil Belakang Kanan',
            'lampu_kecil_belakang_kiri'=>'Lampu Kecil Belakang Kiri','lampu_besar_pendek_kanan'=>'Lampu Besar Pendek Kanan',
            'lampu_besar_pendek_kiri'=>'Lampu Besar Pendek Kiri','lampu_besar_jauh_kanan'=>'Lampu Besar Jauh Kanan',
            'lampu_besar_jauh_kiri'=>'Lampu Besar Jauh Kiri','lampu_kabut_kanan'=>'Lampu Kabut Kanan',
            'lampu_kabut_kiri'=>'Lampu Kabut Kiri','lampu_rem_kanan'=>'Lampu Rem Kanan','lampu_rem_kiri'=>'Lampu Rem Kiri',
            'lampu_spoiler'=>'Lampu Spoiler','lampu_plat_no_pol'=>'Lampu Plat No Polisi','lampu_mundur'=>'Lampu Mundur',
            'sensor_parkir'=>'Sensor Parkir','lampu_plafon_depan'=>'Lampu Plafon Depan',
            'lampu_plafon_tengah'=>'Lampu Plafon Tengah','lampu_panel_speedometer'=>'Lampu Panel Speedometer',
            'lampu_hazard'=>'Lampu Hazard','fungsi_panel_wiper'=>'Panel Wiper','klakson'=>'Klakson',
            'fungsi_radio_tape'=>'Radio/Tape','cd_player'=>'CD Player','fungsi_speaker'=>'Speaker',
            'antena'=>'Antena','central_lock_depan_kanan'=>'Central Lock Depan Kanan',
            'central_lock_depan_kiri'=>'Central Lock Depan Kiri','central_lock_belakang_kanan'=>'Central Lock Belakang Kanan',
            'central_lock_belakang_kiri'=>'Central Lock Belakang Kiri','power_window_depan_kanan'=>'Power Window Depan Kanan',
            'power_window_depan_kiri'=>'Power Window Depan Kiri','power_window_belakang_kanan'=>'Power Window Belakang Kanan',
            'power_window_belakang_kiri'=>'Power Window Belakang Kiri','electric_mirror'=>'Electric Mirror',
            'jam'=>'Jam','pemantik'=>'Pemantik','ac_depan'=>'AC Depan','ac_tengah'=>'AC Tengah',
            'filter_cabin'=>'Filter Cabin','asbak'=>'Asbak','karpet'=>'Karpet',
            'accu_tegangan'=>'Tegangan Accu (kondisi mati)','accu_merk'=>'Merk Accu','accu_kondisi'=>'Kondisi Accu',
            'kursi_depan_kanan'=>'Kursi Depan Kanan','kursi_depan_kiri'=>'Kursi Depan Kiri',
            'kursi_tengah_kanan'=>'Kursi Tengah Kanan','kursi_tengah_kiri'=>'Kursi Tengah Kiri',
            'kursi_belakang_kanan'=>'Kursi Belakang Kanan','kursi_belakang_kiri'=>'Kursi Belakang Kiri',
            'penahan_matahari'=>'Penahan Matahari','wiper_blade_depan'=>'Wiper Depan',
            'wiper_blade_belakang'=>'Wiper Belakang','spion_luar_kanan'=>'Spion Luar Kanan',
            'spion_luar_kiri'=>'Spion Luar Kiri','spion_dalam'=>'Spion Dalam','mirror_lamp'=>'Mirror Lamp',
            'mudguard_depan_kanan'=>'Mudguard Depan Kanan','mudguard_depan_kiri'=>'Mudguard Depan Kiri',
            'mudguard_belakang_kanan'=>'Mudguard Belakang Kanan','mudguard_belakang_kiri'=>'Mudguard Belakang Kiri',
            'foot_step'=>'Foot Step','liner_fender_kanan'=>'Liner Fender Kanan',
            'liner_fender_kiri'=>'Liner Fender Kiri','toolkit_tang'=>'Tang','toolkit_obeng'=>'Obeng',
            'toolkit_kunci_ring'=>'Kunci Ring','toolkit_kunci_pas'=>'Kunci Pas','toolkit_kunci_busi'=>'Kunci Busi',
            'toolkit_handle_towing'=>'Handle Towing','toolkit_kunci_roda'=>'Kunci Roda',
            'toolkit_dongkrak'=>'Dongkrak','segitiga_pengaman'=>'Segitiga Pengaman','kotak_p3k'=>'Kotak P3K',
            'kunci_kontak'=>'Kunci Kontak','remot'=>'Remot','stnk'=>'STNK',
            'buku_kir'=>'Buku KIR','buku_servis'=>'Buku Servis',
        ];

        $skip_keys = ['id','sesi_id','created_at','updated_at'];

        foreach ($kategori_config as $key => [$label, $icon, $data_kat]):
            if (empty($data_kat)) continue;
        ?>
        <div class="kondisi-accordion">
            <div class="accordion-header" onclick="toggleAccordion(this)">
                <div class="accordion-header-left">
                    <i class="fas <?= $icon ?>" style="color:var(--movas-cyan); width:16px;"></i>
                    <?= $label ?>
                </div>
                <i class="fas fa-chevron-down" style="color:var(--movas-text3); font-size:11px; transition:transform 0.2s;"></i>
            </div>
            <div class="accordion-body">
                <?php // Di dalam foreach $data_kat — tambah kondisi khusus accu_tegangan
                foreach ($data_kat as $col => $val):
                    if (in_array($col, $skip_keys) || $val === null) continue;
                    $lbl = $label_map[$col] ?? $col;
                ?>
                <div class="kondisi-row">
                    <div class="kondisi-label"><?= $lbl ?></div>
                    <div>
                        <?php if ($col === 'accu_tegangan' && !empty($val)):
                            $color = accu_color($val);
                        ?>
                            <span style="color:<?= $color ?>; font-weight:600;">
                                <?= $val ?> V
                            </span>
                        <?php else: ?>
                            <?= kondisi_badge($val) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (!empty($kondisi['ban'])): ?>
        <div class="kondisi-accordion">
            <div class="accordion-header" onclick="toggleAccordion(this)">
                <div class="accordion-header-left">
                    <i class="fas fa-circle-notch" style="color:var(--movas-cyan); width:16px;"></i>
                    Ban
                </div>
                <i class="fas fa-chevron-down" style="color:var(--movas-text3); font-size:11px; transition:transform 0.2s;"></i>
            </div>
            <div class="accordion-body">
                <?php foreach ($kondisi['ban'] as $ban):
                    // Skip jika semua field kosong
                    if (empty($ban['ukuran']) && empty($ban['merk']) &&
                        empty($ban['kondisi_pct']) && empty($ban['status'])) continue;
                ?>
                <div style="padding:8px 0; border-bottom:1px solid var(--movas-border);">
                    <div style="font-size:12px; font-weight:600; margin-bottom:5px;">
                        <?= $ban['posisi'] ?>
                    </div>
                    <div style="display:flex; gap:16px; font-size:11px; flex-wrap:wrap;">
                        <?php if ($ban['ukuran']): ?>
                        <span style="color:var(--movas-text2);">Ukuran: <strong><?= $ban['ukuran'] ?></strong></span>
                        <?php endif; ?>
                        <?php if ($ban['merk']): ?>
                        <span style="color:var(--movas-text2);">Merk: <strong><?= $ban['merk'] ?></strong></span>
                        <?php endif; ?>
                        <?php if ($ban['kondisi_pct']): ?>
                        <span style="color:var(--movas-text2);">Kondisi:
                            <strong style="color:<?= $ban['kondisi_pct'] >= 60 ? 'var(--movas-safe)' : ($ban['kondisi_pct'] >= 30 ? 'var(--movas-warn)' : 'var(--movas-danger)') ?>">
                                <?= $ban['kondisi_pct'] ?>%
                            </strong>
                        </span>
                        <?php endif; ?>
                        <?php if ($ban['status']): ?>
                        <span><?= kondisi_badge($ban['status']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($reminder_kendaraan)):
    $r     = $reminder_kendaraan;
    $sisa  = intval($r['sisa_hari']);
    $bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
              '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
              '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
    $tgl_fmt = date('d', strtotime($r['tgl_reminder'])) . ' '
             . $bulan[date('m', strtotime($r['tgl_reminder']))] . ' '
             . date('Y', strtotime($r['tgl_reminder']));

    $color = $sisa <= 7 ? 'var(--movas-danger)' : ($sisa <= 14 ? 'var(--movas-warn)' : 'var(--movas-cyan)');
    $color_bg = $sisa <= 7 ? 'rgba(231,74,59,0.08)' : ($sisa <= 14 ? 'rgba(246,194,62,0.08)' : 'rgba(0,180,216,0.07)');

    // Nomor WA cabang
    $wa_cabang = preg_replace('/[^0-9]/', '', $r['cabang_telp'] ?? '');
    if (substr($wa_cabang, 0, 1) === '0') {
        $wa_cabang = '62' . substr($wa_cabang, 1);
    }
    $nama_cabang = str_replace('MAC ', '', $r['nama_cabang'] ?? 'MAC');
?>
<div class="movas-card mb-4" style="border-left:3px solid <?= $color ?>;">
    <div class="movas-card-body">
        <div style="display:flex; align-items:center;
                    justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; border-radius:10px;
                            background:<?= $color_bg ?>; display:flex;
                            align-items:center; justify-content:center;
                            flex-shrink:0;">
                    <i class="fas fa-bell" style="color:<?= $color ?>; font-size:16px;"></i>
                </div>
                <div>
                    <div style="font-size:11px; color:var(--movas-text3);
                                text-transform:uppercase; letter-spacing:0.5px;
                                margin-bottom:2px;">
                        Reminder Service Berikutnya
                    </div>
                    <div style="font-size:14px; font-weight:700; color:var(--movas-text);">
                        <?= $tgl_fmt ?>
                    </div>
                    <div style="font-size:12px; color:<?= $color ?>; margin-top:2px;">
                        <?= $sisa ?> hari menuju jadwal service
                    </div>
                </div>
            </div>

            <?php if (!empty($wa_cabang)): ?>
            <!-- <?php 
            $wa_text = 'Halo%2C%20saya%20ingin%20melakukan%20booking%20service%20berkala%20untuk%20kendaraan%20saya%20dengan%20nomor%20polisi%20*'
                . urlencode($nopol)
                . '*.%20Jadwal%20reminder%20service%20saya%20adalah%20*'
                . urlencode($tgl_fmt)
                . '*.%20Mohon%20bantuannya%2C%20terima%20kasih.'; 
                ?> -->

            <!-- <a href="https://wa.me/<?= $wa_cabang ?>?text=<?= $wa_text ?>"
               target="_blank"
               style="display:inline-flex; align-items:center; gap:6px;
                      background:linear-gradient(135deg, #25d366, #128c7e);
                      color:#fff; text-decoration:none;
                      padding:8px 14px; border-radius:10px;
                      font-size:12px; font-weight:600;
                      white-space:nowrap; flex-shrink:0;">
                <i class="fab fa-whatsapp" style="font-size:16px;"></i>
                Booking Service
            </a> -->
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── SECTION FOTO KONDISI ─────────────────────────────── -->
<div class="movas-card mb-4" id="foto-gallery-card">
    <div class="movas-card-header">
        <span>
            <i class="fas fa-camera" style="color:var(--movas-cyan);"></i>
            Foto Kondisi
            <span style="font-size:10px; background:rgba(246,194,62,0.15);
                        color:#f6c23e; padding:2px 7px; border-radius:10px;
                        margin-left:6px; font-weight:700;">PRO</span>
        </span>
        <?php if ($is_pro): ?>
        <span id="foto-count-label" style="font-size:11px; color:var(--movas-text3);">
            <?= count($foto_sesi) ?> foto
        </span>
        <?php endif; ?>
    </div>
    <div class="movas-card-body" id="foto-gallery-wrapper">
        <?php if ($is_pro): ?>
            <?php if (empty($foto_sesi)): ?>
            <div style="text-align:center; padding:20px; color:var(--movas-text3);">
                <i class="fas fa-camera" style="font-size:28px; opacity:0.3; display:block; margin-bottom:8px;"></i>
                Belum ada foto kondisi untuk service ini.
            </div>
            <?php else: ?>
            <?php echo renderFotoGallery($foto_sesi); ?>
            <?php endif; ?>
        <?php else: ?>
            <!-- Tampilan untuk FREE -->
            <div style="text-align:center; padding:24px 16px;">
                <i class="fas fa-lock" style="font-size:32px; color:var(--movas-text3);
                   opacity:0.4; display:block; margin-bottom:12px;"></i>
                <div style="font-size:13px; font-weight:600; color:var(--movas-text);
                            margin-bottom:6px;">
                    Foto Kondisi Kendaraan
                </div>
                <div style="font-size:12px; color:var(--movas-text2); margin-bottom:16px; line-height:1.6;">
                    Upgrade ke <strong style="color:#f6c23e;">PRO</strong> untuk melihat
                    foto kondisi komponen kendaraan dari setiap service.
                </div>
                <a href="https://wa.me/6282324001500?text=Halo%20MAC%2C%20saya%20ingin%20upgrade%20MOVAS%20ke%20PRO"
                   target="_blank"
                   style="display:inline-flex; align-items:center; gap:6px;
                          background:linear-gradient(135deg, #f6c23e, #e0a800);
                          color:#000; text-decoration:none;
                          padding:8px 18px; border-radius:10px;
                          font-size:12px; font-weight:700;">
                    <i class="fab fa-whatsapp" style="font-size:16px; color:#25d366;"></i>
                    Upgrade ke PRO
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- SECTION 2: REKOMENDASI -->
<?php if (!empty($rekomendasi)): ?>
<div class="movas-card mb-4">
    <div class="movas-card-header">
        <span><i class="fas fa-exclamation-triangle" style="color:var(--movas-warn);"></i> Rekomendasi</span>
        <span style="font-size:11px; color:var(--movas-text3);"><?= count($rekomendasi) ?> item</span>
    </div>
    <div class="movas-card-body">
        <?php foreach ($rekomendasi as $rek): ?>
        <div class="rekomen-item <?= $rek['level'] ?>">
            <div>
                <div style="font-weight:600; margin-bottom:2px;"><?= $rek['label'] ?></div>
                <div style="font-size:11px; color:var(--movas-text2);"><?= $rek['kondisi'] ?></div>
            </div>
            <span class="kondisi-badge <?= $rek['level'] === 'kritis' ? 'critical' : 'attention' ?>">
                <?= $rek['level'] === 'kritis' ? 'Segera' : 'Perhatikan' ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- SECTION 3: RIWAYAT SERVICE -->
<div class="movas-card mb-4">
    <div class="movas-card-header">
        <span><i class="fas fa-history" style="color:var(--movas-cyan);"></i> Riwayat Service</span>
        <?php if (!$is_pro): ?>
        <span style="font-size:11px; color:var(--movas-warn);">Maks 6 (FREE)</span>
        <?php endif; ?>
    </div>
    <div class="movas-card-body">
        <?php if (empty($riwayat)): ?>
        <div style="text-align:center; padding:20px; color:var(--movas-text3);">
            Belum ada riwayat service.
        </div>
        <?php else: ?>
        <div style="padding-top:8px;">
            <?php foreach ($riwayat as $idx => $inv):
                $bulan = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr',
                          '05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu',
                          '09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
                $tgl = date('d', strtotime($inv['awal_service'])) . ' '
                     . $bulan[date('m', strtotime($inv['awal_service']))] . ' '
                     . date('Y', strtotime($inv['awal_service']));
                $lokasi_service = str_ireplace('MAC ', '', $inv['lokasi_service'] ?? '');
            ?>
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-card">
                    <div class="timeline-header" onclick="toggleTimeline(this, <?= $idx ?>)">
                        <div>
                            <div style="font-size:13px; font-weight:600;"><?= $tgl ?></div>
                            <div style="font-size:11px; color:var(--movas-text2); margin-top:2px;">
                                <?= htmlspecialchars($lokasi_service) ?>
                                <?= $inv['km'] ? ' · ' . number_format($inv['km'], 0, ',', '.') . ' KM' : '' ?>
                                <?= $inv['jenis_service'] ? ' · ' . $inv['jenis_service'] : '' ?>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="font-size:13px; font-weight:600;">
                                Rp <?= number_format($inv['sub_total'], 0, ',', '.') ?>
                            </span>
                            <!-- <a href="<?= site_url($token . '/download_invoice/' . $inv['id']) ?>"
                               onclick="event.stopPropagation();"
                               style="color:var(--movas-text3); font-size:12px; text-decoration:none;"
                               title="Download PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a> -->
                            <i class="fas fa-chevron-down"
                               style="color:var(--movas-text3); font-size:11px; transition:transform 0.2s;"
                               id="chevron-timeline-<?= $idx ?>"></i>
                        </div>
                    </div>

                    <div class="timeline-detail" id="detail-<?= $idx ?>">
                        <?php if (empty($inv['detail'])): ?>
                        <div style="color:var(--movas-text3); font-size:12px;">Tidak ada detail.</div>
                        <?php else: ?>

                        <?php
                        $jasa_list   = array_filter($inv['detail'], fn($d) => $d['tipe_item'] === 'Jasa');
                        $barang_list = array_filter($inv['detail'], fn($d) => $d['tipe_item'] !== 'Jasa');
                        ?>

                        <?php if (!empty($jasa_list)): ?>
                        <div style="font-size:11px; color:var(--movas-text3); margin-bottom:6px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Jasa</div>
                        <?php foreach ($jasa_list as $d): ?>
                        <div class="detail-item">
                            <div class="detail-label">
                                <span class="tipe-badge jasa">Jasa</span>
                                &nbsp;<?= htmlspecialchars($d['item']) ?>
                            </div>
                            <div class="detail-value">
                                <?= number_format($d['qty'], 0, ',', '.') ?> &times;
                                Rp <?= number_format($d['biaya'], 0, ',', '.') ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($barang_list)): ?>
                        <div style="font-size:11px; color:var(--movas-text3); margin:10px 0 6px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Part, Pelumas & Bahan</div>
                        <?php foreach ($barang_list as $d): ?>
                        <div class="detail-item">
                            <div class="detail-label">
                                <span class="tipe-badge barang"><?= $d['tipe_item'] ?></span>
                                &nbsp;<?= htmlspecialchars($d['item']) ?>
                            </div>
                            <div class="detail-value">
                                <?= number_format($d['qty'], 0, ',', '.') ?> &times;
                                Rp <?= number_format($d['biaya'], 0, ',', '.') ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (!$is_pro && count($riwayat) >= 6): ?>
            <div class="upgrade-banner" style="margin-top:16px;">
                <div class="upgrade-banner-text">
                    <strong>⭐ Upgrade ke PRO</strong><br>
                    <span style="font-size:11px;">Lihat seluruh riwayat service tanpa batas.</span>
                </div>
                <a href="https://wa.me/6287883777711?text=Halo+MAC%2C+saya+<?= urlencode($customer['customer_name']) ?>+ingin+upgrade+MOVAS+ke+PRO"
                   class="btn-upgrade" target="_blank">Upgrade</a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>