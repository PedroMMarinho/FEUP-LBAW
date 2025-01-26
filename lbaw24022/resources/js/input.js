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

function validateIntInput(inputElement) {
    var inputValue = inputElement.value;

    // Remove any characters that are not digits
    inputValue = inputValue.replace(/[^\d-]/g, "");

    // Remove leading zeros
    inputValue = inputValue.replace(/^(-)?0+(?=\d)/, "$1");

    var integerRegex = /^-?\d*$/;
    if (!integerRegex.test(inputValue)) inputValue = "0";

    inputElement.value = inputValue;
}
