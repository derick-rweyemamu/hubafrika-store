<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const REASON_RESTOCK = 'restock';

    public const REASON_CORRECTION = 'correction';

    public const REASON_DAMAGED = 'damaged';

    public const REASON_RETURN = 'return';

    public const REASON_ORDER = 'order';

    public const REASON_CANCELLATION = 'cancellation';

    /**
     * Reasons an admin may pick when adjusting stock by hand.
     *
     * @var array<string, string>
     */
    public const MANUAL_REASONS = [
        self::REASON_RESTOCK => 'Restock (new delivery)',
        self::REASON_CORRECTION => 'Stock count correction',
        self::REASON_DAMAGED => 'Damaged or lost',
        self::REASON_RETURN => 'Customer return',
    ];

    /**
     * @var array<string, string>
     */
    public const REASON_LABELS = [
        self::REASON_RESTOCK => 'Restock',
        self::REASON_CORRECTION => 'Correction',
        self::REASON_DAMAGED => 'Damaged',
        self::REASON_RETURN => 'Return',
        self::REASON_ORDER => 'Order',
        self::REASON_CANCELLATION => 'Cancellation',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'integer',
        'balance' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productStock(): BelongsTo
    {
        return $this->belongsTo(ProductStock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a stock change against the current balance already stored on the
     * product or variant row.
     */
    public static function log(
        Product $product,
        ?ProductStock $productStock,
        int $quantity,
        string $reason,
        ?string $note = null,
        ?int $userId = null,
    ): self {
        return self::query()->create([
            'product_id' => $product->id,
            'product_stock_id' => $productStock?->id,
            'user_id' => $userId ?? auth()->id(),
            'quantity' => $quantity,
            'balance' => $productStock !== null ? (int) $productStock->qty : (int) $product->current_stock,
            'reason' => $reason,
            'note' => $note,
        ]);
    }

    public function reasonLabel(): string
    {
        return self::REASON_LABELS[$this->reason] ?? ucfirst(str_replace('_', ' ', (string) $this->reason));
    }
}
