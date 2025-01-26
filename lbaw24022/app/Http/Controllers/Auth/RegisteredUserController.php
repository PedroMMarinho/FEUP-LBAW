<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GeneralUser;
use App\Models\AdminChange;
use App\Models\User;
use App\Models\Admin;
use App\Models\Expert;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:25', 'unique:' . GeneralUser::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . GeneralUser::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'in:admin,expert,user'],
            'created_by' => ['required', 'in:admin,register'],
        ]);

        $generalUser = new GeneralUser([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->user_type === 'admin' ? 'Admin' : ($request->user_type === 'expert' ? 'Expert' : 'Regular User'),
        ]);


        $generalUser->save();

        if ($request->user_type === 'user') {
            $user = new User();
            $user->id = $generalUser->id;
            $user->save();
        } elseif ($request->user_type === 'admin') {
            $admin = new Admin();
            $admin->id = $generalUser->id;
            $admin->save();
        } elseif ($request->user_type === 'expert') {
            $expert = new Expert();
            $expert->id = $generalUser->id;
            $expert->save();
        }

        if ($request->created_by === 'register') {
            event(new Registered($generalUser));
            Auth::login($generalUser);
            return redirect(route('welcome', absolute: false));
        } elseif ($request->created_by === 'admin') {
            AdminChange::create([
                'description' => 'Created ' . $request->user_type . ' account ' . $request->username,
                'admin' => $request->user()->id
            ]);

            $generalUser->email_verified_at = now();
            $generalUser->save();

            return back()->with('status', 'account-created')->with('success', 'Account Successfully created');

        }
    }
}
