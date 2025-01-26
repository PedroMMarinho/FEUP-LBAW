@props([
    'defaultSearchOption' => 'auction',
    'defaultSearchValue' => null,
    'defaultLocationValue' => null,
    'usersOnly' => false,
    'auctionsOnly' => false,
])

<div class="flex flex-wrap bg-white shadow-sm rounded-lg">
    <div class="{{$usersOnly || $auctionsOnly ? 'hidden' : ''}}">
        <x-select 
            :options="[
                'auction' => 'Auction', 
                'user' => 'User',
            ]" 
            :default-value="$defaultSearchOption"
            :id="'search-option'"
            :container-class="'w-full sm:w-36 text-xl'"
            :selected-class="'h-full py-4 px-5 bg-white rounded-md border-0'"
        />    
    </div>

    <div class="flex flex-grow items-center relative overflow-hidden lg:border-r-2 border-gray-100 @if($usersOnly || $auctionsOnly) rounded-l-md @else sm:border-l-2 rounded-t-md lg:rounded-none @endif">
        <svg viewBox="0 0 384 512" class="w-5 h-5 fill-current text-black absolute left-4">
            <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
        </svg>
        <input type="text" 
            name="search" 
            id="search"
            placeholder="@if($defaultSearchOption === 'auction' && !$usersOnly) What are you looking for? @else Who are you looking for? @endif"
            value="{{ $defaultSearchValue }}"
            class="w-full p-4 text-xl pl-14 border-0 border-b-2 border-transparent focus:border-indigo-500 focus:ring-0 rounded-t-md lg:rounded-none">    
    </div>
    
    <div class="lg:w-1/4 w-full items-center relative @if($defaultSearchOption === 'auction' && !$usersOnly) flex @else hidden @endif">
        <svg viewBox="0 0 384 512" class="w-5 h-5 fill-current text-black absolute left-4">
            <path d="M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z"/>
        </svg>
        <input type="text" 
            name="location" 
            id="location"
            placeholder="Everywhere"
            value="{{ $defaultLocationValue }}"
            class="w-full p-4 text-xl pl-14 border-0 border-b-2 border-transparent focus:border-indigo-500 focus:ring-0">    
    </div>
    
    <button class="p-4 text-xl bg-indigo-600 text-white rounded-b-md lg:rounded-none lg:rounded-r-md font-bold hover:bg-indigo-700 active:scale-95 w-full lg:w-1/6 flex items-center justify-center space-x-2">
        <span>
            Search
        </span>
        <svg viewBox="0 0 384 512" class="w-5 h-5 fill-current">
            <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
        </svg>
    </button>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get the search input and search option elements
            const searchInput = document.getElementById('search');
            const searchOption = document.getElementById('search-option');
            const locationInputContainer = document.getElementById('location').parentElement;
            
            // Function to update the search bar based on the selected option
            function updateSearchBar() {
                if (searchOption.value === 'auction') {
                    searchInput.setAttribute('placeholder', 'What are you looking for?');
                    locationInputContainer.classList.remove('hidden');
                    locationInputContainer.classList.add('flex');
                } else if (searchOption.value === 'user') {
                    searchInput.setAttribute('placeholder', 'Who are you looking for?');
                    locationInputContainer.classList.remove('flex');
                    locationInputContainer.classList.add('hidden');
                }
            }

            // Set up a MutationObserver to watch for changes on #search-option
            const observer = new MutationObserver(updateSearchBar);
            
            // Observe changes in the 'value' attribute
            observer.observe(searchOption, {attributes: true})
        });    
    </script>
@endpush
