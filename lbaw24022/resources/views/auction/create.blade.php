@props([
    "auction" => NULL,
    "categories" => [],
])


<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __($auction ? 'Edit your auction' : 'Create your own auction!'  ) }}
        </h2>
    </x-slot>


    <form id="send-verification"
        method="post"
        action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post"
        action="{{ $auction ? route('auction.edit', ['id' => $auction->id]) : route('auction.create') }}"
        class="mt-6 space-y-6"
        enctype="multipart/form-data" 
        novalidate>
        @csrf

        <div class="py-12 ">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    @include('auction.partials.general-information-section', ['auction' => $auction])
                    @include('auction.partials.description-section', ['auction' => $auction])
                    @include('auction.partials.attributes-section', ['auction' => $auction])
                    @include('auction.partials.images-section', ['images' => optional($auction)->getImages()])

                    
                    <div class="flex justify-end">
                        <x-primary-button class="">
                            {{$auction ? "Confirm Edit" : "Create"}}
                        </x-primary-button>
                    </div>
            </div>
     
        </div>


    </form>
</x-app-layout>