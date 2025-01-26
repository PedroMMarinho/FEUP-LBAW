<x-app-layout>
    <form id="search-form" method="POST" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-search-bar 
                default-search-option="user"
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
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </form>

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
