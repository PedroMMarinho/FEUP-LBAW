@extends('adminManagement.management')

@section('slot')
    <div class="w-full">
        <section>
            <header class="flex justify-between">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ 'Categories' }}
                </h2>
            </header>
        
            <form id="send-verification"
                method="post"
                action="{{ route('verification.send') }}">
                @csrf
            </form>
            
            <div id="categories-container" class="flex flex-wrap gap-4 mt-8">
                @foreach ($categories as $category)
                    <div category-id="{{ $category->id }}" category-name="{{ $category->name }}" class="category-container flex gap-3 py-2 px-4 rounded-xl border border-indigo-300 shadow-sm">
                        <p class="text-md font-medium">{{ $category->name }}</p>
                        @if ($category->id !== 1)
                            <a href="/management/categories/{{$category->id}}" title="Edit Category" class="w-6 h-6 fill-current text-black hover:text-blue-700 hover:scale-110 active:scale-100">
                                <svg viewBox="0 0 512 512">
                                    <path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152L0 424c0 48.6 39.4 88 88 88l272 0c48.6 0 88-39.4 88-88l0-112c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 112c0 22.1-17.9 40-40 40L88 464c-22.1 0-40-17.9-40-40l0-272c0-22.1 17.9-40 40-40l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L88 64z"/>
                                </svg>
                            </a>
                            <button title="Delete Category" onclick="openDeleteCategoryModal({{ $category->id }}, '{{ $category->name }}')" class="w-5 h-5 fill-current text-black hover:text-red-700 hover:scale-110 active:scale-100">
                                <svg viewBox="0 0 448 512">
                                    <path d="M170.5 51.6L151.5 80l145 0-19-28.4c-1.5-2.2-4-3.6-6.7-3.6l-93.7 0c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80 368 80l48 0 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-8 0 0 304c0 44.2-35.8 80-80 80l-224 0c-44.2 0-80-35.8-80-80l0-304-8 0c-13.3 0-24-10.7-24-24S10.7 80 24 80l8 0 48 0 13.8 0 36.7-55.1C140.9 9.4 158.4 0 177.1 0l93.7 0c18.7 0 36.2 9.4 46.6 24.9zM80 128l0 304c0 17.7 14.3 32 32 32l224 0c17.7 0 32-14.3 32-32l0-304L80 128zm80 64l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16z"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>



            <div class="flex flex-row justify-center w-full gap-4 pt-5 mt-5 border-t-2">
                <x-text-input
                    class="w-full min-w-40 max-w-80"
                    id="new-category-name"
                    placeholder="New Category Name" />
                <button onclick="createNewCategory()" type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md text-white hover:bg-blue-700 focus:bg-blue-800 active:bg-blue-900 focus:outline-none hover:scale-105 active:scale-100 transition ease-in-out duration-150 whitespace-nowrap">
                    New Category
                </button>
            </div>
        </section>                
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-category-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex justify-center items-center h-full">
            <div class="bg-white m-6 p-6 rounded-lg shadow-lg max-w-lg w-full">
                <h1 class="text-xl font-semibold text-gray-800">Delete Category</h1>
                <p>Are you sure you want to delete the category <span id="category-name-modal" class="font-bold">Category Name</span>? This action is irreversible.</p>
                <p id="delivery-location-error" class="text-red-600 hidden">There was an error!</p>
                <div class="mt-4 flex justify-end gap-2">
                    <button id="delete-category-button-modal" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Confirm</button>
                    <button type="button" onclick="closeDeleteCategoryModal()" class="px-4 py-2 bg-slate-500 text-white rounded-lg">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function closeDeleteCategoryModal() {
            document.getElementById('delete-category-modal-overlay').classList.add('hidden');
        }
        
        function openDeleteCategoryModal(categoryId, categoryName) {
            const categoryNameSpan = document.getElementById('category-name-modal');
            const deleteCategoryBtn = document.getElementById('delete-category-button-modal');

            categoryNameSpan.textContent = categoryName;
            deleteCategoryBtn.removeEventListener('click', handleDeleteClick);
            deleteCategoryBtn.addEventListener('click', handleDeleteClick);

            function handleDeleteClick() {
                deleteCategory(categoryId, categoryName);
            }

            document.getElementById('delete-category-modal-overlay').classList.remove('hidden');
        }

        function createNewCategory() {
            const categoriesContainer = document.getElementById("categories-container");
            const newCategoryNameInput = document.getElementById("new-category-name");
            const categoryName = newCategoryNameInput.value.trim().toLowerCase().replace(/\b\w/g, char => char.toUpperCase());

            if(!categoryName || categoryName.length < 3) {
                showPopUp("Please enter a valid category name", 'error');
                return false;
            }

            const categories = Array.from(document.querySelectorAll('.category-container')).map(categoryContainer => categoryContainer.getAttribute('category-name'));

            if (categories.includes(categoryName)) {
                showPopUp("This category already exists", 'error');
                return false;
            }

            fetch(`/categories`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                },
                body: JSON.stringify({
                    name: categoryName
                })
            })
            .then(response => {
                if (response.ok) {
                    
                    // Create category container
                    return response.json();
                } else {
                    response.json().then(data => {
                        showPopUp(data.error || 'Something went wrong, please try again', 'error');
                    });
                }
            })
            .then(data => {
                const categoryId = data.id;
                const categoryContainer = `
                    <div category-id="${categoryId}" category-name="${categoryName}" class="category-container flex gap-3 py-2 px-4 rounded-xl border border-indigo-300 shadow-sm">
                        <p class="text-md font-medium">${categoryName}</p>
                        <a href="/management/categories/${categoryId}" title="Edit Category" class="w-6 h-6 fill-current text-black hover:text-blue-700 hover:scale-110 active:scale-100">
                            <svg viewBox="0 0 512 512">
                                <path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152L0 424c0 48.6 39.4 88 88 88l272 0c48.6 0 88-39.4 88-88l0-112c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 112c0 22.1-17.9 40-40 40L88 464c-22.1 0-40-17.9-40-40l0-272c0-22.1 17.9-40 40-40l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L88 64z"/>
                            </svg>
                        </a>
                        <button title="Delete Category" onclick="openDeleteCategoryModal(${categoryId}, '${categoryName}')" class="w-5 h-5 fill-current text-black hover:text-red-700 hover:scale-110 active:scale-100">
                            <svg viewBox="0 0 448 512">
                                <path d="M170.5 51.6L151.5 80l145 0-19-28.4c-1.5-2.2-4-3.6-6.7-3.6l-93.7 0c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80 368 80l48 0 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-8 0 0 304c0 44.2-35.8 80-80 80l-224 0c-44.2 0-80-35.8-80-80l0-304-8 0c-13.3 0-24-10.7-24-24S10.7 80 24 80l8 0 48 0 13.8 0 36.7-55.1C140.9 9.4 158.4 0 177.1 0l93.7 0c18.7 0 36.2 9.4 46.6 24.9zM80 128l0 304c0 17.7 14.3 32 32 32l224 0c17.7 0 32-14.3 32-32l0-304L80 128zm80 64l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208c0-8.8 7.2-16 16-16s16 7.2 16 16z"/>
                            </svg>
                        </button>
                    </div>`;
                
                categoriesContainer.insertAdjacentHTML('beforeend', categoryContainer);
                newCategoryNameInput.value = '';
                showPopUp(`Category ${categoryName} created successfully`, 'success');
            })
            .catch(error => {
                showPopUp('Network error, please try again later', 'error');
                console.error('Error creating category:', error);
            });
        }

        function deleteCategory(categoryId, categoryName) {
            fetch(`/categories/${categoryId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                }
            })
            .then(response => {
                if (response.ok) {
                    const categoryContainer = document.querySelector(`.category-container[category-id="${categoryId}"]`);
                    if (categoryContainer) {
                        categoryContainer.remove();
                    }

                    closeDeleteCategoryModal();

                    showPopUp(`Category ${categoryName} deleted successfully`, 'success');
                } else {
                    response.json().then(data => {
                        showPopUp(data.error || 'Something went wrong, please try again', 'error');
                    });
                }
            })
            .catch(error => {
                showPopUp('Network error, please try again later', 'error');
                console.error('Error deleting category:', error);
            });
        }
    </script>
@endpush