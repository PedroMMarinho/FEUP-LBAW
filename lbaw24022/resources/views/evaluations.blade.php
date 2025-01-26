<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Evaluation Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
            <form id="send-verification"
                method="post"
                action="{{ route('verification.send') }}">
                @csrf
            </form>
            <h2 class="text-2xl font-bold text-gray-900">
                {{ $evaluations->total() }} evaluation requests
            </h2>
            <x-auctions-grid :auctions="$evaluations" />
            <div class="mt-6">  
                {{ $evaluations->links() }}
            </div>
        </div>    
    </div>
</x-app-layout>
