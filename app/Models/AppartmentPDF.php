<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCPDF;

class MYPDF extends TCPDF {
    public function _destroy($destroyall = false, $preserve_objcopy = false)
    {
        if ($destroyall) {
            unset($this->imagekeys);
        }
        parent::_destroy($destroyall, $preserve_objcopy);
    }

    // public function Header() {
        
    //     $bMargin = $this->getBreakMargin();
    //     $auto_page_break = $this->AutoPageBreak;

    //     // $img_file = "upload/" . $setting->pdf_background;
    //     $pdf_file = public_path('pdf/back.jpg');
    //     $this->Image($pdf_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
        
    //     $this->SetAutoPageBreak($auto_page_break, $bMargin);
    //     $this->setPageMark();
    // }
    
    public function Header()
    {
        $auto_page_break = $this->AutoPageBreak;
        
        $image_file = public_path('pdf/header.jpg');
        $this->Image($image_file, 0, 0, 210, '', 'jpg', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetAutoPageBreak($auto_page_break, 34);
        $this->setPageMark();        
        
    }

    public function Footer()
    {
        $image_file = public_path('pdf/footer.jpg');
        $this->Image($image_file, 0, 275, 210, '', 'jpg', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }
}
class AppartmentPDF extends Model
{
    use HasFactory;

    public function download($building)
    {
        $view = view('admin.pages.apartment.pdf', ['building' => $building]);
        $html = $view->render();

        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set some language dependent data:
        $lg = array();
        $lg['a_meta_charset'] = 'UTF-8';
        // $lg['a_meta_dir'] = 'rtl';
        $lg['a_meta_language'] = 'fa';
        $lg['w_page'] = 'page';

        // set some language-dependent strings (optional)
        $pdf->setLanguageArray($lg);

        // set font
        $pdf->SetFont('aealarabiya', '', 14);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // add a page
        $pdf->SetMargins(10, 40, 10, true);

        $pdf->AddPage();

        // output the HTML content
        $pdf->writeHTML($html, true, 0, true, 0);

        //Close and output PDF document
        $pdf->Output('Apartment.pdf', 'I');
    }
}
