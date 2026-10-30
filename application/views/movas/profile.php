<div class="page-header">
    <h1><i class="fas fa-user-circle" style="color:var(--movas-cyan);"></i> Profile</h1>
</div>

<!-- Info Customer -->
<div class="movas-card mb-4">
    <div class="movas-card-body">
        <div style="display:flex; align-items:center; gap:16px;">
            <img src="<?= base_url('assets/backend/img/movas-robot-no-text.png') ?>"
                 style="width:64px; height:64px; object-fit:contain;
                        background:var(--movas-surface2); border-radius:50%; padding:8px;">
            <div>
                <div style="font-size:18px; font-weight:700;">
                    <?= htmlspecialchars($customer['customer_name']) ?>
                </div>
                <div style="font-size:12px; color:var(--movas-text2); margin-top:4px;">
                    Member sejak
                    <?php
                    $bulan     = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                                '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                                '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
                    $tgl_token = $customer['movas_token_created_at'];
                    echo date('d', strtotime($tgl_token)) . ' '
                    . $bulan[date('m', strtotime($tgl_token))] . ' '
                    . date('Y', strtotime($tgl_token));
                    ?>
                </div>

                <!-- ── TAMBAHAN: no telp & alamat ── -->
                <?php if (!empty($customer['no_telp'])): ?>
                <div style="font-size:12px; color:var(--movas-text2); margin-top:4px;">
                    <i class="fas fa-phone" style="color:var(--movas-cyan); width:14px;"></i>
                    <?= htmlspecialchars($customer['no_telp']) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($customer['address'])): ?>
                <div style="font-size:12px; color:var(--movas-text2); margin-top:4px;">
                    <i class="fas fa-map-marker-alt" style="color:var(--movas-cyan); width:14px;"></i>
                    <?= htmlspecialchars($customer['address']) ?>
                </div>
                <?php endif; ?>
                <!-- ── END TAMBAHAN ── -->

                <div style="margin-top:6px; display:flex; align-items:center; gap:6px;">
                    <?php if ($is_pro): ?>
                    <span class="plan-badge pro">★ PRO</span>
                    <?php if ($total_pro_unit > 0): ?>
                    <span style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px;
                                background:rgba(246,194,62,0.15); color:#f6c23e;
                                border:1px solid rgba(246,194,62,0.3);">
                        <?= $total_pro_unit ?> unit
                    </span>
                    <?php endif; ?>
                    <?php else: ?>
                    <span class="plan-badge free">FREE</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistik -->
<div class="row mb-4" style="gap:0; align-items:stretch;">
    <div class="<?= $is_pro ? 'col-3' : 'col-4' ?>" style="padding-right:4px; display:flex;">
        <div class="stat-card" style="flex:1;">
            <div class="stat-number" style="margin-top:5px;"><?= $profile['total_kendaraan'] ?></div>
            <div class="stat-label">Unit</div>
        </div>
    </div>
    <div class="<?= $is_pro ? 'col-3' : 'col-4' ?>" style="padding:0 4px; display:flex;">
        <div class="stat-card" style="flex:1;">
            <div class="stat-number" style="margin-top:5px;"><?= $profile['total_service'] ?></div>
            <div class="stat-label">Service</div>
        </div>
    </div>
    <div class="<?= $is_pro ? 'col-3' : 'col-4' ?>"
         style="padding:0 <?= $is_pro ? '4px' : '0 0 0 4px' ?>; display:flex;">
        <div class="stat-card" style="flex:1;">
            <div class="stat-number" style="font-size:13px; line-height:1.3;">
                <?php
                if ($profile['terakhir_service']) {
                    $bulan = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr',
                            '05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu',
                            '09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
                    $tgl = date('d', strtotime($profile['terakhir_service']));
                    $bln = $bulan[date('m', strtotime($profile['terakhir_service']))];
                    $thn = date('Y', strtotime($profile['terakhir_service']));
                    echo $tgl . ' ' . $bln . '<br><span style="font-size:11px;">' . $thn . '</span>';
                } else { echo '—'; }
                ?>
            </div>
            <div class="stat-label">Terakhir</div>
        </div>
    </div>
    <?php if ($is_pro): ?>
        <?php
        $total_pro_unit_stat = $this->db->where('customer_id', $customer['id'])
            ->where('is_pro', 1)
            ->group_start()
                ->where('expired_at IS NULL', NULL, FALSE)
                ->or_where('expired_at >', date('Y-m-d H:i:s'))
            ->group_end()
            ->count_all_results('mac_movas_pro_unit');
        ?>
    <div class="col-3" style="padding-left:4px; display:flex;">
        <div class="stat-card" style="flex:1; cursor:pointer;
            border:1px solid rgba(246,194,62,0.3);
            background:rgba(246,194,62,0.05);
            transition:all 0.15s;"
            onclick="$('#modalUnitPro').modal('show')"
            onmouseover="this.style.background='rgba(246,194,62,0.1)'"
            onmouseout="this.style.background='rgba(246,194,62,0.05)'">
            <div class="stat-number" style="margin-top:5px; color:#f6c23e; font-size:18px;">
                <?= $total_pro_unit_stat ?>
            </div>
            <div class="stat-label" style="color:#f6c23e; font-size:10px;">
                PRO <i class="fas fa-chevron-right" style="font-size:8px;"></i>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ── MODAL UNIT PRO ─────────────────────────────────────── -->
<div class="modal fade" id="modalUnitPro" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content"
             style="border-radius:16px; overflow:hidden; border:none;
                    background:var(--movas-surface);">

            <!-- Header -->
            <div style="background:linear-gradient(135deg,#1a2d7c,#00b4d8);
                        padding:14px 18px; display:flex;
                        align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:16px;">★</span>
                    <div>
                        <div style="color:#fff; font-weight:700; font-size:14px;">Unit PRO</div>
                        <div style="color:rgba(255,255,255,0.6); font-size:11px;">
                            Kendaraan dengan akses PRO aktif
                        </div>
                    </div>
                </div>
                <button type="button" data-dismiss="modal"
                        style="background:none; border:none; color:#fff;
                               font-size:20px; opacity:0.7; cursor:pointer;
                               line-height:1;">&times;</button>
            </div>

            <!-- Body -->
            <div style="padding:0; max-height:400px; overflow-y:auto;">
                <?php
                $pro_units = $this->db->select('p.nopol, p.activated_at, p.expired_at')
                    ->from('mac_movas_pro_unit p')
                    ->where('p.customer_id', $customer['id'])
                    ->where('p.is_pro', 1)
                    ->group_start()
                        ->where('p.expired_at IS NULL', NULL, FALSE)
                        ->or_where('p.expired_at >', date('Y-m-d H:i:s'))
                    ->group_end()
                    ->order_by('p.nopol', 'ASC')
                    ->get()->result_array();

                $bulan_panjang = ['01'=>'Januari','02'=>'Februari','03'=>'Maret',
                    '04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli',
                    '08'=>'Agustus','09'=>'September','10'=>'Oktober',
                    '11'=>'November','12'=>'Desember'];

                if (empty($pro_units)):
                ?>
                <div style="text-align:center; padding:32px 16px; color:var(--movas-text3);">
                    <i class="fas fa-star" style="font-size:28px; opacity:0.2;
                       display:block; margin-bottom:8px;"></i>
                    Belum ada unit PRO.
                </div>
                <?php else: ?>
                <?php foreach ($pro_units as $idx => $pu):
                    // Ambil tipe dari invoice
                    $inv_unit = $this->db->select('tipe, jenis_kendaraan')
                        ->where('customer_id', $customer['id'])
                        ->where('nopol', $pu['nopol'])
                        ->where('app_status', 'approved')
                        ->order_by('created_at', 'DESC')
                        ->limit(1)
                        ->get('mac_invoice')->row_array();

                    $tipe   = $inv_unit['tipe'] ?? '-';
                    $jenis  = $inv_unit['jenis_kendaraan'] ?? 'Mobil';
                    $is_last = ($idx === count($pro_units) - 1);

                    // Sisa hari
                    $sisa_hari = null;
                    if (!empty($pu['expired_at'])) {
                        $sisa_hari = ceil((strtotime($pu['expired_at']) - time()) / 86400);
                    }

                    // Format tanggal
                    $fmt_activated = !empty($pu['activated_at'])
                        ? date('d', strtotime($pu['activated_at'])) . ' '
                          . $bulan_panjang[date('m', strtotime($pu['activated_at']))] . ' '
                          . date('Y', strtotime($pu['activated_at']))
                        : null;

                    $fmt_expired = !empty($pu['expired_at'])
                        ? date('d', strtotime($pu['expired_at'])) . ' '
                          . $bulan_panjang[date('m', strtotime($pu['expired_at']))] . ' '
                          . date('Y', strtotime($pu['expired_at']))
                        : null;
                ?>
                <div style="display:flex; align-items:center; gap:12px;
                            padding:14px 18px;
                            <?= $is_last ? '' : 'border-bottom:1px solid var(--movas-border);' ?>">

                    <!-- Icon -->
                    <div style="width:38px; height:38px; border-radius:10px; flex-shrink:0;
                                background:rgba(246,194,62,0.1);
                                display:flex; align-items:center; justify-content:center;">
                        <i class="fas <?= strtolower($jenis) === 'motor' ? 'fa-motorcycle' : 'fa-car' ?>"
                           style="color:#f6c23e; font-size:15px;"></i>
                    </div>

                    <!-- Info -->
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:700; font-size:14px; color:var(--movas-text);">
                            <?= htmlspecialchars($pu['nopol']) ?>
                        </div>
                        <div style="font-size:11px; color:var(--movas-text3); margin-top:1px;">
                            <?= htmlspecialchars($tipe) ?>
                            <?php if ($fmt_activated): ?>
                            · Aktif sejak <?= $fmt_activated ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Badge expire -->
                    <div style="flex-shrink:0; text-align:right;">
                        <?php if ($sisa_hari === null): ?>
                        <!-- <span style="font-size:10px; font-weight:700; padding:3px 8px;
                                     border-radius:10px; background:rgba(28,200,138,0.12);
                                     color:#1cc88a; border:1px solid rgba(28,200,138,0.3);">
                            Tanpa Expire
                        </span> -->
                        <?php elseif ($sisa_hari <= 3): ?>
                        <span style="font-size:10px; font-weight:700; padding:3px 8px;
                                     border-radius:10px; background:rgba(231,74,59,0.1);
                                     color:#e74a3b; border:1px solid rgba(231,74,59,0.3);">
                            <?= $sisa_hari ?> hari lagi
                        </span>
                        <?php elseif ($sisa_hari <= 7): ?>
                        <span style="font-size:10px; font-weight:700; padding:3px 8px;
                                     border-radius:10px; background:rgba(246,194,62,0.12);
                                     color:#f6c23e; border:1px solid rgba(246,194,62,0.3);">
                            <?= $sisa_hari ?> hari lagi
                        </span>
                        <?php else: ?>
                        <span style="font-size:10px; font-weight:700; padding:3px 8px;
                                     border-radius:10px; background:rgba(77,124,254,0.1);
                                     color:#4d7cfe; border:1px solid rgba(77,124,254,0.3);">
                            s/d <?= $fmt_expired ?>
                        </span>
                        <?php endif; ?>
                    </div>

                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- Kendaraan saya -->
<!-- <div class="movas-card mb-4">
    <div class="movas-card-header">
        <span><i class="fas fa-car" style="color:var(--movas-cyan);"></i> Kendaraan Saya</span>
    </div>
    <div class="movas-card-body" style="padding:0;">
        <?php foreach ($profile['kendaraan'] as $idx => $k):
            $nopol_enc = urlencode($k['nopol']);
        ?>
        <a href="<?= site_url($token . '/kendaraan/' . $nopol_enc) ?>"
           style="display:flex; align-items:center; justify-content:space-between;
                  padding:14px 18px; text-decoration:none; color:var(--movas-text);
                  border-bottom:1px solid var(--movas-border); transition:background 0.15s;"
           onmouseover="this.style.background='rgba(255,255,255,0.03)'"
           onmouseout="this.style.background=''">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; background:var(--movas-surface2);
                            border-radius:8px; display:flex; align-items:center;
                            justify-content:center; font-size:16px;">
                    <?php if (strtolower($k['jenis_kendaraan']) === 'motor'): ?>
                        <i class="fas fa-motorcycle" style="color:var(--movas-cyan);"></i>
                    <?php else: ?>
                        <i class="fas fa-car" style="color:var(--movas-cyan);"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <div style="font-size:14px; font-weight:600;">
                        <?= htmlspecialchars($k['nopol']) ?>
                    </div>
                    <div style="font-size:11px; color:var(--movas-text2);">
                        <?= htmlspecialchars($k['tipe'] ?: $k['jenis_kendaraan']) ?>
                    </div>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:13px; font-weight:600; color:var(--movas-cyan);">
                    <?= $k['total_service'] ?>x service
                </div>
                <div style="font-size:11px; color:var(--movas-text3);">
                    <?php
                    if ($k['terakhir_service']) {
                        $bulan = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr',
                                  '05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu',
                                  '09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
                        echo date('d', strtotime($k['terakhir_service'])) . ' '
                           . $bulan[date('m', strtotime($k['terakhir_service']))] . ' '
                           . date('Y', strtotime($k['terakhir_service']));
                    }
                    ?>
                </div>
                <?php if ($k['has_kondisi']): ?>
                <div style="font-size:10px; color:var(--movas-safe);">
                    <i class="fas fa-check-circle"></i> Ada data kondisi
                </div>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div> -->
<div class="movas-card mb-4">
    <a href="<?= site_url($token . '/kendaraan') ?>"
       style="display:flex; align-items:center; justify-content:space-between;
              padding:14px 18px; text-decoration:none; color:var(--movas-text);">
        <div style="display:flex; align-items:center; gap:10px;">
            <i class="fas fa-car" style="color:var(--movas-cyan);"></i>
            <span style="font-size:13px; font-weight:600;">Lihat Semua Kendaraan</span>
        </div>
        <i class="fas fa-arrow-right" style="color:var(--movas-text3); font-size:12px;"></i>
    </a>
</div>

<?php if (!$is_pro): ?>
<div class="upgrade-banner-profile">
    <div class="upgrade-banner-text-profile">
        <strong>⭐ Upgrade ke PRO</strong><br>
        <span style="font-size:11px;">Akses riwayat lengkap, foto kondisi kendaraan, dan fitur eksklusif lainnya.</span>
    </div>
    <a href="https://wa.me/6287883777711?text=Halo+MAC%2C+saya+<?= urlencode($customer['customer_name']) ?>+ingin+upgrade+akun+MOVAS+ke+PRO"
       class="btn-upgrade" target="_blank">Upgrade</a>
</div>
<?php endif; ?>

<!-- ── FEEDBACK ────────────────────────────────────────── -->
<div class="movas-card mb-4">
    <div class="movas-card-header">
        <span>
            <i class="fas fa-comment-alt" style="color:var(--movas-cyan);"></i>
            Masukan & Saran
        </span>
    </div>
    <div class="movas-card-body">

        <div id="feedback-wrapper">
            <!-- Rating bintang -->
            <div style="margin-bottom:14px;">
                <div style="font-size:12px; color:var(--movas-text2); margin-bottom:8px;">
                    Seberapa puas Anda dengan MOVAS?
                </div>
                <div id="star-rating" style="display:flex; gap:6px;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star star-btn"
                       data-rating="<?= $i ?>"
                       onclick="setRating(<?= $i ?>)"
                       style="font-size:28px; color:var(--movas-border);
                              cursor:pointer; transition:color 0.15s;">
                    </i>
                    <?php endfor; ?>
                </div>
                <input type="hidden" id="feedback-rating" value="0">
            </div>

            <!-- Pesan -->
            <div style="margin-bottom:14px;">
                <textarea id="feedback-pesan"
                          placeholder="Tulis masukan atau saran Anda di sini..."
                          style="width:100%; background:var(--movas-surface2);
                                 border:1px solid var(--movas-border);
                                 color:var(--movas-text); border-radius:10px;
                                 padding:10px 12px; font-size:12px;
                                 resize:none; outline:none;
                                 font-family:'Inter',sans-serif;
                                 transition:border-color 0.15s;"
                          rows="4"
                          onfocus="this.style.borderColor='var(--movas-cyan)'"
                          onblur="this.style.borderColor='var(--movas-border)'"></textarea>
            </div>

            <!-- Tombol kirim -->
            <button onclick="submitFeedback()"
                    id="btn-submit-feedback"
                    style="width:100%; padding:10px; border:none;
                           background:var(--movas-accent);
                           color:#fff; border-radius:10px;
                           font-size:13px; font-weight:600;
                           cursor:pointer; transition:background 0.15s;">
                <i class="fas fa-paper-plane" style="margin-right:6px;"></i>
                Kirim Masukan
            </button>
        </div>

        <!-- Pesan sukses -->
        <div id="feedback-success" style="display:none; text-align:center; padding:20px 0;">
            <i class="fas fa-check-circle"
               style="font-size:36px; color:var(--movas-safe); margin-bottom:10px; display:block;"></i>
            <div style="font-size:14px; font-weight:600; margin-bottom:6px;">
                Terima kasih!
            </div>
            <div style="font-size:12px; color:var(--movas-text2);">
                Masukan Anda sudah kami terima dan akan menjadi bahan evaluasi kami.
            </div>
        </div>

    </div>
</div>