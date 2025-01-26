
@props([
    'categories' => [],
    'attributeValues' => null,
    'auctionCategory' => null,
    'nullOption' => true,
])

<ul class="flex flex-wrap gap-4" id="filters">
               
                @foreach ($categories ?? [] as $category)
                    @foreach ($category->attribute_list ?? [] as $attribute)
                        <li class="category-{{$category->id}}-filter">

                            @php    
                                $defaultValue = null;
                                if ($category->name == optional($auctionCategory)->name)
                                {
                                    $defaultValue = $attributeValues[$attribute['name']] ?? null;
                                }
                            @endphp

                            @if ($attribute['type'] == 'enum')

                                    @php
                                        $defaultValue = old("enum" . $category->id . "-" . $attribute['name'] . "-attribute", array_search($defaultValue, $attribute['options']));
                                    @endphp
                                    <x-filter 
                                        type="enum"
                                        label="{{ $attribute['name'] }}"
                                        :options="$attribute['options']"
                                        :nullOption="$nullOption"
                                        :defaultValue="$defaultValue"
                                        :defaultText="'Select the ' . $attribute['name']"
                                        id="enum{{$category->id}}-{{$attribute['name']}}-attribute"
                                    />
                            @else

                                @php
                                    $defaultValue = old("c". $category->id . "-" . $attribute['name'] . "-attribute", $defaultValue);
                                @endphp
                                <div class="flex flex-col gap-2 grow">

                                    <label class="text-md mb-auto" for="c{{$category->id}}-{{$attribute['name']}}-attribute">{{ $attribute['name']}}</label>
                                    <x-input 
                                        type="{{ $attribute['type'] }}"
                                        label="{{ $attribute['name']}}"
                                        :nullOption=$nullOption
                                        :placeholder="'The ' . $attribute['name']"
                                        :defaultValue=$defaultValue
                                        id="c{{$category->id}}-{{$attribute['name']}}-attribute"
                                    />

                                </div>
                            @endif
                        </li>
                    @endforeach    
                @endforeach                               
            </ul>

 @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const categoryInput = document.getElementById('category');
                const filtersContainer = document.getElementById('filters');
                
                // Function to update the filters on the selected category
                function updateFilters() {
                    const selectedCategoryId = categoryInput.value;

                    const allFilters = filtersContainer.querySelectorAll(':scope > li');
                    allFilters.forEach(filter => {
                        filter.style.display = 'block';

                        const relevantInput = filter.querySelectorAll("input");
                        relevantInput.forEach(input => {
                            input.disabled=false;
                        })
                    });

                    // Now, hide all filters not related to the selected category, excluding category-filter and price-filter
                    const irrelevantFilters = filtersContainer.querySelectorAll(`:scope > li:not(.category-${selectedCategoryId}-filter):not(#category-filter):not(#price-filter)`);
                    
                    irrelevantFilters.forEach(filter => {
                        filter.style.display = 'none';
                        const irrelevantInputs = filter.querySelectorAll("input");
                        irrelevantInputs.forEach(input => {
                            input.disabled=true;
                        })
                    });
                }

                // Initially set the filters on page load
                updateFilters();

                // Set up a MutationObserver to watch for changes on #category
                const observer = new MutationObserver(updateFilters);
                
                // Observe changes in the 'value' attribute
                observer.observe(categoryInput, {attributes: true})
            });
        </script>
    @endpush