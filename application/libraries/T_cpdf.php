<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Memuat file TCPDF dari folder third_party
require_once(APPPATH . 'third_party/TCPDF-main/tcpdf.php');

class T_cpdf extends TCPDF
{
    // Page header
    function Header()
    {
        // Logo
        $this->SetFont('helvetica', 'B', 12);
        $this->Image(base_url('assets/backend/img/Header.png'), 49, 5, 160, 30);
        $this->Ln(5);
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        // $this->SetY(-15);
        // helvetica italic 8
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        // $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->Image(base_url('assets/backend/img/Footer.png'), 0, 280, 210, 5);
    }

    public function __construct()
    {
        parent::__construct();
    }
}
