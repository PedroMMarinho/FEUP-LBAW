<?php

namespace App\Http\Controllers;


use Illuminate\Http\RedirectResponse;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class EvaluationsController extends Controller
{

    public function show(Request $request): View | RedirectResponse
    {
        
        if($request->user()->role !== 'Expert')
        {
            return back()->with('error', 'Only experts can access this area'); 
        }

        $evalutions = Auction::evaluationRequests(8);

        return view('evaluations', [
            'evaluations' => $evalutions,
        ]);
    }

}