<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\MaintenancePDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenancePDFController extends Controller
{
    public function index(Request $request) 
    {
        $date = $request->date;
        if(Auth::user()->isSuperAdmin()){
            $maintenances = Maintenance::where('invoice_date', 'like', $date . '%')->get();
            $pdf = new MaintenancePDF();
            $pdf->download($maintenances);
        }
        elseif(Auth::user()->role_id == 2){
            $maintenances = Maintenance::where('invoice_date', 'like', $date . '%')->where('user_id',Auth::user()->id)->get();
            $pdf = new MaintenancePDF();
            $pdf->download($maintenances);
        }
        else
            abort(404);
    }

}