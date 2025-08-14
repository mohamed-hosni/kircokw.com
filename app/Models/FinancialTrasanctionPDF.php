<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $this->SetAutoPageBreak(TRUE, 34);
    }

    public function Footer()
    {
        $image_file = public_path('pdf/footer.png');
        $this->Image($image_file, 0, 275, 210, '', 'jpg', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }
}


class FinancialTrasanctionPDF extends Model
{
    use HasFactory;

    public function download()
    {
        if (Auth::user()->isSuperAdmin())
            $financial_transactions = Financial_transaction::all();
        elseif (Auth::user()->role_id == 2) {
            $financial_transactions = Financial_transaction::WhereHas('tenancy', function ($query) {
                $query->where('user_id', Auth::user()->id);
            })->get();
        }
        elseif (Auth::user()->role_id == 3) {
            $financial_transactions = Financial_transaction::where('tenant_id', Auth::user()->id)->get();
        }

        $view = view('admin.pages.financial.pdf', ['financial_transactions' => $financial_transactions]);
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
        $pdf->SetMargins(10, 38, 10, false);

        // add a page
        $pdf->AddPage();

        // output the HTML content
        $pdf->writeHTMLCell(0, 0, '', '', $html);

        //Close and output PDF document
        $pdf->Output('Appartment.pdf', 'I');
    }

    public function downloadInvoice(Request $request)
    {
        if (Auth::user()->isSuperAdmin())
            $financial_transaction = Financial_transaction::where('id', $request->id)->first();
        elseif (Auth::user()->role_id == 2) {
            $financial_transaction = Financial_transaction::WhereHas('tenancy', function ($query) {
                $query->where('user_id', Auth::user()->id);
            })->where('id', $request->id)->first();
        } elseif (Auth::user()->role_id == 3) {
            $financial_transaction = Financial_transaction::where('tenant_id', Auth::user()->id)->where('id', $request->id)->first();
        }else{
            abort(404);
        }

        $view = view('admin.pages.financial.invoice', [
            'financial_transaction' => $financial_transaction,
        ]);

        $html = $view->render();

        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetMargins(20, 0, 20);

        // set some language dependent data:
        $lg = array();
        $lg['a_meta_charset'] = 'UTF-8';
        // $lg['a_meta_dir'] = 'rtl';
        $lg['a_meta_language'] = 'fa';
        $lg['w_page'] = 'page';

        // set some language-dependent strings (optional)
        $pdf->setLanguageArray($lg);

        // set font
        $pdf->SetFont('aealarabiya', '', 16);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetMargins(10, 38, 10, false);

        // add a page
        $pdf->AddPage();

        // output the HTML content
        $pdf->writeHTMLCell(0, 0, '', '', $html);

        //Close and output PDF document
        $pdf->Output('FinancialTrasanction.pdf', 'I');
    }
}
