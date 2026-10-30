<div class="page-header">
    <h1>Daftar Kendaraan</h1>
    <p>Daftar semua kendaraan yang terdaftar</p>
</div>

<!-- Search box -->
<div style="margin-bottom:16px; position:relative;">
    <input type="text" id="search-daftar"
           placeholder="Cari nopol atau tipe kendaraan..."
           oninput="filterDaftar(this.value)"
           style="width:100%; padding:10px 16px 10px 40px;
                  background:var(--movas-surface); border:1px solid var(--movas-border);
                  border-radius:10px; color:var(--movas-text); font-size:13px;
                  outline:none; transition:border-color 0.15s;"
           onfocus="this.style.borderColor='var(--movas-cyan)'"
           onblur="this.style.borderColor='var(--movas-border)'">
    <i class="fas fa-search"
       style="position:absolute; left:14px; top:50%; transform:translateY(-50%);
              color:var(--movas-text3); font-size:13px; pointer-events:none;"></i>
    <button id="btn-clear-daftar" onclick="clearDaftar()"
            style="display:none; position:absolute; right:12px; top:50%;
                   transform:translateY(-50%); background:none; border:none;
                   color:var(--movas-text3); cursor:pointer; font-size:14px; padding:0;">
        &times;
    </button>
</div>

<!-- Tidak ditemukan -->
<div id="daftar-empty" style="display:none; text-align:center; padding:40px 20px; color:var(--movas-text2);">
    <i class="fas fa-search" style="font-size:32px; opacity:0.3; display:block; margin-bottom:10px;"></i>
    <div>Tidak ada kendaraan yang cocok.</div>
</div>

<div class="movas-card">
    <div style="padding:0;">
        <?php foreach ($profile['kendaraan'] as $idx => $k):
            $nopol_enc = str_replace(' ', '-', $k['nopol']);
        ?>
        <a href="<?= site_url($token . '/kendaraan/' . $nopol_enc) ?>"
            class="daftar-row"
            data-search="<?= htmlspecialchars(strtolower($k['nopol'] . ' ' . ($k['tipe'] ?: ''))) ?>"
            style="display:flex; flex-direction:column;
                   padding:14px 18px; text-decoration:none; color:var(--movas-text);
                   border-bottom:1px solid var(--movas-border); transition:background 0.15s;"
            onmouseover="this.style.background='rgba(255,255,255,0.03)'"
            onmouseout="this.style.background=''">

            <!-- Baris info kendaraan -->
            <div style="display:flex; align-items:center; justify-content:space-between;">
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
                        <div style="display:flex; align-items:center; gap:6px;">
                            <div style="font-size:14px; font-weight:600;">
                                <?= htmlspecialchars($k['nopol']) ?>
                            </div>
                            <?php if ($is_pro): ?>
                            <span style="font-size:9px; font-weight:700; padding:1px 6px;
                                        border-radius:10px;
                                        <?= !empty($k['is_pro_unit'])
                                            ? 'background:rgba(246,194,62,0.15); color:#f6c23e; border:1px solid rgba(246,194,62,0.3);'
                                            : 'background:rgba(150,150,150,0.1); color:var(--movas-text3); border:1px solid var(--movas-border);' ?>">
                                <?= !empty($k['is_pro_unit']) ? '★ PRO' : 'FREE' ?>
                            </span>
                            <?php endif; ?>
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
            </div>
            <!-- ── Reminder ──────────────────────────────────────────── -->
            <?php if (!empty($k['reminder'])):
                $r       = $k['reminder'];
                $sisa    = intval($r['sisa_hari']);
                $bulan   = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr',
                            '05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu',
                            '09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
                $tgl_fmt = date('d', strtotime($r['tgl_reminder'])) . ' '
                        . $bulan[date('m', strtotime($r['tgl_reminder']))] . ' '
                        . date('Y', strtotime($r['tgl_reminder']));
                $color   = $sisa <= 7
                    ? 'var(--movas-danger)'
                    : ($sisa <= 14 ? 'var(--movas-warn)' : 'var(--movas-cyan)');
            ?>
            <div style="margin-top:10px; padding:8px 12px;
                        background:rgba(0,180,216,0.07);
                        border-left:3px solid <?= $color ?>;
                        border-radius:0 8px 8px 0;
                        display:flex; align-items:center; gap:8px;">
                <i class="fas fa-bell" style="color:<?= $color ?>; font-size:12px; flex-shrink:0;"></i>
                <div>
                    <div style="font-size:10px; color:var(--movas-text3);
                                text-transform:uppercase; letter-spacing:0.5px;">
                        Reminder Service
                    </div>
                    <div style="font-size:12px; font-weight:600; color:var(--movas-text);">
                        <?= $tgl_fmt ?>
                        <span style="font-size:11px; color:<?= $color ?>; font-weight:400;">
                            (<?= $sisa ?> hari menuju jadwal service)
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <!-- ── End Reminder ──────────────────────────────── -->

        </a>
        <?php endforeach; ?>
    </div>
</div>