
@props([
    'prices' => [3, 2, 1],
])

@php
    $user = Auth::user()->specificRole();
    $subscription = $user->subscriptions->last();
    $subscribed = $user->subscribed;
    
@endphp


<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __($subscribed ? "Subscription - ends in " . ceil($subscription->end_time->diffInDays(now(), true)) . " days" : "Subscription") }}
        </h2>
    </x-slot>

     <form id="send-verification"
        method="post"
        action="{{ route('verification.send') }}">
        @csrf
    </form>
        
    <div class="my-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" >

        <form method="post"
            action="{{route('subscription.sub')}}"
            class="">
            @csrf


        <div class="flex  justify-center items-center flex-col-reverse xl:flex-row xl:h-[650px] xl:justify-between flex-wrap" >
            @include('subscription.partials.subscription-plans', ['prices'=> $prices, 'subscribed' => $subscribed])
            @include('subscription.partials.description')
        </div>

        <div class="flex justify-center">
            <x-primary-button :class="'!text-3xl !p-4 !px-9'">  Subscribe </x-primary-button>
        </div>
        </form>
    </div>

</x-app-layout>