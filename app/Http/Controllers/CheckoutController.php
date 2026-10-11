<?php

namespace App\Http\Controllers;

use App\Mail\PurchaseReceiptMail;
use App\Models\Order;
use App\Models\Product;
use App\Services\PayPalCheckoutService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use LogicException;
use Throwable;

class CheckoutController extends Controller
{
    public function store(Request $request, Product $product, PayPalCheckoutService $paypal): RedirectResponse
    {
        abort_unless($product->is_published && $product->published_at, 404);

        $order = $request->user()->orders()->create([
            'number' => 'SW-'.Str::upper(Str::random(12)),
            'status' => 'pending',
            'total' => $product->price,
            'currency' => $product->currency,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_title' => $product->title,
            'unit_price' => $product->price,
            'currency' => $product->currency,
        ]);

        if ((float) $product->price === 0.0) {
            $order->update(['status' => 'paid', 'paid_at' => now()]);
            $this->sendReceipt($order);

            return to_route('customer.dashboard')->with('status', 'Your free product has been added to your library.');
        }

        try {
            $paypalOrder = $paypal->createOrder($order, route('checkout.paypal.return'), route('checkout.paypal.cancel'));
        } catch (LogicException|RequestException) {
            $order->update(['status' => 'payment_failed']);

            return back()->withErrors(['checkout' => 'Payment could not be started. Please try again shortly.']);
        }

        $approvalUrl = collect($paypalOrder['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

        if (($paypalOrder['id'] ?? null) === null || $approvalUrl === null) {
            $order->update(['status' => 'payment_failed']);

            return back()->withErrors(['checkout' => 'Payment could not be started. Please try again shortly.']);
        }

        $order->update(['paypal_order_id' => $paypalOrder['id']]);

        return redirect()->away($approvalUrl);
    }

    public function paypalReturn(Request $request, PayPalCheckoutService $paypal): RedirectResponse
    {
        $paypalOrderId = $request->string('token')->toString();
        $order = $request->user()->orders()->where('paypal_order_id', $paypalOrderId)->where('status', 'pending')->firstOrFail();

        try {
            $capturedOrder = $paypal->captureOrder($paypalOrderId);
        } catch (LogicException|RequestException) {
            return to_route('customer.dashboard')->withErrors(['checkout' => 'We could not confirm your PayPal payment yet. Please contact support if you were charged.']);
        }

        $captureId = data_get($capturedOrder, 'purchase_units.0.payments.captures.0.id');

        if (($capturedOrder['id'] ?? null) !== $paypalOrderId || ($capturedOrder['status'] ?? null) !== 'COMPLETED' || blank($captureId)) {
            return to_route('customer.dashboard')->withErrors(['checkout' => 'Your PayPal payment is not complete yet. Please contact support if you need help.']);
        }

        $order->update(['status' => 'paid', 'paypal_capture_id' => $captureId, 'paid_at' => now()]);
        $this->sendReceipt($order);

        return to_route('customer.dashboard')->with('status', 'Payment complete. Your download is ready in your library.');
    }

    public function paypalCancel(): RedirectResponse
    {
        return to_route('customer.dashboard')->with('status', 'PayPal checkout was cancelled. No payment was taken.');
    }

    private function sendReceipt(Order $order): void
    {
        try {
            Mail::to($order->user)->send(new PurchaseReceiptMail($order->load('items')));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
