<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form id="search-form" method="POST">
                <x-search-bar />
            </form>
            <div class="flex w-full justify-center pt-10">
                <h2 class="p-10 text-4xl font-bold text-gray-900">
                    Categories
                </h2>
            </div>
            <div class="flex flex-wrap justify-center items-center pb-10">
                @foreach ($categories as $category)
                    <a href="/auctions?category={{ $category->id }}"
                        class="rounded-full px-5 py-2 m-2 bg-white shadow-lg hover:bg-gray-200 hover:scale-105 active:scale-100 text-lg">
                            {{ $category->name }}
                    </a>
                @endforeach
            </div>
            <div class="flex w-full justify-center">
                <h2 class="p-10 text-4xl font-bold text-gray-900">
                    Featured Ads
                </h2>
            </div>
            <x-auctions-grid :auctions="$advertisedAuctions" />
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
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
                    // const urlParams = new URLSearchParams(window.location.search);
                    // const currentPage = urlParams.get('page') || 1;
                    const newUrl = window.location.pathname + '?' + queryString;

                    window.history.pushState({}, '', newUrl);

                    if (queryParams.some(param => param.includes('search-option=auction'))) {
                        // If the search-option is 'auction', redirect to '/auctions' page with query parameters
                        window.location.href = '/auctions?' + queryString;
                    }
                    else {
                        window.location.href = '/users?' + queryString;
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
