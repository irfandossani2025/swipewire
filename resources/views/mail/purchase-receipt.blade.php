<x-mail::message>
# Your order is complete

Thank you for your purchase. Order **{{ $order->number }}** has been added permanently to your Swipewire library.

@foreach($order->items as $item)
- {{ $item->product_title }} — {{ $item->currency }} {{ number_format((float) $item->unit_price, 2) }}
@endforeach

**Total:** {{ $order->currency }} {{ number_format((float) $order->total, 2) }}

<x-mail::button :url="route('customer.dashboard')">
Download my products
</x-mail::button>

Need assistance? Reply to this message or contact our support team.<br>
Swipewire Payments
</x-mail::message>
