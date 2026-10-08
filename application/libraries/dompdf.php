<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// Pakai namespace Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf
{
    protected $dompdf;

    public function __construct()
    {
        // Load autoloader Dompdf dari third_party
        require_once APPPATH . 'third_party/dompdf/lib/Cpdf.php';

        // Konfigurasi Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Kalau ada gambar dari URL

        // Inisialisasi Dompdf
        $this->dompdf = new Dompdf($options);
    }

    /**
     * Generate PDF
     *
     * @param string  $html        Konten HTML yang mau diubah jadi PDF
     * @param string  $filename    Nama file PDF
     * @param string  $paper       Ukuran kertas (default A4)
     * @param string  $orientation Portrait atau Landscape
     * @param boolean $stream      True = tampil di browser, False = hanya return output
     */
    public function generate($html, $filename = 'document.pdf', $paper = 'A4', $orientation = 'portrait', $stream = true)
    {
        $this->dompdf->setPaper($paper, $orientation);
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();

        if ($stream) {
            $this->dompdf->stream($filename, ['Attachment' => false]);
        } else {
            return $this->dompdf->output();
        }
    }
}
