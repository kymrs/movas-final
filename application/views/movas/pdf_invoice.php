<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 11px;
        color: #1a1a2e;
    }

    .page-header {
        background: #0d1933;
        color: #fff;
        padding: 18px 20px;
        margin-bottom: 20px;
    }

    .page-header h1 {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .page-header-sub {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 6px;
    }

    .page-header p { font-size: 10px; opacity: 0.6; }

    .invoice-number {
        font-size: 13px;
        font-weight: 700;
        color: #00b4d8;
    }

    .info-grid {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
    }

    .info-box {
        flex: 1;
        background: #f8f9fc;
        border-radius: 8px;
        padding: 12px 14px;
        border: 1px solid #e3e6f0;
    }

    .info-box-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 8px;
        padding-bottom: 5px;
        border-bottom: 1px solid #e3e6f0;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
        font-size: 11px;
    }

    .info-label { color: #6c757d; }
    .info-value { font-weight: 600; }

    .section-title {
        font-size: 13px;
        font-weight: 700;
        color: #0d1933;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 2px solid #1a2d7c;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
        margin-bottom: 4px;
    }

    .detail-table th {
        background: #162040;
        color: #fff;
        padding: 7px 10px;
        text-align: left;
        font-weight: 600;
    }

    .detail-table td {
        padding: 7px 10px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: top;
    }

    .detail-table tbody tr:last-child td { border-bottom: none; }

    .detail-table tbody tr:nth-child(even) td {
        background: #f8f9fc;
    }

    .tipe-jasa   { background:#e0f7fa; color:#006064; padding:1px 6px; border-radius:3px; font-size:9px; }
    .tipe-barang { background:#e8eaf6; color:#283593; padding:1px 6px; border-radius:3px; font-size:9px; }
    .tipe-lain   { background:#f3e5f5; color:#6a1b9a; padding:1px 6px; border-radius:3px; font-size:9px; }

    .total-section {
        margin-top: 12px;
        display: flex;
        justify-content: flex-end;
    }

    .total-box {
        width: 280px;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        overflow: hidden;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        padding: 7px 14px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 11px;
    }

    .total-row:last-child {
        border-bottom: none;
        background: #162040;
        color: #fff;
        font-weight: 700;
        font-size: 12px;
    }

    .total-label { color: inherit; opacity: 0.8; }
    .total-value { font-weight: 600; }

    .divider {
        border: none;
        border-top: 1px solid #e3e6f0;
        margin: 16px 0;
    }

    .pdf-footer {
        text-align: center;
        font-size: 9px;
        color: #aaa;
        margin-top: 24px;
        padding-top: 12px;
        border-top: 1px solid #e3e6f0;
    }

    .badge-jenis {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 600;
        background: rgba(0,180,216,0.15);
        color: #006080;
    }
</style>
</head>
<body>

<?php
$bulan = [
    '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
    '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
    '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
];
$tgl_service = !empty($invoice['awal_service'])
    ? date('d', strtotime($invoice['awal_service'])) . ' '
      . $bulan[date('m', strtotime($invoice['awal_service']))] . ' '
      . date('Y', strtotime($invoice['awal_service']))
    : '—';

$cabang = str_ireplace('MAC ', '', $invoice['nama_cabang'] ?? '');

$jasa_list   = array_filter($detail, fn($d) => $d['tipe_item'] === 'Jasa');
$barang_list = array_filter($detail, fn($d) => $d['tipe_item'] !== 'Jasa');

$total_jasa   = array_sum(array_column(array_values($jasa_list),   'total'));
$total_barang = array_sum(array_column(array_values($barang_list), 'total'));
?>

<!-- Header -->
<div class="page-header">
    <h1>MOVAS — Detail Service</h1>
    <div class="page-header-sub">
        <p>Mobile Vehicle Assistant by Mobile Auto Care</p>
        <div>
            <div class="invoice-number"><?= htmlspecialchars($invoice['invoice_number']) ?></div>
        </div>
    </div>
</div>

<div style="padding: 0 20px;">

    <!-- Info Grid -->
    <div class="info-grid">

        <!-- Info Kendaraan -->
        <div class="info-box">
            <div class="info-box-title">Info Kendaraan</div>
            <div class="info-row">
                <span class="info-label">Nomor Polisi</span>
                <span class="info-value"><?= htmlspecialchars($invoice['nopol']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">KM</span>
                <span class="info-value">
                    <?= $invoice['km']
                        ? number_format($invoice['km'], 0, ',', '.') . ' KM'
                        : '—' ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Jenis Service</span>
                <span class="info-value"><?= $invoice['jenis_service'] ?: '—' ?></span>
            </div>
        </div>

        <!-- Info Service -->
        <div class="info-box">
            <div class="info-box-title">Info Service</div>
            <div class="info-row">
                <span class="info-label">Tanggal</span>
                <span class="info-value"><?= $tgl_service ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Lokasi</span>
                <span class="info-value"><?= htmlspecialchars($cabang ?: '—') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Customer</span>
                <span class="info-value"><?= htmlspecialchars($customer['customer_name']) ?></span>
            </div>
        </div>

    </div>

    <!-- Detail Jasa -->
    <?php if (!empty($jasa_list)): ?>
    <div class="section-title">Jasa yang Dikerjakan</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:50%;">Nama Jasa</th>
                <th style="width:10%; text-align:center;">Qty</th>
                <th style="width:17%; text-align:right;">Harga</th>
                <th style="width:18%; text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($jasa_list as $d): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>
                    <span class="tipe-jasa">Jasa</span>
                    &nbsp;<?= htmlspecialchars($d['item']) ?>
                </td>
                <td style="text-align:center;"><?= number_format($d['qty'], 0, ',', '.') ?></td>
                <td style="text-align:right;">Rp <?= number_format($d['biaya'], 0, ',', '.') ?></td>
                <td style="text-align:right;">Rp <?= number_format($d['total'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <hr class="divider">
    <?php endif; ?>

    <!-- Detail Part & Bahan -->
    <?php if (!empty($barang_list)): ?>
    <div class="section-title">Part & Bahan yang Dipakai</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:45%;">Nama Item</th>
                <th style="width:12%;">Tipe</th>
                <th style="width:10%; text-align:center;">Qty</th>
                <th style="width:14%; text-align:right;">Harga</th>
                <th style="width:14%; text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($barang_list as $d): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($d['item']) ?></td>
                <td>
                    <?php if ($d['tipe_item'] === 'Jasa'): ?>
                        <span class="tipe-jasa">Jasa</span>
                    <?php elseif (in_array($d['tipe_item'], ['Sparepart','Bahan','Pelumas'])): ?>
                        <span class="tipe-barang"><?= $d['tipe_item'] ?></span>
                    <?php else: ?>
                        <span class="tipe-lain"><?= $d['tipe_item'] ?></span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;"><?= number_format($d['qty'], 0, ',', '.') ?></td>
                <td style="text-align:right;">Rp <?= number_format($d['biaya'], 0, ',', '.') ?></td>
                <td style="text-align:right;">Rp <?= number_format($d['total'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <hr class="divider">
    <?php endif; ?>

    <!-- Total -->
    <div class="total-section">
        <div class="total-box">
            <?php if (!empty($jasa_list)): ?>
            <div class="total-row">
                <span class="total-label">Total Jasa</span>
                <span class="total-value">Rp <?= number_format($total_jasa, 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($barang_list)): ?>
            <div class="total-row">
                <span class="total-label">Total Part & Bahan</span>
                <span class="total-value">Rp <?= number_format($total_barang, 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            <div class="total-row">
                <span class="total-label">Grand Total</span>
                <span class="total-value">Rp <?= number_format($invoice['sub_total'], 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <div class="pdf-footer">
        Dokumen ini digenerate otomatis oleh MOVAS — Mobile Vehicle Assistant<br>
        © <?= date('Y') ?> Mobile Auto Care · movas.mobileautocare.co.id
    </div>

</div>
</body>
</html>