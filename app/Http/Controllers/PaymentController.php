<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function handlePayHereIPN(Request $request)
    {
        // Get the Payhere Merchant ID and Secret from environment variables
        $merchantId = env('PAYHERE_MERCHANT_ID'); // For backend use, should not be exposed
        $secretKey = env('PAYHERE_SECRET'); // Your backend secret key for verification

        // Retrieve order ID and status code from Payhere IPN
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $paymentSignature = $request->input('signature'); // Signature from Payhere (optional)

        // Optional: Verify the payment signature (if Payhere provides a way to validate the payment)
        $expectedSignature = $this->generateSignature($orderId, $statusCode, $secretKey);

        // Compare the signature (this is just an example, refer to Payhere documentation for actual signature validation)
        if ($paymentSignature !== $expectedSignature) {
            return response()->json(['status' => 'failed', 'message' => 'Invalid signature'], 400);
        }

        if ($statusCode == 2) { // Status code 2 means payment completed
            // Update order status in the database to 'completed'
            // You can retrieve and process the order ID here

            // Example: Update order in database
            // Order::where('id', $orderId)->update(['status' => 'completed']);

            return response()->json(['status' => 'success']);
        } else {
            // Handle payment failure or other status codes
            return response()->json(['status' => 'failed', 'message' => 'Payment not completed'], 400);
        }
    }

    public function handlePayHereProxy(Request $request)
{
    // Log::info('Received PayHere request', $request->all());

    // Environment variables for merchant details
    $merchantId = env('PAYHERE_MERCHANT_ID');
    $merchantSecret = env('PAYHERE_MERCHANT_SECRET');
    $orderId = $request->order_id;
    $amount = number_format($request->amount, 2, '.', ''); // Ensure two decimal places
    $currency = 'LKR';

    // Generate the hash
    $hashedSecret = strtoupper(md5($merchantSecret));
    $hash = strtoupper(md5($merchantId . $orderId . $amount . $currency . $hashedSecret));

    // Send request to PayHere
    $response = Http::post('https://sandbox.payhere.lk/pay/checkout', [
        'merchant_id' => $merchantId,
        'return_url' => $request->return_url,
        'cancel_url' => $request->cancel_url,
        'notify_url' => $request->notify_url,
        'order_id' => $orderId,
        'items' => json_encode($request->items),
        'amount' => $amount,
        'currency' => $currency,
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
        'phone' => $request->phone,
        'address' => $request->address,
        'sandbox' => true,
        'hash' => $hash,  // Add the generated hash here
    ]);

    return response($response->json(), $response->status());
}

    // Function to generate signature (This is a placeholder; replace with the actual logic from Payhere documentation)
    private function generateSignature($orderId, $statusCode, $secretKey)
    {
        // Example signature generation (this will depend on Payhere's documentation)
        return hash('sha256', $orderId . $statusCode . $secretKey);
    }

    public function createCheckoutSession(Request $request)
    {
        Stripe::setApiKey('sk_test_51PpttpP1asGJ6sjDfIh9J85hBhY48LZYzbi9CIjjNjOTaEaOL1TqoZvl22JGAWvzKrYZbiAUHvXYq18DDZGP0Vg300tsGldgkA');

        try {
            $amount = $request->amount * 100; // Amount in cents
            $currency = 'LKR'; // Or use a different currency code

            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'metadata' => [
                    'user_id' => $request->user_id,
                    'order_id' => $request->order_id,
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}