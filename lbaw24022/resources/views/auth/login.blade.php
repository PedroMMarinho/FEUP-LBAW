<x-guest-layout>
    <!-- Session Status -->

    <x-auth-session-status class="mb-4" :status="session('status')" />


    <div class="min-h-screen py-40 bg-cover" style="background-image: url('{{ asset('images/black.jpg') }}');">
     <p class="text-white text-center text-4xl sm:text-5xl md:text-6xl lg:text-7xl">Okshon</p> <!-- TODO Mudar isto para um butao que redireciona ao welcome page -->
        <div class="container mx-auto">
            <div class="flex flex-col lg:flex-row w-10/12 lg:w-8/12 rounded-xl mx-auto shadow-lg overflow-hidden" style=" background: linear-gradient(100deg, #202020, #5f5f5f);">
                <div class="w-full lg:w-1/2 flex flex-col items-center justify-between p-12 bg-no-repeat bg-cover bg-center">
                    <!-- Welcome Text at the Top -->
                    <h1 class="text-white text-3xl mb-6">Welcome Back Auctioneer</h1>

                    <!-- Spinning Logo (Image) -->
                    <h2 class="relative mb-8">
                        <img src="{{ asset('images/okshon_logo.png') }}" alt="Logo" class="animate-spin py-2 w-[40px] h-[40px] sm:w-[50px] sm:h-[50px] md:w-[60px] md:h-[60px] lg:w-[70px] lg:h-[70px]" style="height: 180px; width: 180px; animation-duration: 120s;" />
                    </h2>

                    <!-- Sign-In Text at the Bottom -->
                    <div class="mt-auto">
                        <p class="text-white text-center">
                            Still not part of the group?
                            <a href="{{ route('register') }}" class="text-purple-500 font-semibold">Click Here to Sign-Up</a>
                        </p>
                    </div>
                </div>
                <div class="w-full lg:w-1/2 py-16 px-12">
                    <h2 class="text-3xl mb-4 text-white">Login</h2>
                    <p class="mb-4 text-white">
                        Another day at the job! Fill in your credentials.
                    </p>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="mt-5">
                            <x-text-input id="email"
                                class="block mt-1 w-full border border-gray-400 py-1 px-2 mb-2"
                                placeholder="Email"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                autocomplete="email" />
                            <x-input-error :messages="$errors->get('email')" class="" />
                        </div>

                        <!-- Password -->
                        <div class="mt-1">
                            <x-text-input id="password"
                                class="block mt-1 w-full border border-gray-400 py-1 px-2"
                                placeholder="Password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('password')" class="" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex justify-between items-center mt-1">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" type="checkbox"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    name="remember">
                                <span class="ms-2 text-sm text-purple-600">{{ __('Remember me') }}</span>
                            </label>

                            @if (Route::has('password.request'))
                            <a class="underline text-sm text-purple-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                            @endif
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="w-full bg-purple-500 py-3 text-white flex justify-center items-center">
                                {{ __('Log in') }}
                            </x-primary-button>
                        </div>

                        <a href="/socialite/google" class="flex flex-row justify-center mt-8 hover:scale-105 active:scale-100">
                            <svg viewBox="0 0 488 512" class="w-8 h-8 fill-current text-white">
                                <path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/>
                            </svg>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-guest-layout>