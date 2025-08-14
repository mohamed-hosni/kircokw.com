<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaintenanceRequest;
use App\Models\Revenue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->isSuperAdmin()) {
            return view('admin.pages.revenue.index', [
                'revenues' => Revenue::filter($request->all())->paginate(10),
            ]);
        } elseif (Auth::user()->role_id == 2) {
            return view('admin.pages.revenue.index', [
                'revenues' => Revenue::where('user_id', Auth::user()->id)->paginate(10),
            ]);
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function upsert(Revenue $revenues)
    {
        if (Auth::user()->isSuperAdmin()) {
            return view('admin.pages.revenue.upsert', [
                'revenue' => $revenues,
            ]);
        } else {
            abort(404);
        }
    }

    public function add()
    {
        if (Auth::user()->isSuperAdmin()) {

            return view('admin.pages.revenue.add');
        } else {

            abort(404);
        }
    }

    public function building(Request $request)
    {
        return Revenue::buildingSelect($request);
    }

    public function modify(MaintenanceRequest $request)
    {
        return Revenue::upsertInstance($request);
    }

    public function destroy(Revenue $revenue)
    {
        return $revenue->deleteInstance();
    }

    public function filter(Request $request)
    {
        return view('admin.pages.revenue.index', [
            'revenues' => Revenue::filter($request->all())->paginate(10)
        ]);
    }
}
