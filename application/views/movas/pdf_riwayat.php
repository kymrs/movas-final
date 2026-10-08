<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="<?= base_url('assets/backend/css/pdf_riwayat.css') ?>">
</head>
<body>

<!-- Header -->
<div class="page-header">
    <div>
        <h1>MOVAS — Riwayat Service</h1>
        <p>Mobile Vehicle Assistant by Mobile Auto Care</p>
    </div>
    <div style="text-align:right; font-size:10px; opacity:0.7;">
        Dicetak: <?= date('d/m/Y H:i') ?><br>
        <?= $is_pro ? '★ PRO' : 'FREE' ?>
    </div>
</div>

<div style="padding: 0 20px;">

    <!-- Info Kendaraan -->
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Nomor Polisi</span>
            <span class="info-value"><?= htmlspecialchars($nopol) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipe Kendaraan</span>
            <span class="info-value"><?= htmlspecialchars($kendaraan['tipe'] ?: '—') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Jenis</span>
            <span class="info-value"><?= $kendaraan['jenis_kendaraan'] ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">KM Terakhir</span>
            <span class="info-value">
                <?= $kendaraan['km_terakhir']
                    ? number_format($kendaraan['km_terakhir'], 0, ',', '.') . ' KM'
                    : '—' ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Customer</span>
            <span class="info-value"><?= htmlspecialchars($customer['customer_name']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Service</span>
            <span class="info-value"><?= count($riwayat) ?>x</span>
        </div>
    </div>

    <!-- Riwayat -->
    <div class="section-title">Riwayat Service</div>

    <?php
    $bulan = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr',
              '05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu',
              '09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
    ?>

    <?php foreach ($riwayat as $inv):
        $tgl    = date('d', strtotime($inv['awal_service'])) . ' '
                . $bulan[date('m', strtotime($inv['awal_service']))] . ' '
                . date('Y', strtotime($inv['awal_service']));
        $lokasi_service = str_ireplace('MAC ', '', $inv['lokasi_service'] ?? '');
        $jasa_list   = array_filter($inv['detail'] ?? [], fn($d) => $d['tipe_item'] === 'Jasa');
        $barang_list = array_filter($inv['detail'] ?? [], fn($d) => $d['tipe_item'] !== 'Jasa');
    ?>
    <div class="invoice-card">
        <div class="invoice-header">
            <div class="invoice-header-left"><?= $tgl ?></div>
            <div class="invoice-header-right">
                <?= htmlspecialchars($lokasi_service) ?>
                <?= $inv['km'] ? ' · ' . number_format($inv['km'], 0, ',', '.') . ' KM' : '' ?>
                <?= $inv['jenis_service'] ? ' · ' . $inv['jenis_service'] : '' ?>
            </div>
        </div>

        <?php if (!empty($inv['detail'])): ?>
        <table class="detail-table">
            <thead>
                <tr>
                    <th style="width:40%;">Item</th>
                    <th style="width:15%;">Tipe</th>
                    <th style="width:10%; text-align:center;">Qty</th>
                    <th style="width:17%; text-align:right;">Harga</th>
                    <th style="width:18%; text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($jasa_list)): ?>
                <?php foreach ($jasa_list as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['item']) ?></td>
                    <td><span class="tipe-jasa">Jasa</span></td>
                    <td style="text-align:center;"><?= number_format($d['qty'], 0, ',', '.') ?></td>
                    <td style="text-align:right;">Rp <?= number_format($d['biaya'], 0, ',', '.') ?></td>
                    <td style="text-align:right;">Rp <?= number_format($d['total'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($barang_list)): ?>
                <?php foreach ($barang_list as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['item']) ?></td>
                    <td><span class="tipe-barang"><?= $d['tipe_item'] ?></span></td>
                    <td style="text-align:center;"><?= number_format($d['qty'], 0, ',', '.') ?></td>
                    <td style="text-align:right;">Rp <?= number_format($d['biaya'], 0, ',', '.') ?></td>
                    <td style="text-align:right;">Rp <?= number_format($d['total'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="invoice-total">
            Total: Rp <?= number_format($inv['sub_total'], 0, ',', '.') ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="pdf-footer">
        Dokumen ini digenerate otomatis oleh MOVAS — Mobile Vehicle Assistant<br>
        © <?= date('Y') ?> Mobile Auto Care · movas.mobileautocare.co.id
    </div>

</div>
</body>
</html>