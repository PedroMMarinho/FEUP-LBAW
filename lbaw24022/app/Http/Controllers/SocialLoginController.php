<?php

namespace App\Http\Controllers;

use App\Models\GeneralUser;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Str;

class SocialLoginController extends Controller
{
    public function toProvider($driver)
    {
        return Socialite::driver($driver)->redirect();
    }

    public function handleCallback($driver)
    {
        if ($driver == 'google') {
            try {
                $socialUser = Socialite::driver('google')->stateless()->user();

                $user = GeneralUser::where('google_id', $socialUser->getId())->first();

                if (!$user) {

                    $baseUsername = Str::slug($socialUser->getName(), '_');
                    $username = $this->generateUniqueUsername($baseUsername);

                    $generalUser = new GeneralUser([
                        'username' => $username,
                        'email' => $socialUser->getEmail(),
                        'google_id' => $socialUser->getId(),
                        'role' => 'Regular User',
                        'email_verified_at' => now(),
                    ]);


                    $generalUser->save();

                    $user = new User();
                    $user->id = $generalUser->id;
                    $user->save();

                    event(new Registered($generalUser));
                    Auth::login($generalUser);
                    return redirect(route('welcome', absolute: false));
                }

                Auth::login($user);
                return redirect(route('welcome', absolute: false));

            } catch (\Exception $e) {
                return redirect()->route('login')->with('error', 'Authentication failed. Please try again.');
            }
        }

        return redirect()->route('login')->with('error', 'Authentication failed. Please try again.');
    }

    protected function generateUniqueUsername(string $baseUsername): string
    {
        $username = $baseUsername;
        $counter = 1;

        while (GeneralUser::where('username', $username)->exists()) {
            $username = $baseUsername . '_' . $counter;
            $counter++;
        }

        return $username;
    }
}
