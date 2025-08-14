<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCPDF;

class MYPDF extends TCPDF
{
    public function _destroy($destroyall = false, $preserve_objcopy = false)
    {
        if ($destroyall) {
            unset($this->imagekeys);
        }
        parent::_destroy($destroyall, $preserve_objcopy);
    }
    public function Header()
    {
        $image_file = public_path('pdf/header.jpg');
        $this->Image($image_file, 0, 0, 210, '', 'jpg', '', 'T', false, 300, '', false, false, 0, false, false, false);

        $this->SetAutoPageBreak(TRUE, 0);
    }

    public function Footer()
    {
        $image_file = public_path('pdf/footer.jpg');
        $this->Image($image_file, 0, 275, 210, '', 'jpg', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }
}

class MaintenancePDF extends Model
{
    use HasFactory;

    public function download($maintenances)
    {
        $view = view('admin.pages.maintenance.pdf', ['maintenances' => $maintenances]);
        $html = $view->render();

        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set some language dependent data:
        $lg = array();
        $lg['a_meta_charset'] = 'UTF-8';
        $lg['a_meta_language'] = 'fa';
        $lg['w_page'] = 'page';

        // set some language-dependent strings (optional)
        $pdf->setLanguageArray($lg);

        // set font
        $pdf->SetFont('aealarabiya', '', 14);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->SetMargins(10, 40, 10, true);
        // add a page
        $pdf->AddPage();

        // output the HTML content
        $pdf->writeHTML($html, true, 0, true, 0);

        //Close and output PDF document
        $pdf->Output('Maintenance.pdf', 'I');
    }
}
