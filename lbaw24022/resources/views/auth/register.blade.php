<x-guest-layout>

  <div class="min-h-screen py-40 bg-cover" style="background-image: url('{{ asset('images/black.jpg') }}');">
    <p class="text-white text-center text-4xl sm:text-5xl md:text-6xl lg:text-7xl">Okshon</p>
    <div class="container mx-auto">
      <div class="flex flex-col lg:flex-row w-10/12 lg:w-8/12 rounded-xl mx-auto shadow-lg overflow-hidden" style=" background: linear-gradient(100deg, #202020, #5f5f5f);">
        <div class="w-full lg:w-1/2 flex flex-col items-center justify-center p-8 lg:p-12 bg-no-repeat bg-cover bg-center">
          <!-- Welcome Text at the Top -->
          <h1 class="text-white text-3xl mb-6 lg:mb-8">Welcome Auctioneer</h1>

          <!-- Spinning Logo (Image) -->
          <h2 class="relative mb-6 lg:mb-8">
            <img src="{{ asset('images/okshon_logo.png') }}" alt="Logo" class="animate-spin py-2 w-[40px] h-[40px] sm:w-[50px] sm:h-[50px] md:w-[60px] md:h-[60px] lg:w-[70px] lg:h-[70px]" style="height: 180px; width: 180px; animation-duration: 120s;" />
          </h2>

          <!-- Sign-In Text at the Bottom -->
          <div class="mt-4 mb-6 lg:mb-8">
            <p class="text-white">
              Already have an account?
              <a href="{{ route('login') }}" class="text-purple-500 font-semibold">Click Here to Sign-In</a>
            </p>
          </div>
        </div>
        <div class="w-full lg:w-1/2 py-12 px-12 lg:py-16 lg:px-16">
          <h2 class="text-3xl mb-4 text-white">Register</h2>
          <p class="mb-4 text-white">
            Lets get started! Create your profile here.
          </p>
          <form method="POST"
            action="{{ route('register') }}">
            @csrf

            <input type="hidden" name="user_type" value="user">
            <input type="hidden" name="created_by" value="register">

            <!-- Username -->
            <div class="mt-5">
              <x-text-input id="username"
                placeholder="Username"
                class="block mt-1 w-full border border-gray-400 py-1 px-2 mb-2"
                type="text"
                name="username"
                :value="old('username')"
                required
                autofocus
                autocomplete="username" />
              <x-input-error :messages="$errors->get('username')"
                class="" />
            </div>

            <!-- Email Address -->
            <div class="">
              <x-text-input id="email"
                placeholder="Email"
                class="block mt-1 w-full border border-gray-400 py-1 px-2 mb-2"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="email" />
              <x-input-error :messages="$errors->get('email')"
                class="" />
            </div>

            <!-- Password -->
            <div class="mt-1">

              <x-text-input id="password"
                placeholder="Password"
                class="block mt-1 w-full border border-gray-400 py-1 px-2 mb-2"
                type="password"
                name="password"
                required
                autocomplete="new-password" />

            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
              <x-text-input id="password_confirmation"
                placeholder="Confirm Password"
                class="block mt-1 w-full border border-gray-400 py-1 px-2 mb-2"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password" />

            </div>
            <div class="max-h-5">
              <x-input-error :messages="$errors->get('password')"
                class="" />
              <x-input-error :messages="$errors->get('password_confirmation')"
                class="" />
            </div>

            <div class="flex items-center justify-center mt-4">
              <x-primary-button class="w-full bg-purple-500 py-3 text-white flex justify-center items-center">
                {{ __('Register') }}
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