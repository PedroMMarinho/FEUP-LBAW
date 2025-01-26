<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Category;
use App\Models\GeneralUser;
use App\Models\User;
use App\Models\ReportUser;
use App\Models\ReportAuction;
use App\Models\AdminChange;
use App\Models\Block;
use App\Models\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ManagementController extends Controller
{

    public function show(Request $request, string $section): View|RedirectResponse
    {
        $user = $request->user();
        $validSections = ['createAccounts', 'userReports', 'userAppeals', 'auctionReports', 'categories', 'adminChanges', 'systemSettings'];

        if ($user->role !== 'Admin') {
            return back()->with('error', 'Only admins can access this area');
        }

        if (!in_array($section, $validSections)) {
            return redirect('/')->with('error', 'management section not found');
        }

        if ($section === 'userReports') {
            $reports = ReportUser::reportsGroupedByUser(5);

            return view("adminManagement.$section", compact('user', 'reports'));
        } elseif ($section === 'userAppeals') {
            $appeals = Block::getNewAppeals(5);

            return view("adminManagement.$section", compact('user', 'appeals'));
        } elseif ($section === 'auctionReports') {
            $reports = ReportAuction::reportsGroupedByAuction(5);

            return view("adminManagement.$section", compact('user', 'reports'));
        } elseif ($section === 'categories') {
            $categories = Category::all();

            return view("adminManagement.$section", compact('user', 'categories'));
        } elseif ($section === 'adminChanges') {
            $changes = AdminChange::orderBy('timestamp', 'desc')->paginate(5);

            return view("adminManagement.$section", compact('user', 'changes'));
        } elseif ($section === 'systemSettings') 
        {
            $config = SiteConfig::getSiteConfig();

            return view("adminManagement.$section", compact('user', 'config'));
        }

        return view("adminManagement.$section", compact('user'));
    }


    public function updateSystemSettings(Request $request)
    {
        $config = SiteConfig::getSiteConfig();

        $request->validate([
            'bidInterval' => 'required|gt:0',
            'subDay' => 'required|gt:0',
            'subMonth' => 'required|gt:0',
            'sub6Months' => 'required|gt:0',
            'adPrice' => 'required|gt:0',
            'adDiscountPrice' => 'required|gt:0',
        ]);

        if ($request->sub6Months > $request->subMonth || $request->subMonth > $request->subDay) {
            return back()->withErrors([
                'sub6Months' => 'The 6-month subscription price must be greater or equal than the monthly price.',
                'subMonth' => 'The monthly subscription price must be greater or equal than the daily price.',
            ])->withInput();
        }
        
        if ($request->adPrice <= $request->adDiscountPrice) {
            return back()->withErrors([
                'adPrice' => 'The advertisement price must be greater than the discounted advertisement price.',
            ])->withInput();
        }

        $config->update([
            'minimal_bid_interval' => $request->bidInterval,
            'subscribe_price_plan_a' => $request->subDay,
            'subscribe_price_plan_b' => $request->subMonth,
            'subscribe_price_plan_c' => $request->sub6Months,
            'ad_price' => $request->adPrice,
            'discounted_ad_price' => $request->adDiscountPrice,
        ]);

        return back()->with('status', 'systemSettings-updated')->with('success', 'System Settings successfully updated');

    } 
}