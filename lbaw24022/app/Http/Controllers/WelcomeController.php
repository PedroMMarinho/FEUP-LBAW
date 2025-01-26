<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;


class WelcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $advertisedAuctions = Auction::with(['bids' => fn($query) => $query->orderBy('value', 'desc')->limit(1)])->advertised()->where('auction_state', 'Active')->limit(12)->get();

        $categories = Category::all();

        return view('welcome', compact('advertisedAuctions', 'categories'));
    }

    public function appeal(Request $request): RedirectResponse
    {
        $currentBlock = $request->user()->user->currentBlock();

        if (!is_null($currentBlock->appeal_message))
        {
            return back()->with('error', 'Block appeal already made');
        }

        $request->validateWithBag('userAppeal', ([
            'motive' => ['required', 'string', 'max:100'],
        ]));

        $currentBlock->appeal_message = $request->motive;

        $currentBlock->save();


        return Redirect::to('/')->with('success','Block Appeal successfully sended');
    }

}
