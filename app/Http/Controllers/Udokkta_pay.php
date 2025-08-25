<?php

namespace App\Http-Controllers\Gateways;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
// ... add your Order/Transaction models

class UddoktaController extends Controller
{
    public function initiatePayment(Request $request)
    {
        // 1. Get order details from your session or database
        $order = /* ... Get your order object ... */;
        $amount = $order->total_price;
        $orderId = $order->id;

        // 2. Get credentials from your database settings
        $apiKey = env('UDDOKTA_API_KEY'); // Best practice: store in .env and access via config
        $apiUrl = env('UDDOKTA_API_URL'); // e.g., 'https://pay.uddoktahost.com/api/create-payment'

        // 3. Prepare the data payload for Uddokta Pay
        $data = [
            'full_name'    => auth()->user()->name,
            'email'        => auth()->user()->email,
            'amount'       => $amount,
            'metadata'     => [
                'order_id' => $orderId,
            ],
            'redirect_url' => route('uddokta.ipn'), // The page user returns to
            'cancel_url'   => route('payment.failed'), // A route for failed payments
            'webhook_url'  => route('uddokta.ipn'), // Uddokta will send payment status here
        ];

        // 4. Send the request to Uddokta Pay
        $response = Http::withHeaders([
            'RT-UDDOKTAPAY-API-KEY' => $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($apiUrl, $data);

        // 5. Redirect user to the payment URL
        if ($response->successful() && isset($response->json()['payment_url'])) {
            // IMPORTANT: Save the transaction details with a 'pending' status before redirecting
            // ... your code to save transaction ...

            return redirect()->away($response->json()['payment_url']);
        }

        // Handle failure
        return redirect()->route('payment.failed')->with('error', 'Payment gateway is not available.');
    }

    public function handleIpn(Request $request)
    {
        // 1. Validate the IPN request (check Uddokta Pay docs for signature verification)
        // ... validation logic ...

        // 2. Get data from the IPN request
        $invoiceId = $request->input('invoice_id');
        $transactionId = $request->input('transaction_id');
        $status = $request->input('status'); // e.g., 'COMPLETED'
        $orderId = $request->input('metadata.order_id');

        // 3. Find the order in your database
        $order = Order::find($orderId);

        if ($order && $status === 'COMPLETED') {
            // 4. Update order status to 'paid' or 'completed'
            $order->payment_status = 'paid';
            $order->transaction_id = $transactionId;
            $order->save();

            // 5. Grant user access to the digital product
            // ... your logic to fulfill the order ...

            return response()->json(['message' => 'IPN Received'], 200);
        }

        return response()->json(['message' => 'IPN Failed'], 400);
    }
}