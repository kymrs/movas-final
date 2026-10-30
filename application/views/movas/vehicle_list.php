<div class="page-header">
    <h1>
        <?= $salam ?>,
        <span style="color:var(--movas-cyan);">
            <?= htmlspecialchars($nama_sapa) ?>
        </span>
    </h1>
    <p>Pilih kendaraan untuk melihat kondisi dan riwayat service</p>
</div>

<?php if (!$is_pro): ?>
<div class="upgrade-banner">
    <div class="upgrade-banner-text">
        <strong>⭐ Upgrade ke PRO</strong><br>
        Aktifkan PRO per unit kendaraan untuk melihat riwayat service lengkap dan kondisi komponen secara detail.
    </div>
    <a href="https://wa.me/6282324001500?text=Halo+MAC%2C+saya+<?= urlencode($customer['customer_name']) ?>+ingin+upgrade+MOVAS+ke+PRO"
       class="btn-upgrade" target="_blank">Upgrade</a>
    <button onclick="dismissUpgradeBanner()"
            style="background:none; border:none; color:var(--movas-text3);
                   cursor:pointer; padding:4px; margin-left:6px; font-size:16px;
                   line-height:1; flex-shrink:0;"
            title="Tutup">
        &times;
    </button>
</div>
<?php endif; ?>

<!-- ── TOMBOL REMINDER ────────────────────────────────────── -->
<?php
// Cek apakah ada reminder untuk customer ini
$has_reminder = $this->db->where('customer_id', $customer['id'])
    ->where('tgl_reminder >=', date('Y-m-d'))
    ->count_all_results('mac_reminder_service');
?>
<?php if ($has_reminder > 0): ?>
<button onclick="$('#modalReminder').modal('show')"
        style="width:100%; display:flex; align-items:center; justify-content:space-between;
               padding:12px 16px; margin-bottom:16px;
               background:linear-gradient(135deg, rgba(0,180,216,0.1), rgba(26,45,124,0.1));
               border:1px solid rgba(0,180,216,0.3); border-radius:12px;
               color:var(--movas-text); cursor:pointer; text-align:left;">
    <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px; height:36px; border-radius:10px;
                    background:rgba(0,180,216,0.15);
                    display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fas fa-bell" style="color:var(--movas-cyan); font-size:15px;"></i>
        </div>
        <div>
            <div style="font-size:13px; font-weight:700;">Reminder Service</div>
            <div style="font-size:11px; color:var(--movas-text3);">
                <?= $has_reminder ?> jadwal service menunggu
            </div>
        </div>
    </div>
    <i class="fas fa-chevron-right" style="color:var(--movas-cyan); font-size:12px;"></i>
</button>
<?php endif; ?>

<!-- ── MODAL REMINDER SERVICE ────────────────────────────── -->
<div class="modal fade" id="modalReminder" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content"
             style="border-radius:16px; overflow:hidden; border:none;
                    background:var(--movas-surface);">

            <!-- Header -->
            <div style="background:linear-gradient(135deg,#1a2d7c,#00b4d8);
                        padding:14px 18px; display:flex;
                        align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:10px;
                                background:rgba(255,255,255,0.15);
                                display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-bell" style="color:#fff; font-size:15px;"></i>
                    </div>
                    <div>
                        <div style="color:#fff; font-weight:700; font-size:14px;">
                            Reminder Service
                        </div>
                        <div style="color:rgba(255,255,255,0.6); font-size:11px;">
                            Jadwal service kendaraan Anda
                        </div>
                    </div>
                </div>
                <button type="button" data-dismiss="modal"
                        style="background:none; border:none; color:#fff;
                               font-size:20px; opacity:0.7; cursor:pointer;
                               line-height:1;">&times;</button>
            </div>

            <!-- Body -->
            <div style="padding:0; max-height:480px; overflow-y:auto;">
                <?php
                $bulan_panjang = ['01'=>'Januari','02'=>'Februari','03'=>'Maret',
                    '04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli',
                    '08'=>'Agustus','09'=>'September','10'=>'Oktober',
                    '11'=>'November','12'=>'Desember'];

                $reminders = $this->db->select('
                        r.nopol, r.tgl_service, r.tgl_reminder,
                        r.durasi_bulan, r.status_wa,
                        i.tipe, i.jenis_kendaraan
                    ', FALSE)
                    ->from('mac_reminder_service r')
                    ->join('mac_invoice i',
                        'i.id = r.invoice_id AND i.is_active = 1', 'left')
                    ->where('r.customer_id', $customer['id'])
                    ->where('r.tgl_reminder >=', date('Y-m-d'))
                    ->order_by('r.tgl_reminder', 'ASC')
                    ->get()->result_array();
                ?>

                <?php if (empty($reminders)): ?>
                <div style="text-align:center; padding:32px 16px; color:var(--movas-text3);">
                    <i class="fas fa-bell-slash" style="font-size:28px; opacity:0.2;
                       display:block; margin-bottom:8px;"></i>
                    Tidak ada reminder service.
                </div>
                <?php else: ?>
                <?php foreach ($reminders as $idx => $r):
                    $tgl_reminder = date('d', strtotime($r['tgl_reminder'])) . ' '
                        . $bulan_panjang[date('m', strtotime($r['tgl_reminder']))] . ' '
                        . date('Y', strtotime($r['tgl_reminder']));

                    $tgl_service = date('d', strtotime($r['tgl_service'])) . ' '
                        . $bulan_panjang[date('m', strtotime($r['tgl_service']))] . ' '
                        . date('Y', strtotime($r['tgl_service']));

                    $sisa = ceil((strtotime($r['tgl_reminder']) - time()) / 86400);
                    $is_last = ($idx === count($reminders) - 1);

                    if ($sisa <= 7)      { $color = '#e74a3b'; $color_bg = 'rgba(231,74,59,0.08)'; }
                    elseif ($sisa <= 14) { $color = '#f6c23e'; $color_bg = 'rgba(246,194,62,0.08)'; }
                    else                 { $color = 'var(--movas-cyan)'; $color_bg = 'rgba(0,180,216,0.07)'; }
                ?>
                <div style="padding:14px 18px;
                            <?= $is_last ? '' : 'border-bottom:1px solid var(--movas-border);' ?>">

                    <!-- Nopol + tipe -->
                    <div style="display:flex; align-items:center;
                                justify-content:space-between; margin-bottom:10px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:36px; height:36px; border-radius:10px; flex-shrink:0;
                                        background:var(--movas-surface2);
                                        display:flex; align-items:center; justify-content:center;">
                                <i class="fas <?= strtolower($r['jenis_kendaraan']) === 'motor'
                                    ? 'fa-motorcycle' : 'fa-car' ?>"
                                   style="color:var(--movas-cyan); font-size:14px;"></i>
                            </div>
                            <div>
                                <div style="font-size:14px; font-weight:700;
                                            color:var(--movas-text);">
                                    <?= htmlspecialchars($r['nopol']) ?>
                                </div>
                                <div style="font-size:11px; color:var(--movas-text3);">
                                    <?= htmlspecialchars($r['tipe'] ?: $r['jenis_kendaraan'] ?: '-') ?>
                                </div>
                            </div>
                        </div>

                        <!-- Sisa hari badge -->
                        <span style="font-size:11px; font-weight:700; padding:4px 10px;
                                     border-radius:20px; white-space:nowrap;
                                     background:<?= $color_bg ?>;
                                     color:<?= $color ?>;
                                     border:1px solid <?= $color ?>44;">
                            <?php if ($sisa <= 0): ?>
                                Hari ini!
                            <?php elseif ($sisa === 1): ?>
                                Besok
                            <?php else: ?>
                                <?= $sisa ?> hari lagi
                            <?php endif; ?>
                        </span>
                    </div>

                    <!-- Info reminder -->
                    <div style="background:<?= $color_bg ?>; border-left:3px solid <?= $color ?>;
                                border-radius:0 8px 8px 0; padding:8px 12px;
                                display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-calendar-alt"
                           style="color:<?= $color ?>; font-size:12px; flex-shrink:0;"></i>
                        <div>
                            <div style="font-size:11px; color:var(--movas-text3);
                                        text-transform:uppercase; letter-spacing:0.5px;">
                                Jadwal Service Berikutnya
                            </div>
                            <div style="font-size:13px; font-weight:700; color:var(--movas-text);">
                                <?= $tgl_reminder ?>
                            </div>
                            <div style="font-size:11px; color:var(--movas-text3); margin-top:2px;">
                                Service terakhir: <?= $tgl_service ?>
                                <?php if ($r['durasi_bulan']): ?>
                                · Berkala <?= $r['durasi_bulan'] ?> bulan
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div style="padding:12px 18px; border-top:1px solid var(--movas-border);
                        text-align:center;">
                <a href="<?= site_url($token . '/booking') ?>"
                   style="display:inline-flex; align-items:center; gap:6px;
                          background:linear-gradient(135deg,#1a2d7c,#00b4d8);
                          color:#fff; text-decoration:none;
                          padding:8px 20px; border-radius:20px;
                          font-size:12px; font-weight:700;">
                    <i class="fas fa-tools"></i>
                    Booking Service Sekarang
                </a>
            </div>

        </div>
    </div>
</div>

<!-- Search box -->
<div style="margin-bottom:16px; position:relative;">
    <input type="text" id="search-kendaraan"
           placeholder="Cari nopol atau tipe kendaraan..."
           oninput="filterKendaraan(this.value)"
           style="width:100%; padding:10px 16px 10px 40px;
                  background:var(--movas-surface); border:1px solid var(--movas-border);
                  border-radius:10px; color:var(--movas-text); font-size:13px;
                  outline:none; transition:border-color 0.15s;"
           onfocus="this.style.borderColor='var(--movas-cyan)'"
           onblur="this.style.borderColor='var(--movas-border)'">
    <i class="fas fa-search"
       style="position:absolute; left:14px; top:50%; transform:translateY(-50%);
              color:var(--movas-text3); font-size:13px; pointer-events:none;"></i>
    <!-- Clear button -->
    <button id="btn-clear-search" onclick="clearSearch()"
            style="display:none; position:absolute; right:12px; top:50%;
                   transform:translateY(-50%); background:none; border:none;
                   color:var(--movas-text3); cursor:pointer; font-size:14px;
                   padding:0; line-height:1;">
        &times;
    </button>
</div>

<!-- Hasil tidak ditemukan -->
<div id="search-empty" style="display:none; text-align:center; padding:40px 20px; color:var(--movas-text2);">
    <i class="fas fa-search" style="font-size:32px; opacity:0.3; display:block; margin-bottom:10px;"></i>
    <div>Tidak ada kendaraan yang cocok.</div>
    <div style="font-size:12px; color:var(--movas-text3); margin-top:4px;">
        Coba kata kunci lain.
    </div>
</div>

<?php if (empty($kendaraan_list)): ?>
<div style="text-align:center; padding:60px 20px; color:var(--movas-text2);">
    <i class="fas fa-car" style="font-size:48px; opacity:0.3; margin-bottom:16px; display:block;"></i>
    <p>Belum ada data kendaraan.</p>
    <p style="font-size:12px; margin-top:6px;">Data akan muncul setelah service pertama selesai.</p>
</div>
<?php else: ?>
<div class="row">
        <?php foreach ($kendaraan_list as $k):
            $status = $k['status_kondisi'];
            $status_label = [
                'aman'      => ['label' => 'Semua Aman',       'icon' => 'fa-check-circle'],
                'perhatian' => ['label' => 'Perlu Perhatian',  'icon' => 'fa-exclamation-circle'],
                'kritis'    => ['label' => 'Perlu Tindakan',   'icon' => 'fa-times-circle'],
                'belum_ada' => ['label' => 'Belum Ada Data',   'icon' => 'fa-question-circle'],
            ];
            $sl        = $status_label[$status];
            $nopol_enc = str_replace(' ', '-', $k['nopol']);
        ?>
        <div class="col-md-6 col-lg-4 vehicle-col"
            data-search="<?= htmlspecialchars(strtolower($k['nopol'] . ' ' . ($k['tipe'] ?: ''))) ?>">
            <a href="<?= site_url($token . '/kendaraan/' . $nopol_enc) ?>"
            class="vehicle-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="vehicle-card-icon">
                    <?php if (strtolower($k['jenis_kendaraan']) === 'motor'): ?>
                        <i class="fas fa-motorcycle" style="color:#00b4d8;"></i>
                    <?php else: ?>
                        <i class="fas fa-car" style="color:#00b4d8;"></i>
                    <?php endif; ?>
                </div>

                <div style="display:flex; align-items:center; gap:6px;">
                    <!-- ── Badge PRO/FREE unit ───────────────────────── -->
                    <?php if ($is_pro): ?>
                    <span style="font-size:9px; font-weight:700; padding:2px 7px; border-radius:10px;
                                <?= !empty($k['is_pro_unit'])
                                    ? 'background:rgba(246,194,62,0.15); color:#f6c23e; border:1px solid rgba(246,194,62,0.3);'
                                    : 'background:rgba(150,150,150,0.1); color:var(--movas-text3); border:1px solid var(--movas-border);' ?>">
                        <?= !empty($k['is_pro_unit']) ? '★ PRO' : 'FREE' ?>
                    </span>
                    <?php endif; ?>

                    <span class="status-badge <?= $status ?>">
                        <i class="fas <?= $sl['icon'] ?>" style="font-size:10px;"></i>
                        <?= $sl['label'] ?>
                    </span>
                </div>
            </div>

            <div style="font-size:18px; font-weight:700; margin-bottom:4px; letter-spacing:0.5px;">
                <?= htmlspecialchars($k['nopol']) ?>
            </div>
            <div style="font-size:13px; color:var(--movas-text2); margin-bottom:12px;">
                <?= $k['jenis_kendaraan'] ?>
                &nbsp;·&nbsp; <?= htmlspecialchars($k['tipe'] ?: 'Kendaraan') ?>
                <?php if (!empty($k['tahun'])): ?>
                &nbsp;·&nbsp; <?= htmlspecialchars($k['tahun']) ?>
                <?php endif; ?>
            </div>

            <div style="display:flex; gap:16px; font-size:12px; color:var(--movas-text3);">
                <div>
                    <div style="color:var(--movas-text2); font-weight:600;">
                        <?= $k['total_service'] ?>x
                    </div>
                    <div>Service</div>
                </div>
                <div>
                    <div style="color:var(--movas-text2); font-weight:600;">
                        <?= $k['km_terakhir']
                            ? number_format($k['km_terakhir'], 0, ',', '.') . ' KM'
                            : '—' ?>
                    </div>
                    <div>KM Terakhir</div>
                </div>
                <div>
                    <div style="color:var(--movas-text2); font-weight:600;">
                        <?php
                        if ($k['terakhir_service']) {
                            $bulan = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr',
                                    '05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu',
                                    '09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
                            echo date('d', strtotime($k['terakhir_service'])) . ' '
                            . $bulan[date('m', strtotime($k['terakhir_service']))] . ' '
                            . date('Y', strtotime($k['terakhir_service']));
                        } else { echo '—'; }
                        ?>
                    </div>
                    <div>Service Terakhir</div>
                </div>
            </div>

            <div style="margin-top:12px; text-align:right;
                        color:var(--movas-cyan); font-size:12px; font-weight:500;">
                Lihat Detail <i class="fas fa-arrow-right" style="font-size:10px;"></i>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>