<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuildingRequest;
use App\Models\Building;
use App\Models\Compound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportApartment;
use App\Exports\ExportSheets;
use App\Http\Requests\BuildingModifyRequest;
use App\Models\User;
use Carbon\Carbon;
use App\Exports\ExportMonthlyTenants;

















class BuildingController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->isSuperAdmin())
            return view('admin.pages.building.index', [
                'buildings' => Building::whereNull('deleted_at')->filter($request->all())->paginate(10),
            ]);
        elseif (Auth::user()->role_id == 2) {
            $compounds = Compound::where('user_id', Auth::user()->id)->pluck('id');

            return view('admin.pages.building.index', [
                'buildings' => Building::WhereHas('compounds', function ($query) use ($compounds) {
                    $query->whereIn('compound_id', $compounds);
                })->paginate(10)
            ]);
        } else
            abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function upsert(Building $building)
    {
        if (Auth::user()->isSuperAdmin())
            return view('admin.pages.building.upsert', [
                'building' => $building,
                'compounds' => Compound::get(),
            ]);
        else
            abort(404);
    }

    public function editBuilding(Building $building)
    {
        if (Auth::user()->isSuperAdmin())
            return view('admin.pages.building.edit', [
                'building' => $building,
                'compounds' => Compound::get(),
            ]);
        elseif (Auth::user()->role_id == 2) {
            if ($building->compounds->first()->user_id == Auth::user()->id)
                return view('admin.pages.building.edit', [
                    'building' => $building,
                    'compounds' => Compound::get(),
                ]);
            else
                abort(404);
        } else
            abort(404);
    }

    public function exportApartment(Request $request)
    {
        $building = Building::where('id', $request->building_id)->first();

        if (Auth::user()->isSuperAdmin())
            return Excel::download(new ExportApartment($request), 'apartments.xlsx');
        elseif (Auth::user()->role_id == 2) {
            if ($building->compounds->first()->user_id == Auth::user()->id)
                return Excel::download(new ExportApartment($request), 'apartments.xlsx');
            else
                abort(404);
        } else
            abort(404);
    }

    public function exportRevenu(Request $request)
    {
        $building = Building::where('id', $request->building_id)->first();
        $excel_name = date('F', strtotime(explode('?', $request->date)[0])) . "-" . Carbon::now()->format('H:i:s') . "-" . $request->building_name;

        if (Auth::user()->isSuperAdmin())
            return Excel::download(new ExportSheets($request), "$excel_name.xlsx");
        elseif (Auth::user()->role_id == 2) {
            if ($building->compounds->first()->user_id == Auth::user()->id)
                return Excel::download(new ExportSheets($request), "$excel_name.xlsx");
            else
                abort(404);
        } else
            abort(404);
    }


    public function reportIndex(Request $request)
    {
        if (Auth::user()->isSuperAdmin()) {
            return view('admin.pages.building.report', [
                'buildings' => Building::filter($request->all())->paginate(20),
                'users' => User::where('role_id', OWNER)->orWhere('role_id', SUPERADMIN)->get(),
            ]);
        } elseif (Auth::user()->role_id == 2) {
            $compounds = Compound::where('user_id', Auth::user()->id)->pluck('id');
            return view('admin.pages.building.report', [
                'buildings' => Building::WhereHas('compounds', function ($query) use ($compounds) {
                    $query->whereIn('compound_id', $compounds);
                })->paginate(20),
                'users' => User::where('role_id', OWNER)->get(),
            ]);
        } else {
            abort(404);
        }
    }

    public function reportFilter(Request $request)
    {
        if (Auth::user()->isSuperAdmin()) {
            return view('admin.pages.building.report', [
                'buildings' => Building::filter($request->all())->paginate(10),
                'users' => User::where('role_id', OWNER)->orWhere('role_id', SUPERADMIN)->get(),
            ]);
        } elseif (Auth::user()->role_id == 2) {
            return view('admin.pages.building.report', [
                'buildings' => Building::where('user_id', Auth::user()->id)->paginate(10),
                'users' => User::where('role_id', OWNER)->get(),
            ]);
        } else {
            abort(404);
        }
    }

    public function reportUpsert(Building $building)
    {
        if (Auth::user()->isSuperAdmin()) {
            return view('admin.pages.building.report-upsert', [
                'building' => $building,
                'users' => User::where('role_id', OWNER)->orWhere('role_id', SUPERADMIN)->get(),
            ]);
        } else {
            abort(404);
        }
    }

    public function reportDestroy(Request $request)
    {
        return Building::reportDestroy($request);
    }

    public function reportModify(Request $request)
    {
        return Building::reportUpsert($request);
    }

    public function reportUpdateData(Request $request)
    {
        return Building::reportUpdateDate($request);
    }

    public function modify(BuildingRequest $request)
    {
        return Building::upsertInstance($request);
    }

    public function status(Request $request)
    {
        return Building::statusUpdate($request);
    }

    public function name(BuildingModifyRequest $request)
    {
        return Building::name($request);
    }

    public function edit(Request $request)
    {
        return Building::edit($request);
    }

    public function appartment(Request $request)
    {
        return Building::appartment($request);
    }

    public function appartmentDelete(Request $request)
    {
        return Building::appartmentDelete($request);
    }

    public function buildings(Request $request)
    {
        return Building::buildingSelect($request);
    }

    public function retrieveUser(Request $request)
    {
        return Building::retrieveUser($request);
    }

    public function retrieveCompound(Request $request)
    {
        return Building::retrieveCompound($request);
    }

    public function destroy(Building $building)
    {
        return $building->deleteInstance();
    }

    public function filter(Request $request)
    {
        return view('admin.pages.building.index', [
            'buildings' => Building::filter($request->all())->paginate(10)
        ]);
    }
    
        public function exportTenantsMonth(Request $request)
    {
        // $request->month على شكل "2025-03"
        // $request->building_id لو تحتاجه
    
        // تأكد أن month موجود وإلا أعد المستخدم مثلاً
        if (!$request->month) {
            return back()->withErrors('الرجاء اختيار الشهر');
        }
    
        // استدعِ الكلاس الذي أنشأناه
        return Excel::download(new ExportMonthlyTenants($request), 'Monthly_Tenants.xlsx');
    }
    
}
