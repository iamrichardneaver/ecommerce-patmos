<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Session;
use App\Models\CombinedOrder;
use App\Models\Order;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;

class HubtelController extends Controller
{
    public function redirectToGateway(Request $request)
    {
        // Generate transaction reference
        $transactionRef = uniqid('hubtel_');
        $request->reference = $transactionRef;

        // Prepare the payload
        $payload = [
            'totalAmount' => $request->amount,
            'description' => 'Order Payment',
            'callbackUrl' => route('hubtel.callback'),
            'returnUrl' => route('payment.success'),
            'merchantAccountNumber' => env('HUBTEL_MERCHANT_ACCOUNT_NUMBER'),
            'cancellationUrl' => route('payment.failure'),
            'clientReference' => $transactionRef,
            'payeeName' => $request->user()->name,
            'payeeMobileNumber' => $request->user()->phone,
            'payeeEmail' => $request->user()->email,
        ];

        // Make the API request
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(env('HUBTEL_API_KEY')),
            'Content-Type' => 'application/json',
        ])->post('https://payproxyapi.hubtel.com/items/initiate', $payload);

        // Handle the response
        if ($response->successful()) {
            $data = $response->json()['data'];
            return redirect($data['checkoutUrl']);
        } else {
            return redirect()->route('payment.failure')->with('error', 'Payment initiation failed.');
        }
    }

    public function handleGatewayCallback(Request $request)
    {
        // Obtain Hubtel payment information
        $payment = $request->all();

        // Save transaction details in the database
        $payment_type = Session::get('payment_type');
        $paymentData = Session::get('payment_data');

        if ($payment_type == 'cart_payment') {
            $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
            $combined_order->payment_status = 'paid';
            $combined_order->save();
            foreach ($combined_order->orders as $order) {
                $order->payment_status = 'paid';
                $order->payment_details = json_encode($payment);
                $order->save();
            }
        } elseif ($payment_type == 'order_re_payment') {
            $order = Order::findOrFail($paymentData['order_id']);
            $order->payment_status = 'paid';
            $order->payment_details = json_encode($payment);
            $order->save();
        } elseif ($payment_type == 'wallet_payment') {
            $walletController = new WalletController;
            return $walletController->wallet_payment_done($paymentData, json_encode($payment));
        } elseif ($payment_type == 'customer_package_payment') {
            $customerPackageController = new CustomerPackageController;
            return $customerPackageController->purchase_payment_done($paymentData, json_encode($payment));
        } elseif ($payment_type == 'seller_package_payment') {
            $sellerPackageController = new SellerPackageController;
            return $sellerPackageController->purchase_payment_done($paymentData, json_encode($payment));
        }

        // Redirect to a success or failure page
        if ($payment['status'] == 'Success') {
            return redirect()->route('payment.success');
        } else {
            return redirect()->route('payment.failure');
        }
    }
}
