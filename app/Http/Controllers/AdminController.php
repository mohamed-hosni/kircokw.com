<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Apartment;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $tenants = Tenant::where('paid', 1)->get();
        
        foreach($tenants as $tenant){
            // $day = date('d', strtotime($tenant->start_date));
            // $today = date('Y-m-d');
            // $end_payment = date('Y-m-d', strtotime($tenant->end_payment . '+ 1 month + ' . $day . ' days'));
            
            $today = date('Y-m');
            $end_payment = date('Y-m', strtotime($tenant->end_payment . '+ 1 month'));
            
            if($today >= $end_payment){
                $tenant->paid = 0;
                $tenant->save();
            }
        }
        
        // $day = date('d');
        // $block_date = date('M-Y');
        // $block_date_year = date('Y-m-d');
        // $trigger = User::where('id', 1)->first();
        
        // if($day == '21' && !$trigger->trigger_block){
        //     $tenants_blocked = Tenant::where('paid', 0)->where('end_payment', '<', $block_date)->whereNotNull('start_date')->whereNull('expire_date')->update([ 'is_blocked' => 1 ]);
        //     $tenants_blocked = Tenant::where('paid', 0)->whereNull('end_payment')->where('start_date', '<', $block_date_year)->whereNull('expire_date')->update([ 'is_blocked' => 1 ]);
        //     $trigger->trigger_block = 1;
        //     $trigger->save();
        // }
        
        // if($day == '1'){
        //     User::where('id', 1)->update([ 'trigger_block' => 0 ]);
        // }
        
        if(Auth::user()->role_id == 3){
            $tenants = DB::table('apartments')
                ->Join('tenants',  function($query){
                    $query->on('tenants.apartment_id', '=', 'apartments.id')
                        ->whereRaw('tenants.id IN (select MAX(tenants.id) from tenants join apartments on apartments.id = tenants.apartment_id WHERE tenants.deleted_at is NULL group by apartments.id)');
                        
                })
                ->leftJoin('buildings', 'buildings.id' , '=' , 'apartments.building_id')
                ->leftJoin('gallaries',  function($query){
                    $query->on('gallaries.imageable_id', '=', 'tenants.id')
                        ->where('gallaries.imageable_type', 'App\Models\Tenant')
                        ->whereNull('gallaries.deleted_at');
                })
                ->select([
                    'tenants.*',
                    'apartments.id as apartment_id',
                    'apartments.name as apartment_name',
                    'buildings.name as building_name',
                    'gallaries.name as picture_name',
                ])
                ->whereNull('tenants.deleted_at')
                ->whereNull('apartments.deleted_at')
                ->whereNull('tenants.expire_date')
                ->where('tenants.tenant_id', Auth::user()->id)
                ->where('apartments.status', 1)->get();

            return view('admin.pages.dashboard', ['tenants' => $tenants]);
        }
        else {
            return view('admin.pages.dashboard', ['tenants' => []]);
        }
    }
    
    public function getData(Request $request)
    {
        return Tenant::data($request);
    }

    public function unpaidUsers(Request $request)
    {
        if(Auth::user()->isSuperAdmin()) {
            $apartments = Apartment::join('buildings', 'buildings.id', '=', 'apartments.building_id')
                ->join('tenants', function($query) {
                    $query->on('tenants.apartment_id', '=', 'apartments.id')
                        ->whereRaw('tenants.id IN (select MAX(tenants.id) from tenants WHERE deleted_at IS NULL  GROUP BY tenants.apartment_id)');
                })
                ->join('users', 'users.id', '=', 'tenants.tenant_id')
                ->leftJoin('payments as payments1', function($query) {
                    $query->on('payments1.tenancy_id', '=', 'tenants.id')
                        ->whereRaw('payments1.id IN (select MAX(payments.id) from payments WHERE deleted_at IS NULL GROUP BY payments.tenancy_id)');
                })
                ->leftJoin('payments as payments2', function($query) {
                    $query->on('payments2.tenancy_id', '=', 'tenants.id')
                        ->whereRaw('payments2.id IN (select MAX(payments.id) from payments  WHERE deleted_at IS NULL and payments.id <> payments1.id GROUP BY payments.tenancy_id)');
                })
                ->leftJoin('payments as payments3', function($query) {
                    $query->on('payments3.tenancy_id', '=', 'tenants.id')
                        ->whereRaw('payments3.id IN (select MAX(payments.id) from payments WHERE deleted_at IS NULL and payments.id <> payments1.id AND payments.id <> payments2.id GROUP BY payments.tenancy_id)');
                })
               
                ->select(
                    'apartments.id as apartment_id',
                    'apartments.name as apartment_name',
                    'buildings.name as building_name',
                    'users.id as user_id',
                    'users.name as user_name',
                    'users.phone as user_phone',
                    'tenants.id as tenant_id',
                    'tenants.start_date as tenant_start_date',
                    'tenants.end_payment as tenant_end_payment',
                    'tenants.paid as tenant_paid',
                    'tenants.price as tenant_price',
                    'tenants.is_blocked as tenant_is_blocked',
                    'tenants.created_at as tenant_created_at',
                    'tenants.updated_at as tenant_updated_at',
                    'payments1.id as payment_id1',
                    'payments1.pay_monthes as payment_amount1',
                    'payments1.pay_time as payment_created_at1',
                    'payments2.id as payment_id2',
                    'payments2.pay_monthes as payment_amount2',
                    'payments2.pay_time as payment_created_at2',
                    'payments3.id as payment_id3',
                    'payments3.pay_monthes as payment_amount3',
                    'payments3.pay_time as payment_created_at3',
                )
                ->where('tenants.paid', 0)
                ->whereNull('tenants.deleted_at')
                ->get();

            return view('admin.pages.unpaid.index', [
                'apartments' => $apartments,
            ]);
        } else {
            abort(404);
        }
    }

    public function filterUnpaidUsers(Request $request)
    {
        if (Auth::user()->isSuperAdmin()) {
            $apartments = Apartment::join('buildings', 'buildings.id', '=', 'apartments.building_id')
                ->join('tenants', function ($query) {
                    $query->on('tenants.apartment_id', '=', 'apartments.id')
                        ->whereRaw('tenants.id IN (select MAX(tenants.id) from tenants GROUP BY tenants.apartment_id)');
                })
                ->join('users', 'users.id', '=', 'tenants.tenant_id')
                ->leftJoin('payments as payments1', function ($query) {
                    $query->on('payments1.tenancy_id', '=', 'tenants.id')
                        ->whereRaw('payments1.id IN (select MAX(payments.id) from payments GROUP BY payments.tenancy_id)');
                })
                ->leftJoin('payments as payments2', function ($query) {
                    $query->on('payments2.tenancy_id', '=', 'tenants.id')
                        ->whereRaw('payments2.id IN (select MAX(payments.id) from payments WHERE payments.id <> payments1.id GROUP BY payments.tenancy_id)');
                })
                ->leftJoin('payments as payments3', function ($query) {
                    $query->on('payments3.tenancy_id', '=', 'tenants.id')
                        ->whereRaw('payments3.id IN (select MAX(payments.id) from payments WHERE payments.id <> payments1.id AND payments.id <> payments2.id GROUP BY payments.tenancy_id)');
                })
                ->select(
                    'apartments.id as apartment_id',
                    'apartments.name as apartment_name',
                    'buildings.name as building_name',
                    'users.id as user_id',
                    'users.name as user_name',
                    'users.phone as user_phone',
                    'tenants.id as tenant_id',
                    'tenants.start_date as tenant_start_date',
                    'tenants.end_payment as tenant_end_payment',
                    'tenants.paid as tenant_paid',
                    'tenants.price as tenant_price',
                    'tenants.is_blocked as tenant_is_blocked',
                    'tenants.created_at as tenant_created_at',
                    'tenants.updated_at as tenant_updated_at',
                    'payments1.id as payment_id1',
                    'payments1.pay_monthes as payment_amount1',
                    'payments1.pay_time as payment_created_at1',
                    'payments2.id as payment_id2',
                    'payments2.pay_monthes as payment_amount2',
                    'payments2.pay_time as payment_created_at2',
                    'payments3.id as payment_id3',
                    'payments3.pay_monthes as payment_amount3',
                    'payments3.pay_time as payment_created_at3',
                )
                ->where('tenants.paid', 0)
                ->where('buildings.name', "LIKE", "%" . $request->input('building') . "%")
                ->whereNull('tenants.deleted_at')
                ->get();

            return view('admin.pages.unpaid.index', [
                'apartments' => $apartments,
            ]);
        } else {
            abort(404);
        }
    }
}
