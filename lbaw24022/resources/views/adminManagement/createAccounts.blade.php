@extends('adminManagement.management')

@section('slot')
    <div class=" max-w-2xl w-full">
        <section>
            <header>
                <h2 class="text-lg font-medium text-gray-900">
                    {{ 'New Account' }}
                </h2>
            </header>
        
            <form id="send-verification"
                method="post"
                action="{{ route('verification.send') }}">
                @csrf
            </form>
        
            <form method="post"
                action="{{ route('management.newAccount') }}"
                class="mt-6 space-y-6">
                @csrf
        
                <input type="hidden" name="created_by" value="admin">

                <div class="mt-4">
                    <x-input-label for="user_type" :value="__('User Type')" />
                    <select id="user_type" name="user_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm hover:bg-gray-200 ">
                        <option value="" disabled selected hidden>{{ __('Select a user type') }}</option>
                        <option class="" value="user" {{ old('user_type') == 'user' ? 'selected' : '' }}>Regular User</option>
                        <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="expert" {{ old('user_type') == 'expert' ? 'selected' : '' }}>Expert</option>
                    </select>
                    <x-input-error :messages="$errors->get('user_type')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="username"
                        :value="__('Username')" />
                    <x-text-input id="username"
                        class="block mt-1 w-full"
                        type="text"
                        name="username"
                        :value="old('username')"
                        required
                        autofocus
                        autocomplete="username" />
                    <x-input-error :messages="$errors->get('username')"
                        class="mt-2" />
                </div>
        
                <!-- Email Address -->
                <div class="mt-4">
                    <x-input-label for="email"
                        :value="__('Email')" />
                    <x-text-input id="email"
                        class="block mt-1 w-full"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autocomplete="email" />
                    <x-input-error :messages="$errors->get('email')"
                        class="mt-2" />
                </div>
        
                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password"
                        :value="__('Password')" />
        
                    <x-text-input id="password"
                        class="block mt-1 w-full"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password" />
        
                    <x-input-error :messages="$errors->get('password')"
                        class="mt-2" />
                </div>
        
                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation"
                        :value="__('Confirm Password')" />
        
                    <x-text-input id="password_confirmation"
                        class="block mt-1 w-full"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password" />
        
                    <x-input-error :messages="$errors->get('password_confirmation')"
                        class="mt-2" />
                </div>
        
        
                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Create Account') }}</x-primary-button>
        
                    @if (session('status') === 'account-created')
                        <p x-data="{ show: true }"
                            x-show="show"
                            x-transition
                            x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm text-gray-600">{{ __('Created.') }}</p>
                    @endif
                </div>
            </form>
        </section>                
    </div>
@endsection