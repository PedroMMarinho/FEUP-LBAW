<section class="mt-6 space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once the account is deleted, all of its resources and data will be permanently deleted.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy', $user->id) }}" class="p-6" autocomplete="off">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to delete this account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once the account is deleted, all of its resources and data will be permanently deleted.') }}
            </p>
            @if(Auth::user()->role === 'Admin' && Auth::id() !== $user->id)
            <p class="text-sm text-gray-600">
                {{ __('Please explain the motive of the deletion and enter your password to confirm you would like to permanently delete this account.') }}
            </p>
            @endif
            @if(Auth::id() === $user->id)
            <p class="text-sm text-gray-600">
                {{ __('Please enter your password to confirm you would like to permanently delete this account.') }}
            </p>
            @endif
            @if(Auth::user()->role === 'Regular User' && Auth::id() === $user->id)
            <p class="text-sm text-gray-600 mt-2">
                <span class="text-red-600 font-bold">{{ __('Note:') }}</span>
                {{ __('The deletion of the account is only possible if there are no pending auctions as a buyer or seller and if you have no money in your wallet!') }}
            </p>
            @endif
            
            
            @if(Auth::user()->role === 'Admin' && Auth::id() !== $user->id)
                <div class="mt-6">
                    <x-input-label for="motive" value="{{ __('Motive') }}" class="sr-only" />

                    <x-text-input
                        id="motive"
                        name="motive"
                        type="text"
                        class="mt-1 block w-3/4"
                        placeholder="{{ __('Motive') }}"
                        value="{{ old('motive') }}"
                        autocomplete="off"
                        
                    />

                    <x-input-error :messages="$errors->userDeletion->get('motive')" class="mt-2" />
                </div>
            @endif

            <div
            @if(Auth::id() === $user->id)
            class="mt-6"
            @endif
            >
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                    autocomplete="new-password"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
