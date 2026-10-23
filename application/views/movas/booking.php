<?php
$bulan = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr',
          '05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu',
          '09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
$bulan_panjang = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                  '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                  '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-tools" style="color:var(--movas-cyan);"></i> Booking Service</h1>
    <p>Buat jadwal service kendaraan Anda</p>
</div>

<!-- ── TAB ─────────────────────────────────────────────────── -->
<div style="display:flex; gap:8px; margin-bottom:20px;">
    <button id="tab-booking-baru" onclick="switchBookingTab('baru')"
            style="flex:1; padding:10px; border-radius:10px; border:none;
                   background:var(--movas-accent); color:#fff;
                   font-size:13px; font-weight:600; cursor:pointer;">
        <i class="fas fa-plus"></i> Booking
    </button>
    <button id="tab-booking-aktif" onclick="switchBookingTab('aktif')"
            style="flex:1; padding:10px; border-radius:10px;
                   border:1px solid var(--movas-border);
                   background:var(--movas-surface); color:var(--movas-text);
                   font-size:13px; font-weight:600; cursor:pointer;">
        <i class="fas fa-clock"></i> Aktif
        <?php if (!empty($booking_aktif)): ?>
        <span style="background:var(--movas-cyan); color:#fff; padding:1px 7px;
                     border-radius:10px; font-size:10px; margin-left:4px;">
            <?= count($booking_aktif) ?>
        </span>
        <?php endif; ?>
    </button>
    <button id="tab-booking-riwayat" onclick="switchBookingTab('riwayat')"
            style="flex:1; padding:10px; border-radius:10px;
                   border:1px solid var(--movas-border);
                   background:var(--movas-surface); color:var(--movas-text);
                   font-size:13px; font-weight:600; cursor:pointer;">
        <i class="fas fa-history"></i> Riwayat
    </button>
</div>

<!-- ── TAB BOOKING BARU ────────────────────────────────────── -->
<div id="panel-booking-baru">

    <!-- Pilih Kendaraan -->
    <div class="movas-card mb-4">
        <div class="movas-card-header">
            <span><i class="fas fa-car" style="color:var(--movas-cyan);"></i> Pilih Kendaraan <span style="color:var(--movas-danger);">*</span></span>
            <span style="font-size:11px; color:var(--movas-text3);">Bisa pilih lebih dari 1</span>
        </div>
        <?php if (count($kendaraan_list) > 6): ?>
        <div style="padding:12px 18px; border-bottom:1px solid var(--movas-border);
                    position:relative;">
            <input type="text" id="search-kendaraan-booking"
                placeholder="Cari nopol atau tipe kendaraan..."
                oninput="filterKendaraanBooking(this.value)"
                style="width:100%; background:var(--movas-surface);
                        border:1px solid var(--movas-border);
                        color:var(--movas-text); padding:8px 12px 8px 36px;
                        border-radius:8px; font-size:13px; outline:none;
                        transition:border-color 0.15s;"
                onfocus="this.style.borderColor='var(--movas-cyan)'"
                onblur="this.style.borderColor='var(--movas-border)'">
            <i class="fas fa-search"
            style="position:absolute; left:28px; top:50%;
                    transform:translateY(-50%);
                    color:var(--movas-text3); font-size:12px;
                    pointer-events:none;"></i>
        </div>
        <?php endif; ?>
        <div class="movas-card-body" style="padding:0;">
            <?php if (empty($kendaraan_list)): ?>
            <div style="text-align:center; padding:24px; color:var(--movas-text3);">
                <i class="fas fa-car" style="font-size:28px; opacity:0.3; display:block; margin-bottom:8px;"></i>
                Belum ada kendaraan terdaftar.
            </div>
            <?php else: ?>
            <?php foreach ($kendaraan_list as $idx => $k): ?>
            <div class="kendaraan-checkbox-row" id="row-<?= $idx ?>"
                 onclick="toggleKendaraan(<?= $idx ?>)"
                 data-search="<?= strtolower($k['nopol'] . ' ' . ($k['tipe'] ?: $k['jenis_kendaraan'])) ?>"
                 style="display:flex; align-items:center; gap:12px;
                        padding:14px 18px; cursor:pointer;
                        border-bottom:1px solid var(--movas-border);
                        transition:background 0.15s;">

                <!-- Checkbox custom -->
                <div class="kendaraan-checkbox" id="cb-<?= $idx ?>"
                     style="width:22px; height:22px; border-radius:6px;
                            border:2px solid var(--movas-border);
                            display:flex; align-items:center; justify-content:center;
                            flex-shrink:0; transition:all 0.15s;">
                </div>

                <!-- Icon -->
                <div style="width:36px; height:36px; background:var(--movas-surface2);
                            border-radius:8px; display:flex; align-items:center;
                            justify-content:center; flex-shrink:0;">
                    <?php if (strtolower($k['jenis_kendaraan']) === 'motor'): ?>
                    <i class="fas fa-motorcycle" style="color:var(--movas-cyan);"></i>
                    <?php else: ?>
                    <i class="fas fa-car" style="color:var(--movas-cyan);"></i>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div style="flex:1; min-width:0;">
                    <div style="font-size:14px; font-weight:700;">
                        <?= htmlspecialchars($k['nopol']) ?>
                    </div>
                    <div style="font-size:11px; color:var(--movas-text2);">
                        <?= htmlspecialchars($k['tipe'] ?: $k['jenis_kendaraan']) ?>
                    </div>
                </div>

                <!-- KM terakhir -->
                <div style="text-align:right; flex-shrink:0;">
                    <div style="font-size:11px; color:var(--movas-text3);">KM Terakhir</div>
                    <div style="font-size:12px; font-weight:600; color:var(--movas-text2);">
                        <?= $k['km_terakhir'] ? number_format($k['km_terakhir'], 0, ',', '.') : '—' ?>
                    </div>
                </div>
            </div>

            <!-- Input KM + Keluhan (hidden, muncul saat dipilih) -->
            <div id="detail-<?= $idx ?>" style="display:none;
                background:var(--movas-surface2); padding:14px 18px;
                border-bottom:1px solid var(--movas-border);">
                <input type="hidden" name="kendaraan[<?= $idx ?>][nopol]"
                    value="<?= htmlspecialchars($k['nopol']) ?>">

                <!-- ── Hidden KM terakhir ──────────────────────────── -->
                <input type="hidden" id="km-last-<?= $idx ?>"
                    value="<?= intval($k['km_terakhir']) ?>">

                <div style="margin-bottom:10px;">
                    <label style="font-size:11px; color:var(--movas-text3);
                                display:block; margin-bottom:4px;">
                        KM Saat Ini <span style="color:var(--movas-danger);">*</span>
                        <?php if ($k['km_terakhir']): ?>
                        <span style="color:var(--movas-text3); font-weight:400;">
                            (min. <?= number_format($k['km_terakhir'], 0, ',', '.') ?> KM)
                        </span>
                        <?php endif; ?>
                    </label>
                    <input type="text"
                        id="km-<?= $idx ?>"
                        placeholder="Masukkan KM saat ini"
                        oninput="formatKM(this)"
                        style="width:100%; background:var(--movas-surface);
                                border:1px solid var(--movas-border);
                                color:var(--movas-text); padding:8px 12px;
                                border-radius:8px; font-size:13px; outline:none;">
                    <input type="hidden" id="km-raw-<?= $idx ?>">
                </div>

                <div>
                    <label style="font-size:11px; color:var(--movas-text3);
                                display:block; margin-bottom:4px;">
                        Keluhan Service <span style="color:var(--movas-danger);">*</span>
                    </label>
                    <textarea id="keluhan-<?= $idx ?>"
                            placeholder="Ceritakan keluhan kendaraan Anda..."
                            rows="2"
                            style="width:100%; background:var(--movas-surface);
                                    border:1px solid var(--movas-border);
                                    color:var(--movas-text); padding:8px 12px;
                                    border-radius:8px; font-size:13px; outline:none;
                                    resize:none; font-family:'Inter',sans-serif;"></textarea>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
            <!-- ── TAMBAH KENDARAAN BARU ──────────────────────────────── -->
            <div style="padding:12px 18px; border-top:1px solid var(--movas-border);">
                <button type="button" onclick="toggleFormUnitBaru()"
                        id="btn-tambah-unit-baru"
                        style="width:100%; padding:10px; border-radius:10px;
                            background:transparent; border:1px dashed var(--movas-cyan);
                            color:var(--movas-cyan); font-size:13px; font-weight:600;
                            cursor:pointer; transition:all 0.15s;">
                    <i class="fas fa-plus-circle"></i> Tambah Kendaraan Baru
                </button>
            </div>

            <!-- Form unit baru — hidden by default -->
            <div id="form-unit-baru-wrapper" style="display:none;">
                <div id="form-unit-baru-list"></div>
                <div style="padding:8px 18px 14px;">
                    <button type="button" onclick="addUnitBaru()"
                            style="width:100%; padding:8px; border-radius:8px;
                                background:transparent; border:1px dashed var(--movas-border);
                                color:var(--movas-text2); font-size:12px; cursor:pointer;">
                        <i class="fas fa-plus"></i> Tambah Unit Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Booking -->
    <div class="movas-card mb-4">
        <div class="movas-card-header">
            <span><i class="fas fa-calendar-alt" style="color:var(--movas-cyan);"></i> Detail Booking</span>
        </div>
        <div class="movas-card-body">

            <!-- Tanggal -->
            <div style="margin-bottom:14px;">
                <label style="font-size:12px; color:var(--movas-text2); display:block; margin-bottom:6px;">
                    Tanggal Service <span style="color:var(--movas-danger);">*</span>
                </label>
                <input type="text" id="tgl_booking"
                    placeholder="Pilih tanggal service..."
                    style="width:100%; background:var(--movas-surface2);
                            border:1px solid var(--movas-border);
                            color:var(--movas-text); padding:10px 12px;
                            border-radius:10px; font-size:13px; outline:none;
                            cursor:pointer;">
            </div>

            <!-- Pilih Waktu -->
            <div style="margin-bottom:14px;">
                <label style="font-size:12px; color:var(--movas-text2); display:block; margin-bottom:6px;">
                    Pilih Waktu <span style="color:var(--movas-danger);">*</span>
                </label>
                <div class="movas-select-wrapper" id="waktu-dropdown">
                    <input type="hidden" id="waktu_catatan">

                    <div class="movas-select-trigger" id="waktu-trigger"
                        onclick="toggleWaktuDropdown()"
                        style="opacity:0.5; pointer-events:none;">
                        <div class="movas-select-selected">
                            <div class="movas-select-dot"></div>
                            <div>
                                <div class="movas-select-tgl" id="waktu-selected-text">
                                    Pilih tanggal terlebih dahulu
                                </div>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down movas-select-arrow"></i>
                    </div>

                    <div class="movas-select-options" id="waktu-options"></div>
                </div>
            </div>

            <!-- Lokasi -->
            <div style="margin-bottom:14px;">
                <label style="font-size:12px; color:var(--movas-text2); display:block; margin-bottom:6px;">
                    Lokasi Service <span style="color:var(--movas-danger);">*</span>
                </label>
                <div style="display:flex; gap:8px;">
                    <button type="button" id="btn-bengkel" onclick="setLokasi('bengkel')"
                            style="flex:1; padding:10px; border-radius:10px;
                                   background:var(--movas-accent); color:#fff;
                                   border:none; font-size:13px; font-weight:600; cursor:pointer;">
                        <i class="fas fa-wrench"></i> Ke Bengkel
                    </button>
                    <button type="button" id="btn-home" onclick="setLokasi('home_service')"
                            style="flex:1; padding:10px; border-radius:10px;
                                   background:var(--movas-surface2);
                                   color:var(--movas-text);
                                   border:1px solid var(--movas-border);
                                   font-size:13px; font-weight:600; cursor:pointer;">
                        <i class="fas fa-home"></i> Home Service
                    </button>
                </div>
                <input type="hidden" id="lokasi_service" value="bengkel">
            </div>

            <!-- Alamat (muncul jika home service) -->
            <div id="row-alamat" style="display:none; margin-bottom:14px;">
                <label style="font-size:12px; color:var(--movas-text2); display:block; margin-bottom:6px;">
                    Alamat Service <span style="color:var(--movas-danger);">*</span>
                </label>
                <textarea id="alamat_service" rows="3"
                          placeholder="Masukkan alamat lengkap..."
                          style="width:100%; background:var(--movas-surface2);
                                 border:1px solid var(--movas-border);
                                 color:var(--movas-text); padding:10px 12px;
                                 border-radius:10px; font-size:13px; outline:none;
                                 resize:none; font-family:'Inter',sans-serif;"><?= htmlspecialchars($customer_address) ?></textarea>
            </div>

            <!-- Cabang Tujuan — Custom Dropdown -->
            <div style="margin-bottom:14px;">
                <label style="font-size:12px; color:var(--movas-text2); display:block; margin-bottom:6px;">
                    Cabang Tujuan <span style="color:var(--movas-danger);">*</span>
                </label>
                <div class="movas-select-wrapper" id="cabang-dropdown">
                    <input type="hidden" id="cabang_nama" value="">

                    <!-- Trigger -->
                    <div class="movas-select-trigger" onclick="toggleCabangDropdown()">
                        <div class="movas-select-selected">
                            <div class="movas-select-dot"></div>
                            <div>
                                <div class="movas-select-tgl" id="cabang-selected-text" style="position: relative; top: 1.5px">— Pilih Cabang —</div>
                                <div class="movas-select-sub" id="cabang-selected-alamat"></div>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down movas-select-arrow"></i>
                    </div>

                    <!-- Options -->
                    <div class="movas-select-options" id="cabang-options">
                        <?php foreach ($cabang_list as $cab): ?>
                        <div class="movas-select-option <?= $cab['id'] == $cabang_default_id ? 'active' : '' ?>"
                            data-value="<?= htmlspecialchars($cab['nama']) ?>"
                            data-alamat="<?= htmlspecialchars($cab['alamat']) ?>"
                            data-id="<?= $cab['id'] ?>"
                            onclick="selectCabang(this)">
                            <div class="movas-option-dot <?= $cab['id'] == $cabang_default_id ? 'active' : '' ?>"></div>
                            <div>
                                <div class="movas-option-tgl"><?= htmlspecialchars($cab['nama']) ?></div>
                                <div class="movas-option-sub"><?= htmlspecialchars($cab['alamat']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Catatan tambahan -->
            <div style="margin-bottom:14px;">
                <label style="font-size:12px; color:var(--movas-text2); display:block; margin-bottom:6px;">
                    Catatan Tambahan <span style="color:var(--movas-text3);">(opsional)</span>
                </label>
                <textarea id="catatan" rows="2"
                          placeholder="Catatan lain untuk tim MAC..."
                          style="width:100%; background:var(--movas-surface2);
                                 border:1px solid var(--movas-border);
                                 color:var(--movas-text); padding:10px 12px;
                                 border-radius:10px; font-size:13px; outline:none;
                                 resize:none; font-family:'Inter',sans-serif;"></textarea>
            </div>

        </div>
    </div>

    <!-- Tombol Submit -->
    <button id="btn-submit-booking" onclick="submitBooking()"
            style="width:100%; padding:14px; border:none; border-radius:12px;
                   background:linear-gradient(135deg, #1a2d7c, #00b4d8);
                   color:#fff; font-size:14px; font-weight:700; cursor:pointer;
                   transition:opacity 0.2s;">
        <i class="fas fa-tools"></i> Kirim Booking Service
    </button>

</div>

<!-- ── TAB BOOKING AKTIF ────────────────────────────────────── -->
<div id="panel-booking-aktif" style="display:none;">
    <?php if (empty($booking_aktif)): ?>
    <div style="text-align:center; padding:40px 20px; color:var(--movas-text3);">
        <i class="fas fa-calendar-check" style="font-size:40px; opacity:0.3;
           display:block; margin-bottom:12px;"></i>
        <div style="font-size:14px; font-weight:600; margin-bottom:6px;">
            Tidak Ada Booking Aktif
        </div>
        <div style="font-size:12px;">Buat booking service baru untuk kendaraan Anda.</div>
    </div>
    <?php else: ?>
    <?php foreach ($booking_aktif as $b):
        $tgl_fmt = date('d', strtotime($b['tgl_booking'])) . ' '
                 . $bulan_panjang[date('m', strtotime($b['tgl_booking']))] . ' '
                 . date('Y', strtotime($b['tgl_booking']));
        $status_label = [
            'pending'      => ['label' => 'Menunggu Konfirmasi', 'color' => '#f6c23e'],
            'dikonfirmasi' => ['label' => 'Dikonfirmasi',        'color' => '#1cc88a'],
        ];
        $sl = $status_label[$b['status']] ?? ['label' => $b['status'], 'color' => '#aaa'];
    ?>
    <div class="movas-card mb-3">
        <div class="movas-card-body">

            <!-- Status -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="font-size:11px; font-weight:700; padding:4px 10px;
                             border-radius:20px;
                             background:<?= $sl['color'] ?>22;
                             color:<?= $sl['color'] ?>;">
                    <?= $sl['label'] ?>
                </span>
                <span style="font-size:11px; color:var(--movas-text3);">
                    <?= date('d/m/Y H:i', strtotime($b['created_at'])) ?>
                </span>
            </div>

            <!-- Detail -->
            <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:12px;">
                <div style="display:flex; gap:10px; align-items:center;">
                    <i class="fas fa-calendar-alt" style="color:var(--movas-cyan); width:16px;"></i>
                    <span style="font-size:13px; color:var(--movas-text);"><?= $tgl_fmt ?></span>
                </div>
                <?php if ($b['waktu_catatan']): ?>
                <div style="display:flex; gap:10px; align-items:center;">
                    <i class="fas fa-clock" style="color:var(--movas-cyan); width:16px;"></i>
                    <span style="font-size:13px; color:var(--movas-text);"><?= htmlspecialchars($b['waktu_catatan']) ?></span>
                </div>
                <?php endif; ?>
                <div style="display:flex; gap:10px; align-items:center;">
                    <i class="fas fa-<?= $b['lokasi_service'] === 'home_service' ? 'home' : 'wrench' ?>"
                       style="color:var(--movas-cyan); width:16px;"></i>
                    <span style="font-size:13px; color:var(--movas-text);">
                        <?= $b['lokasi_service'] === 'home_service' ? 'Home Service' : 'Ke Bengkel' ?>
                        <?php if ($b['lokasi_service'] === 'home_service' && $b['alamat_service']): ?>
                        <span style="font-size:11px; color:var(--movas-text3); display:block;">
                            <?= htmlspecialchars($b['alamat_service']) ?>
                        </span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($b['catatan']): ?>
                <div style="display:flex; gap:10px; align-items:flex-start;">
                    <i class="fas fa-sticky-note" style="color:var(--movas-cyan); width:16px; margin-top:2px;"></i>
                    <span style="font-size:13px; color:var(--movas-text);">
                        <?= htmlspecialchars($b['catatan']) ?>
                    </span>
                </div>
                <?php endif; ?>
                <div style="display:flex; gap:10px; align-items:center;">
                    <i class="fas fa-map-marker-alt" style="color:var(--movas-cyan); width:16px;"></i>
                    <span style="font-size:13px; color:var(--movas-text);">
                        <?= htmlspecialchars(str_replace('MAC ', '', $b['nama_cabang'])) ?>
                    </span>
                </div>
            </div>

            <!-- List kendaraan -->
            <div style="background:var(--movas-surface2); border-radius:10px; padding:10px 12px; margin-bottom:12px;">
                <div style="font-size:11px; color:var(--movas-text3); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">
                    Kendaraan
                </div>
                <?php foreach ($b['detail'] as $d): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center;
                                padding:4px 0; border-bottom:1px solid var(--movas-border);">
                        <div>
                            <span style="font-size:13px; font-weight:600;"><?= htmlspecialchars($d['nopol']) ?></span>
                            <?php if (!empty($d['is_new_unit'])): ?>
                            <span style="background:var(--movas-accent); color:#fff; font-size:9px;
                                        font-weight:700; padding:1px 6px; border-radius:8px;
                                        margin-left:6px; letter-spacing:0.5px;">BARU</span>
                            <?php endif; ?>
                            <?php if (!empty($d['tipe'])): ?>
                            <div style="font-size:11px; color:var(--movas-text2);"><?= htmlspecialchars($d['tipe']) ?></div>
                            <?php endif; ?>
                        </div>
                        <span style="font-size:11px; color:var(--movas-text3);">
                            <?= $d['km'] ? number_format($d['km'], 0, ',', '.') . ' KM' : '' ?>
                        </span>
                    </div>
                <?php if ($d['keluhan']): ?>
                <div style="font-size:11px; color:var(--movas-text2); padding:4px 0;">
                    <i class="fas fa-comment-alt" style="color:var(--movas-text3); margin-right:4px;"></i>
                    <?= htmlspecialchars($d['keluhan']) ?>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Tombol cancel hanya jika pending -->
            <?php if ($b['status'] === 'pending'): ?>
            <button onclick="cancelBooking(<?= $b['id'] ?>)"
                    style="width:100%; padding:10px; border-radius:10px;
                           background:transparent; border:1px solid var(--movas-danger);
                           color:var(--movas-danger); font-size:13px; font-weight:600;
                           cursor:pointer;">
                <i class="fas fa-times-circle"></i> Batalkan Booking
            </button>
            <?php endif; ?>

        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ── TAB RIWAYAT ──────────────────────────────────────────── -->
<div id="panel-booking-riwayat" style="display:none;">
    <?php if (empty($riwayat_booking)): ?>
    <div style="text-align:center; padding:40px 20px; color:var(--movas-text3);">
        <i class="fas fa-history" style="font-size:40px; opacity:0.3;
           display:block; margin-bottom:12px;"></i>
        <div style="font-size:14px; font-weight:600; margin-bottom:6px;">
            Belum Ada Riwayat Booking
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($riwayat_booking as $b):
        $tgl_fmt = date('d', strtotime($b['tgl_booking'])) . ' '
                 . $bulan_panjang[date('m', strtotime($b['tgl_booking']))] . ' '
                 . date('Y', strtotime($b['tgl_booking']));
        $status_label = [
            'selesai'    => ['label' => '✓ Selesai',    'color' => '#1cc88a'],
            'dibatalkan' => ['label' => '✗ Dibatalkan', 'color' => '#e74a3b'],
        ];
        $sl = $status_label[$b['status']] ?? ['label' => $b['status'], 'color' => '#aaa'];
    ?>
    <div class="movas-card mb-3">
        <div class="movas-card-body">

            <div style="display:flex; justify-content:space-between;
                        align-items:center; margin-bottom:10px;">
                <span style="font-size:11px; font-weight:700; padding:4px 10px;
                             border-radius:20px;
                             background:<?= $sl['color'] ?>22;
                             color:<?= $sl['color'] ?>;">
                    <?= $sl['label'] ?>
                </span>
                <span style="font-size:11px; color:var(--movas-text3);">
                    <?= $tgl_fmt ?>
                </span>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:12px; color:var(--movas-text2);">
                    <i class="fas fa-map-marker-alt" style="color:var(--movas-text3); margin-right:4px;"></i>
                    <?= htmlspecialchars(str_replace('MAC ', '', $b['nama_cabang'])) ?>
                </div>
                <div style="font-size:12px; color:var(--movas-text2);">
                    <?= count($b['detail']) ?> kendaraan
                </div>
            </div>

            <?php if ($b['status'] === 'dibatalkan' && $b['alasan_batal']): ?>
            <div style="margin-top:8px; font-size:11px; color:var(--movas-danger);">
                <i class="fas fa-info-circle"></i> <?= htmlspecialchars($b['alasan_batal']) ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ── JS ────────────────────────────────────────────────────── -->
<script>
var selectedKendaraan = {};
var lokasiService     = 'bengkel';

window.addEventListener('load', function() {
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#tgl_booking', {
            locale       : 'id',
            minDate      : 'today',
            dateFormat   : 'Y-m-d',
            altInput     : true,
            altFormat    : 'j F Y',
            disableMobile: true,
            disable      : [
                function(date) {
                    return date.getDay() === 0;
                }
            ],
            onReady: function(selectedDates, dateStr, instance) {
                instance.altInput.style.background   = 'var(--movas-surface2)';
                instance.altInput.style.color        = 'var(--movas-text)';
                instance.altInput.style.border       = '1px solid var(--movas-border)';
                instance.altInput.style.borderRadius = '10px';
                instance.altInput.style.padding      = '20px 12px';
                instance.altInput.style.fontSize     = '13px';
                instance.altInput.style.width        = '100%';
                instance.altInput.style.outline      = 'none';
                instance.altInput.style.cursor       = 'pointer';
            },
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    updateWaktuSlots(selectedDates[0].getDay());
                }
            },
        });
    }

    initKendaraanShowMore();
});

// Tutup dropdown jika klik di luar
document.addEventListener('click', function(e) {
    // ── Tutup dropdown cabang ──────────────────────────────
    var wrapper = document.getElementById('cabang-dropdown');
    if (wrapper && !wrapper.contains(e.target)) {
        var trigger = document.querySelector('#cabang-dropdown .movas-select-trigger');
        var options = document.getElementById('cabang-options');
        if (trigger) trigger.classList.remove('open');
        if (options) options.classList.remove('show');
    }

    // ── Tutup dropdown waktu ───────────────────────────────
    var waktuWrapper = document.getElementById('waktu-dropdown');
    if (waktuWrapper && !waktuWrapper.contains(e.target)) {
        var waktuTrigger = document.querySelector('#waktu-dropdown .movas-select-trigger');
        var waktuOptions = document.getElementById('waktu-options');
        if (waktuTrigger) waktuTrigger.classList.remove('open');
        if (waktuOptions) waktuOptions.classList.remove('show');
    }
});

// SEARCH KENDARAAN

function filterKendaraanBooking(val) {
    var keyword = val.toLowerCase().trim().replace(/\s+/g, '');
    var rows    = document.querySelectorAll('.kendaraan-checkbox-row');
    var found   = 0;

    rows.forEach(function(row) {
        var text   = (row.getAttribute('data-search') || '').replace(/\s+/g, '');
        var idx    = row.id.replace('row-', '');
        var detail = document.getElementById('detail-' + idx);

        if (!keyword || text.indexOf(keyword) > -1) {
            row.style.display = 'flex';
            found++;
        } else {
            row.style.display = 'none';
            // Sembunyikan juga detail jika ada
            if (detail) detail.style.display = 'none';
        }
    });
}

// INPUT JAM

function updateWaktuSlots(dayOfWeek) {
    var slots = [];

    if (dayOfWeek >= 1 && dayOfWeek <= 5) {
        slots = [
            '08.00 - 09.00', '09.00 - 10.00', '10.00 - 11.00',
            '11.00 - 12.00', '13.00 - 14.00', '14.00 - 15.00',
            '15.00 - 16.00',
        ];
    } else if (dayOfWeek === 6) {
        slots = [
            '08.00 - 09.00', '09.00 - 10.00', '10.00 - 11.00',
            '11.00 - 12.00', '13.00 - 14.00',
        ];
    }

    // Reset
    document.getElementById('waktu_catatan').value = '';
    document.getElementById('waktu-selected-text').textContent = '— Pilih Waktu —';

    // Enable trigger
    var trigger = document.getElementById('waktu-trigger');
    trigger.style.opacity       = '1';
    trigger.style.pointerEvents = 'auto';

    // Render options
    var html = '';
    slots.forEach(function(slot) {
        html += '<div class="movas-select-option"'
              + ' data-value="' + slot + '"'
              + ' onclick="selectWaktu(this)">'
              + '<div class="movas-option-dot"></div>'
              + '<div>'
              + '<div class="movas-option-tgl">'
              + '<i class="fas fa-clock" style="color:var(--movas-cyan); margin-right:6px; font-size:11px;"></i>'
              + slot
              + '</div>'
              + '</div>'
              + '</div>';
    });

    document.getElementById('waktu-options').innerHTML = html;
}

function toggleWaktuDropdown() {
    var trigger = document.querySelector('#waktu-dropdown .movas-select-trigger');
    var options = document.getElementById('waktu-options');
    if (!trigger || !options) return;
    var isOpen = options.classList.contains('show');
    if (isOpen) {
        trigger.classList.remove('open');
        options.classList.remove('show');
    } else {
        trigger.classList.add('open');
        options.classList.add('show');
    }
}

function selectWaktu(el) {
    var slot = el.getAttribute('data-value');

    document.getElementById('waktu_catatan').value          = slot;
    document.getElementById('waktu-selected-text').textContent = slot;

    document.querySelectorAll('#waktu-options .movas-select-option').forEach(function(opt) {
        opt.classList.remove('active');
    });
    document.querySelectorAll('#waktu-options .movas-option-dot').forEach(function(dot) {
        dot.classList.remove('active');
    });
    el.classList.add('active');
    el.querySelector('.movas-option-dot').classList.add('active');

    document.querySelector('#waktu-dropdown .movas-select-trigger').classList.remove('open');
    document.getElementById('waktu-options').classList.remove('show');
}

// END INPUT JAM

// ── SWITCH TAB ─────────────────────────────────────────────────
function switchBookingTab(tab) {
    var panels = ['baru', 'aktif', 'riwayat'];
    panels.forEach(function(p) {
        document.getElementById('panel-booking-' + p).style.display = p === tab ? 'block' : 'none';
        var btn = document.getElementById('tab-booking-' + p);
        if (p === tab) {
            btn.style.background = 'var(--movas-accent)';
            btn.style.color      = '#fff';
            btn.style.border     = 'none';
        } else {
            btn.style.background = 'var(--movas-surface)';
            btn.style.color      = 'var(--movas-text)';
            btn.style.border     = '1px solid var(--movas-border)';
        }
    });
}

// ── TOGGLE KENDARAAN ───────────────────────────────────────────
function toggleKendaraan(idx) {
    var cb     = document.getElementById('cb-' + idx);
    var detail = document.getElementById('detail-' + idx);
    var row    = document.getElementById('row-' + idx);

    if (selectedKendaraan[idx]) {
        delete selectedKendaraan[idx];
        cb.style.background  = 'transparent';
        cb.style.borderColor = 'var(--movas-border)';
        cb.innerHTML         = '';
        row.style.background = '';

        // ── Smooth hide ────────────────────────────────────
        detail.style.maxHeight = '0';
        detail.style.opacity   = '0';
        detail.style.padding   = '0 18px';
        setTimeout(function() {
            detail.style.display = 'none';
        }, 250);

    } else {
        selectedKendaraan[idx] = true;
        cb.style.background  = 'var(--movas-cyan)';
        cb.style.borderColor = 'var(--movas-cyan)';
        cb.innerHTML         = '<i class="fas fa-check" style="color:#fff; font-size:12px;"></i>';
        row.style.background = 'rgba(0,180,216,0.05)';

        // ── Smooth show ────────────────────────────────────
        detail.style.display   = 'block';
        detail.style.maxHeight = '0';
        detail.style.opacity   = '0';
        detail.style.padding   = '0 18px';

        setTimeout(function() {
            detail.style.maxHeight = '300px';
            detail.style.opacity   = '1';
            detail.style.padding   = '14px 18px';
        }, 10);
    }
}

var unitBaruCount = 0;
var unitBaruList  = {};

function toggleFormUnitBaru() {
    var wrapper = document.getElementById('form-unit-baru-wrapper');
    var btn     = document.getElementById('btn-tambah-unit-baru');
    var isOpen  = wrapper.style.display !== 'none';

    if (isOpen) {
        wrapper.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-plus-circle"></i> Tambah Kendaraan Baru';
        btn.style.borderColor = 'var(--movas-cyan)';
        btn.style.color       = 'var(--movas-cyan)';
        // Hapus semua unit baru
        document.getElementById('form-unit-baru-list').innerHTML = '';
        unitBaruCount = 0;
        unitBaruList  = {};
    } else {
        wrapper.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-times-circle"></i> Tutup';
        btn.style.borderColor = 'var(--movas-danger)';
        btn.style.color       = 'var(--movas-danger)';
        addUnitBaru(); // otomatis tambah 1 form
    }
}

function addUnitBaru() {
    var idx  = unitBaruCount++;
    var list = document.getElementById('form-unit-baru-list');

    var html = '<div id="unit-baru-' + idx + '"'
             + ' style="background:var(--movas-surface2); padding:14px 18px;'
             + ' border-bottom:1px solid var(--movas-border); position:relative;">';

    // Tombol hapus
    html += '<button type="button" onclick="removeUnitBaru(' + idx + ')"'
          + ' style="position:absolute; top:10px; right:14px; background:none;'
          + ' border:none; color:var(--movas-danger); cursor:pointer; font-size:14px;">'
          + '<i class="fas fa-times-circle"></i></button>';

    // Badge unit baru
    html += '<div style="display:inline-block; background:var(--movas-accent);'
          + ' color:#fff; font-size:10px; font-weight:700; padding:2px 8px;'
          + ' border-radius:10px; margin-bottom:10px; letter-spacing:0.5px;">UNIT BARU</div>';

    // Nopol
    html += '<div style="margin-bottom:10px;">'
          + '<label style="font-size:11px; color:var(--movas-text3); display:block; margin-bottom:4px;">'
          + 'Nopol <span style="color:var(--movas-danger);">*</span></label>'
          + '<input type="text" id="new-nopol-' + idx + '"'
          + ' placeholder="Contoh: B 1234 ABC"'
          + ' oninput="this.value = this.value.toUpperCase()"'
          + ' style="width:100%; background:var(--movas-surface);'
          + ' border:1px solid var(--movas-border);'
          + ' color:var(--movas-text); padding:8px 12px;'
          + ' border-radius:8px; font-size:13px; outline:none;">'
          + '</div>';

    // Tipe
    html += '<div style="margin-bottom:10px;">'
          + '<label style="font-size:11px; color:var(--movas-text3); display:block; margin-bottom:4px;">'
          + 'Tipe Kendaraan <span style="color:var(--movas-danger);">*</span></label>'
          + '<input type="text" id="new-tipe-' + idx + '"'
          + ' placeholder="Contoh: Toyota Avanza"'
          + ' style="width:100%; background:var(--movas-surface);'
          + ' border:1px solid var(--movas-border);'
          + ' color:var(--movas-text); padding:8px 12px;'
          + ' border-radius:8px; font-size:13px; outline:none;">'
          + '</div>';

    // Jenis kendaraan
    html += '<div style="margin-bottom:10px;">'
          + '<label style="font-size:11px; color:var(--movas-text3); display:block; margin-bottom:4px;">'
          + 'Jenis Kendaraan <span style="color:var(--movas-danger);">*</span></label>'
          + '<div style="display:flex; gap:8px;">'
          + '<button type="button" id="jenis-mobil-' + idx + '"'
          + ' onclick="setJenisKendaraan(' + idx + ', \'Mobil\')"'
          + ' style="flex:1; padding:8px; border-radius:8px; font-size:12px; font-weight:600;'
          + ' cursor:pointer; background:var(--movas-accent); color:#fff; border:none;">'
          + '<i class="fas fa-car"></i> Mobil</button>'
          + '<button type="button" id="jenis-motor-' + idx + '"'
          + ' onclick="setJenisKendaraan(' + idx + ', \'Motor\')"'
          + ' style="flex:1; padding:8px; border-radius:8px; font-size:12px; font-weight:600;'
          + ' cursor:pointer; background:var(--movas-surface); color:var(--movas-text);'
          + ' border:1px solid var(--movas-border);">'
          + '<i class="fas fa-motorcycle"></i> Motor</button>'
          + '</div>'
          + '<input type="hidden" id="new-jenis-' + idx + '" value="Mobil">'
          + '</div>';

    // KM
    html += '<div style="margin-bottom:10px;">'
          + '<label style="font-size:11px; color:var(--movas-text3); display:block; margin-bottom:4px;">'
          + 'KM Saat Ini <span style="color:var(--movas-danger);">*</span></label>'
          + '<input type="text" id="new-km-' + idx + '"'
          + ' id="new-km-raw-' + idx + '"'
          + ' placeholder="Masukkan KM"'
          + ' oninput="formatNewKM(this, ' + idx + ')"'
          + ' style="width:100%; background:var(--movas-surface);'
          + ' border:1px solid var(--movas-border);'
          + ' color:var(--movas-text); padding:8px 12px;'
          + ' border-radius:8px; font-size:13px; outline:none;">'
          + '<input type="hidden" id="new-km-raw-' + idx + '">'
          + '</div>';

    // Keluhan
    html += '<div>'
          + '<label style="font-size:11px; color:var(--movas-text3); display:block; margin-bottom:4px;">'
          + 'Keluhan Service <span style="color:var(--movas-danger);">*</span></label>'
          + '<textarea id="new-keluhan-' + idx + '"'
          + ' placeholder="Ceritakan keluhan kendaraan Anda..."'
          + ' rows="2"'
          + ' style="width:100%; background:var(--movas-surface);'
          + ' border:1px solid var(--movas-border);'
          + ' color:var(--movas-text); padding:8px 12px;'
          + ' border-radius:8px; font-size:13px; outline:none;'
          + ' resize:none; font-family:\'Inter\',sans-serif;"></textarea>'
          + '</div>';

    html += '</div>'; // end unit-baru

    list.insertAdjacentHTML('beforeend', html);
    unitBaruList[idx] = true;
}

function removeUnitBaru(idx) {
    var el = document.getElementById('unit-baru-' + idx);
    if (el) el.remove();
    delete unitBaruList[idx];
}

function setJenisKendaraan(idx, jenis) {
    document.getElementById('new-jenis-' + idx).value = jenis;
    var btnMobil = document.getElementById('jenis-mobil-' + idx);
    var btnMotor = document.getElementById('jenis-motor-' + idx);

    if (jenis === 'Mobil') {
        btnMobil.style.background = 'var(--movas-accent)';
        btnMobil.style.color      = '#fff';
        btnMobil.style.border     = 'none';
        btnMotor.style.background = 'var(--movas-surface)';
        btnMotor.style.color      = 'var(--movas-text)';
        btnMotor.style.border     = '1px solid var(--movas-border)';
    } else {
        btnMotor.style.background = 'var(--movas-accent)';
        btnMotor.style.color      = '#fff';
        btnMotor.style.border     = 'none';
        btnMobil.style.background = 'var(--movas-surface)';
        btnMobil.style.color      = 'var(--movas-text)';
        btnMobil.style.border     = '1px solid var(--movas-border)';
    }
}

function formatNewKM(el, idx) {
    var raw = el.value.replace(/\D/g, '');
    el.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    document.getElementById('new-km-raw-' + idx).value = raw;
}

// ── Validasi format nopol ──────────────────────────────────
function validasiNopol(nopol) {
    var regex = /^[A-Z]{1,2}\s?\d{1,5}\s?[A-Z]{0,3}$/i;
    return regex.test(nopol.trim());
}

// Show More list skendaraan
function initKendaraanShowMore() {
    var rows = document.querySelectorAll('.kendaraan-checkbox-row');
    if (rows.length <= 6) return;

    var sisa = rows.length - 6;

    for (var i = 6; i < rows.length; i++) {
        rows[i].style.display = 'none';
    }

    // ── Insert SEBELUM tombol tambah kendaraan baru ────────
    var btnTambah = document.getElementById('btn-tambah-unit-baru');
    var btnHtml   = '<div id="btn-show-more-kendaraan"'
                  + ' onclick="toggleMoreKendaraan()"'
                  + ' style="padding:12px 18px; text-align:center; cursor:pointer;'
                  + ' border-top:1px solid var(--movas-border);'
                  + ' color:var(--movas-cyan); font-size:12px; font-weight:600;'
                  + ' background:var(--movas-surface2); transition:background 0.15s;"'
                  + ' onmouseover="this.style.background=\'var(--movas-surface)\'"'
                  + ' onmouseout="this.style.background=\'var(--movas-surface2)\'">'
                  + '<i class="fas fa-chevron-down" id="icon-show-more" style="margin-right:6px; transition:transform 0.2s;"></i>'
                  + '<span id="text-show-more">Lihat ' + sisa + ' kendaraan lainnya</span>'
                  + '</div>';

    // Insert sebelum parent div tombol tambah
    btnTambah.closest('div').insertAdjacentHTML('beforebegin', btnHtml);
}

function toggleMoreKendaraan() {
    var rows     = document.querySelectorAll('.kendaraan-checkbox-row');
    var icon     = document.getElementById('icon-show-more');
    var text     = document.getElementById('text-show-more');
    var sisa     = rows.length - 6;
    var isHidden = rows[6] && rows[6].style.display === 'none';

    for (var i = 6; i < rows.length; i++) {
        rows[i].style.display = isHidden ? 'flex' : 'none';
    }

    if (isHidden) {
        icon.style.transform = 'rotate(180deg)';
        text.textContent     = 'Sembunyikan';
    } else {
        icon.style.transform = 'rotate(0deg)';
        text.textContent     = 'Lihat ' + sisa + ' kendaraan lainnya';
    }
}

// ── SET LOKASI ─────────────────────────────────────────────────
function setLokasi(val) {
    lokasiService = val;
    var btnBengkel = document.getElementById('btn-bengkel');
    var btnHome    = document.getElementById('btn-home');
    var rowAlamat  = document.getElementById('row-alamat');

    if (val === 'bengkel') {
        btnBengkel.style.background = 'var(--movas-accent)';
        btnBengkel.style.color      = '#fff';
        btnBengkel.style.border     = 'none';
        btnHome.style.background    = 'var(--movas-surface2)';
        btnHome.style.color         = 'var(--movas-text)';
        btnHome.style.border        = '1px solid var(--movas-border)';
        rowAlamat.style.display     = 'none';
    } else {
        btnHome.style.background    = 'var(--movas-accent)';
        btnHome.style.color         = '#fff';
        btnHome.style.border        = 'none';
        btnBengkel.style.background = 'var(--movas-surface2)';
        btnBengkel.style.color      = 'var(--movas-text)';
        btnBengkel.style.border     = '1px solid var(--movas-border)';
        rowAlamat.style.display     = 'block';
    }

    document.getElementById('lokasi_service').value = val;
}

// ── SUBMIT BOOKING ─────────────────────────────────────────────
function submitBooking() {
    var tgl       = document.getElementById('tgl_booking').value;
    var nama_cabang = document.getElementById('cabang_nama')
    ? document.getElementById('cabang_nama').value : '';
    var alamat    = document.getElementById('alamat_service').value;

    // Validasi kendaraan — minimal 1 existing ATAU 1 unit baru
    var adaUnitBaru = Object.keys(unitBaruList).filter(function(idx) {
        return document.getElementById('unit-baru-' + idx) !== null;
    }).length > 0;

    if (Object.keys(selectedKendaraan).length === 0 && !adaUnitBaru) {
        Swal.fire({
            icon: 'warning', title: 'Pilih Kendaraan',
            text: 'Pilih minimal 1 kendaraan atau tambahkan kendaraan baru.',
            confirmButtonColor: '#1a2d7c',
        });
        return;
    }

    // Validasi tanggal
    if (!tgl) {
        Swal.fire({
            icon: 'warning', title: 'Tanggal Belum Dipilih',
            text: 'Pilih tanggal service terlebih dahulu.',
            confirmButtonColor: '#1a2d7c',
        });
        return;
    }

    // ── Validasi waktu ─────────────────────────────────────────
    var waktu = document.getElementById('waktu_catatan').value;
    if (!waktu) {
        Swal.fire({
            icon: 'warning', title: 'Waktu Belum Dipilih',
            text: 'Pilih waktu service terlebih dahulu.',
            confirmButtonColor: '#1a2d7c',
        });
        return;
    }

    // Validasi alamat home service
    if (lokasiService === 'home_service' && !alamat.trim()) {
        Swal.fire({
            icon: 'warning', title: 'Alamat Belum Diisi',
            text: 'Masukkan alamat untuk Home Service.',
            confirmButtonColor: '#1a2d7c',
        });
        return;
    }

    // ── Validasi cabang ────────────────────────────────────────
    if (!nama_cabang) {
        Swal.fire({
            icon: 'warning', title: 'Cabang Belum Dipilih',
            text: 'Pilih cabang tujuan terlebih dahulu.',
            confirmButtonColor: '#1a2d7c',
        });
        return;
    }

    // Build data kendaraan
    var kendaraan = [];
    Object.keys(selectedKendaraan).forEach(function(idx) {
        kendaraan.push({
            nopol   : document.querySelector('[name="kendaraan[' + idx + '][nopol]"]').value,
            km      : document.getElementById('km-raw-' + idx).value,
            keluhan : document.getElementById('keluhan-' + idx).value,
        });
    });

    // ── Validasi cabang ────────────────────────────────────────
    if (!nama_cabang) {
        Swal.fire({
            icon: 'warning', title: 'Cabang Belum Dipilih',
            text: 'Pilih cabang tujuan terlebih dahulu.',
            confirmButtonColor: '#1a2d7c',
        });
        return;
    }

    // ── Build + Validasi data kendaraan ───────────────────────
    var kendaraan    = [];
    var kmError      = [];
    var keluhanError = [];

    Object.keys(selectedKendaraan).forEach(function(idx) {
        var nopol   = document.querySelector('[name="kendaraan[' + idx + '][nopol]"]').value;
        var kmRaw   = document.getElementById('km-raw-' + idx).value;
        var kmLast  = parseInt(document.getElementById('km-last-' + idx).value) || 0;
        var keluhan = document.getElementById('keluhan-' + idx).value.trim();
        var km      = parseInt(kmRaw) || 0;

        if (!kmRaw || km === 0) {
            kmError.push(nopol + ' (KM wajib diisi)');
            return;
        }

        if (km <= kmLast) {
            kmError.push(nopol + ' (KM harus lebih dari ' + kmLast.toLocaleString('id-ID') + ')');
            return;
        }

        if (!keluhan) {
            keluhanError.push(nopol);
            return;
        }

        kendaraan.push({
            nopol   : nopol,
            km      : kmRaw,
            keluhan : keluhan,
        });
    });

    if (kmError.length > 0) {
        Swal.fire({
            icon : 'warning',
            title: 'KM Tidak Valid',
            html : '<ul style="text-align:left;">'
                + kmError.map(function(n) { return '<li>' + n + '</li>'; }).join('')
                + '</ul>',
            confirmButtonColor: '#1a2d7c',
        });
        return;
    }

    if (keluhanError.length > 0) {
        Swal.fire({
            icon : 'warning',
            title: 'Keluhan Wajib Diisi',
            html : 'Mohon isi keluhan untuk kendaraan berikut:<br><br>'
                + '<ul style="text-align:left;">'
                + keluhanError.map(function(n) { return '<li><strong>' + n + '</strong></li>'; }).join('')
                + '</ul>',
            confirmButtonColor: '#1a2d7c',
        });
        return;
    }
    // ── END VALIDASI KENDARAAN ─────────────────────────────────

    // ── Tambah unit baru ───────────────────────────────────────
    var unitBaruError = [];

    Object.keys(unitBaruList).forEach(function(idx) {
        if (!document.getElementById('unit-baru-' + idx)) return;

        var nopol   = document.getElementById('new-nopol-' + idx).value.trim().toUpperCase();
        var tipe    = document.getElementById('new-tipe-' + idx).value.trim();
        var jenis   = document.getElementById('new-jenis-' + idx).value;
        var kmRaw   = document.getElementById('new-km-raw-' + idx).value;
        var keluhan = document.getElementById('new-keluhan-' + idx).value.trim();

        // Validasi
        if (!nopol) {
            unitBaruError.push('Nopol unit baru wajib diisi.');
            return;
        }
        if (!validasiNopol(nopol)) {
            unitBaruError.push('Format nopol "' + nopol + '" tidak valid. Contoh: B 1234 ABC');
            return;
        }
        if (!tipe) {
            unitBaruError.push('Tipe kendaraan "' + nopol + '" wajib diisi.');
            return;
        }
        if (!kmRaw || parseInt(kmRaw) === 0) {
            unitBaruError.push('KM kendaraan "' + nopol + '" wajib diisi.');
            return;
        }
        if (!keluhan) {
            unitBaruError.push('Keluhan kendaraan "' + nopol + '" wajib diisi.');
            return;
        }

        kendaraan.push({
            nopol          : nopol,
            tipe           : tipe,
            jenis_kendaraan: jenis,
            km             : kmRaw,
            keluhan        : keluhan,
            is_new_unit    : 1,
        });
    });

    if (unitBaruError.length > 0) {
        Swal.fire({
            icon : 'warning',
            title: 'Data Unit Baru Tidak Lengkap',
            html : '<ul style="text-align:left;">'
                + unitBaruError.map(function(e) { return '<li>' + e + '</li>'; }).join('')
                + '</ul>',
            confirmButtonColor: '#1a2d7c',
        });
        return;
    }
    // ── END UNIT BARU ──────────────────────────────────────────

    var btn = document.getElementById('btn-submit-booking');
    
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

    $.ajax({
        url     : SITE_URL + '<?= $token ?>/booking/submit',
        type    : 'POST',
        dataType: 'JSON',
        data: {
            token          : MOVAS_TOKEN,
            tgl_booking    : tgl,
            waktu_catatan  : document.getElementById('waktu_catatan').value,
            lokasi_service : lokasiService,
            alamat_service : alamat,
            nama_cabang    : nama_cabang,
            catatan        : document.getElementById('catatan').value,
            kendaraan      : kendaraan,
        },
        success: function(res) {
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-calendar-check"></i> Kirim Booking Service';

            if (res.status) {
                Swal.fire({
                    icon             : 'success',
                    title            : 'Booking Terkirim!',
                    text             : res.message,
                    confirmButtonColor: '#1a2d7c',
                }).then(function() {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error', title: 'Gagal',
                    text: res.error,
                    confirmButtonColor: '#1a2d7c',
                });
            }
        },
        error: function() {
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-calendar-check"></i> Kirim Booking Service';
            Swal.fire({
                icon: 'error', title: 'Terjadi Kesalahan',
                text: 'Coba lagi beberapa saat.',
                confirmButtonColor: '#1a2d7c',
            });
        }
    });
}

// ── CANCEL BOOKING ─────────────────────────────────────────────
function cancelBooking(bookingId) {
    Swal.fire({
        icon             : 'warning',
        title            : 'Batalkan Booking?',
        text             : 'Booking yang dibatalkan tidak dapat dikembalikan.',
        showCancelButton : true,
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText : 'Tidak',
        confirmButtonColor: '#e74a3b',
    }).then(function(result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url     : SITE_URL + '<?= $token ?>/booking/cancel/' + bookingId,
            type    : 'POST',
            dataType: 'JSON',
            data    : { token: MOVAS_TOKEN },
            success : function(res) {
                if (res.status) {
                    Swal.fire({
                        icon             : 'success',
                        title            : 'Booking Dibatalkan',
                        confirmButtonColor: '#1a2d7c',
                    }).then(function() { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.error });
                }
            }
        });
    });
}

function formatKM(el) {
    var raw   = el.value.replace(/\D/g, '');
    var idx   = el.id.replace('km-', '');
    el.value  = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    // Simpan raw value ke hidden input
    document.getElementById('km-raw-' + idx).value = raw;
}

function toggleCabangDropdown() {
    var trigger = document.querySelector('#cabang-dropdown .movas-select-trigger');
    var options = document.getElementById('cabang-options');
    if (!trigger || !options) return;
    var isOpen = options.classList.contains('show');
    if (isOpen) {
        trigger.classList.remove('open');
        options.classList.remove('show');
    } else {
        trigger.classList.add('open');
        options.classList.add('show');
    }
}

function selectCabang(el) {
    var value = el.getAttribute('data-value');
    var alamat = el.getAttribute('data-alamat');

    document.getElementById('cabang_nama').value          = value;
    document.getElementById('cabang-selected-text').textContent  = value;
    document.getElementById('cabang-selected-alamat').textContent = alamat;

    document.querySelectorAll('#cabang-options .movas-select-option').forEach(function(opt) {
        opt.classList.remove('active');
    });
    document.querySelectorAll('#cabang-options .movas-option-dot').forEach(function(dot) {
        dot.classList.remove('active');
    });
    el.classList.add('active');
    el.querySelector('.movas-option-dot').classList.add('active');

    document.querySelector('#cabang-dropdown .movas-select-trigger').classList.remove('open');
    document.getElementById('cabang-options').classList.remove('show');
}

// Set default cabang dari invoice terakhir
$(function() {
    var defaultId = <?= intval($cabang_default_id) ?>;
    if (defaultId) {
        var defaultOpt = document.querySelector(
            '#cabang-options .movas-select-option[data-id="' + defaultId + '"]'
        );
        if (defaultOpt) selectCabang(defaultOpt);
    }
});
</script>