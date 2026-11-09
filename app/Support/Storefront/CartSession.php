<?php

namespace App\Support\Storefront;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A cart belongs to the signed-in customer, or - while the shopper is still a
 * guest - to the browser session that collected it. The token lives in the
 * session, so an anonymous cart survives page changes and can be handed over
 * to the account the shopper creates at checkout.
 */
class CartSession
{
    public const SESSION_KEY = 'storefront.cart_token';

    /**
     * The cart rows that belong to the visitor of the current request.
     */
    public static function query(?User $customer = null): Builder
    {
        $customer ??= auth()->user();

        if ($customer instanceof User) {
            return Cart::query()->whereBelongsTo($customer, 'user');
        }

        return Cart::query()->where('temp_user_id', self::token(create: false) ?? '');
    }

    /**
     * Owner columns for a newly collected cart row.
     *
     * @return array{user_id: int|null, temp_user_id: string|null}
     */
    public static function attributes(?User $customer = null): array
    {
        $customer ??= auth()->user();

        if ($customer instanceof User) {
            return ['user_id' => $customer->id, 'temp_user_id' => null];
        }

        return ['user_id' => null, 'temp_user_id' => self::token()];
    }

    /**
     * The browser session token, created on first use.
     */
    public static function token(bool $create = true): ?string
    {
        $token = session(self::SESSION_KEY);

        if (is_string($token) && $token !== '') {
            return $token;
        }

        if (! $create) {
            return null;
        }

        $token = (string) Str::uuid();
        session([self::SESSION_KEY => $token]);

        return $token;
    }

    /**
     * Put a product in the cart of the current visitor, folding the quantity
     * into the row that already holds the same product and variant.
     *
     * @return array{quantity: int, error: string|null} units added, or why nothing was added
     */
    public static function add(Product $product, ?ProductStock $stock = null): array
    {
        $variation = $stock === null
            ? null
            : json_encode(['product_stock_id' => $stock->id], JSON_THROW_ON_ERROR);
        $availableStock = $stock === null ? (int) $product->current_stock : (int) $stock->qty;
        $quantityToAdd = max(1, (int) $product->min_qty);

        if ($availableStock < $quantityToAdd) {
            return ['quantity' => 0, 'error' => "{$product->name} does not have enough stock."];
        }

        return DB::transaction(function () use ($product, $stock, $variation, $quantityToAdd, $availableStock): array {
            $cartItem = self::query()
                ->where('product_id', $product->id)
                ->where(function (Builder $query) use ($variation): void {
                    if ($variation === null) {
                        $query->whereNull('variation')->orWhere('variation', '');

                        return;
                    }

                    $query->where('variation', $variation);
                })
                ->lockForUpdate()
                ->first();

            $quantity = $quantityToAdd + ($cartItem?->quantity ?? 0);

            if ($quantity > $availableStock) {
                return ['quantity' => 0, 'error' => "{$product->name} does not have enough stock for that quantity."];
            }

            $unitPrice = $stock !== null && $stock->price > 0
                ? $stock->price
                : $product->unit_price;
            $cartItem ??= new Cart;
            $cartItem->fill([
                ...self::attributes(),
                'product_id' => $product->id,
                'variation' => $variation,
                'quantity' => $quantity,
                'price' => $unitPrice,
            ]);
            $cartItem->save();

            return ['quantity' => $quantityToAdd, 'error' => null];
        });
    }

    /**
     * Move the guest cart onto a freshly authenticated account, folding lines
     * the account already holds into a single row per product and variant.
     */
    public static function claim(User $customer): void
    {
        $token = self::token(create: false);

        if ($token === null) {
            return;
        }

        DB::transaction(function () use ($customer, $token): void {
            $guestItems = Cart::query()
                ->where('temp_user_id', $token)
                ->lockForUpdate()
                ->get();

            foreach ($guestItems as $guestItem) {
                $ownedItem = Cart::query()
                    ->whereBelongsTo($customer, 'user')
                    ->where('product_id', $guestItem->product_id)
                    ->where('variation', $guestItem->variation)
                    ->lockForUpdate()
                    ->first();

                if ($ownedItem === null) {
                    $guestItem->fill([
                        'user_id' => $customer->id,
                        'temp_user_id' => null,
                    ])->save();

                    continue;
                }

                $ownedItem->quantity = (int) $ownedItem->quantity + (int) $guestItem->quantity;
                $ownedItem->save();
                $guestItem->delete();
            }
        });

        session()->forget(self::SESSION_KEY);
    }
}
