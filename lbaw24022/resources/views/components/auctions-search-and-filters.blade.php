@props([
    'sectionPage' => false,
    'auctionsOnly' => false,
    'defaultSearchOption' => 'auction',
    'search',
    'location',
    'categories',
    'auctions',
])

<form id="search-form" method="POST" class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <x-search-bar 
        :auctions-only="$auctionsOnly" 
        :default-search-option="$defaultSearchOption"
        :default-search-value="$search"
        :default-location-value="$location"/>
    <h2 class="pt-10 pb-5 text-2xl font-bold text-gray-900">
        Filters
    </h2>
    <ul class="flex flex-wrap gap-4" id="filters">
        <li id="category-filter">
            <x-filter 
                type="enum"
                id="category"
                label="Category"
                :options="$categories->pluck('name', 'id')->toArray()"
                :default-value="$selectedCategory->id"
            />
        </li>
        <li id="bid-filter">
            <x-filter 
                type="price"
                id="bid"
                label="Current bid"
                :from-default-value="$fromBid"
                :to-default-value="$toBid"
            />
        </li>
        @foreach ($categories as $category)
            @if ($category->attribute_list)
                @foreach ($category->attribute_list as $attribute)
                    <li class="category-{{$category->id}}-filter">
                        @if ($attribute['type'] == 'enum')
                            @if ($category->id == $selectedCategory->id)
                                <x-filter 
                                    type="enum"
                                    id="attribute-{{ $category->id }}-{{ $attribute['name'] }}"
                                    name="attributes[{{ $category->id }}][{{ $attribute['name'] }}]"
                                    label="{{ ucfirst($attribute['name']) }}"
                                    :options="$attribute['options']"
                                    :default-value="array_search(request()->query('attributes')[$category->id][$attribute['name']] ?? null, $attribute['options'])"
                                />
                            @else
                                <x-filter 
                                    type="enum"
                                    id="attribute-{{ $category->id }}-{{ $attribute['name'] }}"
                                    name="attributes[{{ $category->id }}][{{ $attribute['name'] }}]"
                                    label="{{ ucfirst($attribute['name']) }}"
                                    :options="$attribute['options']"
                                />
                            @endif
                        @elseif ($attribute['type'] == 'string')
                            @if ($category->id == $selectedCategory->id)
                                <x-filter 
                                    type="{{ $attribute['type'] }}"
                                    id="attribute-{{ $category->id }}-{{ $attribute['name'] }}"
                                    name="attributes[{{ $category->id }}][{{ $attribute['name'] }}]"
                                    label="{{ ucfirst($attribute['name']) }}"
                                    :default-value="request()->query('attributes')[$category->id][$attribute['name']] ?? null"
                                />
                            @else
                                <x-filter 
                                    type="{{ $attribute['type'] }}"
                                    id="attribute-{{ $category->id }}-{{ $attribute['name'] }}"
                                    name="attributes[{{ $category->id }}][{{ $attribute['name'] }}]"
                                    label="{{ ucfirst($attribute['name']) }}"
                                />
                            @endif
                        @else
                            @if ($category->id == $selectedCategory->id)
                                <x-filter 
                                    type="{{ $attribute['type'] }}"
                                    id="attribute-{{ $category->id }}-{{ $attribute['name'] }}"
                                    name="attributes[{{ $category->id }}][{{ $attribute['name'] }}]"
                                    label="{{ ucfirst($attribute['name']) }}"
                                    :from-default-value="request()->query('attributes')[$category->id][$attribute['name']]['from'] ?? null"
                                    :to-default-value="request()->query('attributes')[$category->id][$attribute['name']]['to'] ?? null"
                                />
                            @else
                                <x-filter 
                                    type="{{ $attribute['type'] }}"
                                    id="attribute-{{ $category->id }}-{{ $attribute['name'] }}"
                                    name="attributes[{{ $category->id }}][{{ $attribute['name'] }}]"
                                    label="{{ ucfirst($attribute['name']) }}"
                                />
                            @endif
                        @endif
                    </li>
                @endforeach
            @endif    
        @endforeach                               
    </ul>
    <h2 class="pt-10 text-2xl font-bold text-gray-900">
        @if ($totalAuctions > 1000)
            We found more than 1000 results
        @else
            We found {{ $totalAuctions }} results
        @endif
    </h2>
    <x-auctions-grid :auctions="$auctions" />
    <div class="mt-6">
        {{ $auctions->appends(request()->query())->links() }}
    </div>
</form>


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sectionPage = {!! json_encode($sectionPage) !!}; 
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

            // -------------------------------------------------------------
            // Search Logic
            // ------------------------------------------------------------- 

            const form = document.getElementById('search-form');

            // Add an event listener for form submission
            form.addEventListener('submit', function(event) {
                const selectedCategoryId = categoryInput.value;
                selectedCategory = categories.find(category => category.id == selectedCategoryId);

                // Prevent the default form submission behavior
                event.preventDefault();

                // Log all form data to the console
                const formData = new FormData(form);

                // Initialize an empty array to hold query parameters
                const queryParams = [];
                
                formData.forEach((value, key) => {
                    const prefix = `attributes[${selectedCategoryId}]`;
                    if (!key.startsWith(prefix) &&
                        !['category', 'search', 'location', 'bid[from]', 'bid[to]'].includes(key)
                    ) {
                        return;
                    }
                        
                    if (key.startsWith(prefix)) {
                        // /attributes\[2\]\[(\w+)\]/;
                        const attributeNameRegex = new RegExp(`attributes\\[${selectedCategoryId}\\]\\[(\\w+)\\]`);
                        const match = key.match(attributeNameRegex);
                        if(!match) return;

                        const attribute = selectedCategory.attribute_list.find(attribute => attribute.name === match[1]);

                        if (attribute.type == 'enum') {
                            value = parseInt(value, 10)
                            if (value < 0) return;
                            value = attribute.options[value];
                        }
                        else if (attribute.type == 'float') {
                            value = parseFloat(value);
                        }
                        else if (attribute.type == 'int') {
                            value = parseInt(value, 10);
                        }
                        else if (attribute.type == 'text') {
                            value = String(value);
                        }
                    }

                    if (!value || (key === 'category' && value === '-1')) {
                        return;
                    }

                    queryParams.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`);
                });
                
                if (formData.get('search-option') == 'auction') {
                    // If the search-option is 'auction', redirect to '/auctions' page with query parameters
                    
                    // Join all query parameters into a single string
                    const queryString = queryParams.join('&');
                    let newUrl = window.location.pathname + '?' + queryString;
                    if (sectionPage) {
                        const urlParams = new URLSearchParams(window.location.search);
                        const sectionValue = urlParams.get('section');
                        newUrl = window.location.pathname + '?' + 'section=' + sectionValue + '&' + queryString;
                    }

                    window.history.pushState({}, '', newUrl);
                    location.reload();
                }
                else {  
                    // If the search-option is not 'auction', only include 'search' and 'location'
                    const searchLocationParams = queryParams.filter(param => param.includes('search=') || param.includes('location='));

                    // Join only the search and location parameters
                    const queryString = searchLocationParams.join('&');
                    const newUrl = window.location.pathname + '?' + queryString;
                    window.history.pushState({}, '', newUrl);

                    // Redirect to the '/users' page with query parameters
                    window.location.href = '/users?' + queryString;
                }
            });
        });
    </script>
@endpush