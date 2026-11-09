<x-mail::message>
# Thank you, {{ $order->customer_name }}

We received order **{{ $order->order_number }}**. This email is your confirmation.

<x-mail::table>
| Item | Qty | Line total |
|:-----|:---:|-----------:|
@foreach ($order->items as $item)
| {{ $item->product_name }}{{ $item->variant ? ' ('.$item->variant.')' : '' }} | {{ $item->quantity }} | {{ $order->money($item->line_total) }} |
@endforeach
</x-mail::table>

**Order total:** {{ $order->money($order->total) }}
**Payment method:** {{ $paymentMethodLabel }}

@if ($isLipa)
## Pay with Vodacom Lipa (M-Pesa)

Send **{{ $order->money($order->total) }}** to Lipa number **{{ $lipaNumber }}**@if ($lipaBusinessName !== '') ({{ $lipaBusinessName }})@endif.

@if ($lipaInstructions !== '')
{{ $lipaInstructions }}
@endif

@if ($order->payment_reference)
We received your transaction ID **{{ $order->payment_reference }}** and are checking it. You will get another email as soon as the payment is confirmed.
@else
After paying, open your order and submit the transaction ID from the M-Pesa SMS so we can verify it.
@endif
@else
## Cash on delivery

Pay **{{ $order->money($order->total) }}** to the courier when your order arrives.
@endif

<x-mail::button :url="$orderUrl">
View your order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
