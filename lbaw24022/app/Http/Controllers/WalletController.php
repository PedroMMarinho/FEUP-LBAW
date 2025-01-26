<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auction;
use App\Models\UserTransaction;
use App\Models\GeneralUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PayPalController;


class WalletController extends Controller
{

    public function show()
    {
        if(Auth::user()->role !== 'Regular User')
        {
            return redirect('/')->with('error', 'Cannot access this area');
        }

        return view('wallet.wallet');
    }

    public function withdraw(Request $request) 
    {
        session()->flash('transferType', 'Withdraw');


        if(Auth::user()->role !== 'Regular User')
        {
            return redirect('/')->with('error', 'Cannot access this area');
        }

        $user = $request->user()->specificRole();
        $wallet = $user->wallet;
        $available = $user->available_balance;
        $ibanRegex = '/^PT\d{2}\s?\d{4}\s?\d{4}\s?\d{4}\s?\d{4}\s?\d{4}\s?\d{1}$/';


        $request->validate([
            'withdrawAmount' => "required|numeric|min:1|max:$available",
            'iban' => "required|string|regex:$ibanRegex",
        ]);

        // Create transaction type wallet (trigger takes out money from user)
        $transaction = UserTransaction::create([
            'amount' => -$request['withdrawAmount'],
            'transaction_type' => 'Wallet',
            'user_id' => $user->id,
        ]);



        return back()->with([
            'success' => 'Withdrawal succesfull',
        ]);
    }


    public function deposit(int $amount)
    {

        $transaction = UserTransaction::create([
            'amount' => $amount,
            'transaction_type' => 'Wallet',
            'user_id' => Auth::user()->id,
        ]);

    }
}