<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TryTransaction;


class TryTransactionController extends Controller
{
    public function showAll()
    {
        if(Auth::user()->isSuperAdmin()){
           $tryTransactions= TryTransaction::showAll();
            return view('admin.pages.tryTransactions.index',["tryTransactions"=>$tryTransactions]);  
         }
         else{
              abort(404);
         }

    }

    public function filter(Request $request)
    {
        if(Auth::user()->isSuperAdmin())
            return view('admin.pages.tryTransactions.index',['tryTransactions' => TryTransaction::filter($request->all())]);
        // elseif(Auth::user()->role_id == 2){
        //     return view('admin.pages.financial.index',[
        //         'financial_transactions' => TryTransaction::WhereHas('tenancy', function ($query) {
        //             $query->where('user_id', Auth::user()->id);
        //         })->orderByDesc('created_at')->paginate(100),
        //     ]);
        // }
        // elseif(Auth::user()->role_id == 3){
        //     return view('admin.pages.financial.index',[
        //       'financial_transactions' => TryTransaction::where('tenant_id', Auth::user()->id)->orderByDesc('created_at')->paginate(100)
        //     ]);
        // }
        else 
            abort(404);
    }



}
