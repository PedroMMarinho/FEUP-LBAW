<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-slot name="header">
                <h2 id="auctions-header" class="font-semibold text-xl text-gray-800 leading-tight">
                    @if ($section == 'following-users')
                        {{ __('Following Users') }}
                    @elseif ($section == 'following-auctions')
                        {{ __('Following Auctions') }}
                    @elseif ($section == 'won-auctions')
                        {{ __('Won Auctions') }}
                    @elseif ($section == 'shipped-auctions')
                        {{ __('Shipped Auctions') }}
                    @else
                        {{ __('My Active Bids') }}
                    @endif
                </h2>
            </x-slot>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <div class="flex flex-col items-center justify-center p-3 h-full aspect-square rounded-xl gap-4 bg-white shadow-sm">
                    <h2 class="text-5xl" id='active-bids-count'>{{ $counts['active-bids'] }}</h2>
                    <h2 class="text-lg text-center">Active Bids</h2>
                </div>
                <div class="flex flex-col items-center justify-center p-4 h-full aspect-square rounded-xl gap-4 bg-white shadow-sm">
                    <h2 class="text-5xl" id='won-auctions-count'>{{ $counts['won-auctions'] }}</h2>
                    <h2 class="text-lg text-center">Won Auctions</h2>
                </div>
                <div class="flex flex-col items-center justify-center p-4 h-full aspect-square rounded-xl gap-4 bg-white shadow-sm">
                    <h2 class="text-5xl" id='shipped-auctions-count'>{{ $counts['shipped-auctions'] }}</h2>
                    <h2 class="text-kg text-center">Shipped Auctions</h2>
                </div>
                <div class="flex flex-col items-center justify-center p-4 h-full aspect-square rounded-xl gap-4 bg-white shadow-sm">
                    <h2 class="text-5xl" id='following-auctions-count'>{{ $counts['following-auctions'] }}</h2>
                    <h2 class="text-lg text-center">Following Auctions</h2>
                </div>
                <div class="flex flex-col items-center justify-center p-2 h-full aspect-square rounded-xl gap-4 bg-white shadow-sm">
                    <h2 class="text-5xl" id='following-users-count'>{{ $counts['following-users'] }}</h2>
                    <h2 class="text-lg text-center">Following Users</h2>
                </div>
            </div>
            <div class="flex flex-wrap justify-start gap-2 my-6">
                <a href="/buyer-dashboard" class="px-4 py-2  {{ $section == null || $section == 'active-bids' ? 'bg-indigo-600' : 'bg-slate-400' }} text-md text-white rounded-full hover:bg-indigo-700 hover:scale-105 active:scale-100">
                    Active Bids
                </a>
                <a href="/buyer-dashboard?section=won-auctions" class="px-4 py-2  {{ $section == 'won-auctions' ? 'bg-indigo-600' : 'bg-slate-400' }} text-md text-white rounded-full hover:bg-indigo-700 hover:scale-105 active:scale-100">
                    Won Auctions
                </a>
                <a href="/buyer-dashboard?section=shipped-auctions" class="px-4 py-2  {{ $section == 'shipped-auctions' ? 'bg-indigo-600' : 'bg-slate-400' }} text-md text-white rounded-full hover:bg-indigo-700 hover:scale-105 active:scale-100">
                    Shipped Auctions
                </a>
                <a href="/buyer-dashboard?section=following-auctions" class="px-4 py-2 {{ $section == 'following-auctions' ? 'bg-indigo-600' : 'bg-slate-400' }} text-md text-white rounded-full hover:bg-indigo-700 hover:scale-105 active:scale-100">
                    Following Auctions
                </a>
                <a href="/buyer-dashboard?section=following-users" class="px-4 py-2 {{ $section == 'following-users' ? 'bg-indigo-600' : 'bg-slate-400' }} text-md text-white rounded-full hover:bg-indigo-700 hover:scale-105 active:scale-100">
                    Following Users
                </a>
            </div>
            @if ($section == 'following-users')
                <form id="search-form" method="POST">
                    <div id="users-section">
                        <x-search-bar
                            usersOnly="true" 
                            defaultSearchOption="user"
                            :default-search-value="$search"
                        />
                        <h2 class="pt-10 text-2xl font-bold text-gray-900">
                            @if ($totalUsers > 1000)
                                We found more than 1000 results
                            @else
                                We found {{ $totalUsers }} results
                            @endif
                        </h2>
                        <x-users-grid :users="$users" />
                        <div class="mt-6">
                            {{ $users->appends(request()->except('page'))->links() }}
                        </div>
                    </div>
                </form>
            @endif
        </div>
        @if ($section != 'following-users')
            <div id="auctions-section">
                <x-auctions-search-and-filters 
                    :search="$search"
                    :location="$location"
                    :categories="$categories"
                    :selected-category="$selectedCategory"
                    :from-bid="$fromBid"
                    :to-bid="$toBid"
                    :total-auctions="$totalAuctions"
                    :auctions="$auctions"
                    :auctions-only="true"
                    :section-page="true"
                    :default-search-option="'auction'"
                />
            </div>
        @endif
    </div>
    
    <!-- Add Delivery Location Modal Overlay -->
    <div id="add-delivery-location-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex justify-center items-center h-full">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                <h1 class="text-xl font-semibold text-gray-800">Add delivery location</h1>
                <x-text-input id="delivery-location-input" placeholder="Delivery Location" class="mt-2 w-full"/>
                <p id="delivery-location-error" class="text-red-600 hidden">There was an error!</p>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="addDeliveryLocation(event)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Confirm</button>
                    <button type="button" onclick="closeAddDeliveryLocationModal()" class="px-4 py-2 bg-slate-500 text-white rounded-lg">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function closeAddDeliveryLocationModal() {
                document.getElementById('add-delivery-location-modal-overlay').classList.add('hidden');
            }
            
            // To open the modal, you can call this function wherever needed
            function openAddDeliveryLocationModal() {
                document.getElementById('add-delivery-location-modal-overlay').classList.remove('hidden');
            }

            async function addDeliveryLocation(event) {
                event.preventDefault();
                event.stopPropagation();
                const deliveryLocationInput = document.getElementById('delivery-location-input');
                const deliveryLocationError = document.getElementById('delivery-location-error');
                const auctionId = deliveryLocationInput.getAttribute('auction-id');
                const deliveryLocation = deliveryLocationInput.value;

                if (!deliveryLocation || deliveryLocation.length >= 255) {
                    deliveryLocationError.classList.remove('hidden');
                    deliveryLocationError.textContent = "Invalid delivery location!";
                    return;
                }

                try {
                    const response = await fetch(`/addDeliveryLocation/${auctionId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ deliveryLocation: deliveryLocation })
                    });

                    const data = await response.json();

                    if (data.success) {
                        const button = document.querySelector(`.add-delivery-location-button[auction-id="${auctionId}"]`);
                        const newButton = button.cloneNode(true);
                        button.parentNode.replaceChild(newButton, button);
                        newButton.innerHTML = `
                            <svg viewBox="0 0 448 512" class="w-5 h-5 fill-current text-black">
                                <path d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/>
                            </svg>
                        `;
                        newButton.title = 'Waiting for item to be shipped';
                        newButton.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            showPopUp("Already has a delivery location! Wait for it to be shipped", 'error');
                            return false;
                        });
                        closeAddDeliveryLocationModal();
                        showPopUp('Delivery location added succesfuly!', 'sucess')
                    } else {
                        deliveryLocationError.classList.remove('hidden');
                        deliveryLocationError.textContent = data.message;
                    }
                } catch (error) {
                    console.error('Error adding delivery location:', error);
                    deliveryLocationError.classList.remove('hidden');
                    deliveryLocationError.textContent = 'Error adding delivery location. Try again later!';
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const section = {!! json_encode($section) !!};

                if(section != 'following-users') {
                    const categories = {!! json_encode($categories) !!};
                    const categoryInput = document.getElementById('category');
                    const filtersContainer = document.getElementById('filters');
                    
                    // Function to update the filters on the selected category
                    function updateFilters() {
                        const selectedCategoryId = categoryInput.value;

                        const allFilters = filtersContainer.querySelectorAll(':scope > li');
                        allFilters.forEach(filter => {
                            filter.style.display = 'block';
                        });

                        // Now, hide all filters not related to the selected category, excluding category-filter and bid-filter
                        const irrelevantFilters = filtersContainer.querySelectorAll(`:scope > li:not(.category-${selectedCategoryId}-filter):not(#category-filter):not(#bid-filter)`);
                        
                        irrelevantFilters.forEach(filter => {
                            filter.style.display = 'none';
                        });
                    }

                    // Initially set the filters on page load
                    updateFilters();

                    // Set up a MutationObserver to watch for changes on #category
                    const observer = new MutationObserver(updateFilters);
                    
                    // Observe changes in the 'value' attribute
                    observer.observe(categoryInput, {attributes: true})
                }
                // -------------------------------------------------------------
                // Search Logic
                // -------------------------------------------------------------
                
                const urlParams = new URLSearchParams(window.location.search);
                const sectionValue = urlParams.get('section');

                const form = document.getElementById('search-form');

                // Add an event listener for form submission
                form.addEventListener('submit', function(event) {
                    // Prevent the default form submission behavior
                    event.preventDefault();

                    // Log all form data to the console
                    const formData = new FormData(form);

                    // Initialize an empty array to hold query parameters
                    const queryParams = [];

                    formData.forEach((value, key) => {
                        if (value == null || value === '') {
                            return;
                        }

                        queryParams.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`);
                    });

                    // Join all query parameters into a single string
                    const queryString = queryParams.join('&');
                    const urlParams = new URLSearchParams(window.location.search);
                    const sectionValue = urlParams.get('section');
                    const newUrl = window.location.pathname + '?' + 'section=' + sectionValue + '&' + queryString + '&users=true'

                    window.history.pushState({}, '', newUrl);
                    location.reload();
                });

                // -------------------------------------------------------------
                // Add Delivery Location Buttons
                // -------------------------------------------------------------

                const addDeliveryLocationBtns = document.querySelectorAll('.add-delivery-location-button');

                addDeliveryLocationBtns.forEach(function(button) {
                    button.addEventListener('click', async function(event) {
                        event.preventDefault();
                        event.stopPropagation();
                        const auctionId = button.getAttribute('auction-id');

                        const deliveryLocationInput = document.getElementById('delivery-location-input');
                        const deliveryLocationError = document.getElementById('delivery-location-error');
                        deliveryLocationInput.setAttribute('auction-id', auctionId);
                        deliveryLocationInput.value = "";
                        deliveryLocationError.classList.add('hidden');

                        // Open Modal with the location input
                        openAddDeliveryLocationModal();
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>