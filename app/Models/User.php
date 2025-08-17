<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Hesabe\Controllers\PaymentController;
use Illuminate\Support\Facades\Log;
use Image;
use Session;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role_id',
        'national_id',
        'suspend',
        'trigger_block'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted()
    {
        if (Auth::hasUser()) {
            if (!Auth::user()->isSuperAdmin()) {
                static::addGlobalScope(new TenantScope());
            }
        }
    }

    static function upsertInstance($request)
    {
        $request->merge([
            'password' => Hash::make($request->password),
        ]);

        if ($request->password) {
            $user = User::updateOrCreate(
                [
                    'id' => $request->id ?? null
                ],
                $request->all()
            );
        } else {
            $user = User::updateOrCreate(
                [
                    'id' => $request->id ?? null
                ],
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'role_id' => $request->role_id,
                    'national_id' => $request->national_id,
                ]
            );
        }


        if ($request->file('picture')) {
            $name = 'picture_' . $user->id . '.' . $request->file('picture')->getClientOriginalExtension();

            if (!file_exists(public_path('users/' . $user->id . '/'))) {
                mkdir(public_path('users/' . $user->id . '/'));
            }

            $image = $request->file('picture');

            $image->move(public_path('users/' . $user->id . '/'), $name);

            $user->picture()->updateOrCreate(
                [
                    'imageable_id' => $user->id,
                    'use_for' => 'picture'
                ],
                [
                    'name' => $name,
                    'use_for' => 'picture'
                ]
            );
        } else {
            $user->picture()->delete();
        }

        if ($request->file('contract')) {
            $name2 = 'contract_' . $user->id . $request->file('contract')->getClientOriginalExtension();

            if (!file_exists(public_path('users/' . $user->id . '/'))) {
                mkdir(public_path('users/' . $user->id . '/'));
            }

            $image = $request->file('contract');

            $image->move(public_path('users/' . $user->id . '/'), $name2);

            $user->contract()->updateOrCreate(
                [
                    'imageable_id' => $user->id,
                    'use_for' => 'contract'
                ],
                [
                    'name' => $name2,
                    'use_for' => 'contract'
                ]
            );
        } else {
            $user->contract()->delete();
        }

        return $user;
    }

    static function modifyPassword($request)
    {
        $user = User::where('id', $request->id)->update(['password' => Hash::make($request->password)]);
    }

    static function userSelect($request)
    {
        $results = count($request->term) == 2 ? User::where(function ($query) {
            $query->where('role_id', OWNER)
                ->orWhere('role_id', SUPERADMIN);
        })->where('name', 'like', '%' . $request->term["term"] . '%')->take(10)->get()->toArray() : User::filter($request->all())->where('role_id', OWNER)->orWhere('role_id', SUPERADMIN)->take(10)->get()->toArray();

        return response()->json($results);
    }

    static function payment($request)
    {
        $tenant_relations = Tenant::where('id', $request->tenant_id)->first();
        $user_name = $tenant_relations->tenant->name;
        $user_email = $tenant_relations->tenant->email;
        $user_phone = $tenant_relations->tenant->phone;

        if (!$request->quantity) {
            $tenant = Tenant::where('id', $request->tenant_id)->first();
            if (!$tenant->end_payment) {
                $date = [strtotime('+1 month', strtotime($tenant->apartment->contract_date)), strtotime('+1 month', strtotime($tenant->apartment->building->contract_date)), strtotime($tenant->start_date)];
                $end_payment = max($date);
                $end_payment = date('M-Y', $end_payment);
            } else {
                $end_payment = date('M-Y', strtotime('+1 month', strtotime($tenant->end_payment)));
            }
            $request->merge([
                'quantity' => [$end_payment]
            ]);
        }

        $request->merge([
            'cost' => Tenant::where('id', $request->tenant_id)->first()->price
        ]);

        $current_month = date('Y-m');
        $start_date_month = explode('-', $request->start_date)[0] . '-' . explode('-', $request->start_date)[1];
        $current_date = explode('-', $request->start_date)[2];

        $cost = $request->cost * count($request->quantity);


        if ($tenant_relations->end_payment) {
            if (strtotime($request->quantity[0]) != strtotime('+1 month', strtotime($tenant_relations->end_payment))) {
                $quantities = $request->input('quantity');
                foreach ($quantities as $key => $value) {
                    $val = $key + 1;
                    $quantities[$key] = date('M-Y', strtotime("+{$val} month", strtotime($tenant_relations->end_payment)));
                }
                $request->quantity = $quantities;
            }
        }

        // if($start_date_month == $current_month && $request->quantity[0] == date('M-Y')){
        //     $day_numbers = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        //     $days_remainings = $day_numbers + 1 - $current_date;
        //     $cost = ( $request->cost / $day_numbers ) * $days_remainings;
        //     $cost += $request->cost * (count($request->quantity) - 1);
        //     $cost = number_format($cost, 3, '.', '');
        // }

        $paid_month_imploded = implode(",", $request->quantity);
        $date = \Carbon\Carbon::now();
        $request->merge([
            'merchantCode' => '842217',
            // live 'merchantCode' => '86721223',
            'amount' => $cost,
            'currency' => 'KWD',
            'paymentType' => '1',
            'orderReferenceNumber' => $date->timestamp . $request->id . $request->user_id . $request->tenant_id . $request->apartment_id,
            'variable1' => $request->id,
            'variable2' => $request->tenant_id,
            'variable3' => count($request->quantity),
            'variable4' => "$paid_month_imploded",
            'variable5' => $request->apartment_id,
            //this description will not appear or save to us but it will be appear to the message only to elkolify
            "description" => "$paid_month_imploded",
            'paymentType' => '0',
            'name' => $user_name,
            "email" => $user_email,
            'mobile_number' => $user_phone,
            // 'responseUrl' => 'https://kircokw.com/admin/payment/success',
            //'failureUrl' => 'https://kircokw.com/admin/payment/failure',
            'responseUrl' => 'http://127.0.0.1:8000/admin/payment/success',
            'failureUrl' => 'http://127.0.0.1:8000/admin/payment/failure',
            'version' => '2.0',
            'isOrderReference' => '1'
        ]);


        $payment_data = [
            'apartment_id' => $request->apartment_id,
            'user_id' => $request->user_id,
            'tenancy_id' => $request->tenant_id,
            'tenant_id' => $request->id,
            'cost' => $cost,
            'quantity' => count($request->quantity),
            'monthes' => $request->quantity,
            'time' => $date,
            'order_reference_number' => $date->timestamp . $request->id . $request->user_id . $request->tenant_id . $request->apartment_id
        ];

        TryTransaction::upsertInstance($payment_data);

        $paymentController = new PaymentController();

        $url = $paymentController->formSubmit($request);

        $request->session()->regenerate();
        session()->put('payment.data', $payment_data);
        Cache::put('payment.data', $payment_data, now()->addMinutes(30));
        cookie('payment.data', json_encode($payment_data));

        $credential = [
            'id' => $request->user_id,
            'username' => Auth::user()->email,
            'password' => Auth::user()->password,
        ];

        Cache::put('credential', $credential, now()->addMinutes(30));

        return $url;
    }

    static function paymentCash($request)
    {
        // check if the there is a month is selected
        if (count($request->quantity) < 1) {
            return false;
        }

        $current_month = date('Y-m');
        $start_date_month = explode('-', $request->start_date)[0] . '-' . explode('-', $request->start_date)[1];
        $current_date = explode('-', $request->start_date)[2];

        $cost = $request->cost * count($request->quantity);

        // if($start_date_month == $current_month && $request->quantity[0] == date('M-Y')){
        //     $day_numbers = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        //     $days_remainings = $day_numbers + 1 - $current_date;
        //     $cost = ( $request->cost / $day_numbers ) * $days_remainings;
        //     $cost += $request->cost * (count($request->quantity) - 1);
        // }

        $date = \Carbon\Carbon::now();
        $financial_transaction = Financial_transaction::create([
            'resultCode' => 'CAPTURED',
            'total_amount' => number_format($cost, 3, '.', ''),
            'paymentToken' => null,
            'paymentId' => null,
            'paidOn' => $date,
            'orderReferenceNumber' => $request->order_reference_number,
            'variable1' => null,
            'variable2' => null,
            'variable3' => null,
            'variable4' => null,
            'variable5' => null,
            'method' => 'CASH',
            'administrativeCharge' => null,
            'paid' => 1,
            'tenant_id' => $request->id,
            'tenancy_id' => $request->tenant_id,
            'quantity' => count($request->quantity),
        ]);

        Tenant::where('id', $request->tenant_id)->update([
            'paid' => strtotime($request->quantity[count($request->quantity) - 1]) >= strtotime(date('M-Y')) ? 1 : 0,
            'end_payment' => $request->quantity[count($request->quantity) - 1],
        ]);

        Payment::create([
            'apartment_id' => $request->apartment_id,
            'user_id' => $request->user_id,
            'tenant_id' => $request->id,
            'tenancy_id' => $request->tenant_id,
            'total_amount' => number_format($cost, 3, '.', ''),
            'financial_transaction_id' => $financial_transaction->id,
            'pay_time' => $date,
            'pay_monthes' => implode(',', $request->quantity),
            'notes' => $request->notes ?? null,
        ]);

        return true;
    }

    static function saveData($request)
    {
        // if (!Auth::check()) {
        //     if (Cache::has('credential')) {
        //         $credential = Cache::get('credential');
        //     } else {
        //         return false;
        //     }
        //     $user = User::where('id', $credential['id'])->first();
        //     Auth::login($user);
        // }

        $paymentController = new PaymentController();
        $response = $paymentController->getPaymentResponse($request->data);

        //  $payment_data_cookie = json_decode(cookie('payment.data'));

        //because we use it as associative array
        $payment_data_cookie = json_decode(cookie('payment.data'), true) ?? [];
        $payment_data_cache = Cache::get('payment.data');

        $date = \Carbon\Carbon::now();

        // Store payment response data in cookie
        // $responseData = $response->response;
        // $filePath = storage_path('app/payment_response_data.txt');
        // file_put_contents($filePath, json_encode($responseData, JSON_PRETTY_PRINT));



        $tenant_id = $response->response['variable1'] ?? $payment_data_cache['tenant_id'] ?? $payment_data_cookie['tenant_id'] ?? getDataFromPayment('tenant_id') ?? null;
        $tenancy_id = $response->response['variable2'] ?? $payment_data_cache['tenancy_id'] ?? $payment_data_cookie['tenancy_id'] ?? getDataFromPayment('tenancy_id') ?? null;
        $quantity = $response->response['variable3'] ?? $payment_data_cache['quantity'] ?? $payment_data_cookie['quantity'] ?? getDataFromPayment('quantity') ?? null;
        $monthes = $payment_data_cache['monthes'] ?? $payment_data_cookie['monthes'] ?? getDataFromPayment('monthes') ?? $response->response['variable4'] ?? null;
        $time = $payment_data_cache['time'] ?? $payment_data_cookie['time'] ?? getDataFromPayment('time') ?? $date;
        $apartment_id = $response->response['variable5'] ?? $payment_data_cache['apartment_id'] ?? $payment_data_cookie['apartment_id'] ?? getDataFromPayment('apartment_id') ?? null;


        // جِب user_id من العقد لو ناقص
        $user_id_from_tenant = null;
        if ($tenancy_id) {
            $tenancy_row = Tenant::find($tenancy_id);
            if ($tenancy_row) {
                $user_id_from_tenant = $tenancy_row->user_id;
            }
        }

        $user_id = ($payment_data_cache['user_id'] ?? null)
            ?? ($payment_data_cookie['user_id'] ?? null)
            ?? getDataFromPayment('user_id')
            ?? $user_id_from_tenant
            ?? null;


        //check if the monthes is an array 

        // ✨ هنا التطبيع المهم
        //that because some thing as  $response->response['variable4']  it may come as    "variable4" => "Feb-2026,Mar-2026"
        if (!is_array($monthes)) {
            if (is_string($monthes)) {
                // مثال: "Aug-2025,Sep-2025" -> ["Aug-2025", "Sep-2025"]
                $monthes = preg_split('/\s*,\s*/', $monthes, -1, PREG_SPLIT_NO_EMPTY);
            } else {
                //keep it make error 
                // $monthes = [];
            }
        }




        if (Financial_transaction::where('paymentToken', $response->response['paymentToken'] ?? null)->exists()) {
            return false;
        }

        $financial_transaction = Financial_transaction::create([
            'resultCode' => $response->status ? 'CAPTURED' : 'NOT CAPTURED',
            'total_amount' => $response->response['amount'] ?? null,
            'paymentToken' => $response->response['paymentToken'] ?? null,
            'paymentId' => $response->response['paymentId'] ?? null,
            'paidOn' => $response->response['paidOn'] ?? null,
            'orderReferenceNumber' => $response->response['orderReferenceNumber'] ?? null,
            'variable1' => $response->response['variable1'] ?? null,
            'variable2' => $response->response['variable2'] ?? null,
            'variable3' => $response->response['variable3'] ?? null,
            'variable4' => $response->response['variable4'] ?? null,
            'variable5' => $response->response['variable5'] ?? null,
            'method' => $response->response['method'] ?? null,
            'administrativeCharge' => $response->response['administrativeCharge'] ?? null,
            'paid' => $response->status ? 1 : 0,
            'tenant_id' => $tenant_id ?? null,
            'tenancy_id' => $tenancy_id ?? null,
            'quantity' => $quantity ?? null,
        ]);

        if ($response->status) {
            $tenant = Tenant::where('id', $tenancy_id)->first();

            if ($tenant && is_array($monthes) && count($monthes) > 0) {
                $tenant->paid = strtotime($monthes[count($monthes) - 1] ?? $tenant->end_payment) >= strtotime(date('M-Y')) ? 1 : 0;
                $tenant->end_payment = $monthes[count($monthes) - 1] ?? $tenant->end_payment ?? null;
                $tenant->save();
            }

            // Tenant::where('id', $tenancy_id)->update([
            //     'paid' => 1,
            //     'end_payment' => $monthes[count($monthes) - 1] ?? null,
            // ]);

            try {
                Payment::create([
                    'apartment_id' => $apartment_id ?? null,
                    'user_id' => $user_id ?? null,
                    'tenant_id' => $tenant_id ?? null,
                    'tenancy_id' => $tenancy_id ?? null,
                    'total_amount' => isset($response->response['amount']) ? $response->response['amount'] : null,
                    'financial_transaction_id' => $financial_transaction->id ?? null,
                    'pay_time' => $time ?? null,
                    'pay_monthes' => is_array($monthes) ? implode(",", $monthes) : ($monthes ?? null),
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating payment', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'data' => [
                        'apartment_id' => $apartment_id,
                        'user_id' => $user_id,
                        'tenant_id' => $tenant_id,
                        'tenancy_id' => $tenancy_id,
                        'response' => $response,
                        'financial_transaction' => $financial_transaction,
                        'time' => $time,
                        'monthes' => $monthes,
                    ],
                ]);
            }
        }

        Cache::flush();
        cookie()->forget('payment.data');
        $request->session()->regenerate();

        return true;
    }

    static function usersTenant($request)
    {
        $results = count($request->term) == 2 ? User::where('role_id', TENANT)->where('name', 'like', '%' . $request->term["term"] . '%')->take(10)->get()->toArray() : User::filter($request->all())->where('role_id', TENANT)->take(10)->get()->toArray();

        return response()->json($results);
    }

    public function scopeFilter($query, $request)
    {
        if (isset($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%')
                ->orWhere('national_id', 'like', '%' . $request['name'] . '%')
                ->orWhere('email', 'like', '%' . $request['name'] . '%')
                ->orWhere('phone', 'like', '%' . $request['name'] . '%');
        }

        return $query;
    }

    //Roles
    public function isSuperAdmin()
    {
        return Auth::user()->role_id == SUPERADMIN;
    }

    public function isOwner()
    {
        return Auth::user()->role_id == OWNER;
    }

    public function isTenant()
    {
        return Auth::user()->role_id == TENANT;
    }

    public function deleteInstance()
    {
        foreach ($this->compounds as $compound) {
            foreach ($compound->buildings as $building) {
                if (count($building->compounds) > 1) {
                    $building->pivot->where('building_compound.compound_id', $compound->id)->delete();
                } else {
                    Tenant::where('building_id', $building->id)->delete();
                    Maintenance::where('building_id', $building->id)->delete();
                    Apartment::where('building_id', $building->id)->delete();
                    Building::where('id', $building->id)->delete();
                }
            }
        }

        $this->compounds()->delete();
        return $this->delete();
    }

    public function picture()
    {
        return $this->morphOne(Gallary::class, 'imageable')->where('use_for', 'picture');
    }

    public function contract()
    {
        return $this->morphOne(Gallary::class, 'imageable')->where('use_for', 'contract');
    }

    public function compounds()
    {
        return $this->hasMany(Compound::class);
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'tenant_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'tenant_id');
    }
}
