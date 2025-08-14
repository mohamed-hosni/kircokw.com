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
use Illuminate\Support\Facades\DB;
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
        
        $paid_month_imploded=implode(",", $request->quantity);
        $date = \Carbon\Carbon::now();
        $request->merge([
            // 'merchantCode' => '842217',
            'merchantCode' => '86721223',
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
            'responseUrl' => 'https://kircokw.com/admin/payment/success',
            'failureUrl' => 'https://kircokw.com/admin/payment/failure',
            'version' => '2.0',
            'isOrderReference' => '1'
        ]);


        $payment_data = [
            'apartment_id' => $request->apartment_id,
            'user_id' => $request->user_id,  // CRITICAL: Must be saved for recovery
            'tenancy_id' => $request->tenant_id,
            'tenant_id' => $request->id,
            'cost' => $cost,
            'quantity' => count($request->quantity),
            'monthes' => $request->quantity,
            'time' => $date,
            'order_reference_number' => $date->timestamp . $request->id . $request->user_id . $request->tenant_id . $request->apartment_id,
            'logged_id' => $request->user_id  // Add this for TryTransaction table
        ];

        // Store in TryTransaction as backup for recovery
        TryTransaction::upsertInstance($payment_data);

        $paymentController = new PaymentController();

        $url = $paymentController->formSubmit($request);

        // ===== FIX #6: IMPROVED SESSION MANAGEMENT =====
        // OLD CODE (regenerating session during payment causes data loss):
        // $request->session()->regenerate();
        
        // NEW CODE: Don't regenerate session during payment process to avoid data loss
        // Store data with longer timeout and create backup
        session()->put('payment.data', $payment_data);
        
        // Increase cache timeout from 30 minutes to 2 hours for payment processing
        Cache::put('payment.data', $payment_data, now()->addHours(2));
        
        // Create backup cache with 7-day expiry for recovery
        Cache::put('payment.backup.' . $payment_data['order_reference_number'], $payment_data, now()->addDays(7));
        
        // Set cookie as additional backup
        setcookie('payment.data', json_encode($payment_data), time() + 7200, '/', '', true, true);

        $credential = [
            'id' => $request->user_id,
            'username' => Auth::user()->email,
            'password' => Auth::user()->password,
        ];

        // Increase credential cache timeout to match payment data timeout
        Cache::put('credential', $credential, now()->addHours(2));

        return $url;
    }

    static function paymentCash($request)
    {
        // ===== FIX #5: PAYMENT CASH VALIDATION =====
        // Add comprehensive validation before processing cash payment
        
        // Check if months are selected
        if (count($request->quantity) < 1) {
            Log::error('Cash payment: No months selected');
            return false;
        }
        
        // NEW CODE: Validate all required fields exist
        if (!$request->tenant_id || !$request->id || !$request->apartment_id || !$request->user_id) {
            Log::error('Cash payment missing required fields', [
                'tenant_id' => $request->tenant_id,
                'id' => $request->id,
                'apartment_id' => $request->apartment_id,
                'user_id' => $request->user_id
            ]);
            return false;
        }
        
        // NEW CODE: Verify tenant exists before processing payment
        $tenant = Tenant::find($request->tenant_id);
        if (!$tenant) {
            Log::error('Tenant not found for cash payment', ['tenant_id' => $request->tenant_id]);
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
        
        // ===== WRAP CASH PAYMENT IN TRANSACTION =====
        // Use database transaction to ensure all operations succeed or fail together
        DB::beginTransaction();
        try {
            // Create financial transaction record
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

            // OLD CODE (direct update without validation):
            // Tenant::where('id', $request->tenant_id)->update([
            //     'paid' => strtotime($request->quantity[count($request->quantity) - 1]) >= strtotime(date('M-Y')) ? 1 : 0,
            //     'end_payment' => $request->quantity[count($request->quantity) - 1],
            // ]);
            
            // NEW CODE: Update tenant with validation (we already verified tenant exists above)
            $last_payment_month = $request->quantity[count($request->quantity) - 1];
            $tenant->paid = strtotime($last_payment_month) >= strtotime(date('M-Y')) ? 1 : 0;
            $tenant->end_payment = $last_payment_month;
            $tenant->save();

            // Create payment record
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
            
            // Commit transaction if all operations succeeded
            DB::commit();
            
            Log::info('Cash payment processed successfully', [
                'tenant_id' => $request->tenant_id,
                'amount' => $cost,
                'months' => $request->quantity
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollback();
            
            Log::error('Cash payment processing failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $request->tenant_id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    static function saveData($request)
    {
        // ===== FIX #3: IMPROVED AUTHENTICATION CHECK =====
        // IMPORTANT: Hesabe payment gateway breaks the session when redirecting back
        // We MUST restore the user session from cached credentials
        // This is NOT optional - without this, user_id will be NULL after payment
        
        // OLD CODE (commented out - was missing proper validation):
        // if (!Auth::check()) {
        //     if (Cache::has('credential')) {
        //         $credential = Cache::get('credential');
        //     } else {
        //         return false;
        //     }
        //     $user = User::where('id', $credential['id'])->first();
        //     Auth::login($user);
        // }
        
        // NEW CODE: Always try to restore session after Hesabe redirect
        // Check if user is NOT logged in (session was broken by Hesabe)
        if (!Auth::check()) {
            // Try to restore from cached credentials
            if (Cache::has('credential')) {
                $credential = Cache::get('credential');
                // Use find() for better performance
                $user = User::find($credential['id']);
                if ($user) {
                    // Re-authenticate the user after payment gateway return
                    Auth::login($user);
                    Log::info('User session restored after payment gateway return', [
                        'user_id' => $user->id
                    ]);
                } else {
                    // User not found - this is critical
                    Log::error('User not found for stored credential', $credential);
                    // Don't return false immediately - try to continue with payment data recovery
                    // return false;
                }
            } else {
                // No cached credentials available
                Log::warning('No cached credentials available for session restoration');
                // Don't return false - try to process payment with available data
                // return false;
            }
        }

        $paymentController = new PaymentController();
        $response = $paymentController->getPaymentResponse($request->data);

        // ===== FIX #1: ROBUST DATA RETRIEVAL =====
        // OLD CODE (was causing NULL user_id):
        // $payment_data_cookie = json_decode(cookie('payment.data'));
        // $payment_data_cache = Cache::get('payment.data');
        
        // NEW CODE: Get payment data from most reliable source with priority order
        $payment_data = null;
        
        // Priority 1: Check cache first (most reliable)
        if (Cache::has('payment.data')) {
            $payment_data = Cache::get('payment.data');
        } 
        // Priority 2: Check session
        elseif (session()->has('payment.data')) {
            $payment_data = session()->get('payment.data');
        } 
        // Priority 3: Check cookie (least reliable)
        elseif (isset($_COOKIE['payment.data'])) {
            $payment_data = json_decode($_COOKIE['payment.data'], true);
        }
        
        // Recovery mechanism: Use the new recovery helper function
        if (!$payment_data && isset($response->response['orderReferenceNumber'])) {
            // Try the comprehensive recovery function from AsideHelper
            $payment_data = recoverPaymentData($response->response['orderReferenceNumber']);
            
            if ($payment_data) {
                Log::info('Payment data recovered successfully', [
                    'order_ref' => $response->response['orderReferenceNumber']
                ]);
            }
        }

        // Validate that we have critical fields before proceeding
        // IMPORTANT: Be less strict here since Hesabe breaks sessions
        if (!$payment_data) {
            Log::error('No payment data available at all', [
                'order_ref' => $response->response['orderReferenceNumber'] ?? null
            ]);
            return false;
        }
        
        // Check for critical fields but log warnings instead of failing immediately
        if (!isset($payment_data['user_id'])) {
            Log::error('CRITICAL: user_id missing from payment data', [
                'payment_data' => $payment_data,
                'order_ref' => $response->response['orderReferenceNumber'] ?? null
            ]);
            // Try to get user_id from current auth or cached credential
            if (Auth::check()) {
                $payment_data['user_id'] = Auth::id();
                Log::info('user_id recovered from authenticated session');
            } elseif (Cache::has('credential')) {
                $credential = Cache::get('credential');
                $payment_data['user_id'] = $credential['id'] ?? null;
                Log::info('user_id recovered from cached credential');
            } else {
                // Cannot proceed without user_id
                Log::error('Cannot recover user_id - payment cannot proceed');
                return false;
            }
        }
        
        if (!isset($payment_data['tenancy_id'])) {
            Log::error('tenancy_id missing from payment data', [
                'payment_data' => $payment_data,
                'order_ref' => $response->response['orderReferenceNumber'] ?? null  
            ]);
            // Try to get from response variables
            if (isset($response->response['variable2'])) {
                $payment_data['tenancy_id'] = $response->response['variable2'];
                Log::info('tenancy_id recovered from response variable2');
            } else {
                return false;
            }
        }

        $date = \Carbon\Carbon::now();

        // OLD CODE (multiple fallbacks causing NULL values):
        // $tenant_id = $response->response['variable1'] ?? $payment_data_cache['tenant_id'] ?? $payment_data_cookie['tenant_id'] ?? getDataFromPayment('tenant_id') ?? null;
        // $tenancy_id = $response->response['variable2'] ?? $payment_data_cache['tenancy_id'] ?? $payment_data_cookie['tenancy_id'] ?? getDataFromPayment('tenancy_id') ?? null;
        // $quantity = $response->response['variable3'] ?? $payment_data_cache['quantity'] ?? $payment_data_cookie['quantity'] ?? getDataFromPayment('quantity') ?? null;
        // $monthes = $payment_data_cache['monthes'] ?? $payment_data_cookie['monthes'] ?? getDataFromPayment('monthes') ?? $response->response['variable4'] ?? null;
        // $time = $payment_data_cache['time'] ?? $payment_data_cookie['time'] ?? getDataFromPayment('time') ?? $date;
        // $apartment_id = $response->response['variable5'] ?? $payment_data_cache['apartment_id'] ?? $payment_data_cookie['apartment_id'] ?? getDataFromPayment('apartment_id') ?? null;
        // $user_id = $payment_data_cache['user_id'] ?? $payment_data_cookie['user_id'] ?? getDataFromPayment('user_id') ?? null;
        
        // NEW CODE: Simplified data retrieval from validated payment_data
        $tenant_id = $response->response['variable1'] ?? $payment_data['tenant_id'];
        $tenancy_id = $response->response['variable2'] ?? $payment_data['tenancy_id'];
        $quantity = $response->response['variable3'] ?? $payment_data['quantity'];
        // Handle monthes - ensure it's an array
        $monthes = $payment_data['monthes'] ?? explode(',', $response->response['variable4'] ?? '');
        $apartment_id = $response->response['variable5'] ?? $payment_data['apartment_id'];
        // user_id is now guaranteed to exist from validation above
        $user_id = $payment_data['user_id'];
        $time = $payment_data['time'] ?? $date;

        // ===== FIX #4: STRONGER DUPLICATE PAYMENT CHECK =====
        // OLD CODE (only checked paymentToken):
        // if (Financial_transaction::where('paymentToken', $response->response['paymentToken'] ?? null)->exists())  {
        //     return false;
        // }
        
        // NEW CODE: Check both paymentToken AND orderReferenceNumber
        $existingPayment = Financial_transaction::where('paymentToken', $response->response['paymentToken'] ?? null)
            ->orWhere('orderReferenceNumber', $response->response['orderReferenceNumber'] ?? null)
            ->first();
            
        if ($existingPayment) {
            Log::warning('Duplicate payment attempt detected', [
                'token' => $response->response['paymentToken'] ?? null,
                'order_ref' => $response->response['orderReferenceNumber'] ?? null
            ]);
            return false;
        }

        // ===== FIX #2: DATABASE TRANSACTION WRAPPER =====
        // Wrap all database operations in a transaction to ensure data integrity
        DB::beginTransaction();
        try {
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
                // ===== IMPROVED TENANT UPDATE LOGIC =====
                // OLD CODE (didn't verify tenant exists):
                // $tenant = Tenant::where('id', $tenancy_id)->first();
                // if ($tenant && is_array($monthes) && count($monthes) > 0) {
                //     $tenant->paid = strtotime($monthes[count($monthes) - 1] ?? $tenant->end_payment) >= strtotime(date('M-Y')) ? 1 : 0;
                //     $tenant->end_payment = $monthes[count($monthes) - 1] ?? $tenant->end_payment ?? null;
                //     $tenant->save();
                // }
                
                // NEW CODE: Verify tenant exists before updating
                $tenant = Tenant::find($tenancy_id);
                if (!$tenant) {
                    Log::error('Tenant not found for payment', ['tenancy_id' => $tenancy_id]);
                    DB::rollback();
                    return false;
                }
                
                // Ensure monthes is array before processing
                if (!is_array($monthes)) {
                    $monthes = !empty($monthes) ? [$monthes] : [];
                }
                
                // Update tenant payment status if we have months data
                if (count($monthes) > 0) {
                    $last_month = $monthes[count($monthes) - 1];
                    $tenant->paid = strtotime($last_month) >= strtotime(date('M-Y')) ? 1 : 0;
                    $tenant->end_payment = $last_month;
                    $tenant->save();
                }

                // OLD CODE (kept commented as reference):
                // Tenant::where('id', $tenancy_id)->update([
                //     'paid' => 1,
                //     'end_payment' => $monthes[count($monthes) - 1] ?? null,
                // ]);

                // Create payment record with validation
                Payment::create([
                    'apartment_id' => $apartment_id,
                    'user_id' => $user_id,
                    'tenant_id' => $tenant_id,
                    'tenancy_id' => $tenancy_id,
                    'total_amount' => $response->response['amount'] ?? 0,
                    'financial_transaction_id' => $financial_transaction->id,
                    'pay_time' => $time,
                    'pay_monthes' => is_array($monthes) ? implode(",", $monthes) : ($monthes ?? ''),
                ]);
            }
            
            // Commit the transaction if everything succeeded
            DB::commit();
            
            // Clear data only after successful commit
            Cache::forget('payment.data');
            session()->forget('payment.data');
            setcookie('payment.data', '', time() - 3600, '/');
            
            // Don't regenerate session after successful payment to avoid losing data
            // OLD CODE: $request->session()->regenerate();
            
            return true;
            
        } catch (\Exception $e) {
            // Rollback transaction on any error
            DB::rollback();
            Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => compact('user_id', 'tenant_id', 'tenancy_id', 'apartment_id')
            ]);
            return false;
        }
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
        return  $this->hasMany(Compound::class);
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
