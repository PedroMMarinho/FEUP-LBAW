<?php

namespace App\Http\Controllers;

use App\Models\GeneralUser;
use App\Models\ReportUser;
use App\Models\RateUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class ProfileController extends Controller
{

    public function show(Request $request, int $userId): View | RedirectResponse
    {

        $user = GeneralUser::findOrFail($userId);

        return view('profile.profile', [
            'user' => $user,
        ]);
    }

    public function report(Request $request, int $userId): RedirectResponse
    {

        $reportedUser = GeneralUser::findOrFail($userId);

        //alerta error
        if (Gate::denies('report', $reportedUser))
        {
            return back()->with('error', 'Cannot report this user');
        }

        if($reportedUser->user->myReporter($request->user()->id))
        {
            return back()->with('error', 'User already reported');
        }

        $request->validateWithBag('userReport',([
            'motive' => ['required', 'string', 'max:100'],
        ]));
        
        ReportUser::create([
            'description' => $request->motive,
            'reporter' => $request->user()->id,
            'reported' => $reportedUser->id,
        ]);

        return Redirect::to("/profile/{$userId}")->with('success', 'User successfully reported');

    }

    public function rate(Request $request, int $userId): RedirectResponse
    {

        $ratedUser = GeneralUser::findOrFail($userId);

        //alerta error
        if (Gate::denies('rate', $ratedUser))
        {
            return back()->with('error', 'Cannot rate this user');
        }

        $request->validateWithBag('userRate',([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]));

        $rate = $ratedUser->user->myRater($request->user()->id);


        if($rate)
        {
            $rate->update([
                'rate' => $request->rating,
            ]);
        }
        else
        {
            RateUser::create([
                'rate' => $request->rating,
                'rater_user' => $request->user()->id,
                'rated_user' => $ratedUser->id,
            ]);
        }

        return Redirect::to("/profile/{$userId}")->with('success', 'User successfully rated');

    }


}
