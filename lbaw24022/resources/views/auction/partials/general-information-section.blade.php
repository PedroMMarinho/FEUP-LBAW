@props([
    'auction' => NULL
])

@php
    $finish=optional($auction)->end_time;
    $minimumBid=optional($auction)->minimum_bid;
    $title=optional($auction)->name;
    $location=optional($auction)->location;
@endphp

<section>
    <x-section-header
        :title="'General Information'">
    </x-section-header>

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div class="w-full flex justify-between flex-wrap gap-y-4 gap-x-4">
            <div class="w-3/12 min-w-72">

                <x-input-label for="title"
                    :value="__('Product title *')" />
                <x-input id="title"
                    name="title"
                    class="mt-1 w-full"
                    :type="'text'"
                    :placeholder="'Simple and descriptive'"
                    :defaultValue="old('title', $title ?? '')"
                    required
                    autofocus/>
                <div class="flex justify-between px-3">
                    <x-input-error class="mt-2"
                        :messages="$errors->get('title')" />
                    <x-character-count targetId="title" maxLength="40" />

                </div>
            </div>

            <div class="w-2/12 min-w-32">
                <x-input-label for="minimumBid"
                    :value="__('Minimum Bid')" />
                <div class="relative ">
                    <x-input id="minimumBid"
                        name="minimumBid"
                        class="mt-1 block w-full appearance-none" 
                        :type="'price'"
                        :defaultValue="old('minimumBid', $minimumBid ?? '')"
                        :placeholder="'Ex. 150'"
                        />
                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600">€</span>
                </div>
                <x-input-error class="mt-2"
                    :messages="$errors->get('minimumBid')" />
            </div>

            <div class="w-2/12 min-w-52">
                <x-input-label for="endTime"
                    :value="__('Finish Date *  < 1 month')" />
                <x-text-input id="endTime"
                    name="endTime"
                    type="datetime-local"
                    class="mt-1 block w-full !appearance-none"
                    value="{{old('endTime', $finish ?? '')}}"
                    min="{{now()->addHours(2)->format('Y-m-d\TH:i')}}"
                    />
                <x-input-error class="mt-2"
                    :messages="$errors->get('endTime')" />
            </div>

            <div class="w-3/12 min-w-52">
                <x-input-label for="location"
                    :value="__('Product location *')" />
                <x-text-input id="location"
                    name="location"
                    type="text"
                    class="mt-1 block w-full appearance-none"
                    value="{{old('location', $location ?? '')}}"
                    placeholder="Ex. Porto"
                    />
                <x-input-error class="mt-2"
                    :messages="$errors->get('location')" />
            </div>



        </div>
    </div>
</section>
