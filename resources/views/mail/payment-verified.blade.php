<x-mail::message>
# Payment confirmed

We confirmed the payment for order **{{ $order->order_number }}**.

**Amount:** {{ $order->money($order->total) }}
**Payment method:** {{ $paymentMethodLabel }}
@if ($reference)
**Transaction ID:** {{ $reference }}
@endif

We are packing your order now and will email you again when it ships.

<x-mail::button :url="$orderUrl">
View your order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
