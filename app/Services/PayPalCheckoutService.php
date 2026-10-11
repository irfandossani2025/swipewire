<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use LogicException;

class PayPalCheckoutService
{
    /** @return array<string, mixed> */
    public function createOrder(Order $order, string $returnUrl, string $cancelUrl): array
    {
        return $this->client()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $order->number,
                'description' => $order->items()->value('product_title'),
                'amount' => ['currency_code' => $order->currency, 'value' => number_format((float) $order->total, 2, '.', '')],
            ]],
            'application_context' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW',
            ],
        ])->throw()->json();
    }

    /** @return array<string, mixed> */
    public function captureOrder(string $paypalOrderId): array
    {
        return $this->client()->post("/v2/checkout/orders/{$paypalOrderId}/capture", [])->throw()->json();
    }

    private function client(): PendingRequest
    {
        $clientId = config('paypal.client_id');
        $clientSecret = config('paypal.client_secret');

        if (blank($clientId) || blank($clientSecret)) {
            throw new LogicException('PayPal credentials have not been configured.');
        }

        $token = Http::asForm()->withBasicAuth($clientId, $clientSecret)
            ->post(config('paypal.base_url').'/v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->throw()->json('access_token');

        return Http::baseUrl(config('paypal.base_url'))->acceptJson()->withToken($token)
            ->withHeaders(['PayPal-Request-Id' => (string) str()->uuid()]);
    }
}
