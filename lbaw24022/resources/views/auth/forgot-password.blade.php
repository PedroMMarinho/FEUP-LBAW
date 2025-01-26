<x-guest-layout>
    <div class="bg-gray-100 gap-8 min-h-screen flex flex-col justify-center items-center">
        <a href="/" class="text-6xl">Okshon</a>
        <div class="max-w-7xl mx-auto flex flex-col justify-center px-4 sm:px-6 lg:px-8">
            <div class="max-w-xl mx-auto my-auto sm:px-6 lg:px-8 bg-white px-10 py-8 rounded-lg shadow-lg">
                <h2 class="text-xl">Recover Password</h2>
                <div class="my-4 text-sm text-gray-600">
                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button>
                            {{ __('Email Password Reset Link') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
