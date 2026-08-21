<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class PayPalService
{
    private string $baseUrl;
    private string $clientId;
    private string $secret;

    public function __construct()
    {
        // Determine the base URL based on environment
        $this->baseUrl = config('services.paypal.mode') === 'live' 
            ? 'https://api-m.paypal.com' 
            : 'https://api-m.sandbox.paypal.com';
            
        $this->clientId = config('services.paypal.client_id');
        $this->secret   = config('services.paypal.secret');
    }

    /**
     * Get OAuth2 Access Token from PayPal
     */
    private function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->clientId, $this->secret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials'
            ]);

        if ($response->failed()) {
            throw new Exception('PayPal authentication failed: ' . $response->body());
        }

        return $response->json('access_token');
    }
    
    /**
     * Create a PayPal Order supporting both PayPal and Visa card methods.
     */
    public function createOrder(float $amountInVnd, string $method = 'paypal'): array
    {
        // Note: PayPal doesn't support VND natively. Convert it to USD.
        // Assuming 1 USD = 25000 VND.
        $usdAmount = round($amountInVnd / 25000, 2);

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => (string) $usdAmount
                    ]
                ]
            ]
        ];

        // Specify payment source preferences if the method is strictly PayPal
        // For 'visa' (card), omitting the payment_source allows the Frontend PayPal JS SDK 
        // to handle the card fields rendering securely.
        if ($method === 'paypal') {
            $payload['payment_source'] = [
                'paypal' => [
                    'experience_context' => [
                        'payment_method_preference' => 'UNRESTRICTED',
                        'user_action' => 'PAY_NOW',
                    ]
                ]
            ];
        }

        $response = Http::withToken($this->getAccessToken())
            ->post("{$this->baseUrl}/v2/checkout/orders", $payload);

        if ($response->failed()) {
            throw new Exception('Failed to create PayPal order: ' . $response->body());
        }

        return $response->json();
    }
    public function captureOrder(string $orderId): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        if ($response->failed()) {
            throw new Exception('Failed to capture PayPal order: ' . $response->body());
        }

        return $response->json();
    }
}