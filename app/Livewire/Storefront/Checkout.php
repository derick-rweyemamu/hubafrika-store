<?php

namespace App\Livewire\Storefront;

use App\Mail\OrderPlacedMail;
use App\Models\BusinessSetting;
use App\Models\Cart as CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Support\Storefront\CartSession;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Checkout extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $shippingAddress = '';

    public string $shippingCountry = '';

    public string $shippingState = '';

    public string $shippingCity = '';

    public string $shippingPostalCode = '';

    public string $shippingPhone = '';

    public string $paymentMethod = '';

    public string $paymentPayerPhone = '';

    public string $paymentReference = '';

    public function mount(): void
    {
        $customer = auth()->user();
        $defaultAddress = $customer?->addresses()->where('set_default', true)->first()
            ?? $customer?->addresses()->first();

        $this->shippingAddress = $defaultAddress?->address ?? $customer?->address ?? '';
        $this->shippingCountry = $customer?->country ?? '';
        $this->shippingState = $customer?->state ?? '';
        $this->shippingCity = $customer?->city ?? '';
        $this->shippingPostalCode = $defaultAddress?->postal_code ?? $customer?->postal_code ?? '';
        $this->shippingPhone = $defaultAddress?->phone ?? $customer?->phone ?? '';
        $this->paymentMethod = (string) (array_key_first(Payment::availableMethods()) ?? '');
        $this->paymentPayerPhone = $customer?->phone ?? '';

        // Signing in from the guest form should come back here with the cart.
        if (! $customer instanceof User) {
            session()->put('url.intended', route('checkout'));
        }
    }

    public function placeOrder(): void
    {
        $customer = auth()->user();

        if (! CartSession::query($customer)->exists()) {
            $this->addError('checkout', 'Your cart is empty.');

            return;
        }

        // A guest registers on this screen, so the account details are checked
        // together with the delivery details before anything is created.
        $account = [];

        if (! $customer instanceof User) {
            $account = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'string', 'confirmed', Password::defaults()],
            ]);
        }

        $paymentMethods = Payment::availableMethods();

        if ($paymentMethods === []) {
            $this->addError('checkout', 'No payment method is available right now.');

            return;
        }

        $validated = $this->validate([
            'shippingAddress' => ['required', 'string', 'max:1000'],
            'shippingCountry' => ['nullable', 'string', 'max:100'],
            'shippingState' => ['nullable', 'string', 'max:100'],
            'shippingCity' => ['nullable', 'string', 'max:100'],
            'shippingPostalCode' => ['nullable', 'string', 'max:30'],
            'shippingPhone' => ['nullable', 'string', 'max:50'],
            'paymentMethod' => ['required', Rule::in(array_keys($paymentMethods))],
            'paymentPayerPhone' => ['nullable', 'string', 'max:50'],
            'paymentReference' => ['nullable', 'string', 'max:100'],
        ]);

        $paymentMethod = $validated['paymentMethod'];
        $isLipa = $paymentMethod === Payment::METHOD_VODACOM_LIPA;
        $lipaAccount = Payment::lipaAccount();
        $payerPhone = null;

        if ($isLipa) {
            $payerPhone = (string) preg_replace('/\D+/', '', $validated['paymentPayerPhone']);

            if (preg_match('/\A(?:0\d{9}|255\d{9})\z/', $payerPhone) !== 1) {
                throw ValidationException::withMessages([
                    'paymentPayerPhone' => 'Enter the M-Pesa number that sent the payment, for example 0754000000.',
                ]);
            }

            $this->validate([
                'paymentReference' => ['required', 'string', 'max:100', Rule::unique('payments', 'reference')],
            ], [
                'paymentReference.unique' => 'That transaction ID has already been submitted for another order.',
            ]);
        }

        // Everything validated: create the account now so the guest cart can be
        // handed over before the order is written.
        if (! $customer instanceof User) {
            $customer = $this->createCustomer($account);
        }

        $order = DB::transaction(function () use ($customer, $validated, $paymentMethod, $isLipa, $payerPhone, $lipaAccount): Order {
            $cartItems = CartSession::query($customer)
                ->with('product')
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'checkout' => 'Your cart is empty.',
                ]);
            }

            if ($customer->email === null || $customer->name === '') {
                throw ValidationException::withMessages([
                    'checkout' => 'Add your name and email to your profile before placing an order.',
                ]);
            }

            $currency = BusinessSetting::query()->where('type', 'currency_code')->value('value');

            if (! is_string($currency) || preg_match('/\A[A-Z]{3}\z/', $currency) !== 1) {
                throw ValidationException::withMessages([
                    'checkout' => 'The store currency is not configured correctly.',
                ]);
            }

            $preparedItems = [];
            $subtotal = 0.0;
            $discountTotal = 0.0;
            $taxTotal = 0.0;

            foreach ($cartItems as $cartItem) {
                $product = Product::query()->whereKey($cartItem->product_id)->lockForUpdate()->first();

                if ($product === null || ! $product->published || ! $product->approved) {
                    throw ValidationException::withMessages([
                        'checkout' => 'A product in your cart is no longer available.',
                    ]);
                }

                [$productStock, $variation] = $this->selectedStock($cartItem, $product);
                $availableStock = $productStock?->qty ?? $product->current_stock;

                if ($availableStock < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'checkout' => "There is not enough stock for {$product->name}.",
                    ]);
                }

                if ($cartItem->quantity < max(1, (int) $product->min_qty)) {
                    throw ValidationException::withMessages([
                        'checkout' => "{$product->name} requires a minimum quantity of {$product->min_qty}.",
                    ]);
                }

                $pricing = $this->linePricing($product, $productStock, (int) $cartItem->quantity);

                $preparedItems[] = [
                    'product' => $product,
                    'productStock' => $productStock,
                    'variation' => $variation,
                    'quantity' => (int) $cartItem->quantity,
                    ...$pricing,
                ];
                $subtotal += $pricing['subtotal'];
                $discountTotal += $pricing['discountAmount'];
                $taxTotal += $pricing['taxAmount'];
            }

            $freeShipping = BusinessSetting::query()->where('type', 'free_shipping')->value('value') === '1';
            $shippingTotal = $freeShipping
                ? 0
                : max(0, (float) (BusinessSetting::query()
                    ->where('type', 'flat_rate_shipping_cost')
                    ->value('value') ?? 0));
            $subtotal = round($subtotal, 2);
            $discountTotal = round($discountTotal, 2);
            $taxTotal = round($taxTotal, 2);
            $shippingTotal = round($shippingTotal, 2);

            $order = $customer->orders()->create([
                'order_number' => 'ORD-'.Str::upper((string) Str::ulid()),
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $validated['shippingPhone'] ?: null,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'payment_status' => $isLipa && $lipaAccount['requires_verification'] ? 'awaiting_verification' : 'pending',
                'payment_reference' => $isLipa ? $validated['paymentReference'] : null,
                'currency' => $currency,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'shipping_total' => $shippingTotal,
                'total' => round($subtotal - $discountTotal + $taxTotal + $shippingTotal, 2),
                'shipping_address' => $validated['shippingAddress'],
                'shipping_country' => $validated['shippingCountry'] ?: null,
                'shipping_state' => $validated['shippingState'] ?: null,
                'shipping_city' => $validated['shippingCity'] ?: null,
                'shipping_postal_code' => $validated['shippingPostalCode'] ?: null,
            ]);

            foreach ($preparedItems as $preparedItem) {
                $product = $preparedItem['product'];
                $productStock = $preparedItem['productStock'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_stock_id' => $productStock?->id,
                    'product_name' => $product->name,
                    'sku' => $productStock?->sku ?? $product->barcode,
                    'variant' => $preparedItem['variation'],
                    'thumbnail' => $product->thumbnail_img,
                    'quantity' => $preparedItem['quantity'],
                    'unit_price' => $preparedItem['unitPrice'],
                    'discount_amount' => $preparedItem['discountAmount'],
                    'tax_amount' => $preparedItem['taxAmount'],
                    'line_total' => $preparedItem['lineTotal'],
                ]);
            }

            CartItem::query()->whereBelongsTo($customer, 'user')->delete();

            $order->payments()->create([
                'method' => $paymentMethod,
                'amount' => $order->total,
                'currency' => $currency,
                'reference' => $isLipa ? $validated['paymentReference'] : null,
                'payer_phone' => $payerPhone,
                'status' => $isLipa ? Payment::STATUS_AWAITING_VERIFICATION : Payment::STATUS_PENDING,
                'submitted_at' => $isLipa ? now() : null,
            ]);

            return $order;
        });

        session()->flash('message', $isLipa
            ? sprintf(
                'Order %s received. Pay %s to Lipa number %s, then wait for our confirmation.',
                $order->order_number,
                $order->money($order->total),
                $lipaAccount['number'],
            )
            : "Order {$order->order_number} was placed successfully. Pay cash when it is delivered.");

        $order->notifyCustomer(new OrderPlacedMail($order));

        $this->redirectRoute('orders.show', ['order' => $order]);
    }

    /**
     * Register the shopper who reached checkout without an account and hand
     * the guest cart over to the fresh account.
     *
     * @param  array{name: string, email: string, password: string}  $account
     */
    private function createCustomer(array $account): User
    {
        $customer = User::query()->create([
            'name' => $account['name'],
            'email' => $account['email'],
            'password' => Hash::make($account['password']),
        ]);

        if (Role::where('name', 'customer')->where('guard_name', 'web')->exists()) {
            $customer->assignRole('customer');
        }

        event(new Registered($customer));

        Auth::login($customer);
        CartSession::claim($customer);

        return $customer;
    }

    public function render()
    {
        $cartItems = CartSession::query()->with('product')->get();
        $selectedStockIds = $cartItems
            ->filter(fn (CartItem $cartItem): bool => $cartItem->product?->variant_product ?? false)
            ->map(function (CartItem $cartItem): int|false {
                $selection = json_decode((string) $cartItem->variation, true);

                return is_array($selection)
                    ? filter_var($selection['product_stock_id'] ?? null, FILTER_VALIDATE_INT)
                    : false;
            })
            ->filter(fn (int|false $id): bool => is_int($id) && $id > 0)
            ->unique()
            ->values();
        $variantStocks = $selectedStockIds->isNotEmpty()
            ? ProductStock::query()->whereIn('id', $selectedStockIds)->get()->keyBy('id')
            : collect();
        $previewItems = [];
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            $productStock = null;
            $requiresSelection = false;

            if ($product?->variant_product) {
                $selection = json_decode((string) $cartItem->variation, true);
                $stockId = is_array($selection)
                    ? filter_var($selection['product_stock_id'] ?? null, FILTER_VALIDATE_INT)
                    : false;
                $productStock = is_int($stockId) ? $variantStocks->get($stockId) : null;
                $productStock = $productStock?->product_id === $product->id ? $productStock : null;
                $requiresSelection = $productStock === null;
            }

            if ($product === null) {
                $previewItems[$cartItem->id] = ['unavailable' => true];

                continue;
            }

            $pricing = $this->linePricing($product, $productStock, (int) $cartItem->quantity);
            $previewItems[$cartItem->id] = [
                'requiresSelection' => $requiresSelection,
                ...$pricing,
            ];
            $subtotal += $pricing['subtotal'];
            $discountTotal += $pricing['discountAmount'];
            $taxTotal += $pricing['taxAmount'];
        }

        $settings = BusinessSetting::query()
            ->whereIn('type', ['currency_code', 'free_shipping', 'flat_rate_shipping_cost'])
            ->pluck('value', 'type');
        $shippingTotal = $settings->get('free_shipping') === '1'
            ? 0
            : max(0, (float) ($settings->get('flat_rate_shipping_cost') ?? 0));
        $subtotal = round($subtotal, 2);
        $discountTotal = round($discountTotal, 2);
        $taxTotal = round($taxTotal, 2);
        $shippingTotal = round($shippingTotal, 2);

        return view('livewire.storefront.checkout', [
            'cartItems' => $cartItems,
            'previewItems' => $previewItems,
            'currency' => $settings->get('currency_code') ?? 'TZS',
            'subtotal' => $subtotal,
            'discountTotal' => $discountTotal,
            'taxTotal' => $taxTotal,
            'shippingTotal' => $shippingTotal,
            'total' => round($subtotal - $discountTotal + $taxTotal + $shippingTotal, 2),
            'paymentOptions' => Payment::availableMethods(),
            'lipaAccount' => Payment::lipaAccount(),
        ])->layout('components.layouts.storefront', [
            'title' => 'Checkout',
            'navigationArea' => 'store',
            'pageHeader' => [
                'title' => 'Checkout',
                'parents' => [
                    ['label' => 'Shop', 'url' => route('shop')],
                    ['label' => 'Your cart', 'url' => route('cart')],
                ],
            ],
        ]);
    }

    /**
     * @return array{unitPrice: float, subtotal: float, discountAmount: float, taxAmount: float, lineTotal: float}
     */
    private function linePricing(Product $product, ?ProductStock $productStock, int $quantity): array
    {
        $unitPrice = $productStock !== null && $productStock->price > 0
            ? (float) $productStock->price
            : (float) $product->unit_price;
        $discountPerUnit = $product->discount_type === 'percent'
            ? $unitPrice * (float) $product->discount / 100
            : (float) $product->discount;
        $discountPerUnit = min($unitPrice, max(0, $discountPerUnit));
        $lineSubtotal = round($unitPrice * $quantity, 2);
        $lineDiscount = round($discountPerUnit * $quantity, 2);
        $taxableTotal = $lineSubtotal - $lineDiscount;
        $lineTax = $product->tax_type === 'percent'
            ? round($taxableTotal * (float) $product->tax / 100, 2)
            : round((float) $product->tax * $quantity, 2);

        return [
            'unitPrice' => round($unitPrice, 2),
            'subtotal' => $lineSubtotal,
            'discountAmount' => $lineDiscount,
            'taxAmount' => $lineTax,
            'lineTotal' => round($taxableTotal + $lineTax, 2),
        ];
    }

    /**
     * @return array{0: ProductStock|null, 1: string|null}
     */
    private function selectedStock(CartItem $cartItem, Product $product): array
    {
        if (! $product->variant_product) {
            return [null, null];
        }

        $selection = json_decode((string) $cartItem->variation, true);
        $stockId = is_array($selection) ? filter_var($selection['product_stock_id'] ?? null, FILTER_VALIDATE_INT) : false;

        if (! is_int($stockId) || $stockId < 1) {
            throw ValidationException::withMessages([
                'checkout' => "Choose a valid option for {$product->name} before checking out.",
            ]);
        }

        $stock = ProductStock::query()
            ->where('product_id', $product->id)
            ->whereKey($stockId)
            ->lockForUpdate()
            ->first();

        if ($stock === null) {
            throw ValidationException::withMessages([
                'checkout' => "The selected option for {$product->name} is no longer available.",
            ]);
        }

        return [$stock, $stock->variant];
    }
}
