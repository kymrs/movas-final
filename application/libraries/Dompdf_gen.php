<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Panggil autoload dari dompdf third_party
require_once APPPATH . 'third_party/dompdf/lib/Cpdf.php';

use Dompdf\Dompdf;

class Dompdf_gen extends Dompdf
{
    public function __construct()
    {
        parent::__construct();
    }
}
