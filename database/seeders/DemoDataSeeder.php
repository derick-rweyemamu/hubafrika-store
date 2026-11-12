<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Review;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Fills a freshly seeded store with believable demo content so every admin
 * screen and the storefront can be exercised by hand.
 *
 * The data is deliberately uneven — products with variants, a product that is
 * out of stock, one that is unpublished, orders in every lifecycle state, one
 * payment still waiting for verification — because that is where bugs hide.
 */
class DemoDataSeeder extends Seeder
{
    /** @var array<string, Product> */
    protected array $products = [];

    /** @var array<string, User> */
    protected array $customers = [];

    /** @var array<string, int> */
    protected array $unitsSold = [];

    /** @var array<string, array{product: Product, stock: ?ProductStock}> */
    protected array $stockTargets = [];

    /** @var array<string, list<array{at: Carbon, qty: int, reference: string}>> */
    protected array $saleEvents = [];

    protected int $adminId = 0;

    public function run(): void
    {
        if (Product::query()->exists()) {
            $this->command?->warn('Demo data is already present; nothing to do.');

            return;
        }

        $this->adminId = (int) User::query()->where('is_admin', true)->value('id');

        $this->seedBrands();
        $this->seedSubCategories();
        $this->seedProducts();
        $this->seedCustomers();
        $this->seedStaff();
        $this->seedOrders();
        $this->seedStockMovements();
        $this->seedReviews();
        $this->seedCartsAndWishlists();
        $this->configureStoreSettings();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(sprintf(
            'Demo store ready: %d products, %d customers, %d orders.',
            Product::query()->count(),
            User::query()->where('is_admin', false)->count(),
            Order::query()->count(),
        ));
    }

    protected function seedBrands(): void
    {
        $brands = [
            'jinko-solar' => ['Jinko Solar', '#0f766e'],
            'longi' => ['LONGi', '#b45309'],
            'growatt' => ['Growatt', '#1d4ed8'],
            'victron-energy' => ['Victron Energy', '#0369a1'],
            'felicity-solar' => ['Felicity Solar', '#7c2d12'],
            'must-energy' => ['Must Energy', '#4338ca'],
        ];

        foreach ($brands as $slug => [$name, $colour]) {
            Brand::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'logo' => $this->placeholderImage("demo/brands/{$slug}.svg", $name, $colour),
                'top' => 1,
            ]);
        }
    }

    protected function seedSubCategories(): void
    {
        $children = [
            ['solar-panels', 'Solar Panels', 'conversion', 1],
            ['inverters', 'Inverters', 'conversion', 2],
            ['charge-controllers', 'Charge Controllers', 'conversion', 3],
            ['lithium-batteries', 'Lithium Batteries', 'power-storage', 4],
            ['lead-acid-batteries', 'Lead Acid Batteries', 'power-storage', 5],
            ['cables-connectors', 'Cables & Connectors', 'solar-accessory', 6],
            ['mounting', 'Mounting Hardware', 'solar-accessory', 7],
        ];

        foreach ($children as [$slug, $name, $parentSlug, $order]) {
            $parent = Category::query()->where('slug', $parentSlug)->first();

            Category::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'parent_id' => $parent?->id,
                'level' => 1,
                'order_level' => $order,
                'featured' => in_array($slug, ['solar-panels', 'lithium-batteries'], true),
                'top' => in_array($slug, ['solar-panels', 'inverters'], true),
            ]);
        }
    }

    protected function seedProducts(): void
    {
        $catalogue = [
            [
                'slug' => 'jinko-tiger-neo-450w',
                'name' => 'Jinko Tiger Neo 450W Mono Solar Panel',
                'category' => 'solar-panels',
                'brand' => 'jinko-solar',
                'price' => 420000,
                'cost' => 348000,
                'stock' => 26,
                'low' => 6,
                'weight' => 22.5,
                'featured' => true,
                'discount' => 5,
                'discount_type' => 'percent',
                'tags' => 'solar panel,450w,monocrystalline,n-type',
                'description' => "The Tiger Neo uses N-type TOPCon cells with a 22.5% module efficiency.\n\n- 450W peak output, 12-year product warranty\n- IP68 junction box with three bypass diodes\n- Suited to 24V and 48V off-grid systems",
                'age' => 48,
            ],
            [
                'slug' => 'longi-himo5-550w',
                'name' => 'LONGi Hi-MO 5 550W Solar Panel',
                'category' => 'solar-panels',
                'brand' => 'longi',
                'price' => 512000,
                'cost' => 430000,
                'stock' => 0,
                'low' => 4,
                'weight' => 27.0,
                'tags' => 'solar panel,550w,longi',
                'description' => "High-output bifacial-ready module for ground mount arrays.\n\nCurrently out of stock — kept in the catalogue to exercise the sold-out state.",
                'age' => 44,
            ],
            [
                'slug' => 'growatt-spf-5000es',
                'name' => 'Growatt SPF 5000ES Off-Grid Inverter',
                'category' => 'inverters',
                'brand' => 'growatt',
                'price' => 1850000,
                'cost' => 1620000,
                'stock' => 0,
                'low' => 3,
                'weight' => 12.0,
                'featured' => true,
                'tags' => 'inverter,hybrid,5kw,growatt',
                'description' => "Pure sine wave off-grid inverter with a built-in 80A MPPT charger.\n\nPick the 3kW model for small homes or the 5kW model for a full household.",
                'variants' => [
                    ['3kW / 24V', 1380000, 5, 'GRO-SPF3000'],
                    ['5kW / 48V', 1850000, 4, 'GRO-SPF5000'],
                ],
                'age' => 40,
            ],
            [
                'slug' => 'victron-multiplus-3000',
                'name' => 'Victron MultiPlus-II 48/3000 Inverter Charger',
                'category' => 'inverters',
                'brand' => 'victron-energy',
                'price' => 2890000,
                'cost' => 2560000,
                'stock' => 6,
                'low' => 2,
                'weight' => 19.0,
                'tags' => 'inverter,victron,48v,hybrid',
                'description' => 'Marine-grade inverter charger with a 35A transfer switch and PowerAssist.',
                'age' => 38,
            ],
            [
                'slug' => 'victron-smartsolar-100-50',
                'name' => 'Victron SmartSolar MPPT 100/50 Charge Controller',
                'category' => 'charge-controllers',
                'brand' => 'victron-energy',
                'price' => 690000,
                'cost' => 585000,
                'stock' => 14,
                'low' => 4,
                'weight' => 1.5,
                'tags' => 'mppt,charge controller,victron,50a',
                'description' => 'Bluetooth-enabled MPPT controller, 100V input and 50A output with VE.Direct telemetry.',
                'age' => 36,
            ],
            [
                'slug' => 'must-pc18-6015',
                'name' => 'Must PC18-6015 60A MPPT Controller',
                'category' => 'charge-controllers',
                'brand' => 'must-energy',
                'price' => 380000,
                'cost' => 315000,
                'stock' => 9,
                'low' => 3,
                'weight' => 2.2,
                'published' => false,
                'tags' => 'mppt,must,60a',
                'description' => 'Entry-level 60A controller. Hidden from the storefront while the supplier pricing is reviewed.',
                'age' => 34,
            ],
            [
                'slug' => 'felicity-5kwh-lithium',
                'name' => 'Felicity 51.2V Lithium Battery Pack',
                'category' => 'lithium-batteries',
                'brand' => 'felicity-solar',
                'price' => 3450000,
                'cost' => 3050000,
                'stock' => 0,
                'low' => 2,
                'weight' => 52.0,
                'featured' => true,
                'deal' => true,
                'tags' => 'lithium,battery,5kwh,48v',
                'description' => "LiFePO4 storage with an integrated BMS, 6,000 cycles at 80% depth of discharge.\n\nThe 10.24kWh unit is currently sold out.",
                'variants' => [
                    ['5.12kWh', 3450000, 4, 'FEL-LFP-5K'],
                    ['10.24kWh', 6250000, 0, 'FEL-LFP-10K'],
                ],
                'age' => 32,
            ],
            [
                'slug' => 'victron-smart-lithium-200ah',
                'name' => 'Victron Smart Lithium 12.8V 200Ah Battery',
                'category' => 'lithium-batteries',
                'brand' => 'victron-energy',
                'price' => 4120000,
                'cost' => 3700000,
                'stock' => 2,
                'low' => 3,
                'weight' => 28.0,
                'tags' => 'lithium,victron,12v,200ah',
                'description' => 'Low-stock demo item: only two units left, below the reorder level of three.',
                'age' => 30,
            ],
            [
                'slug' => 'trojan-t105',
                'name' => 'Trojan T-105 6V 225Ah Deep Cycle Battery',
                'category' => 'lead-acid-batteries',
                'brand' => 'victron-energy',
                'price' => 465000,
                'cost' => 398000,
                'stock' => 18,
                'low' => 6,
                'weight' => 28.0,
                'published' => false,
                'draft' => true,
                'tags' => 'lead acid,deep cycle,trojan',
                'description' => 'Flooded deep-cycle battery. Saved as a draft until the datasheet is attached.',
                'age' => 28,
            ],
            [
                'slug' => 'twin-solar-cable-100m',
                'name' => 'Twin Solar Cable (100m Roll)',
                'category' => 'cables-connectors',
                'brand' => 'jinko-solar',
                'price' => 285000,
                'cost' => 232000,
                'stock' => 0,
                'low' => 5,
                'weight' => 9.0,
                'tags' => 'cable,solar,dc,twin',
                'description' => 'UV-resistant twin-core DC cable supplied on a 100 metre roll.',
                'variants' => [
                    ['4mm²', 245000, 8, 'CBL-4MM-100'],
                    ['6mm²', 285000, 6, 'CBL-6MM-100'],
                    ['10mm²', 410000, 3, 'CBL-10MM-100'],
                ],
                'age' => 26,
            ],
            [
                'slug' => 'mc4-connector-pair',
                'name' => 'MC4 Connector Pair (Pack of 10)',
                'category' => 'cables-connectors',
                'brand' => 'jinko-solar',
                'price' => 38000,
                'cost' => 24500,
                'stock' => 64,
                'low' => 20,
                'unit' => 6,
                'weight' => 0.4,
                'tags' => 'mc4,connector,cable',
                'description' => 'IP67 rated male and female MC4 pairs with silicone seals.',
                'age' => 24,
            ],
            [
                'slug' => 'mounting-rail-4m',
                'name' => 'Aluminium Mounting Rail (4m)',
                'category' => 'mounting',
                'brand' => 'must-energy',
                'price' => 96000,
                'cost' => 74000,
                'stock' => 40,
                'low' => 10,
                'weight' => 4.2,
                'tags' => 'mounting,rail,aluminium',
                'description' => 'Anodised 40x40 aluminium rail with pre-drilled mounting channels.',
                'age' => 22,
            ],
            [
                'slug' => 'panel-z-bracket-set',
                'name' => 'Solar Panel Z-Bracket Set (4 pieces)',
                'category' => 'mounting',
                'brand' => 'must-energy',
                'price' => 45000,
                'cost' => 31000,
                'stock' => 55,
                'low' => 15,
                'unit' => 5,
                'weight' => 1.8,
                'tags' => 'bracket,mounting,z-bracket',
                'description' => 'Stainless steel Z-brackets for pitched roof installations.',
                'age' => 20,
            ],
        ];

        foreach ($catalogue as $row) {
            $category = Category::query()->where('slug', $row['category'])->firstOrFail();
            $brand = Brand::query()->where('slug', $row['brand'])->first();
            $variants = $row['variants'] ?? [];
            $photos = [];

            for ($shot = 1; $shot <= 3; $shot++) {
                $photos[] = $this->placeholderImage(
                    "demo/products/{$row['slug']}-{$shot}.svg",
                    $row['name'],
                    $this->categoryColour($row['category']),
                );
            }

            $product = Product::query()->create([
                'name' => $row['name'],
                'slug' => $row['slug'],
                'category_id' => $category->id,
                'brand_id' => $brand?->id,
                'unit' => $row['unit'] ?? 1,
                'unit_price' => $row['price'],
                'purchase_price' => $row['cost'],
                'photos' => $photos,
                'thumbnail_img' => $photos[0],
                'description' => $row['description'],
                'tags' => $row['tags'],
                'published' => $row['published'] ?? true,
                'approved' => true,
                'draft' => $row['draft'] ?? false,
                'featured' => $row['featured'] ?? false,
                'todays_deal' => $row['deal'] ?? false,
                'variant_product' => $variants === [] ? 0 : 1,
                'current_stock' => $row['stock'],
                'low_stock_quantity' => $row['low'],
                'min_qty' => 1,
                'weight' => $row['weight'],
                'discount' => $row['discount'] ?? 0,
                'discount_type' => $row['discount_type'] ?? 'amount',
                'cash_on_delivery' => true,
                'shipping_type' => 'flat_rate',
                'shipping_cost' => 0,
                'tax' => 0,
                'tax_type' => 'flat',
                'rating' => 0,
                'num_of_sale' => 0,
                'created_at' => now()->subDays($row['age']),
            ]);

            $this->products[$row['slug']] = $product;

            DB::table('product_categories')->insertOrIgnore([
                'product_id' => $product->id,
                'category_id' => $category->id,
            ]);

            if ($variants === []) {
                $this->stockTargets["p{$product->id}"] = ['product' => $product, 'stock' => null];

                continue;
            }

            $total = 0;

            foreach ($variants as [$label, $variantPrice, $quantity, $sku]) {
                $stock = ProductStock::query()->create([
                    'product_id' => $product->id,
                    'variant' => $label,
                    'sku' => $sku,
                    'price' => $variantPrice,
                    'qty' => $quantity,
                ]);
                $total += $quantity;

                $this->stockTargets["p{$product->id}s{$stock->id}"] = ['product' => $product, 'stock' => $stock];
            }

            $product->update(['current_stock' => $total]);
        }
    }

    protected function seedCustomers(): void
    {
        $customers = [
            [
                'email' => 'amina.hassan@example.com',
                'name' => 'Amina Hassan',
                'phone' => '+255 754 118 220',
                'street' => 'Plot 44, Msasani Peninsula',
                'state' => 'Dar es Salaam',
                'state_id' => 3506,
                'city' => 'Dar es Salaam',
                'city_id' => null,
                'postal_code' => '14111',
                'verified' => true,
                'age' => 120,
            ],
            [
                'email' => 'joseph.mwita@example.com',
                'name' => 'Joseph Mwita',
                'phone' => '+255 715 903 411',
                'street' => 'Njiro Road, House 12',
                'state' => 'Arusha',
                'state_id' => 3505,
                'city' => 'Arusha',
                'city_id' => 39768,
                'postal_code' => '23103',
                'verified' => true,
                'age' => 96,
            ],
            [
                'email' => 'neema.kimaro@example.com',
                'name' => 'Neema Kimaro',
                'phone' => '+255 786 552 108',
                'street' => 'Kaloleni Street, Block C',
                'state' => 'Kilimanjaro',
                'state_id' => 3511,
                'city' => 'Moshi',
                'city_id' => 39780,
                'postal_code' => '25101',
                'verified' => true,
                'age' => 74,
            ],
            [
                'email' => 'baraka.mushi@example.com',
                'name' => 'Baraka Mushi',
                'phone' => '+255 673 441 907',
                'street' => 'Uyole Industrial Area',
                'state' => 'Mbeya',
                'state_id' => 3514,
                'city' => 'Mbeya',
                'city_id' => 39787,
                'postal_code' => '53100',
                'verified' => true,
                'age' => 58,
            ],
            [
                'email' => 'fatuma.salum@example.com',
                'name' => 'Fatuma Salum',
                'phone' => '+255 719 220 664',
                'street' => 'Area C, Uhuru Avenue',
                'state' => 'Dodoma',
                'state_id' => 3507,
                'city' => 'Dodoma',
                'city_id' => 39769,
                'postal_code' => '41103',
                'verified' => false,
                'age' => 26,
            ],
            [
                'email' => 'suspended.buyer@example.com',
                'name' => 'Hamisi Juma',
                'phone' => '+255 762 330 118',
                'street' => 'Kariakoo Market Road',
                'state' => 'Dar es Salaam',
                'state_id' => 3506,
                'city' => 'Dar es Salaam',
                'city_id' => null,
                'postal_code' => '14107',
                'verified' => true,
                'banned' => true,
                'age' => 40,
            ],
        ];

        foreach ($customers as $index => $row) {
            $banned = (bool) ($row['banned'] ?? false);
            $user = User::query()->forceCreate([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => $row['verified'] ? now()->subDays($row['age']) : null,
                'verification_status' => $row['verified'] ? 1 : 0,
                'is_admin' => false,
                'banned' => $banned,
                'is_suspicious' => $banned,
                'phone' => $row['phone'],
                'address' => $row['street'],
                'country' => 'Tanzania',
                'state' => $row['state'],
                'city' => $row['city'],
                'postal_code' => $row['postal_code'],
                'balance' => 0,
                'created_at' => now()->subDays($row['age']),
            ]);

            Address::query()->create([
                'user_id' => $user->id,
                'address' => $row['street'],
                'country_id' => 216,
                'state_id' => $row['state_id'],
                'city_id' => $row['city_id'],
                'postal_code' => $row['postal_code'],
                'phone' => $row['phone'],
                'set_default' => 1,
                'latitude' => -6.7924 + ($index * 0.4),
                'longitude' => 39.2083 - ($index * 0.6),
            ]);

            $user->assignRole('customer');

            $this->customers[$row['email']] = $user;
        }
    }

    protected function seedStaff(): void
    {
        $staff = [
            ['email' => 'manager@hubafrika.test', 'name' => 'Grace Mushi', 'role' => 'manager', 'phone' => '+255 755 400 921'],
            ['email' => 'editor@hubafrika.test', 'name' => 'Daniel Otieno', 'role' => 'content editor', 'phone' => '+255 758 771 203'],
        ];

        foreach ($staff as $row) {
            $role = Role::query()
                ->where('name', $row['role'])
                ->where('guard_name', 'web')
                ->first();

            if ($role === null) {
                $this->command?->warn("Role [{$row['role']}] is missing; run RoleSeeder first.");

                continue;
            }

            $user = User::query()->forceCreate([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'verification_status' => 1,
                'is_admin' => false,
                'banned' => false,
                'phone' => $row['phone'],
                'country' => 'Tanzania',
                'balance' => 0,
            ]);

            $user->syncRoles([$role->name]);
        }
    }

    protected function seedOrders(): void
    {
        $orders = [
            [
                'days' => 24,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'method' => Payment::METHOD_CASH_ON_DELIVERY,
                'customer' => 'amina.hassan@example.com',
                'shipping' => 15000,
                'items' => [['jinko-tiger-neo-450w', 4], ['mc4-connector-pair', 2]],
            ],
            [
                'days' => 19,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'method' => Payment::METHOD_VODACOM_LIPA,
                'customer' => 'joseph.mwita@example.com',
                'shipping' => 40000,
                'payment' => ['status' => Payment::STATUS_VERIFIED, 'reference' => 'MPESA7TQ4X21', 'payer' => '+255 715 903 411'],
                'items' => [['growatt-spf-5000es', 1, 1], ['mounting-rail-4m', 2]],
            ],
            [
                'days' => 13,
                'status' => 'shipped',
                'payment_status' => 'paid',
                'method' => Payment::METHOD_CASH_ON_DELIVERY,
                'customer' => 'neema.kimaro@example.com',
                'shipping' => 60000,
                'items' => [['felicity-5kwh-lithium', 1, 0]],
            ],
            [
                'days' => 8,
                'status' => 'processing',
                'payment_status' => 'paid',
                'method' => Payment::METHOD_VODACOM_LIPA,
                'customer' => 'baraka.mushi@example.com',
                'shipping' => 35000,
                'payment' => ['status' => Payment::STATUS_VERIFIED, 'reference' => 'MPESA3JW9L55', 'payer' => '+255 673 441 907'],
                'items' => [['victron-multiplus-3000', 1], ['panel-z-bracket-set', 2]],
            ],
            [
                'days' => 5,
                'status' => 'processing',
                'payment_status' => 'paid',
                'method' => Payment::METHOD_CASH_ON_DELIVERY,
                'customer' => 'amina.hassan@example.com',
                'shipping' => 12000,
                'items' => [['twin-solar-cable-100m', 1, 1], ['mc4-connector-pair', 1]],
            ],
            [
                'days' => 3,
                'status' => 'pending',
                'payment_status' => 'awaiting_verification',
                'method' => Payment::METHOD_VODACOM_LIPA,
                'customer' => 'fatuma.salum@example.com',
                'shipping' => 18000,
                'payment' => ['status' => Payment::STATUS_AWAITING_VERIFICATION, 'reference' => 'MPESA9HB2R07', 'payer' => '+255 719 220 664'],
                'items' => [['victron-smartsolar-100-50', 1]],
            ],
            [
                'days' => 2,
                'status' => 'pending',
                'payment_status' => 'pending',
                'method' => Payment::METHOD_CASH_ON_DELIVERY,
                'customer' => null,
                'guest' => ['name' => 'Salma Abdallah', 'email' => 'salma.abdallah@example.com', 'phone' => '+255 787 004 512'],
                'shipping' => 22000,
                'items' => [['jinko-tiger-neo-450w', 2]],
            ],
            [
                'days' => 11,
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'method' => Payment::METHOD_CASH_ON_DELIVERY,
                'customer' => 'joseph.mwita@example.com',
                'shipping' => 10000,
                'items' => [['panel-z-bracket-set', 1]],
            ],
        ];

        $number = 1;

        foreach ($orders as $row) {
            $customer = $row['customer'] !== null ? ($this->customers[$row['customer']] ?? null) : null;
            $guest = $row['guest'] ?? null;
            $placedAt = now()->subDays($row['days'])->setTime(9 + ($number % 8), 15 + ($number % 40));
            $lines = [];
            $subtotal = 0;

            foreach ($row['items'] as $item) {
                [$slug, $quantity] = [$item[0], $item[1]];
                $variantIndex = $item[2] ?? null;
                $product = $this->products[$slug] ?? null;

                if ($product === null) {
                    continue;
                }

                $stock = null;

                if ($variantIndex !== null) {
                    $stock = $product->stocks()->orderBy('id')->get()->values()->get($variantIndex);
                }

                $unitPrice = $stock !== null && $stock->price > 0 ? (float) $stock->price : (float) $product->unit_price;
                $lineTotal = $unitPrice * $quantity;
                $subtotal += $lineTotal;

                $lines[] = [
                    'product_id' => $product->id,
                    'product_stock_id' => $stock?->id,
                    'product_name' => $product->name,
                    'sku' => $stock?->sku ?? mb_strtoupper($slug),
                    'variant' => $stock?->variant,
                    'thumbnail' => $product->thumbnail_img,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_total' => $lineTotal,
                ];

                $key = $stock !== null ? "p{$product->id}s{$stock->id}" : "p{$product->id}";
                $reference = sprintf('HB-%05d', $number);
                $this->saleEvents[$key][] = ['at' => $placedAt->copy(), 'qty' => $quantity, 'reference' => $reference];
                $this->unitsSold[$slug] = ($this->unitsSold[$slug] ?? 0) + $quantity;
            }

            if ($lines === []) {
                continue;
            }

            $shipping = (float) $row['shipping'];
            $order = Order::query()->create([
                'order_number' => sprintf('HB-%05d', $number),
                'user_id' => $customer?->id,
                'customer_name' => $customer?->name ?? $guest['name'],
                'customer_email' => $customer?->email ?? $guest['email'],
                'customer_phone' => $customer?->phone ?? $guest['phone'],
                'status' => $row['status'],
                'payment_method' => $row['method'],
                'payment_status' => $row['payment_status'],
                'payment_reference' => $row['payment']['reference'] ?? null,
                'currency' => 'TZS',
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => 0,
                'shipping_total' => $shipping,
                'total' => $subtotal + $shipping,
                'shipping_address' => $customer?->address ?? 'Kariakoo, Dar es Salaam',
                'shipping_country' => 'Tanzania',
                'shipping_state' => $customer?->state ?? 'Dar es Salaam',
                'shipping_city' => $customer?->city ?? 'Dar es Salaam',
                'shipping_postal_code' => $customer?->postal_code,
                'created_at' => $placedAt,
            ]);

            foreach ($lines as $line) {
                OrderItem::query()->create($line + ['order_id' => $order->id, 'created_at' => $placedAt]);
            }

            if (isset($row['payment'])) {
                Payment::query()->create([
                    'order_id' => $order->id,
                    'method' => $row['method'],
                    'amount' => $order->total,
                    'currency' => 'TZS',
                    'reference' => $row['payment']['reference'],
                    'payer_phone' => $row['payment']['payer'],
                    'status' => $row['payment']['status'],
                    'payload' => ['source' => 'demo-seeder'],
                    'submitted_at' => $placedAt->copy()->addHours(2),
                    'verified_at' => $row['payment']['status'] === Payment::STATUS_VERIFIED
                        ? $placedAt->copy()->addHours(3)
                        : null,
                    'verified_by' => $row['payment']['status'] === Payment::STATUS_VERIFIED
                        ? $this->adminId
                        : null,
                ]);
            }

            $number++;
        }
    }

    /**
     * Build a stock ledger that ends on exactly the quantity stored on the
     * product or variant, so the inventory screen never contradicts itself.
     */
    protected function seedStockMovements(): void
    {
        foreach ($this->stockTargets as $key => $target) {
            $product = $target['product'];
            $stock = $target['stock'];
            $remaining = $stock !== null ? (int) $stock->qty : (int) $product->current_stock;
            $events = $this->saleEvents[$key] ?? [];
            $opening = $remaining + array_sum(array_column($events, 'qty'));
            $openingAt = now()->subDays(45);

            DB::table('stock_movements')->insert([
                'product_id' => $product->id,
                'product_stock_id' => $stock?->id,
                'user_id' => $this->adminId,
                'quantity' => $opening,
                'balance' => $opening,
                'reason' => StockMovement::REASON_RESTOCK,
                'note' => 'Opening stock, first delivery',
                'created_at' => $openingAt,
                'updated_at' => $openingAt,
            ]);

            $balance = $opening;

            usort($events, static fn (array $left, array $right): int => $left['at'] <=> $right['at']);

            foreach ($events as $event) {
                $balance -= $event['qty'];

                DB::table('stock_movements')->insert([
                    'product_id' => $product->id,
                    'product_stock_id' => $stock?->id,
                    'user_id' => null,
                    'quantity' => -$event['qty'],
                    'balance' => $balance,
                    'reason' => StockMovement::REASON_ORDER,
                    'note' => 'Order '.$event['reference'],
                    'created_at' => $event['at'],
                    'updated_at' => $event['at'],
                ]);
            }
        }

        $damaged = $this->products['mc4-connector-pair'] ?? null;

        if ($damaged !== null && $damaged->current_stock >= 2) {
            $balance = (int) $damaged->current_stock - 2;
            $damaged->update(['current_stock' => $balance]);

            DB::table('stock_movements')->insert([
                'product_id' => $damaged->id,
                'product_stock_id' => null,
                'user_id' => $this->adminId,
                'quantity' => -2,
                'balance' => $balance,
                'reason' => StockMovement::REASON_DAMAGED,
                'note' => 'Two packs crushed on delivery to the shop',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]);
        }

        foreach ($this->products as $slug => $product) {
            $product->update(['num_of_sale' => $this->unitsSold[$slug] ?? 0]);
        }
    }

    protected function seedReviews(): void
    {
        $reviews = [
            ['jinko-tiger-neo-450w', 'amina.hassan@example.com', 5, 'Output is exactly as rated, even on a hazy afternoon. Packaging arrived intact.', 20, 1],
            ['jinko-tiger-neo-450w', 'joseph.mwita@example.com', 4, 'Solid panels. The frames needed slightly longer bolts than the ones in the box.', 12, 1],
            ['growatt-spf-5000es', 'joseph.mwita@example.com', 5, 'Runs my fridge, lights and TV without a hiccup. The MPPT is genuinely good.', 16, 1],
            ['victron-multiplus-3000', 'baraka.mushi@example.com', 5, 'Expensive but worth it. The transfer switch is instant, no flicker on the fridge.', 7, 1],
            ['victron-smartsolar-100-50', 'fatuma.salum@example.com', 4, 'Bluetooth app is very clear. Would like a bigger screen on the unit itself.', 4, 1],
            ['felicity-5kwh-lithium', 'neema.kimaro@example.com', 5, 'Two days of backup with no generator. Installation was straightforward.', 10, 1],
            ['mc4-connector-pair', 'amina.hassan@example.com', 4, 'Good seals, they click together firmly. Extra pair in the box was a nice touch.', 6, 0],
        ];

        $ratings = [];

        foreach ($reviews as [$slug, $email, $rating, $comment, $days, $status]) {
            $product = $this->products[$slug] ?? null;
            $customer = $this->customers[$email] ?? null;

            if ($product === null || $customer === null) {
                continue;
            }

            Review::query()->create([
                'product_id' => $product->id,
                'user_id' => $customer->id,
                'custom_reviewer_name' => $customer->name,
                'rating' => $rating,
                'comment' => $comment,
                'status' => $status,
                'viewed' => 0,
                'created_at' => now()->subDays($days),
            ]);

            if ($status === 1) {
                $ratings[$slug][] = $rating;
            }
        }

        foreach ($ratings as $slug => $scores) {
            $this->products[$slug]->update(['rating' => round(array_sum($scores) / count($scores), 1)]);
        }
    }

    protected function seedCartsAndWishlists(): void
    {
        $amina = $this->customers['amina.hassan@example.com'] ?? null;
        $neema = $this->customers['neema.kimaro@example.com'] ?? null;

        foreach ([[$amina, 'victron-smartsolar-100-50', 1], [$amina, 'panel-z-bracket-set', 2], [$neema, 'jinko-tiger-neo-450w', 6]] as [$customer, $slug, $quantity]) {
            $product = $this->products[$slug] ?? null;

            if ($customer === null || $product === null) {
                continue;
            }

            Cart::query()->create([
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->unit_price,
                'status' => true,
                'address_id' => 0,
                'billing_address' => 0,
            ]);
        }

        foreach ([[$amina, 'felicity-5kwh-lithium'], [$amina, 'victron-multiplus-3000'], [$neema, 'twin-solar-cable-100m'], [$neema, 'mc4-connector-pair']] as [$customer, $slug]) {
            $product = $this->products[$slug] ?? null;

            if ($customer === null || $product === null) {
                continue;
            }

            Wishlist::query()->firstOrCreate([
                'user_id' => $customer->id,
                'product_id' => $product->id,
            ]);
        }
    }

    /**
     * Give the payment screen a Lipa account so both checkout methods can be
     * exercised, and take the store out of maintenance mode.
     */
    protected function configureStoreSettings(): void
    {
        $settings = [
            'lipa_enabled' => '1',
            'lipa_number' => '4001234',
            'lipa_business_name' => 'Hubafrika Store',
            'flat_rate_shipping_cost' => '15000',
            'maintenance_mode' => '0',
        ];

        foreach ($settings as $type => $value) {
            DB::table('business_settings')
                ->where('type', $type)
                ->update(['value' => $value, 'updated_at' => now()]);
        }
    }

    /**
     * Write a self-contained SVG so the demo never depends on a remote image
     * host, and so every product, brand and category has a visible picture.
     */
    protected function placeholderImage(string $path, string $label, string $colour): string
    {
        $initials = collect(preg_split('/\s+/', trim($label)) ?: [])
            ->filter()
            ->take(2)
            ->map(static fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
        $caption = mb_strtoupper(mb_substr($label, 0, 30));

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" role="img" aria-label="{$caption}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$colour}"/>
      <stop offset="100%" stop-color="#0f172a"/>
    </linearGradient>
  </defs>
  <rect width="600" height="600" fill="url(#bg)"/>
  <circle cx="300" cy="250" r="130" fill="#ffffff" opacity="0.12"/>
  <text x="300" y="300" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="150" font-weight="700" fill="#ffffff">{$initials}</text>
  <text x="300" y="470" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="32" fill="#e2e8f0">{$caption}</text>
</svg>
SVG;

        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    protected function categoryColour(string $slug): string
    {
        return match ($slug) {
            'solar-panels' => '#0f766e',
            'inverters' => '#1d4ed8',
            'charge-controllers' => '#0369a1',
            'lithium-batteries' => '#7c2d12',
            'lead-acid-batteries' => '#57534e',
            'cables-connectors' => '#b45309',
            'mounting' => '#4338ca',
            default => '#0f172a',
        };
    }
}
