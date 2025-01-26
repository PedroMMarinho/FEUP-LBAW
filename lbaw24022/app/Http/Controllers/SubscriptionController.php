<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteConfig;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function show()
    {
        if(Auth::user()->role !== 'Regular User' || Auth::user()->user->blocked)
        {
            return redirect('/')->with('error', 'Cannot access this area');
        }

        $priceA = SiteConfig::getSiteConfig()->subscribe_price_plan_a;
        $priceB = SiteConfig::getSiteConfig()->subscribe_price_plan_b;
        $priceC = SiteConfig::getSiteConfig()->subscribe_price_plan_c;
        return view('subscription.subscription', ['prices'=>[$priceA, $priceB, $priceC]]);
    }


    public function subscribe(Request $request)
    {

        $request->validate([
            'plan' => 'required|in:1,2,3',
            'numberDaysInput' => 'nullable|integer|max:999|min:1',
        ]);



        $plan = $request['plan'];
        $numberDays = (int)$request['numberDaysInput'];
        $priceA = SiteConfig::getSiteConfig()->subscribe_price_plan_a;
        $priceB = SiteConfig::getSiteConfig()->subscribe_price_plan_b;
        $priceC = SiteConfig::getSiteConfig()->subscribe_price_plan_c;
        $price = 0;
        $time = 0;


        // Plan with variable number of days
        if ($plan == 1) {
            if ($numberDays == NULL) {
                return back()->withErrors(['numberDays' => 'Chosen plan requires number of days']);
            }
            $time = $numberDays;
            $price = $priceA * $time;
        } elseif ($plan == 2) {
            $time = 31;
            $price = $priceB * $time; 
        } else {
            $time = 186;
            $price = $priceC * $time;
        }

        $user = $request->user()->specificRole();
        // Check wallet balance
        if ($user->available_balance < $price) {
            return back()->withErrors(['wallet' => 'Insufficient funds in your wallet to complete this transaction.']);
        }



        if ($user->subscribed)
        {
            $lastSubscription = $user->activeLastSubscription();

            // Create new subscription
            Subscription::create([
                'start_time' => $lastSubscription->end_time,
                'end_time' => $lastSubscription->end_time->addDays($time), 
                'cost' => $price,
                'user_id' => $user->id,
            ]);
        }else 
        {
            // create subscription
            Subscription::create([
                'end_time' => now()->addDays($time), 
                'cost' => $price,
                'user_id' => $user->id,
            ]);
        }
        return back()->with('success', 'Subscribed Successfully');
    }
}
