@props([
    'type' => 'text',
    'defaultValue' => null,
    'id' => 'costum',
    'name' => $id,
    'placeholder' => 'Enter text',
    'class' => '',
    'limitInt' => null,
])

@php
    $class = 'w-64 border border-gray-300 bg-white rounded-md py-2 px-3 ' . $class
@endphp


@if ($type == 'price')
    <input type="text" 
        id="{{ $id }}" 
        name="{{ $name }}" 
        value="{{ $defaultValue }}"
        placeholder="{{ $placeholder }}"
        oninput="validatePriceInput(this)"
        class="cursor-pointer flex justify-between items-center {{ $class }}">
@elseif ($type == 'float')
    <input type="text" 
        id="{{ $id }}" 
        name="{{ $name }}" 
        value="{{ $defaultValue }}"
        placeholder="{{ $placeholder }}"
        oninput="validateFloatInput(this)"
        class="cursor-pointer flex justify-between items-center {{ $class }}">
@elseif ($type == 'int')
    <input type="text" 
        id="{{ $id }}" 
        name="{{ $name }}" 
        value="{{ $defaultValue }}"
        placeholder="{{ $placeholder }}"
        oninput="validateIntInput(this, {{$limitInt}})"
        class="cursor-pointer flex justify-between items-center {{ $class }}">
@else
    <input type="text" 
        id="{{ $id }}" 
        name="{{ $name }}" 
        value="{{ $defaultValue }}"
        placeholder="{{ $placeholder }}"
        class="cursor-pointer flex justify-between items-center {{ $class }}">
@endif

@pushOnce('scripts')
    <script>
        function validatePriceInput(inputElement) {
            var inputValue = inputElement.value;

            // Remove any characters that are not digits or a decimal point
            inputValue = inputValue.replace(/[^\d.]/g, "");

            // Ensure there is only one decimal point
            var dotIndex = inputValue.indexOf(".");
            if (dotIndex !== -1)
                inputValue =
                    inputValue.substr(0, dotIndex + 1) +
                    inputValue.substr(dotIndex + 1).replace(/\./g, "");

            // Remove leading zeros except for "0." case
            inputValue = inputValue.replace(/^0+(?=\d)/, "");

            // Allow only two decimal places
            var decimalRegex = /^\d*\.?\d{0,2}$/;
            if (!decimalRegex.test(inputValue)) inputValue = "0";

            inputElement.value = inputValue;
        }

        function validateFloatInput(inputElement) {
            var inputValue = inputElement.value;

            // Remove any characters that are not digits or a decimal point
            inputValue = inputValue.replace(/[^\d.-]/g, "");

            // Ensure there is only one decimal point
            var dotIndex = inputValue.indexOf(".");
            if (dotIndex !== -1)
                inputValue =
                    inputValue.substr(0, dotIndex + 1) +
                    inputValue.substr(dotIndex + 1).replace(/\./g, "");

            // Remove leading zeros except for "0." case
            inputValue = inputValue.replace(/^(-)?0+(?=\d)/, "$1");

            var decimalRegex = /^-?\d*\.?\d*$/;
            if (!decimalRegex.test(inputValue)) inputValue = "0";

            inputElement.value = inputValue;
        }

        function validateIntInput(inputElement, max) {
            var inputValue = inputElement.value;

            // Remove any characters that are not digits
            inputValue = inputValue.replace(/[^\d-]/g, "");

            // Remove leading zeros
            inputValue = inputValue.replace(/^(-)?0+(?=\d)/, "$1");

            var integerRegex = /^-?\d*$/;
            if (!integerRegex.test(inputValue)) inputValue = "0";

            if (max !== null && inputValue.length > max) {
                inputValue = inputValue.slice(0, max);
            }

            inputElement.value = inputValue;
        }
    </script>
@endPushOnce
