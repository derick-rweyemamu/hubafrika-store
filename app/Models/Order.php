<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class Order extends Model
{
    public const STATUSES = [
        'pending',
        'processing',
        'shipped',
        'delivered',
        'cancelled',
    ];

    public const PAYMENT_STATUSES = [
        'pending',
        'awaiting_verification',
        'paid',
        'failed',
        'refunded',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Whether the order can still take a payment.
     *
     * A refund gives the money back, so the order is closed for good: taking
     * another payment would silently reverse the refund in the ledger.
     */
    public function canAcceptPayment(): bool
    {
        return ! in_array($this->payment_status, ['paid', 'refunded'], true);
    }

    /**
     * Explain why a closed order refuses further payment changes.
     */
    public function paymentLockedMessage(): ?string
    {
        return match ($this->payment_status) {
            'paid' => 'This order is already marked as paid.',
            'refunded' => 'This payment was refunded, so the order is closed. Create a new order to take a new payment.',
            default => null,
        };
    }

    /**
     * Format an amount in the order currency, omitting cents for zero-decimal currencies.
     */
    public function money(float|int|string $amount): string
    {
        $currency = strtoupper((string) $this->currency);
        $zeroDecimal = [
            'TZS', 'UGX', 'RWF', 'BIF', 'JPY', 'KRW', 'VND', 'CLP', 'ISK',
            'XOF', 'XAF', 'KMF', 'DJF', 'GNF', 'PYG', 'VUV', 'XPF',
        ];

        return $currency.' '.number_format((float) $amount, in_array($currency, $zeroDecimal, true) ? 0 : 2);
    }

    public function paymentMethodLabel(): string
    {
        return Payment::METHOD_LABELS[$this->payment_method]
            ?? ucfirst(str_replace('_', ' ', (string) $this->payment_method));
    }

    public function paymentStatusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', (string) $this->payment_status));
    }

    /**
     * Email the customer about this order.
     *
     * Delivery problems are logged instead of thrown so a broken mail
     * configuration can never roll back an order or a payment.
     */
    public function notifyCustomer(Mailable $mailable): void
    {
        if (! is_string($this->customer_email) || $this->customer_email === '') {
            return;
        }

        try {
            Mail::to($this->customer_email)->send($mailable);
        } catch (Throwable $exception) {
            Log::warning('Order notification could not be sent.', [
                'order_id' => $this->id,
                'mailable' => $mailable::class,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
