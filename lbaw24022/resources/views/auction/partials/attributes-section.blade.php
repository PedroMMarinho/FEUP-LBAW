@props([
    'auction' => null,
])

@php
    $category = optional($auction)->category;
    $attributes = optional($auction)->attribute_values;
@endphp

<section>
    <x-section-header
        :title="'Category and Attributes'">
    </x-section-header>
    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div id="category-filter" class="">
            <x-filter 
                type="enum"
                id="category"
                label="Category"
                :options="$categories->pluck('name', 'id')->toArray()"
                :defaultValue="old('category', optional($category)->id)"
                :defaultText="'Select a category'"
                :nullOption="false"
                class="bg-black"
            />
            <x-input-error class="mt-2"
                :messages="$errors->get('category')" />
        </div>

        <h1 class="mb-8">Attributes</h1>
        @php
            $categoryId = old('category', optional($category)->id);
            $auctionCategory = \App\Models\Category::find($categoryId); // Find the actual Category object
        @endphp
        <x-attribute-sellectors 
            :auctionCategory="$auctionCategory"
            :attributeValues="old('category') ? null : optional($auction)->attribute_values"
            :categories="$categories"
            :nullOption="false"
        />
            <x-input-error class="mt-2"
                :messages="$errors->get('attributes')" />
    </div>
</section>
