<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'apartment_id',
        'tenancy_id',
        'total_amount',
        'pay_monthes',
        'tenant_id',
        'logged_id',
        'mac_id',
        'order_reference_number'
    ];
   
    public static function upsertInstance($request)
    {
        return TryTransaction::create([
            'apartment_id' => $request['apartment_id'],
            'tenancy_id' => $request['tenancy_id'],
            'total_amount' => $request['cost'],
            'pay_monthes' => implode(',', $request['monthes']),
            'tenant_id' => $request['tenant_id'],
            'logged_id' => Auth::user()->id,
            'mac_id' => $_SERVER['REMOTE_ADDR'],
            'order_reference_number'  => $request['order_reference_number'] ?? null,
        ]);
    }
    public static function ShowAll(){
        $tryTransactions = TryTransaction::join('users', 'try_transactions.tenant_id', '=', 'users.id')
            ->join('apartments', 'try_transactions.apartment_id', '=', 'apartments.id')
            ->join("buildings","apartments.building_id","=","buildings.id")
            ->join("building_compound","building_compound.building_id","=","buildings.id")
            ->join("compounds","compounds.id","=","building_compound.compound_id")
            // Fix typo here
            ->select(
                'try_transactions.*',
                'users.id as user_id',
                'users.name as user_name',
                'apartments.id as appartment_id', // Fix typo here
                'apartments.name as appartment_name', // Fix typo here
                'buildings.id as building_id',
                'buildings.name as building_name',
                'building_compound.building_id as building_compound_building_id',
                'building_compound.compound_id as building_compound_compound_id',
                'compounds.id as compound_id',
                'compounds.name as compound_name'
            )->orderBy("try_transactions.created_at","desc")
        ->paginate(30);

    return $tryTransactions;
   
    }
    
    
    
     public static function filter($request)
    {            

        $tryTransactions = TryTransaction::join('users', 'try_transactions.tenant_id', '=', 'users.id')
            ->join('apartments', 'try_transactions.apartment_id', '=', 'apartments.id')
            ->join("buildings","apartments.building_id","=","buildings.id")
            ->join("building_compound","building_compound.building_id","=","buildings.id")
            ->join("compounds","compounds.id","=","building_compound.compound_id")
            ->select(
                'try_transactions.*',
                'users.id as user_id',
                'users.name as user_name',
                'apartments.id as appartment_id',
                'apartments.name as appartment_name',
                'buildings.id as building_id',
                'buildings.name as building_name',
                'building_compound.building_id as building_compound_building_id',
                'building_compound.compound_id as building_compound_compound_id',
                'compounds.id as compound_id',
                'compounds.name as compound_name'
            )
            ->where("users.name", "like", "%".$request['name']."%")
            ->orderBy("try_transactions.created_at","desc")
            ->paginate(100);
        
        return ($tryTransactions);
 
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id');
    }

    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class, 'tenancy_id');
    }
    
    

    
}
