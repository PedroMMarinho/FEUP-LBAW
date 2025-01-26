<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $category->name }} Category
        </h2>
    </x-slot>
    <form id="edit-category-form" method="POST" action="/categories/{{$category->id}}" class="py-12">
        @csrf
        @method('PATCH')
        <div class="max-w-7xl mx-auto px-6 lg:px-8 gap-4">
            <div id="attributes-container" class="flex flex-col w-full gap-8">
                @foreach ($category->attribute_list as $attribute)
                    <div attribute-name="{{ $attribute['name'] }}" class="attribute-container flex flex-col-reverse md:flex-row w-full border-b-2 py-4 gap-4">
                        <div class="flex flex-col w-full">
                            <div class="flex flex-row flex-wrap gap-4">
                                <div class="flex flex-col justify-start gap-2 w-full md:w-fit">
                                    <label>Attribute name:</label>
                                    <p class="py-2 px-4 min-w-40 max-w-80 overflow-x-auto bg-white border-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ $attribute['name'] }}</p>
                                    <input type="hidden" name="attributes[{{ $attribute['name'] }}][name]" value="{{ $attribute['name'] }}">
                                </div>
                                <div class="flex flex-col justify-start gap-2 w-full md:w-fit">
                                    <label>Attribute type:</label>
                                    <x-select
                                        :options="[
                                            'string' => 'String',
                                            'enum' => 'Enum',
                                            'float' => 'Float',
                                            'int' => 'Int',
                                        ]"                                        
                                        :default-value="$attribute['type']"
                                        container-class="w-40"
                                        :id="'attribute-type-' . $attribute['name']" 
                                        name="attributes[{{ $attribute['name'] }}][type]"
                                        placeholder="Attribute Type" />
                                </div>
                            </div>
                            <div class="flex flex-col justify-start w-full mt-8 gap-2 {{ ($attribute['type'] == 'enum') ? '' : 'hidden' }}">
                                <p>Attribute options:</p>
                                <div class="flex flex-row flex-wrap w-full gap-2">
                                    <div attribute-name="{{ $attribute['name'] }}" class="attribute-options-container flex flex-row flex-wrap gap-2">
                                        @if ($attribute['type'] == 'enum')
                                            @foreach ($attribute['options'] as $option)
                                                <div class="flex flex-row gap-3 items-center rounded-2xl bg-white px-6 py-2 shadow-sm">
                                                    <p>{{ $option }}</p>
                                                    <button onclick="deleteAttributeOption(event)" type="button" class="w-4 h-5 fill-current text-black hover:text-blue-700 hover:scale-110 active:scale-100">
                                                        <svg viewBox="0 0 384 512">
                                                            <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                                                        </svg>
                                                    </button>
                                                    <input type="hidden" name="attributes[{{ $attribute['name'] }}][options][]" value="{{ $option }}">
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="flex flex-row items-center gap-3">
                                        <x-text-input
                                            class="w-full new_option"
                                            name="new_option"
                                            attribute-name="{{ $attribute['name'] }}"
                                            placeholder="New option" />
                                        <button onclick="addNewOption(event, '{{ $attribute['name'] }}')" type="button" class="w-5 h-6 fill-current text-black hover:text-blue-700 hover:scale-110 active:scale-100">
                                            <svg viewBox="0 0 448 512">
                                                <path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 144L48 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l144 0 0 144c0 17.7 14.3 32 32 32s32-14.3 32-32l0-144 144 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-144 0 0-144z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <button onclick="openDeleteAttributeModal('{{ $attribute['name'] }}')" type="button" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md text-white hover:bg-red-700 focus:bg-red-800 active:bg-red-900 focus:outline-none hover:scale-105 active:scale-100 transition ease-in-out duration-150 whitespace-nowrap">
                                Delete Attribute
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex flex-row justify-center w-full border-b-2 gap-4 py-10">
                <x-text-input
                    class="w-full min-w-40 max-w-80"
                    id="new-attribute-name"
                    name="new-attribute-name"
                    placeholder="New Attribute" />
                <button onclick="createNewAttribute()" type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md text-white hover:bg-blue-700 focus:bg-blue-800 active:bg-blue-900 focus:outline-none hover:scale-105 active:scale-100 transition ease-in-out duration-150 whitespace-nowrap">
                    New Attribute
                </button>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:bg-blue-800 active:bg-blue-900 focus:outline-none hover:scale-105 active:scale-100 transition ease-in-out duration-150 whitespace-nowrap">
                    Confirm Changes
                </button>
                <a href="/management/categories" class="px-4 py-2 bg-slate-500 text-white rounded-lg hover:bg-slate-700 focus:bg-slate-800 active:bg-slate-900 focus:outline-none hover:scale-105 active:scale-100 transition ease-in-out duration-150 whitespace-nowrap">
                    Cancel
                </a>
            </div>
        </div>
    </form>

    <!-- Delete Attribute Confirmation Modal -->
    <div id="delete-attribute-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex justify-center items-center h-full">
            <div class="bg-white m-6 p-6 rounded-lg shadow-lg max-w-lg w-full">
                <h1 class="text-xl font-semibold text-gray-800">Delete Attribute</h1>
                <p>Are you sure you want to delete the attribute <span id="attribute-name-modal" class="font-bold">Attribute Name</span>?</p>
                <div class="mt-4 flex justify-end gap-2">
                    <button id="delete-attribute-button-modal" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Confirm</button>
                    <button type="button" onclick="closeDeleteAttributeModal()" class="px-4 py-2 bg-slate-500 text-white rounded-lg">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function closeDeleteAttributeModal() {
                document.getElementById('delete-attribute-modal-overlay').classList.add('hidden');
            }
            
            function openDeleteAttributeModal(attributeName) {
                const attributeNameSpan = document.getElementById('attribute-name-modal');
                const deleteAttributeBtn = document.getElementById('delete-attribute-button-modal');
                console.log(attributeName);

                attributeNameSpan.textContent = attributeName;
                // deleteAttributeBtn.removeEventListener('click', handleDeleteClick);
                // deleteAttributeBtn.addEventListener('click', handleDeleteClick);
                deleteAttributeBtn.onclick = function() {
                    deleteAttribute(attributeName);
                };

                function handleDeleteClick() {
                    deleteAttribute(attributeName);
                }

                document.getElementById('delete-attribute-modal-overlay').classList.remove('hidden');
            }

            function deleteAttribute(attributeName) {
                const attributeContainer = document.querySelector(`.attribute-container[attribute-name="${attributeName}"]`);
                attributeContainer.remove();
                closeDeleteAttributeModal();
            }

            function deleteAttributeOption(event) {
                const optionContainer = event.target.closest('div');
                optionContainer.remove();
            }

            function addNewOption(event, attributeName) {
                const optionsContainer = document.querySelector(`.attribute-options-container[attribute-name="${attributeName}"]`);
                const newOptionInput = document.querySelector(`.new_option[attribute-name="${attributeName}"]`);
                const newOption = newOptionInput.value.trim().toLowerCase();

                if(!newOption) {
                    showPopUp("Please enter a valid attribute option", 'error');
                    return false;
                }

                const options = Array.from(document.querySelectorAll(`input[name="attributes[${attributeName}][options][]"]`)).map(input => input.value);

                if (options.includes(newOption)) {
                    showPopUp("This option already exists", 'error');
                    return false;
                }

                const optionContainer = `
                    <div class="flex flex-row gap-3 items-center rounded-2xl bg-white px-6 py-2 shadow-sm">
                        <p>${newOption}</p>
                        <button onclick="deleteAttributeOption(event)" type="button" class="w-4 h-5 fill-current text-black hover:text-blue-700 hover:scale-110 active:scale-100">
                            <svg viewBox="0 0 384 512">
                                <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                            </svg>
                        </button>
                        <input type="hidden" name="attributes[${attributeName}][options][]" value="${newOption}">
                    </div>`;

                optionsContainer.insertAdjacentHTML('beforeend', optionContainer);
                newOptionInput.value = '';
            }

            function updateAttributeOptionsContainer(attributeName) {
                const optionsContainer = document.querySelector(`.attribute-options-container[attribute-name="${attributeName}"]`).parentNode.parentNode;
                const attributeTypeInput = document.getElementById(`attribute-type-${attributeName}`);
                const attributeType = attributeTypeInput.value;

                if (attributeType == 'enum') {
                    optionsContainer.classList.remove('hidden');
                }
                else {
                    optionsContainer.classList.add('hidden');
                }
            }

            async function createNewAttribute() {
                const attributesContainer = document.getElementById("attributes-container");
                const newAttibuteNameInput = document.getElementById("new-attribute-name");
                const attributeName = newAttibuteNameInput.value.trim().toLowerCase().replace(/\s+/g, '_');

                if(!attributeName) {
                    showPopUp("Please enter a valid attribute name", 'error');
                    return false;
                }

                const attributes = Array.from(document.querySelectorAll('.attribute-container')).map(attributeContainer => attributeContainer.getAttribute('attribute-name'));

                if (attributes.includes(attributeName)) {
                    showPopUp("This attribute already exists", 'error');
                    return false;
                }

                await fetch(`/partials/new-attribute?attributeName=${encodeURIComponent(attributeName)}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    }
                })
                .then(response => response.text())
                .then(html => {
                    attributesContainer.insertAdjacentHTML('beforeend', html);

                    const attributeTypeInput = document.getElementById(`attribute-type-${attributeName}`);

                    const observer = new MutationObserver(function () {
                        updateAttributeOptionsContainer(attributeName);
                    });

                    observer.observe(attributeTypeInput, {attributes: true, attributeFilter: ['value'] });

                    const id = `attribute-type-${attributeName}`
                    const defaultValue = 'string';
                    const selectedOptionElement = document.getElementById(`${id}-selected-option`);
                    const optionsList = document.getElementById(`${id}-options-list`);
                    const selectedValueInput = document.getElementById(`${id}`);

                    // Set the default option text
                    if (defaultValue !== "" && defaultValue !== null) {
                        const defaultOption = document.querySelector(`#${id}-options-list .option[data-value="${defaultValue}"]`);
                        if (defaultOption) {
                            selectedOptionElement.textContent = defaultOption.textContent;
                            defaultOption.classList.add('bg-gray-100');
                        }
                    }

                    // Toggle dropdown visibility when clicked
                    document.getElementById(`${id}-select`).addEventListener('click', function() {
                        optionsList.classList.toggle('hidden');
                    });

                    // Update selected option when clicked
                    document.querySelectorAll(`#${id}-options-list .option`).forEach(function(option) {
                        option.addEventListener('click', function() {
                            selectedOptionElement.textContent = option.textContent;
                            selectedValueInput.value = option.getAttribute('data-value');  // Update hidden input value

                            // Highlight selected option
                            document.querySelectorAll(`#${id}-options-list .option`).forEach(function(opt) {
                                opt.classList.remove('bg-gray-100');
                            });
                            option.classList.add('bg-gray-100');

                            optionsList.classList.add('hidden');
                        });
                    });

                    // Close dropdown if clicked outside
                    document.addEventListener('click', function(event) {
                        if (!document.getElementById(`${id}-select`).contains(event.target)) {
                            optionsList.classList.add('hidden');
                        }
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const attributesTypeInputs = document.querySelectorAll('input[id^="attribute-type-"]');

                attributesTypeInputs.forEach(attributeTypeInput => {
                    const attributeName = attributeTypeInput.id.replace('attribute-type-', '');

                    const observer = new MutationObserver(function () {
                        updateAttributeOptionsContainer(attributeName);
                    });

                    observer.observe(attributeTypeInput, {attributes: true, attributeFilter: ['value'] });
                });
            });
        </script>
    @endpush
</x-app-layout>