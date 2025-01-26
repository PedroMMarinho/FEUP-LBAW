

<div>
    <div id="paypal-button-container" class="paypal-button-container"></div>

    <div class="flex items-center">
        <div class="border-t border-gray-300 flex-grow"></div>
        <p class="mx-4 text-xs">OR</p>
        <div class="border-t border-gray-300 flex-grow"></div>
    </div>

    <div id="card-form" class="card_container">
        <div class="relative">
            <div id="loading-indicator" class="absolute !hidden flex flex-col justify-center items-center opacity-50 bg-black z-50 h-full w-full rounded-md">
                <div class="w-14 h-14 border-4 border-t-transparent border-blue-500 rounded-full animate-spin"></div>
                <p class="text-white">Processing payment...</p>
            </div>

            <div id="card-name-field-container"></div>
            <div id="card-number-field-container"></div>
            <div id="card-expiry-field-container"></div>
            <div id="card-cvv-field-container"></div>

        </div>

        <div class="flex justify-between px-2 align-middle">
            <p id="result-message" class="text-red-600 space-y-1"></p>
            <x-primary-button id="card-field-submit-button" type="button" >
                Pay with Card
            </x-primary-button>

        </div>
    </div>
</div>


@push('scripts')
<script>

window.onload = async function(){
// Render the button component
paypal
    .Buttons({
        // Sets up the transaction when a payment button is clicked
        createOrder: createOrderCallback,
        onApprove: onApproveCallback,
        onError: function (error) {
            // Do something with the error from the SDK
        },

        style: {
            shape: "rect",
            layout: "vertical",
            color: "gold",
            label: "paypal",
        } ,
    })
    .render("#paypal-button-container"); 

// Render each field after checking for eligibility
const cardField = window.paypal.CardFields({
    createOrder: createOrderCallback,
    onApprove: onApproveCallback,
    style: {
        input: {
            "font-size": "12px",
            "font-family": "courier, monospace",
            "font-weight": "lighter",
            color: "#fff",
        },
        ".invalid": { color: "purple" },
    },
    onError: (err) => {
        // redirect to your specific error page
        resultMessage("Invalid data");
    } ,
});

if (cardField.isEligible()) {
    const commonStyle = {
        input: {
            color: "#000000",
            borderRadius: "0.375rem", // rounded-md
            boxShadow: "0 1px 2px 0 rgba(0, 0, 0, 0.05)", // shadow-sm
            padding: "0.5rem 0.75rem", // Tailwind-like padding
            transition: "border-color 0.2s, box-shadow 0.2s",
            fontSize: "0.875rem", // text-sm
        },
        ":focus": {
            boxShadow: "0 0 0 1px rgba(99, 102, 241, 0.5)", // focus:ring-indigo-500
        },
        ".invalid": {
            color: "purple",
        },
    };


    const nameField = cardField.NameField({
        style: commonStyle,
    });
    nameField.render("#card-name-field-container");

    const numberField = cardField.NumberField({
        style: commonStyle,
    });
    numberField.render("#card-number-field-container");

    const cvvField = cardField.CVVField({
        style: commonStyle,
    });
    cvvField.render("#card-cvv-field-container");

    const expiryField = cardField.ExpiryField({
        style: commonStyle,
    });
    expiryField.render("#card-expiry-field-container");

    // Add click listener to submit button and call the submit function on the CardField component
    document
        .getElementById("card-field-submit-button")
        .addEventListener("click", () => {
            document.getElementById("loading-indicator").classList.remove("!hidden");
            document.getElementById("card-field-submit-button").disabled = true;

            cardField.submit({}).then(() => {
                // submit successful
                createOrderCallback();
                document.getElementById("loading-indicator").classList.add("!hidden");
                document.getElementById("card-field-submit-button").disabled = false;
            }).catch((error) => {
                resultMessage('Invalid input');

                document.getElementById("loading-indicator").classList.add("!hidden");
                document.getElementById("card-field-submit-button").disabled = false;
            });
        });
}

}



async function createOrderCallback() {
    resultMessage("");
    const depositAmount = document.getElementById("depositAmount").value;
    try {
        const response = await fetch("/api/orders", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                "Content-Type": "application/json",
            },
           
            body: JSON.stringify({
                amount: depositAmount,
            }),
        });

        const orderData = await response.json();
        document.querySelector('#depositAmount-error li').textContent = '';

        if (orderData.id) {
            return orderData.id;
        } else {
            if (orderData.inputError)
            {
                document.querySelector('#depositAmount-error li').textContent = orderData.depositAmount[0];

            } else 
            {
                const errorDetail = orderData?.details?.[0];
                const errorMessage = errorDetail
                    ? `${errorDetail.issue} ${errorDetail.description} (${orderData.debug_id})`
                    : JSON.stringify(orderData);

                throw new Error(errorMessage);
            }


        }
    } catch (error) {
        showPopUp("Error creating checkout", 'error');
    }
}

async function onApproveCallback(data, actions) {
    try {
        const response = await fetch(`/api/orders/${data.orderID}/capture`, {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                "Content-Type": "application/json",
            },
        });

        const orderData = await response.json();
        // Three cases to handle:
        //   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
        //   (2) Other non-recoverable errors -> Show a failure message
        //   (3) Successful transaction -> Show confirmation or thank you message
        const transaction =
            orderData?.purchase_units?.[0]?.payments?.captures?.[0] ||
            orderData?.purchase_units?.[0]?.payments?.authorizations?.[0];
        const errorDetail = orderData?.details?.[0];

        // this actions.restart() behavior only applies to the Buttons component
        if (
            errorDetail?.issue === "INSTRUMENT_DECLINED" &&
            !data.card &&
            actions
        ) {
            // (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
            // recoverable state, per https://developer.paypal.com/docs/checkout/standard/customize/handle-funding-failures/
            return actions.restart();
        } else if (
            errorDetail ||
            !transaction ||
            transaction.status === "DECLINED"
        ) {
            // (2) Other non-recoverable errors -> Show a failure message
            let errorMessage;
            if (transaction) {
                errorMessage = `Transaction ${transaction.status}: ${transaction.id}`;
            } else if (errorDetail) {
                errorMessage = `${errorDetail.description} (${orderData.debug_id})`;
            } else {
                errorMessage = JSON.stringify(orderData);
            }

            throw new Error(errorMessage);
        } else {
           
            showPopUp("Deposit succesfull", 'success');

        }
    } catch (error) {
        showPopUp("Deposit failed", 'error');
    }
}

// Example function to show a result to the user. Your site's UI library can be used instead.
function resultMessage(message) {
    const container = document.querySelector("#result-message");
    container.innerHTML = message;
}








</script>
@endpush