<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Memuat file TCPDF dari folder third_party
require_once(APPPATH . 'third_party/TCPDF-main/tcpdf.php');

class Tcpdf_invoice extends TCPDf
{
    public $M_bmn_invoice;
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A5', $unicode = true, $encoding = 'UTF-8', $diskcache = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);
    }

    // Page header
    function Header()
    {
        // Logo
        $this->SetFont('helvetica', 'B', 12);
        $this->Image('assets/backend/img/bymoment.png', 5, 4, 37, 20);
        $this->SetX(117);
        $this->SetFont('Poppins-Regular', '', 9);
        $this->Cell(40, 16, 'cs@bymoment.id', 0, 0);
        $this->SetX(121);
        $this->Cell(40, 26, '0812-90700033', 0, 1);
        $style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

        $this->Line(4, 26, 145, 26, $style);
        $this->Ln(5);
    }

    public function generateInvoice($data)
    {
        $CI = &get_instance();
        if ($data['sub'] == 'bmn') {
            $CI->load->model('M_bmn_invoice'); // Load model jika diperlukan
        }

        // INISIAI VARIABLE
        $invoice = $CI->M_bmn_invoice->get_by_id($data['id']);
        $invoice_details = $CI->M_bmn_invoice->get_detail($data['id']);
        $invoice_rek = $CI->M_bmn_invoice->get_rek($data['id']);

        // Initialize the TCPDF object
        $t_cpdf2 = new Tcpdf_invoice('P', 'mm', 'A5', true, 'UTF-8', false);

        // Set document properties
        $t_cpdf2->SetCreator(PDF_CREATOR);
        $t_cpdf2->SetAuthor('Author Name');
        if ($data['sub'] == 'bmn') {
            $t_cpdf2->SetTitle('Invoice ByMoment PDF');
        }

        $t_cpdf2->SetMargins(15, 28, 15); // Margin kiri, atas (untuk header), kanan
        // $t_cpdf2->SetHeaderMargin(30);    // Jarak antara header dan konten
        $t_cpdf2->SetAutoPageBreak(true, 15); // Penanganan otomatis margin bawah
        $t_cpdf2->setPrintHeader(true);

        // Add a new page
        $t_cpdf2->AddPage();

        $t_cpdf2->SetY(30);

        // Pilih font untuk isi
        $t_cpdf2->SetFont('Poppins-Bold', '', 22);

        $t_cpdf2->SetX(57);
        $t_cpdf2->Cell(100, 10, 'INVOICE', 0, 1, 'L');

        $t_cpdf2->SetFont('Poppins-Regular', '', 11);
        $t_cpdf2->SetX(48);
        $t_cpdf2->Cell(19, 4, 'No. Invoice : ' . $invoice->kode_invoice, 0, 0, 'L');

        $t_cpdf2->SetY($t_cpdf2->GetY());
        $t_cpdf2->SetX(4);
        $t_cpdf2->SetFont('Poppins-Regular', '', 10);
        $t_cpdf2->Cell(74, 20, 'Kepada Yth.', 0, 0);
        $t_cpdf2->Cell(45, 20, 'Tanggal Invoice', 0, 0);
        $t_cpdf2->Cell(19, 20, ': ' . date('d/m/Y', strtotime($invoice->tgl_invoice)), 0, 0);
        $t_cpdf2->SetY($t_cpdf2->GetY() + 13);
        $t_cpdf2->SetX(4);
        $t_cpdf2->SetFont('Poppins-Bold', '', 14);
        $t_cpdf2->Cell(74, 5, $invoice->ctc2_nama, 0, 0);
        $t_cpdf2->SetFont('Poppins-Regular', '', 10);
        $t_cpdf2->Cell(45, 5, 'Tanggal Jatuh Tempo', 0, 0);
        $t_cpdf2->Cell(19, 5, ': ' . date('d/m/Y', strtotime($invoice->tgl_tempo)), 0, 0);

        $t_cpdf2->SetFont('Poppins-Regular', '', 10);
        $t_cpdf2->SetY($t_cpdf2->GetY() + 7);
        $t_cpdf2->SetX(4);
        $t_cpdf2->Cell(0, 0, 'Email', 0, 0);
        $t_cpdf2->SetX(23);
        $t_cpdf2->Cell(0, 0, ': ' . $invoice->ctc2_email, 0, 0);

        $t_cpdf2->SetFont('Poppins-Regular', '', 10);
        $t_cpdf2->SetY($t_cpdf2->GetY() + 6);
        $t_cpdf2->SetX(4);
        $t_cpdf2->Cell(75, 0, 'Telepon', 0, 0);
        $t_cpdf2->SetX(23);
        $t_cpdf2->Cell(0, 0, ': ' . $invoice->ctc2_nomor, 0, 0);

        $t_cpdf2->SetFont('Poppins-Regular', '', 10);
        $t_cpdf2->SetY($t_cpdf2->GetY() + 6);
        $t_cpdf2->SetX(4);
        $t_cpdf2->Cell(0, 0, 'Alamat', 0, 0);
        $t_cpdf2->SetY($t_cpdf2->GetY());
        $t_cpdf2->SetX(23);
        $t_cpdf2->Cell(0, 0, ':', 0, 0);
        $t_cpdf2->SetY($t_cpdf2->GetY());
        $t_cpdf2->SetX(24.5);
        $t_cpdf2->MultiCell(58, 0, $invoice->ctc2_alamat, 0, 'L');

        $t_cpdf2->SetY($t_cpdf2->GetY() + 2);
        // HEADER DETAIL PEMESANAN
        $t_cpdf2->SetFont('Helvetica', '', 11);
        if ($data['sub'] = 'bmn') {
            $t_cpdf2->SetFillColor(69, 87, 123);
            $t_cpdf2->SetTextColor(255, 255, 255);
        }
        $t_cpdf2->SetX(5);
        $t_cpdf2->Cell(140, 10, 'Detail Pemesanan', 0, 1, 'L', true);
        $t_cpdf2->SetTextColor(0, 0, 0);

        $tbl = <<<EOD
        <table border="1" cellpadding="3">
            <thead>
                <tr>
                    <th align="center" width="30%"><b>DESKRIPSI</b></th>
                    <th align="center" width="20%"><b>JUMLAH</b></th>
                    <th align="center" width="25%"><b>HARGA</b></th>
                    <th align="center" width="25%"><b>TOTAL</b></th>
                </tr>
            </thead>
            <tbody>
    EOD;
        foreach ($invoice_details as $detail) {
            $tbl .= '<tr>';
            $tbl .= '<td width="30%">' . $detail->deskripsi . '</td>';
            $tbl .= '<td width="20%" style="text-align: center">' . $detail->jumlah . '</td>';
            $tbl .= '<td width="25%" style="text-align: center">' . 'Rp. ' . number_format($detail->harga, 0, ',', '.') . '</td>';
            $tbl .= '<td width="25%" style="text-align: center">' . 'Rp. ' . number_format($detail->total, 0, ',', '.') . '</td>';
            $tbl .= '</tr>';
        }
        $tbl .= <<<EOD
    </tbody>
</table>
EOD;

        $t_cpdf2->writeHTMLCell(142, 0, 4, $t_cpdf2->GetY() + 4, $tbl, 0, 1, false, true, 'L', true);

        $table2 = <<<EOD
            <table>
            <tbody>
            EOD;

        $total = 0;
        $diskon = $invoice->diskon;
        foreach ($invoice_details as $detail) {
            $total += $detail->total;
        }
        $grand_total = $total;

        if ($diskon != 0 || $diskon == '') {
            $table2 .= '<tr style="border: none;">';
            $table2 .= '<td colspan="2"></td>';
            $table2 .= '<td width="25%" style="border: 1px solid black; text-align: center">Diskon</td>';
            $table2 .= '<td width="25%" style="border: 1px solid black; text-align: center">Rp. ' . number_format($invoice->diskon, 0, ',', '.') .  '</td>';
            $table2 .= '</tr>';
            $grand_total = $total - $diskon;
        }
        $table2 .= '<tr style="border: none;">';
        $table2 .= '<td colspan="2" style="border: none;"></td>';
        $table2 .= '<td width="25%" style="border: 1px solid black; text-align: center;"> <b>Total</b></td>';
        $table2 .= '<td width="25%" style="border: 1px solid black; text-align: center;"> ' . 'Rp. ' . number_format($grand_total, 0, ',', '.') . '</td>';
        $table2 .= '</tr>';
        $table2 .=  <<<EOD
            <tbody>
            </table>
        EOD;

        $t_cpdf2->writeHTMLCell(142, 0, 4, $t_cpdf2->GetY(), $table2, 0, 1, false, true, 'L', true);

        if ($data['status'] == 1) {
            $text = 'Lunas';
            $x = $t_cpdf2->GetX() + 113;
            $y = $t_cpdf2->GetY() + 4;
            $radius = 12;
            $angle = 0;

            $t_cpdf2->SetTextColor(69, 87, 123);
            $t_cpdf2->SetAlpha(0.25);
            $t_cpdf2->SetFont('Courier', '', 20);

            $t_cpdf2->StartTransform();
            $t_cpdf2->Rotate($angle, $x, $y);

            $t_cpdf2->SetDrawColor(69, 87, 123);
            $t_cpdf2->SetLineWidth(1);
            // $t_cpdf2->Rect($x - 9, $y - 2, 45, 15);
            // $t_cpdf2->Ellipse($x, $y, 17, 8, 'D'); // Bentuk elips horizontal
            $t_cpdf2->Circle($x, $y, $radius, 'D'); // 'D' untuk border tanpa fill

            // $t_cpdf2->Text($x, $y, $text);
            $t_cpdf2->Text($x - 11, $y - 5, $text);

            $t_cpdf2->StopTransform();
            $t_cpdf2->SetTextColor(0, 0, 0); // Reset warna teks ke hitam
            $t_cpdf2->SetAlpha(1); // Pastikan tidak ada transparansi yang tersisa
        }

        $t_cpdf2->SetY($t_cpdf2->GetY() + 2);
        $t_cpdf2->SetX(4);
        $t_cpdf2->SetFont('Poppins-Bold', '', 11);
        $t_cpdf2->Cell(14, 9, 'Metode Pembayaran', 0, 1);
        $list = <<<EOD
        <ol>
        EOD;

        foreach ($invoice_rek as $rek) {
            $list .= '<li>Nama : ' . $rek->nama . '<br>Bank : ' . $rek->nama_bank . '<br>No. Rekening : ' . $rek->no_rek . '</li>';
        }
        $list .= <<<EOD
        </ol>
        EOD;
        $t_cpdf2->SetFont('Poppins-Regular', '', 10);
        $y = $t_cpdf2->GetY();
        $x = -4;
        $t_cpdf2->writeHTMLCell(0, 0, $x, $y, $list, 0, 1, false, true, 'L', true);

        $t_cpdf2->SetY($t_cpdf2->GetY() - 1);
        $t_cpdf2->SetX(4);

        $t_cpdf2->SetFont('Poppins-Bold', '', 11);
        $t_cpdf2->Cell(19, 9, 'Atas Nama : PT. Kolaborasi Para Sahabat', 0, 1);

        $t_cpdf2->setY($t_cpdf2->GetY());
        $t_cpdf2->SetX(4);

        $t_cpdf2->SetFont('Poppins-Regular', '', 10);
        $t_cpdf2->Cell(19, 9, 'Catatan :', 0, 1);
        if ($invoice->keterangan != '') {
            $catatan = $invoice->keterangan;
        } else {
            $catatan = '';
        }

        $t_cpdf2->writeHTMLCell(0, 0, 4, $t_cpdf2->GetY(), $catatan, 0, 1, false, true, 'L', true);

        $t_cpdf2->setY($t_cpdf2->GetY() + 3);
        $t_cpdf2->SetX(97);
        $t_cpdf2->SetFont('Poppins-Bold', '', 10);
        $t_cpdf2->Cell(26, 10, 'Terima Kasih, ', 0, 0);
        $t_cpdf2->SetFont('Poppins-Regular', '', 10);
        $t_cpdf2->Cell(19, 10, 'By Moment', 0, 1);

        // Output PDF
        if ($data['output'] == 'view') {
            $t_cpdf2->Output('Invoice ByMoment.pdf', 'I'); // 'I' untuk menampilkan di browser
        } elseif ($data['output'] == 'save') {
            $pdf_path = FCPATH . 'assets/backend/uploads/Invoice ByMoment.pdf'; // Simpan di folder uploads
            $t_cpdf2->Output($pdf_path, 'F'); // 'F' berarti simpan ke file
            // $t_cpdf2->Output('Invoice ByMoment.pdf', 'S'); // 'S' berarti string
        }
    }
}
