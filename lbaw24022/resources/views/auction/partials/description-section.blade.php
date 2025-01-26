@props([
  'auction' => NULL,  
])


@php
    $description = optional($auction)->description;   
@endphp

<section>
    <x-section-header
        :title="'Description'">
    </x-section-header>

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

        <x-input-label for="description"
            :value="__('Product Description *')" />
        <x-textarea-input
            id="description"
            name="description"
            :value="old('description', $description ?? '')"
            maxLength="500"
            class="mt-1 w-full !h-60"
            required
        />

    </div>
</section>
