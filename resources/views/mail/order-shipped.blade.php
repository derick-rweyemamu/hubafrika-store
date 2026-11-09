<x-mail::message>
# Your order has shipped

Order **{{ $order->order_number }}** has left our warehouse.

**Delivering to:** {{ $deliveryAddress }}

<x-mail::table>
| Item | Qty |
|:-----|:---:|
@foreach ($order->items as $item)
| {{ $item->product_name }}{{ $item->variant ? ' ('.$item->variant.')' : '' }} | {{ $item->quantity }} |
@endforeach
</x-mail::table>

**Order total:** {{ $order->money($order->total) }}

<x-mail::button :url="$orderUrl">
View your order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
