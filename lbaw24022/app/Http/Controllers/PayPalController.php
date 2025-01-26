<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Log;
use App\Models\GeneralUser;
use Illuminate\Support\Facades\Gate;


use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\LiveEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersAuthorizeRequest;
use PayPalCheckoutSdk\Payments\AuthorizationsCaptureRequest;
use PayPalCheckoutSdk\Payments\PaymentsCreateRequest;

use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Models\Builders\MoneyBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\ShippingDetailsBuilder;
use PaypalServerSdkLib\Models\Builders\ShippingOptionBuilder;
use PaypalServerSdkLib\Models\Builders\PaymentSourceBuilder;
use PaypalServerSdkLib\Models\Builders\CardRequestBuilder;
use PaypalServerSdkLib\Models\Builders\CardAttributesBuilder;
use PaypalServerSdkLib\Models\Builders\CardVerificationBuilder;
use PaypalServerSdkLib\Models\ShippingType;


class PayPalController extends Controller
{
    protected $client;

    public function __construct()
    {
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.client_secret');
        $environment = config('services.paypal.sandbox') ? Environment::SANDBOX : Environment::PRODUCTION;
        
        $this->client = PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init(
                    $clientId,
                    $clientSecret
                )
            )
            ->environment($environment)
            ->build();
    }



    function handleResponse($response)
    {
        // Get the JSON body of the response
        $jsonResponse = json_decode($response->getBody(), true);
        
        // Check if the response has an error or not
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            // Success: return the parsed JSON response and status code
            return [
                "jsonResponse" => $jsonResponse,
                "httpStatusCode" => $response->getStatusCode(),
            ];
        } else {
            // Error: return the error message with the status code
            $errorDetail = isset($jsonResponse['details']) ? $jsonResponse['details'] : 'Unknown error';
            $errorMessage = isset($jsonResponse['message']) ? $jsonResponse['message'] : 'Something went wrong';

            // Throw an exception with a descriptive message
            throw new \Exception("PayPal API Error: {$errorMessage} - {$errorDetail}");
        }
    }


    public function createOrder(Request $request)
    {
        if (Gate::denies('transferMoney', GeneralUser::class)) {
            session()->flash('error', 'Invalid Operation');
            return response()->json(['error' => true, 'reply' => '']);
        }
        Log::info("Start");

        $amount = $request['amount'];

        if ($amount <= 0)
        {
            return response()->json([
                    'inputError' => true,
                    'depositAmount' => ['Amount has to be larger than 0'],
                ]);
        }
        if ($amount > 500000)
        {
            return response()->json([
                    'inputError' => true,
                    'depositAmount' => ['Amount has to be less than 500000'],
                ]);
        }

        try {
            // Get PayPal client
            $client = $this->client;

            // Set up order details like items, amount, etc.
            $orderBody = [
                'body' => OrderRequestBuilder::init("CAPTURE", [
                    PurchaseUnitRequestBuilder::init(
                        AmountWithBreakdownBuilder::init('EUR', $amount)->build()
                    )->build(),
                ])->build(),
            ];

            // Create the order through PayPal API
            $apiResponse = $client->getOrdersController()->ordersCreate($orderBody);

            // Handle response
            $response = $this->handleResponse($apiResponse);
            return response()->json($response['jsonResponse']);
        } catch (Exception $e) {
            // Handle errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function captureOrder($orderID)
    {
        Log::info("Started capture");

        try {
            // Capture the payment
            $captureBody = ['id' => $orderID];
            $apiResponse = $this->client->getOrdersController()->ordersCapture($captureBody);
            

            // Handle response
            $response = $this->handleResponse($apiResponse);
            Log::info("Handled response");


            // Call the function here
            $walletController = new WalletController();
            Log::info($response['jsonResponse']['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['gross_amount']['value']);
            $walletController->deposit((int) $response['jsonResponse']['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['gross_amount']['value']);

            return response()->json($response['jsonResponse']);
        } catch (Exception $e) {
            Log::info("Error catched");
            // Handle errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function authorizeOrder($orderID)
    {
        try {
            // Authorize the payment
            $authorizeBody = ['id' => $orderID];
            $apiResponse = $this->client->getOrdersController()->ordersAuthorize($authorizeBody);

            // Handle response
            $response = $this->handleResponse($apiResponse);
            return response()->json($response['jsonResponse']);
        } catch (Exception $e) {
            // Handle errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
