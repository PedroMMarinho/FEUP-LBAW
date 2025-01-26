
<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Your wallet') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto">
        <div class="flex xl:flex-row xl:flex-wrap xl:justify-between flex-col items-center space-around mb-6 mx-auto gap-10">

            <div class="min-w-1/2 xl:pl-48 xl:text-left mb-10 text-center">
                @include('wallet.partials.general-info')
            </div>
            <div class="px-11" style="width: 33rem; min-width: 33rem;">
                @include('wallet.partials.deposit')
            </div>

        </div>

        <div class="w-full mt-16">
            @include('wallet.partials.transaction-history')
        </div>
    </div>


</x-app-layout>



