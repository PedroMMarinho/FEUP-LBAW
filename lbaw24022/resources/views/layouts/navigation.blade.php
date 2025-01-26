<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <!-- Logo -->
            <div class="shrink-0 flex items-center">

                <a href="{{ url('/') }}" class="flex text-2xl gap-3 items-center">
                    <img src="{{ asset('images/okshon_logo_black.png') }}" alt="Logo" class="py-2 w-[40px] h-[40px] sm:w-[50px] sm:h-[50px] md:w-[60px] md:h-[60px] lg:w-[70px] lg:h-[70px]" style="width: 40px; height: auto;" />
                    <p>Okshon</p>
                </a>
            </div>
            <div class="flex items-center gap-5">
                @guest
                    <div class="hidden space-x-8 md:-my-px md:ms-10 md:flex">
                        <x-nav-link :href="route('login')" :active="request()->routeIs('login')">
                            {{ __('Log In') }}
                        </x-nav-link>
                    </div> 
                    <div class="hidden space-x-8 md:-my-px md:ms-10 md:flex">
                        <x-nav-link :href="route('register')" :active="request()->routeIs('register')">
                            {{ __('Register') }}
                        </x-nav-link>
                    </div> 
                @endguest

                @auth
                    @regularuser
                        <div class="hidden md:flex items-center gap-5">
                            @if(!Auth::user()->user->blocked)
                                <a href="{{route('auction.create.show')}}" class="md:items-center md:flex text-xs text-gray-500 font-semibold hover:text-gray-700  focus:outline-none transition ease-in-out duration-150"> Create Auction</a>
                                <a href="{{route('subscription.show')}}" class="md:items-center md:flex text-xs text-gray-500 font-semibold hover:text-gray-700  focus:outline-none transition ease-in-out duration-150 ms-6">
                                    @if (Auth::user()->user->subscribed)
                                    <div class="text-center">
                                        <p>Subscribed &#10209;</p>
                                        <p class="text-xs">{{ceil(Auth::user()->user->activeLastSubscription()->end_time->diffInDays(now(), true))}} days</p>
                                    </div>
                                    @else
                                        Subscribe &#10209;
                                    @endif    
                                </a>
                            @endif
                        </div>
                        <div class="flex items-center gap-5">
                            <a href="{{route('wallet.show')}}" id= "current-available-balance-user" class="pl-4 md:items-center md:flex text-xs text-gray-500 font-semibold hover:text-gray-700  focus:outline-none transition ease-in-out duration-150"> Available: {{Auth::user()->specificRole()->available_balance}} €</a>
                        </div>
                    @endregularuser
                    @regularuser
                    <!-- Notifications container -->
                    <div id="notification-container" class="relative">
                        <div class="notification-icon">
                            <div id="notification-count" class="notification-count">
                                
                            </div>
                        </div>
                        <div class="absolute right-0 mt-2 w-64 bg-white shadow-lg rounded-md p-2" id="dropdown-notifications">
                            <!-- Clear All button -->
                            <div class="flex justify-between items-center mb-2 px-3">
                                <div class="text-sm font-semibold text-gray-700">Notifications</div>
                                <div id="clear-all" class="text-blue-500 font-semibold cursor-pointer text-xs hover:underline hidden">
                                    Clear All
                                </div>
                            </div>
                            <!-- Notification List -->
                            <ul id="notification-list">
                                
                            </ul>
                            <!-- No Notifications Message -->
                            <div id="no-notifications" class="py-2 px-4 text-gray-500 text-center hidden">
                                No current notifications
                            </div>
                        </div>
                    </div>
                    @endregularuser
                    <!-- Settings Dropdown -->
                    <div class="hidden md:flex md:items-center">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->username }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.show', ['userId' => Auth::user()->id])">
                                    {{ __('Profile') }}
                                </x-dropdown-link>
                                @admin
                                    <x-dropdown-link :href="route('management.show', 'createAccounts')">
                                        {{ __('Management') }}
                                    </x-dropdown-link>
                                @endadmin
                                @regularuser
                                    <x-dropdown-link :href="route('wallet.show', ['userId' => Auth::user()->id])">
                                        {{ __('Wallet') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('seller-dashboard')">
                                        {{ __('Seller Dashboard') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('buyer-dashboard')">
                                        {{ __('Buyer Dashboard') }}
                                    </x-dropdown-link>
                                @endregularuser
                                @expert
                                <x-dropdown-link :href="route('expert.evaluationsRequested')">
                                    {{ __('Requested Evaluations') }}
                                </x-dropdown-link>
                                @endexpert
                                <x-dropdown-link :href="route('settings.show', ['section' => 'profile', 'userId' => Auth::user()->id])">
                                    {{ __('Settings') }}
                                </x-dropdown-link>


                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endauth

                <!-- Hamburger -->
                <div class="-me-2 flex items-center md:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden">
        @auth
            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="flex flex-row justify-between">
                    <div class="flex-1 px-4">
                        <div class="font-medium text-base text-gray-800">{{ Auth::user()->username }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    @regularuser
                        @if(!Auth::user()->user->blocked)
                            <x-responsive-nav-link :href="route('auction.create.show')">
                                {{ __('Create Auction') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('auction.create.show')">
                                Subscribe &#10209;
                            </x-responsive-nav-link>
                        @endif
                    @endregularuser
                    <x-responsive-nav-link :href="route('profile.show', ['userId' => Auth::user()->id])">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                    @admin
                        <x-responsive-nav-link :href="route('management.show', 'createAccounts')">
                            {{ __('Management') }}
                        </x-responsive-nav-link>
                    @endadmin
                    @regularuser
 
                        <x-responsive-nav-link :href="route('wallet.show', ['userId' => Auth::user()->id])">
                            {{ __('Wallet') }}
                        </x-responsive-nav-link>
                        
                        <x-responsive-nav-link :href="route('seller-dashboard')">
                            {{ __('Seller Dashboard') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('buyer-dashboard')">
                            {{ __('Buyer Dashboard') }}
                        </x-responsive-nav-link>
                    @endregularuser
                    <x-responsive-nav-link :href="route('settings.show', ['section' => 'profile', 'userId' => Auth::user()->id])">
                        {{ __('Settings') }}
                    </x-responsive-nav-link>


                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>
