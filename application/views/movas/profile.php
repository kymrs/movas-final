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

                <div style="margin-top:6px;">
                    <?php if ($is_pro): ?>
                    <span class="plan-badge pro">★ PRO</span>
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
    <div class="col-4" style="display:flex;">
        <div class="stat-card" style="flex:1;">
            <div class="stat-number" style="margin-top: 5px"><?= $profile['total_kendaraan'] ?></div>
            <div class="stat-label">Kendaraan</div>
        </div>
    </div>
    <div class="col-4" style="padding:0 6px; display:flex;">
        <div class="stat-card" style="flex:1;">
            <div class="stat-number" style="margin-top: 5px"><?= $profile['total_service'] ?></div>
            <div class="stat-label">Total Service</div>
        </div>
    </div>
    <div class="col-4" style="display:flex;">
        <div class="stat-card" style="flex:1;">
            <div class="stat-number" style="font-size:14px; line-height:1.3;">
                <?php
                if ($profile['terakhir_service']) {
                    $bulan = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr',
                            '05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu',
                            '09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
                    $tgl = date('d', strtotime($profile['terakhir_service']));
                    $bln = $bulan[date('m', strtotime($profile['terakhir_service']))];
                    $thn = date('Y', strtotime($profile['terakhir_service']));
                    echo $tgl . ' ' . $bln . '<br><span style="font-size:12px;">' . $thn . '</span>';
                } else { echo '—'; }
                ?>
            </div>
            <div class="stat-label">Terakhir Service</div>
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