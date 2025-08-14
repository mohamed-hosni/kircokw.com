<?php

namespace App\Http\Controllers;

use App\Exports\ExportMaintenanceAll;
use App\Http\Requests\MaintenanceRequest;
use App\Models\Maintenance;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        if(Auth::user()->isSuperAdmin())
            return view('admin.pages.maintenance.index',[
                'maintenances' => Maintenance::filter($request->all())->orderBy('id', 'DESC')->paginate(100),
            ]);
        elseif(Auth::user()->role_id == 2)
            return view('admin.pages.maintenance.index',[
                'maintenances' => Maintenance::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->paginate(100),
            ]);
        else 
            abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function upsert(Maintenance $maintenance)
    {
        if(Auth::user()->isSuperAdmin())
            return view('admin.pages.maintenance.upsert', [
                'maintenance' => $maintenance,
            ]);
        else 
            abort(404);
    }
    
    public function add()
    {
        if(Auth::user()->isSuperAdmin())
            return view('admin.pages.maintenance.add');
        else 
            abort(404);
    }
    
    public function building(Request $request)
    {
        return Maintenance::buildingSelect($request);
    }

    public function modify(MaintenanceRequest $request)
    {
        return Maintenance::upsertInstance($request);
    }

    public function destroy(Maintenance $maintenance)
    {
        return $maintenance->deleteInstance();
    }
    
    // public function exportMaintenance(Request $request)
    // {
    //     $excel_name = date('F', strtotime(explode('?', $request->date)[0])) . "-" . Carbon::now()->format('H:i:s');

    //     if (Auth::user()->isSuperAdmin())
    //         return Excel::download(new ExportMaintenanceAll(), "$excel_name.xlsx");
    //     elseif (Auth::user()->role_id == 2) {
    //             return Excel::download(new ExportMaintenanceAll(), "$excel_name.xlsx");
    //     } else
    //         abort(404);
    // }
    
    
       public function exportMaintenance(Request $request)
    {
        
      //  dd($request->all());
        
        if(isset($request->from)&& isset($request->to)){
             $excel_name = date('F', strtotime($request->from)) . "-- to--  " . date('F', strtotime($request->to)) ;
        }
        else{
        $excel_name = date('F', strtotime(explode('?', $request->date)[0])) . "-" . Carbon::now()->format('H:i:s');
            }
        if (Auth::user()->isSuperAdmin())
            return Excel::download(new ExportMaintenanceAll($request), "$excel_name.xlsx");
        elseif (Auth::user()->role_id == 2) {
                return Excel::download(new ExportMaintenanceAll($request), "$excel_name.xlsx");
        } else
            abort(404);
    }
    

    public function filter(Request $request)
    {
        if(Auth::user()->isSuperAdmin())
            return view('admin.pages.maintenance.index',[
                'maintenances' => Maintenance::filter($request->all())->orderBy('id', 'DESC')->paginate(100),
            ]);
        elseif(Auth::user()->role_id == 2)
            return view('admin.pages.maintenance.index',[
                'maintenances' => Maintenance::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->paginate(100),
            ]);
        else 
            abort(404);
    }
}
