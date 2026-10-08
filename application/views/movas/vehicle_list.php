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
        Akses riwayat lengkap tanpa batas untuk semua kendaraan Anda.
    </div>
    <a href="https://wa.me/6287883777711?text=Halo+MAC%2C+saya+<?= urlencode($customer['customer_name']) ?>+ingin+upgrade+akun+MOVAS+ke+PRO"
       class="btn-upgrade" target="_blank">Upgrade</a>
    <button onclick="dismissUpgradeBanner()"
            style="background:none; border:none; color:rgba(255,255,255,0.5);
                   cursor:pointer; padding:4px; margin-left:6px; font-size:16px;
                   line-height:1; flex-shrink:0;"
            title="Tutup">
        &times;
    </button>
</div>
<?php endif; ?>

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
                <span class="status-badge <?= $status ?>">
                    <i class="fas <?= $sl['icon'] ?>" style="font-size:10px;"></i>
                    <?= $sl['label'] ?>
                </span>
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