<x-guest-layout>
    <div class="bg-gray-100 gap-8 min-h-screen flex flex-col justify-center items-center">
        <a href="/" class="text-6xl">Okshon</a>
        <div class="max-w-7xl mx-auto flex flex-col justify-center px-4 sm:px-6 lg:px-8">
            <div class="max-w-xl mx-auto my-auto sm:px-6 lg:px-8 bg-white px-10 py-8 rounded-lg shadow-lg">
                <h2 class="text-xl">Reset Password</h2>
                <div class="my-4 text-sm text-gray-600">
                    {{ __('Please choose a new password to complete the reset process. If you didn’t request a password reset, please contact support.') }}
                </div>

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf
            
                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">
            
                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
            
                    <!-- Password -->
                    <div class="mt-4">
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
            
                    <!-- Confirm Password -->
                    <div class="mt-4">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            
                        <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                            type="password"
                                            name="password_confirmation" required autocomplete="new-password" />
            
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
            
                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button>
                            {{ __('Reset Password') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
