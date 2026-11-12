<?php

namespace App\Livewire\Admin;

use App\Mail\OrderShippedMail;
use App\Mail\PaymentVerifiedMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;

    public function boot(): void
    {
        abort_unless(auth()->user()?->can('orders.view'), 403);
    }

    public string $search = '';

    public string $status = 'all';

    public string $paymentStatus = 'all';

    /** @var array<int, string> */
    public array $paymentReferences = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentStatus(): void
    {
        $this->resetPage();
    }

    public function updateStatus(int $id, string $nextStatus): void
    {
        $this->authorizeAdmin();

        if (! in_array($nextStatus, Order::STATUSES, true)) {
            $this->addError("orders.{$id}", 'Choose a valid order status.');

            return;
        }

        /** @var array{failure: string|null, shipped: bool, restored: bool} $result */
        $result = DB::transaction(function () use ($id, $nextStatus): array {
            $order = Order::query()->lockForUpdate()->findOrFail($id);

            if ($nextStatus === $order->status) {
                return ['failure' => null, 'shipped' => false, 'restored' => false];
            }

            $allowedTransitions = [
                'pending' => ['processing', 'cancelled'],
                'processing' => ['shipped', 'cancelled'],
                'shipped' => ['delivered'],
                'delivered' => [],
                'cancelled' => [],
            ];

            if (! in_array($nextStatus, $allowedTransitions[$order->status] ?? [], true)) {
                return [
                    'failure' => 'This order cannot move from '.ucfirst($order->status).' to '.ucfirst($nextStatus).'.',
                    'shipped' => false,
                    'restored' => false,
                ];
            }

            if (
                $nextStatus === 'processing'
                && $order->payment_method === Payment::METHOD_VODACOM_LIPA
                && $order->payment_status !== 'paid'
                && Payment::lipaAccount()['requires_verification']
            ) {
                return [
                    'failure' => 'Verify the Vodacom Lipa payment before processing this order.',
                    'shipped' => false,
                    'restored' => false,
                ];
            }

            $inventory = [];

            if ($nextStatus === 'processing') {
                foreach ($order->items as $item) {
                    $product = Product::query()->lockForUpdate()->find($item->product_id);

                    if ($product === null) {
                        return [
                            'failure' => 'A product in this order is no longer available for processing.',
                            'shipped' => false,
                            'restored' => false,
                        ];
                    }

                    if ($item->product_stock_id !== null || $product->variant_product) {
                        $stock = $item->product_stock_id === null
                            ? null
                            : ProductStock::query()
                                ->where('product_id', $product->id)
                                ->lockForUpdate()
                                ->find($item->product_stock_id);

                        if ($stock === null || $stock->qty < $item->quantity) {
                            return [
                                'failure' => "Insufficient stock for {$item->product_name}.",
                                'shipped' => false,
                                'restored' => false,
                            ];
                        }

                        $inventory[] = ['product' => $product, 'stock' => $stock, 'quantity' => $item->quantity];

                        continue;
                    }

                    if ($product->current_stock < $item->quantity) {
                        return [
                            'failure' => "Insufficient stock for {$item->product_name}.",
                            'shipped' => false,
                            'restored' => false,
                        ];
                    }

                    $inventory[] = ['product' => $product, 'stock' => null, 'quantity' => $item->quantity];
                }

                foreach ($inventory as $inventoryItem) {
                    $inventoryItem['stock']?->decrement('qty', $inventoryItem['quantity']);
                    $inventoryItem['product']->increment('num_of_sale', $inventoryItem['quantity']);

                    if ($inventoryItem['stock'] === null) {
                        $inventoryItem['product']->decrement('current_stock', $inventoryItem['quantity']);
                    }

                    StockMovement::log(
                        $inventoryItem['product'],
                        $inventoryItem['stock'],
                        -$inventoryItem['quantity'],
                        StockMovement::REASON_ORDER,
                        'Order '.$order->order_number,
                    );
                }
            }

            // Stock leaves inventory when an order is processed, so cancelling a
            // processed or shipped order has to put it back.
            $restored = false;

            if ($nextStatus === 'cancelled' && in_array($order->status, ['processing', 'shipped'], true)) {
                foreach ($order->items as $item) {
                    $product = Product::query()->lockForUpdate()->find($item->product_id);

                    if ($product === null) {
                        continue;
                    }

                    $stock = $item->product_stock_id === null
                        ? null
                        : ProductStock::query()
                            ->where('product_id', $product->id)
                            ->lockForUpdate()
                            ->find($item->product_stock_id);

                    if ($stock !== null) {
                        $stock->increment('qty', $item->quantity);
                    } elseif (! $product->variant_product) {
                        $product->increment('current_stock', $item->quantity);
                    }

                    $product->decrement('num_of_sale', min($item->quantity, (int) $product->num_of_sale));

                    StockMovement::log(
                        $product,
                        $stock,
                        $item->quantity,
                        StockMovement::REASON_CANCELLATION,
                        'Order '.$order->order_number,
                    );

                    $restored = true;
                }
            }

            $order->status = $nextStatus;
            $order->save();

            return [
                'failure' => null,
                'shipped' => $nextStatus === 'shipped',
                'restored' => $restored,
            ];
        });

        if ($result['failure'] !== null) {
            session()->flash('error', $result['failure']);

            return;
        }

        if ($result['shipped']) {
            $order = Order::query()->find($id);

            if ($order !== null) {
                $order->notifyCustomer(new OrderShippedMail($order));
            }
        }

        session()->flash(
            'message',
            match (true) {
                $result['restored'] => 'Order cancelled and the stock was returned to inventory.',
                $nextStatus === 'cancelled' => 'Order cancelled. Stock was never deducted for this order.',
                default => 'Order status updated successfully.',
            },
        );
    }

    public function markPaymentReceived(int $id): void
    {
        $this->authorizeAdmin();
        $this->validate([
            "paymentReferences.{$id}" => ['nullable', 'string', 'max:100'],
        ]);

        $failure = DB::transaction(function () use ($id): ?string {
            $order = Order::query()->lockForUpdate()->findOrFail($id);
            $locked = $order->paymentLockedMessage();

            if ($locked !== null) {
                return $locked;
            }

            $order->payment_status = 'paid';
            $order->payment_reference = $this->paymentReferences[$id] ?? $order->payment_reference;
            $order->save();

            $order->payments()->create([
                'method' => $order->payment_method,
                'amount' => $order->total,
                'currency' => $order->currency,
                'reference' => $order->payment_reference,
                'status' => Payment::STATUS_VERIFIED,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            return null;
        });

        if ($failure !== null) {
            session()->flash('error', $failure);

            return;
        }

        session()->flash('message', 'Payment recorded successfully.');

        $order = Order::query()->find($id);

        if ($order !== null) {
            $order->notifyCustomer(new PaymentVerifiedMail($order));
        }
    }

    public function verifyPayment(int $id): void
    {
        $this->authorizeAdmin();

        $failure = DB::transaction(function () use ($id): ?string {
            $order = Order::query()->lockForUpdate()->findOrFail($id);
            $locked = $order->paymentLockedMessage();

            if ($locked !== null) {
                return $locked;
            }

            $payment = $order->payments()->latest('id')->lockForUpdate()->first();

            if ($payment === null) {
                return 'This order has no payment record to verify.';
            }

            if ($payment->reference === null || $payment->reference === '') {
                return 'The customer has not submitted a transaction ID yet.';
            }

            $payment->forceFill([
                'status' => Payment::STATUS_VERIFIED,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ])->save();

            $order->payment_status = 'paid';
            $order->payment_reference = $payment->reference;
            $order->save();

            return null;
        });

        session()->flash($failure === null ? 'message' : 'error', $failure ?? 'Payment verified.');

        if ($failure === null) {
            $order = Order::query()->find($id);

            if ($order !== null) {
                $order->notifyCustomer(new PaymentVerifiedMail($order));
            }
        }
    }

    public function rejectPayment(int $id): void
    {
        $this->authorizeAdmin();

        $failure = DB::transaction(function () use ($id): ?string {
            $order = Order::query()->lockForUpdate()->findOrFail($id);
            $locked = $order->paymentLockedMessage();

            if ($locked !== null) {
                return $locked;
            }

            $payment = $order->payments()->latest('id')->lockForUpdate()->first();

            if ($payment === null) {
                return 'This order has no payment record to reject.';
            }

            $payment->forceFill([
                'status' => Payment::STATUS_REJECTED,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ])->save();

            $order->payment_status = 'failed';
            $order->save();

            return null;
        });

        session()->flash(
            $failure === null ? 'message' : 'error',
            $failure ?? 'Payment rejected. Ask the customer for a valid transaction ID.',
        );
    }

    public function markPaymentFailed(int $id): void
    {
        $this->authorizeAdmin();

        $order = Order::query()->findOrFail($id);
        $locked = $order->paymentLockedMessage();

        if ($locked !== null) {
            session()->flash('error', $locked);

            return;
        }

        $order->payment_status = 'failed';
        $order->save();

        session()->flash('message', 'Payment marked as failed.');
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('orders.view'), 403);
        $search = mb_substr($this->search, 0, 100);

        return view('livewire.admin.orders', [
            'orders' => Order::query()
                ->with(['items', 'payments'])
                ->when($this->status !== 'all', fn (Builder $query) => $query->where('status', $this->status))
                ->when($this->paymentStatus !== 'all', fn (Builder $query) => $query->where('payment_status', $this->paymentStatus))
                ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                    $query->where('order_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('customer_email', 'like', '%'.$search.'%');
                }))
                ->latest()
                ->paginate(10),
        ])->layout('components.layouts.admin', ['title' => 'Orders']);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->can('orders.manage'), 403);
    }
}
