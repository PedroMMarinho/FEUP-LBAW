@props(['footer' => true])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <meta name="is-logged-in" content="{{ auth()->check() ? 'true' : 'false' }}">
    <meta name="user-id" content="{{ auth()->id() ?? '' }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    
    <!-- Paypal -->
        <script
            src="https://www.paypal.com/sdk/js?client-id=Aff5Tz2FNmzPBAfbYIxEsKAazADoXNowMEDJD7TmE9ZaLunNlJNU3gvx9EXKX1tCU4pJtnn65VqgJimI&buyer-country=PT&currency=EUR&components=buttons,card-fields&enable-funding=venmo"
            data-sdk-integration-source="developer-studio"
        ></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
</head>

<body class="font-sans antialiased">
    <div class="flex flex-col min-h-screen bg-gray-100">
        @include('layouts.navigation')

        @auth
            @if(Auth::user()->role === 'Regular User' && Auth::user()->user->blocked)
                <div class="grid grid-cols-1 sm:grid-cols-3 grid-rows-3 sm:grid-rows-1 font-bold text-white bg-red-500 hover:bg-red-600 select-none cursor-pointer py-6 px-4 sm:px-6 lg:px-8"
                    x-data=""
                    x-on:click.prevent="{{ is_null(Auth::user()->user->currentBlock()->appeal_message) ? '$dispatch(\'open-modal\', \'confirm-user-appeal\')' : ''}}">
                    <div class="col-span-full row-start-2 row-end-3 sm:col-start-1 sm:col-end-2 sm:row-span-full mt-1 sm:mt-0 text-center justify-self-center self-center text-xl leading-tight">
                        Block Ends: {{Auth::user()->user->currentBlock()->end_time == '2999-12-31 23:59:59' ? 'Permanently' : Auth::user()->user->currentBlock()->end_time->format('d/m/Y')}}
                    </div>
                    <h2 class="col-span-full row-start-1 row-end-2 sm:col-start-2 sm:col-end-3 sm:row-span-full justify-self-center self-center text-xl leading-tight">
                        {{ __('You are blocked') }}
                    </h2>
                    <div class="col-span-full row-start-3 row-end-4 sm:col-start-3 sm:col-end-4 mt-2 sm:mt-0 sm:row-span-full justify-self-center self-center text-xl leading-tight">
                        @if (is_null(Auth::user()->user->currentBlock()->appeal_message))
                            Appeal Now
                        @elseif (is_null(Auth::user()->user->currentBlock()->appeal_accepted))
                            Appeal Under Validation
                        @else 
                            Appeal Rejected
                        @endif
                    </div>  

                </div>
                <x-modal name="confirm-user-appeal" :show="$errors->userAppeal->isNotEmpty()" focusable>
                    <form id="appeal-form" method="post" action="{{ route('user.appeal', Auth::id()) }}" class="p-6">
                        @csrf
                        @method('post')
            
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Are you sure you want to submit a block appeal?') }}
                        </h2>
            
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Please provide a reason for your appeal. Note that you only have one opportunity to submit this appeal.') }}
                        </p>
                        
                        <div class="mt-6">
                            <x-input-label for="motive" value="{{ __('Motive') }}" class="sr-only" />
            
                            <x-text-input
                                id="motive"
                                name="motive"
                                type="text"
                                class="mt-1 block w-3/4"
                                placeholder="{{ __('Motive') }}"
                                value="{{ old('motive') }}"
                            />
            
                            <x-input-error :messages="$errors->userAppeal->get('motive')" class="mt-2" />
                        </div>
            
                        <div class="mt-6 flex justify-end">
                            <x-secondary-button x-on:click="$dispatch('close')">
                                {{ __('Cancel') }}
                            </x-secondary-button>
            
                            <x-primary-button class="ms-3">
                                {{ __('Appeal') }}
                            </x-primary-button>
                        </div>
                    </form>
                </x-modal>
            @endif
        @endauth

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset
        
        <div 
            x-data="{ show: false, message: '', type: '' }"
            x-init="
                @if(session('error'))
                    show = true;
                    message = '{{ session('error') }}';
                    type = 'error';
                    setTimeout(() => show = false, 3000);
                @elseif(session('success'))
                    show = true;
                    message = '{{ session('success') }}';
                    type = 'success';
                    setTimeout(() => show = false, 3000);
                @endif
            "
            x-on:notify.window="
            show = true;
            message = $event.detail.message;
            type = $event.detail.type;
            setTimeout(() => show = false, 3000);
            "
            x-show="show"
            x-on:click= "show = false"
            x-transition
            class="pop-up fixed bottom-10 right-5 p-4 flex items-center space-x-3 rounded-lg shadow-lg cursor-pointer select-none capitalize" 
            :class="{
                'bg-red-100 border-l-4 border-red-500 text-red-700': type === 'error',
                'bg-green-100 border-l-4 border-green-500 text-green-700': type === 'success'
            }"
                style="display: none; min-width: 250px;">
            <div>
                <svg x-show="type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" stroke="none">
                    <circle cx="12" cy="12" r="10" class="text-red-500" fill="currentColor"/>
                    <path class="text-white" d="M15.41 8.59L12 12.00 8.59 8.59 7.17 10.00 10.59 13.41 7.17 16.83 8.59 18.24 12.00 14.83 15.41 18.24 16.83 16.83 13.41 13.41 16.83 10.00z" />
                </svg>
                <svg x-show="type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p x-text="message" class="text-sm font-medium"></p>
        </div>


        <!-- Page Content -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        <!-- Page Footer -->
        @if ($footer)
            <x-footer />
        @endif
    </div>
    <script>
        function showPopUp(message, type) {

            console.log(message, type);
            const event = new CustomEvent('notify', {
                detail: {
                    message: message, 
                    type: type        
                }
            });
            window.dispatchEvent(event);
        }

    </script>
    @stack('scripts')
</body>

</html>
