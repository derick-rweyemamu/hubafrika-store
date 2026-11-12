<?php

namespace App\Livewire\Storefront;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class OrderDetail extends Component
{
    public Order $order;

    public string $reference = '';

    public function mount(Order $order): void
    {
        abort_unless(auth()->id() === $order->user_id, 403);

        $this->order = $order->load(['items', 'payments']);
    }

    public function submitReference(): void
    {
        abort_unless(auth()->id() === $this->order->user_id, 403);

        $order = $this->order;

        if (
            $order->payment_method !== Payment::METHOD_VODACOM_LIPA
            || $order->payment_status !== 'awaiting_verification'
        ) {
            $this->addError('reference', 'This order is not waiting for a transaction ID.');

            return;
        }

        $this->validate([
            'reference' => [
                'required',
                'string',
                'max:100',
                Rule::unique('payments', 'reference')->ignore($order->payment_reference, 'reference'),
            ],
        ], [
            'reference.unique' => 'That transaction ID has already been submitted for another order.',
        ]);

        $reference = trim($this->reference);

        DB::transaction(function () use ($order, $reference): void {
            $order->payment_reference = $reference;
            $order->save();

            $payment = $order->payments()->latest('id')->first();

            if ($payment === null) {
                $order->payments()->create([
                    'method' => Payment::METHOD_VODACOM_LIPA,
                    'amount' => $order->total,
                    'currency' => $order->currency,
                    'reference' => $reference,
                    'payer_phone' => $order->customer_phone,
                    'status' => Payment::STATUS_AWAITING_VERIFICATION,
                    'submitted_at' => now(),
                ]);

                return;
            }

            $payment->forceFill([
                'reference' => $reference,
                'status' => Payment::STATUS_AWAITING_VERIFICATION,
                'submitted_at' => now(),
                'verified_at' => null,
                'verified_by' => null,
                'notes' => null,
            ])->save();
        });

        $this->order->refresh()->load(['items', 'payments']);
        $this->reference = '';

        session()->flash('message', 'Transaction ID submitted. We will confirm your payment before dispatch.');
    }

    public function render()
    {
        return view('livewire.storefront.order-detail', [
            'lipaAccount' => Payment::lipaAccount(),
            'timeline' => $this->timeline(),
        ])->layout('components.layouts.storefront', [
            'title' => 'Order '.$this->order->order_number,
            'navigationArea' => 'store',
            'pageHeader' => [
                'title' => 'Order '.$this->order->order_number,
                'parents' => [
                    ['label' => 'My orders', 'url' => route('orders.index')],
                ],
            ],
        ]);
    }

    /**
     * Fulfilment steps with their state for the timeline.
     *
     * @return array<int, array{label: string, description: string, state: string}>
     */
    private function timeline(): array
    {
        $order = $this->order;
        $position = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3][$order->status] ?? 0;

        if ($order->status === 'cancelled') {
            return [
                [
                    'label' => 'Order placed',
                    'description' => $order->created_at?->format('M j, Y g:i A') ?? '',
                    'state' => 'done',
                ],
                [
                    'label' => 'Order cancelled',
                    'description' => 'This order was cancelled. Contact us if you already paid.',
                    'state' => 'failed',
                ],
            ];
        }

        $paymentState = match (true) {
            $order->payment_status === 'paid' => 'done',
            $order->payment_status === 'failed' => 'failed',
            $order->payment_status === 'awaiting_verification' => 'current',
            default => 'current',
        };

        return [
            [
                'label' => 'Order placed',
                'description' => $order->created_at?->format('M j, Y g:i A') ?? '',
                'state' => 'done',
            ],
            [
                'label' => 'Payment',
                'description' => match ($order->payment_status) {
                    'paid' => 'Confirmed'.($order->payment_reference ? ' · '.$order->payment_reference : ''),
                    'awaiting_verification' => 'Waiting for us to verify your transaction ID',
                    'failed' => 'Could not be verified',
                    'refunded' => 'Refunded',
                    default => $order->payment_method === Payment::METHOD_CASH_ON_DELIVERY
                        ? 'Pay cash on delivery'
                        : 'Not paid yet',
                },
                'state' => $paymentState,
            ],
            [
                'label' => 'Processing',
                'description' => 'We pack and prepare your items',
                'state' => $position >= 1 ? 'done' : 'pending',
            ],
            [
                'label' => 'Shipped',
                'description' => 'Your parcel is on the way',
                'state' => $position >= 2 ? 'done' : ($position === 1 ? 'current' : 'pending'),
            ],
            [
                'label' => 'Delivered',
                'description' => 'Handed over to you',
                'state' => $position >= 3 ? 'done' : ($position === 2 ? 'current' : 'pending'),
            ],
        ];
    }
}
