<?php 

// Add necessary imports for the recovery function
use Illuminate\Support\Facades\Cache;

function is_active($name) {
    $route_name = request()->route()->getName();    
    $route_name_array = explode('.',$route_name);

    if ( in_array($name,$route_name_array) )
        return 'active';

}

function getDataFromPayment($col)
{
    if (!empty(session()->get('payment.data')) ) {
        $session_collection = collect(session()->get('payment.data'));
        
        if (count($session_collection) ) {
            return collect(session()->get('payment.data'))[$col] ?? null;
        }
    }

    return null;
}

/**
 * ===== FIX #7: PAYMENT DATA RECOVERY MECHANISM =====
 * Recovers payment data from various backup sources when main sources fail
 * This helps prevent NULL user_id issues when session/cache is lost
 * 
 * @param string $orderReferenceNumber The order reference number to recover data for
 * @return array|null Payment data array if found, null otherwise
 */
function recoverPaymentData($orderReferenceNumber) {
    // Priority 1: Try to recover from backup cache (7-day retention)
    if (Cache::has('payment.backup.' . $orderReferenceNumber)) {
        return Cache::get('payment.backup.' . $orderReferenceNumber);
    }
    
    // Priority 2: Try to recover from TryTransaction database record
    $tryTransaction = \App\Models\TryTransaction::where('order_reference_number', $orderReferenceNumber)->first();
    if ($tryTransaction) {
        // Reconstruct payment data from TryTransaction record
        return [
            'user_id' => $tryTransaction->logged_id,        // Critical field
            'tenant_id' => $tryTransaction->tenant_id,       
            'tenancy_id' => $tryTransaction->tenancy_id,     // Critical field
            'apartment_id' => $tryTransaction->apartment_id,
            'monthes' => explode(',', $tryTransaction->pay_monthes),
            'quantity' => count(explode(',', $tryTransaction->pay_monthes)),
            'cost' => $tryTransaction->total_amount,
            'time' => $tryTransaction->created_at,
            'order_reference_number' => $orderReferenceNumber
        ];
    }
    
    // Return null if no recovery source available
    return null;
}