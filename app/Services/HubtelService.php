<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HubtelService
{
    protected $clientId;
    protected $clientSecret;
    protected $currencyCode;
    protected $merchantAccountNumber;

    public function __construct()
    {
        $this->clientId = config('services.hubtel.client_id');
        $this->clientSecret = config('services.hubtel.client_secret');
        $this->currencyCode = config('services.hubtel.currency_code');
        $this->merchantAccountNumber = config('services.hubtel.merchant_account_number');
    }

    public function initiatePayment($amount, $description, $callbackUrl, $returnUrl, $cancellationUrl, $clientReference, $payeeName = null, $payeeMobileNumber = null, $payeeEmail = null)
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->post('https://payproxyapi.hubtel.com/items/initiate', [
                'totalAmount' => $amount,
                'description' => $description,
                'callbackUrl' => $callbackUrl,
                'returnUrl' => $returnUrl,
                'cancellationUrl' => $cancellationUrl,
                'merchantAccountNumber' => $this->merchantAccountNumber,
                'clientReference' => $clientReference,
                'payeeName' => $payeeName,
                'payeeMobileNumber' => $payeeMobileNumber,
                'payeeEmail' => $payeeEmail,
            ]);

        return $response->json();
    }

    public function verifyPayment($clientReference)
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->get("https://api-txnstatus.hubtel.com/transactions/{$this->merchantAccountNumber}/status", [
                'clientReference' => $clientReference,
            ]);

        return $response->json();
    }
}
