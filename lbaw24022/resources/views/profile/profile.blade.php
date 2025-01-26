@php

 function formatNumber($num) {
    if ($num < 1000) {
        return (string)$num;
    } elseif ($num < 100000) {
        return rtrim(number_format($num / 1000, 1, '.', ''), '.0') . 'K';
    } elseif ($num < 1000000) {
        return floor($num / 1000) . 'K';
    } elseif ($num < 100000000) {
        return rtrim(number_format($num / 1000000, 1, '.', ''), '.0') . 'M';
    } elseif ($num < 1000000000) {
        return floor($num / 1000000) . 'M';
    } elseif ($num < 100000000000) {
        return rtrim(number_format($num / 1000000000, 1, '.', ''), '.0') . 'B';
    } else {
        return floor($num / 1000000000) . 'B';
    }

}   

    function formatNumberSpace($number) {
        if (strpos($number, '.') !== false) {
        
            list($integer, $decimal) = explode('.', $number);


            if ((int)$decimal === 0) {

                $decimal = null;
            }
        } else {

            $integer = $number;
            $decimal = null;
        }


        $formattedInteger = number_format($integer, 0, '', '.');


        return $decimal === null ? $formattedInteger : $formattedInteger . ',' . $decimal;
    }


@endphp


<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="mt-12">
        <div style="grid-template-rows: 100px 200px; grid-template-columns: 250px 1fr 200px" class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 grid">
            <div class="col-start-1 col-end-2 row-start-1 row-end-3">
                <div style="height:150px" class="text-center content-center flex justify-center items-center">
                    <img  src="{{$user->getProfileImage()}}" class="aspect-square object-cover rounded-full h-40" alt="Profile Image">
                </div>
                @if ($user->role === 'Regular User' && $user->id != 1)
                    <p class="mt-6 text-center select-none">User Rating</p>
                    <div class="flex justify-center select-none mt-2 space-x-1">
                        @php
                        $averageRating = $user->user->myrating(); 
                        $fullStars = round($averageRating);
                        $emptyStars = 5 - $fullStars;
                        @endphp
        
                        @for ($i = 0; $i < $fullStars; $i++)
                            <svg class="w-7" viewBox="0 0 24 24" fill="#facc15" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                <path d="M11.245 4.174C11.4765 3.50808 11.5922 3.17513 11.7634 3.08285C11.9115 3.00298 12.0898 3.00298 12.238 3.08285C12.4091 3.17513 12.5248 3.50808 12.7563 4.174L14.2866 8.57639C14.3525 8.76592 14.3854 8.86068 14.4448 8.93125C14.4972 8.99359 14.5641 9.04218 14.6396 9.07278C14.725 9.10743 14.8253 9.10947 15.0259 9.11356L19.6857 9.20852C20.3906 9.22288 20.743 9.23007 20.8837 9.36432C21.0054 9.48051 21.0605 9.65014 21.0303 9.81569C20.9955 10.007 20.7146 10.2199 20.1528 10.6459L16.4387 13.4616C16.2788 13.5829 16.1989 13.6435 16.1501 13.7217C16.107 13.7909 16.0815 13.8695 16.0757 13.9507C16.0692 14.0427 16.0982 14.1387 16.1563 14.3308L17.506 18.7919C17.7101 19.4667 17.8122 19.8041 17.728 19.9793C17.6551 20.131 17.5108 20.2358 17.344 20.2583C17.1513 20.2842 16.862 20.0829 16.2833 19.6802L12.4576 17.0181C12.2929 16.9035 12.2106 16.8462 12.1211 16.8239C12.042 16.8043 11.9593 16.8043 11.8803 16.8239C11.7908 16.8462 11.7084 16.9035 11.5437 17.0181L7.71805 19.6802C7.13937 20.0829 6.85003 20.2842 6.65733 20.2583C6.49056 20.2358 6.34626 20.131 6.27337 19.9793C6.18915 19.8041 6.29123 19.4667 6.49538 18.7919L7.84503 14.3308C7.90313 14.1387 7.93218 14.0427 7.92564 13.9507C7.91986 13.8695 7.89432 13.7909 7.85123 13.7217C7.80246 13.6435 7.72251 13.5829 7.56262 13.4616L3.84858 10.6459C3.28678 10.2199 3.00588 10.007 2.97101 9.81569C2.94082 9.65014 2.99594 9.48051 3.11767 9.36432C3.25831 9.23007 3.61074 9.22289 4.31559 9.20852L8.9754 9.11356C9.176 9.10947 9.27631 9.10743 9.36177 9.07278C9.43726 9.04218 9.50414 8.99359 9.55657 8.93125C9.61593 8.86068 9.64887 8.76592 9.71475 8.57639L11.245 4.174Z" stroke="#000000" stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round"></path> </g>
                            </svg>
                        @endfor
        
                        @for ($i = 0; $i < $emptyStars; $i++)
                            <svg class="w-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                <path d="M11.245 4.174C11.4765 3.50808 11.5922 3.17513 11.7634 3.08285C11.9115 3.00298 12.0898 3.00298 12.238 3.08285C12.4091 3.17513 12.5248 3.50808 12.7563 4.174L14.2866 8.57639C14.3525 8.76592 14.3854 8.86068 14.4448 8.93125C14.4972 8.99359 14.5641 9.04218 14.6396 9.07278C14.725 9.10743 14.8253 9.10947 15.0259 9.11356L19.6857 9.20852C20.3906 9.22288 20.743 9.23007 20.8837 9.36432C21.0054 9.48051 21.0605 9.65014 21.0303 9.81569C20.9955 10.007 20.7146 10.2199 20.1528 10.6459L16.4387 13.4616C16.2788 13.5829 16.1989 13.6435 16.1501 13.7217C16.107 13.7909 16.0815 13.8695 16.0757 13.9507C16.0692 14.0427 16.0982 14.1387 16.1563 14.3308L17.506 18.7919C17.7101 19.4667 17.8122 19.8041 17.728 19.9793C17.6551 20.131 17.5108 20.2358 17.344 20.2583C17.1513 20.2842 16.862 20.0829 16.2833 19.6802L12.4576 17.0181C12.2929 16.9035 12.2106 16.8462 12.1211 16.8239C12.042 16.8043 11.9593 16.8043 11.8803 16.8239C11.7908 16.8462 11.7084 16.9035 11.5437 17.0181L7.71805 19.6802C7.13937 20.0829 6.85003 20.2842 6.65733 20.2583C6.49056 20.2358 6.34626 20.131 6.27337 19.9793C6.18915 19.8041 6.29123 19.4667 6.49538 18.7919L7.84503 14.3308C7.90313 14.1387 7.93218 14.0427 7.92564 13.9507C7.91986 13.8695 7.89432 13.7909 7.85123 13.7217C7.80246 13.6435 7.72251 13.5829 7.56262 13.4616L3.84858 10.6459C3.28678 10.2199 3.00588 10.007 2.97101 9.81569C2.94082 9.65014 2.99594 9.48051 3.11767 9.36432C3.25831 9.23007 3.61074 9.22289 4.31559 9.20852L8.9754 9.11356C9.176 9.10947 9.27631 9.10743 9.36177 9.07278C9.43726 9.04218 9.50414 8.99359 9.55657 8.93125C9.61593 8.86068 9.64887 8.76592 9.71475 8.57639L11.245 4.174Z" stroke="#000000" stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round"></path> </g>
                            </svg>
                        @endfor
                    </div>
                @endif
                @can('rate',$user)
                    @php
                        $rate = $user->user->myRater(Auth::id());
                    @endphp
                    @if($rate) 
                        @php
                            $fullStars = round($rate->rate);
                            $emptyStars = 5 - $fullStars;                        
                        @endphp
                        <div class="cursor-pointer"
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'user-rating')">
                            <p class="mt-4 text-center select-none">Rate</p>
                            <div class="flex justify-center select-none mt-2 space-x-1">
                                @for ($i = 0; $i < $fullStars; $i++)
                                <svg class="w-7" viewBox="0 0 24 24" fill="#facc15" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                    <path d="M11.245 4.174C11.4765 3.50808 11.5922 3.17513 11.7634 3.08285C11.9115 3.00298 12.0898 3.00298 12.238 3.08285C12.4091 3.17513 12.5248 3.50808 12.7563 4.174L14.2866 8.57639C14.3525 8.76592 14.3854 8.86068 14.4448 8.93125C14.4972 8.99359 14.5641 9.04218 14.6396 9.07278C14.725 9.10743 14.8253 9.10947 15.0259 9.11356L19.6857 9.20852C20.3906 9.22288 20.743 9.23007 20.8837 9.36432C21.0054 9.48051 21.0605 9.65014 21.0303 9.81569C20.9955 10.007 20.7146 10.2199 20.1528 10.6459L16.4387 13.4616C16.2788 13.5829 16.1989 13.6435 16.1501 13.7217C16.107 13.7909 16.0815 13.8695 16.0757 13.9507C16.0692 14.0427 16.0982 14.1387 16.1563 14.3308L17.506 18.7919C17.7101 19.4667 17.8122 19.8041 17.728 19.9793C17.6551 20.131 17.5108 20.2358 17.344 20.2583C17.1513 20.2842 16.862 20.0829 16.2833 19.6802L12.4576 17.0181C12.2929 16.9035 12.2106 16.8462 12.1211 16.8239C12.042 16.8043 11.9593 16.8043 11.8803 16.8239C11.7908 16.8462 11.7084 16.9035 11.5437 17.0181L7.71805 19.6802C7.13937 20.0829 6.85003 20.2842 6.65733 20.2583C6.49056 20.2358 6.34626 20.131 6.27337 19.9793C6.18915 19.8041 6.29123 19.4667 6.49538 18.7919L7.84503 14.3308C7.90313 14.1387 7.93218 14.0427 7.92564 13.9507C7.91986 13.8695 7.89432 13.7909 7.85123 13.7217C7.80246 13.6435 7.72251 13.5829 7.56262 13.4616L3.84858 10.6459C3.28678 10.2199 3.00588 10.007 2.97101 9.81569C2.94082 9.65014 2.99594 9.48051 3.11767 9.36432C3.25831 9.23007 3.61074 9.22289 4.31559 9.20852L8.9754 9.11356C9.176 9.10947 9.27631 9.10743 9.36177 9.07278C9.43726 9.04218 9.50414 8.99359 9.55657 8.93125C9.61593 8.86068 9.64887 8.76592 9.71475 8.57639L11.245 4.174Z" stroke="#000000" stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round"></path> </g>
                                </svg>
                            @endfor
            
                            @for ($i = 0; $i < $emptyStars; $i++)
                                <svg class="w-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                    <path d="M11.245 4.174C11.4765 3.50808 11.5922 3.17513 11.7634 3.08285C11.9115 3.00298 12.0898 3.00298 12.238 3.08285C12.4091 3.17513 12.5248 3.50808 12.7563 4.174L14.2866 8.57639C14.3525 8.76592 14.3854 8.86068 14.4448 8.93125C14.4972 8.99359 14.5641 9.04218 14.6396 9.07278C14.725 9.10743 14.8253 9.10947 15.0259 9.11356L19.6857 9.20852C20.3906 9.22288 20.743 9.23007 20.8837 9.36432C21.0054 9.48051 21.0605 9.65014 21.0303 9.81569C20.9955 10.007 20.7146 10.2199 20.1528 10.6459L16.4387 13.4616C16.2788 13.5829 16.1989 13.6435 16.1501 13.7217C16.107 13.7909 16.0815 13.8695 16.0757 13.9507C16.0692 14.0427 16.0982 14.1387 16.1563 14.3308L17.506 18.7919C17.7101 19.4667 17.8122 19.8041 17.728 19.9793C17.6551 20.131 17.5108 20.2358 17.344 20.2583C17.1513 20.2842 16.862 20.0829 16.2833 19.6802L12.4576 17.0181C12.2929 16.9035 12.2106 16.8462 12.1211 16.8239C12.042 16.8043 11.9593 16.8043 11.8803 16.8239C11.7908 16.8462 11.7084 16.9035 11.5437 17.0181L7.71805 19.6802C7.13937 20.0829 6.85003 20.2842 6.65733 20.2583C6.49056 20.2358 6.34626 20.131 6.27337 19.9793C6.18915 19.8041 6.29123 19.4667 6.49538 18.7919L7.84503 14.3308C7.90313 14.1387 7.93218 14.0427 7.92564 13.9507C7.91986 13.8695 7.89432 13.7909 7.85123 13.7217C7.80246 13.6435 7.72251 13.5829 7.56262 13.4616L3.84858 10.6459C3.28678 10.2199 3.00588 10.007 2.97101 9.81569C2.94082 9.65014 2.99594 9.48051 3.11767 9.36432C3.25831 9.23007 3.61074 9.22289 4.31559 9.20852L8.9754 9.11356C9.176 9.10947 9.27631 9.10743 9.36177 9.07278C9.43726 9.04218 9.50414 8.99359 9.55657 8.93125C9.61593 8.86068 9.64887 8.76592 9.71475 8.57639L11.245 4.174Z" stroke="#000000" stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round"></path> </g>
                                </svg>
                            @endfor
                            </div>
                        </div>
 
                    @else
                        <div class="cursor-pointer select-none mt-6" 
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'user-rating')">
                            <p class="mt-4 text-center select-none">Not Rated</p>
                        </div>  
                    @endif
                @endcan 
            </div>

            <div class="col-start-2 col-end-3 row-start-1 row-end-2">
                <h2 class="text-lg font-medium text-gray-900 mb-3"> Username </h2>
                <p class=" px-3">{{$user->username}}</p>
            </div>
            <div class="col-start-2 col-end-3 row-start-2 row-end-3">
                <h2 class="text-lg font-medium text-gray-900 mb-3"> Description </h2>
                <p class=" px-3">
                    {{ $user->description === null || $user->description === '' ? 'No description.' : $user->description }}
                </p>
            </div>

            <div class="col-start-3 col-end-4 row-start-1 row-end-2 mx-auto">
                @can('follow', $user)
                    <div class="follow-btn-usr flex flex-row items-center justify-between cursor-pointer size-fit mr-5">
                        <p class="follow-txt w-20 text-end" user-id="{{ $user->user->id }}">{{ $user->user->myFollower(Auth::id()) ? 'Following' : 'Follow' }}</p>
                        <svg height="25px" width="25px"  viewBox="0 0 512 512" class="heart-icon ml-3" style="fill: {{ $user->user->myFollower(Auth::id()) ? 'red' : 'black' }}">
                            <path d="M47.6 300.4L228.3 469.1c7.5 7 17.4 10.9 27.7 10.9s20.2-3.9 27.7-10.9L464.4 300.4c30.4-28.3 47.6-68 47.6-109.5v-5.8c0-69.9-50.5-129.5-119.4-141C347 36.5 300.6 51.4 268 84L256 96 244 84c-32.6-32.6-79-47.5-124.6-39.9C50.5 55.6 0 115.2 0 185.1v5.8c0 41.5 17.2 81.2 47.6 109.5z"/>
                        </svg>
                    </div>  
                @endcan
                @can('report', $user)
                    <div class="report-btn-usr flex flex-row items-center justify-between cursor-pointer size-fit mr-5 mt-6" 
                        x-data=""
                        x-on:click.prevent="
                        @if($user->user->myReporter(Auth::id()))
                            $dispatch('notify', { message: 'User already reported', type: 'error' })
                        @else
                            $dispatch('open-modal', 'user-report')
                        @endif 
                        ">
                    
                        <p class="report-txt w-20 text-end ">{{ $user->user->myReporter(Auth::id()) ? 'Reported' : 'Report' }}</p>
                        <svg class="report-icon ml-3 hover:scale-105 transition-transform"  style="fill: {{ ($user->user->myReporter(Auth::id())) ? '#aa0000' : '#000000' }}"  height="25px" width="25px" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <style type="text/css"> .st0{fill:#000000;} </style> <g> 
                            <path d="M387.317,0.005H284.666h-57.332h-102.65L0,124.688v102.67v57.294v102.67l124.684,124.674h102.65h57.332 h102.651L512,387.321v-102.67v-57.294v-102.67L387.317,0.005z M255.45,411.299c-19.082,0-34.53-15.467-34.53-34.549 c0-19.053,15.447-34.52,34.53-34.52c19.082,0,34.53,15.467,34.53,34.52C289.98,395.832,274.532,411.299,255.45,411.299z M283.414,278.692c0,15.448-12.516,27.964-27.964,27.964c-15.458,0-27.964-12.516-27.964-27.964l-6.566-135.368 c0-19.072,15.447-34.54,34.53-34.54c19.082,0,34.53,15.467,34.53,34.54L283.414,278.692z"></path> </g> </g>
                        </svg>
                    </div>  
                @endcan
            </div>

            @if(Auth::user()->id == $user->id)
                <a href="{{ route('settings.show', ['section' => 'profile', 'userId' => Auth::user()->id]) }}" class="col-start-3 col-end-4 row-start-1 row-end-2 text-center content-end"> <x-secondary-button> Edit Profile </x-secondary-button> </a>
            @else
                @admin
                    @if($user->id > 3)
                        <a href="{{ route('settings.show', ['section' => 'profile', 'userId' => $user->id]) }}" class="col-start-3 col-end-4 row-start-1 row-end-2 text-center content-end"><x-secondary-button> Manage Profile </x-secondary-button></a>
                    @endif
                @endadmin
            @endif
            
        </div>
    </div>



    @can('rate', $user)
        <x-modal name="user-rating" :show="$errors->userRate->isNotEmpty()" focusable>
            <form id="rate-form" method="post" action="{{ route('user.rate', $user->id) }}" class="p-6">
                @csrf
                @method('post')

                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Rate this user') }}
                </h2>
        
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Please select a rating for this user.') }}
                </p>

                <div class="mt-4 flex justify-center">
                    <div class="flex items-center">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg data-value="{{ $i }}" class="star cursor-pointer w-12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                <path d="M11.245 4.174C11.4765 3.50808 11.5922 3.17513 11.7634 3.08285C11.9115 3.00298 12.0898 3.00298 12.238 3.08285C12.4091 3.17513 12.5248 3.50808 12.7563 4.174L14.2866 8.57639C14.3525 8.76592 14.3854 8.86068 14.4448 8.93125C14.4972 8.99359 14.5641 9.04218 14.6396 9.07278C14.725 9.10743 14.8253 9.10947 15.0259 9.11356L19.6857 9.20852C20.3906 9.22288 20.743 9.23007 20.8837 9.36432C21.0054 9.48051 21.0605 9.65014 21.0303 9.81569C20.9955 10.007 20.7146 10.2199 20.1528 10.6459L16.4387 13.4616C16.2788 13.5829 16.1989 13.6435 16.1501 13.7217C16.107 13.7909 16.0815 13.8695 16.0757 13.9507C16.0692 14.0427 16.0982 14.1387 16.1563 14.3308L17.506 18.7919C17.7101 19.4667 17.8122 19.8041 17.728 19.9793C17.6551 20.131 17.5108 20.2358 17.344 20.2583C17.1513 20.2842 16.862 20.0829 16.2833 19.6802L12.4576 17.0181C12.2929 16.9035 12.2106 16.8462 12.1211 16.8239C12.042 16.8043 11.9593 16.8043 11.8803 16.8239C11.7908 16.8462 11.7084 16.9035 11.5437 17.0181L7.71805 19.6802C7.13937 20.0829 6.85003 20.2842 6.65733 20.2583C6.49056 20.2358 6.34626 20.131 6.27337 19.9793C6.18915 19.8041 6.29123 19.4667 6.49538 18.7919L7.84503 14.3308C7.90313 14.1387 7.93218 14.0427 7.92564 13.9507C7.91986 13.8695 7.89432 13.7909 7.85123 13.7217C7.80246 13.6435 7.72251 13.5829 7.56262 13.4616L3.84858 10.6459C3.28678 10.2199 3.00588 10.007 2.97101 9.81569C2.94082 9.65014 2.99594 9.48051 3.11767 9.36432C3.25831 9.23007 3.61074 9.22289 4.31559 9.20852L8.9754 9.11356C9.176 9.10947 9.27631 9.10743 9.36177 9.07278C9.43726 9.04218 9.50414 8.99359 9.55657 8.93125C9.61593 8.86068 9.64887 8.76592 9.71475 8.57639L11.245 4.174Z" stroke="#000000" stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round"></path> </g>
                            </svg>
                        @endfor
                    </div>
                </div>

                <input type="hidden" name="rating" id="rating" value="0">

                <x-input-error :messages="$errors->userRate->get('rating')" class="mt-2" />
        

                <div class="flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button class="ms-3">
                        {{ __('Rate') }}
                    </x-primary-button>
                </div>
            </form>
        </x-modal>
    @endcan
    @can('report', $user)
        <x-modal name="user-report" :show="$errors->userReport->isNotEmpty()" focusable>
            <form id="report-form" method="post" action="{{ route('user.report', $user->id) }}" class="p-6">
                @csrf
                @method('post')

                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Are you sure you want to report this account?') }}
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Please provide the reason for reporying this user.') }}
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

                    <x-input-error :messages="$errors->userReport->get('motive')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button class="ms-3">
                        {{ __('Report') }}
                    </x-primary-button>
                </div>
            </form>
        </x-modal>
    @endcan


    <!-- Statistics -->
    @regularuser
        @if (Auth::id() == $user->id)
            <div class="max-w-7xl mx-auto">
                @include('profile.partials.general-statistics')
                @if (Auth::user()->user->subscribed)
                    @include('profile.partials.advanced-statistics')
                @endif


            </div>
        @endif
    @endregularuser








    @push('scripts')
        @can('rate', $user)
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    let rating = 0;

                    function setRating(value) {
                        rating = value;
                        // Update the hidden input value
                        document.getElementById('rating').value = rating;
                        highlightStars();
                    }

                    const stars = document.querySelectorAll('.star');

                    function highlightStars() {
                        stars.forEach(star => {
                            const starValue = parseInt(star.getAttribute('data-value'));
                            if (starValue <= rating) {
                                star.setAttribute('fill', '#facc15');
                            } else {
                                star.setAttribute('fill','none');
                            }
                        });
                    }

                    stars.forEach(star => {
                        star.addEventListener('click', (event) => {
                            const value = parseInt(event.target.getAttribute('data-value'));
                            setRating(value);
                        });
                    });
                });
            </script>
        @endcan

        @can('follow', $user)
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const followButton = document.querySelector('.follow-btn-usr');
                    const followTxt = document.querySelector('.follow-txt');
                    const heartIcon = document.querySelector('.heart-icon');

                    followButton.addEventListener('click', async () => {
                        const userId = followTxt.getAttribute('user-id');

                        try {
                            const response = await fetch(`/followUser/${userId}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json',
                                },
                            });

                            const data = await response.json();

                            if (data.success) {

                                if (data.isFollowing) {
                                    followTxt.textContent = 'Following';
                                    heartIcon.style.fill = 'red';
                                } else {
                                    followTxt.textContent = 'Follow';
                                    heartIcon.style.fill = 'black';
                                }
                            } else {
                                console.error(data.message);
                            }
                        } catch (error) {
                            console.error('Error toggling follow:', error);
                        }
                    });
                });
            </script>
        @endcan
    @endpush

</x-app-layout>
