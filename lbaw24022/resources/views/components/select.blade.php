@props([
    'options' => [],
    'defaultValue' => null,
    'id' => 'costum',
    'containerClass' => 'w-64',
    'selectedClass' => '',
    'nullOption' => true,
    'name' => $id,
])

@php
    $selectedClass = 'border border-gray-300 bg-white rounded-md py-2 px-3 ' . $selectedClass;
@endphp

<div class="relative inline-block {{ $containerClass }}">
    <div id="{{ $id }}-select" class="cursor-pointer flex justify-between items-center {{ $selectedClass }}">
        <p id="{{ $id }}-selected-option" class="text-gray-700 truncate">
            @if ($defaultValue)
                {{ $options[$defaultValue] ?? 'Select an option' }}
            @else
                Select an option
            @endif
        </p>
        <svg class="w-5 h-5 text-gray-500 transform pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>

    <ul id="{{ $id }}-options-list" class="list-none absolute left-0 right-0 mt-1 bg-white border border-gray-300 rounded-md shadow-lg z-10 hidden">
        @foreach ($options as $value => $label)
            @if (($nullOption ) || !($value == -1))
                <li class="option cursor-pointer py-2 px-3 hover:bg-gray-100 text-gray-700" data-value="{{ $value }}" data-option="{{ $label }}">
                    {{ $label }}
                </li>
            @endif
        @endforeach
    </ul>
</div>

<input type="hidden" id="{{ $id }}" name="{{ $name }}" value="{{ $defaultValue }}">

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const id = {!! json_encode($id) !!};
            const defaultValue = {!! json_encode($defaultValue) !!};
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
    </script>
@endpush
