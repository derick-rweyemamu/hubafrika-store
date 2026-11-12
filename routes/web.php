<?php

use App\Http\Controllers\AddToCartController;
use App\Http\Controllers\Admin\CmsPageBuilderController;
use App\Http\Controllers\Admin\MediaUploadController;
use App\Http\Controllers\NewsletterSubscribeController;
use App\Http\Controllers\Payments\VodacomCallbackController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Admin\Brands;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\CmsMenus;
use App\Livewire\Admin\CmsPages;
use App\Livewire\Admin\CmsPosts;
use App\Livewire\Admin\Customers;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Inventory;
use App\Livewire\Admin\MediaBrowser;
use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\Reports;
use App\Livewire\Admin\Roles;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Units;
use App\Livewire\Admin\Users;
use App\Livewire\Storefront\Cart;
use App\Livewire\Storefront\Checkout;
use App\Livewire\Storefront\CmsPage;
use App\Livewire\Storefront\CmsPost as StorefrontCmsPost;
use App\Livewire\Storefront\MyOrders;
use App\Livewire\Storefront\OrderDetail;
use App\Livewire\Storefront\Payments;
use App\Livewire\Storefront\Shop;
use Illuminate\Support\Facades\Route;

// Landing Page & CMS Pages
Route::get('/', function () {
    $page = App\Models\CmsPage::where('slug', 'home')->first() ?? App\Models\CmsPage::first();
    if (! $page) {
        abort(404);
    }

    return view('welcome', compact('page'));
})->name('home');

// ==============================================================

Route::get('/shop', Shop::class)->name('shop');

// Guests collect a cart and reach checkout; the account is created there.
Route::get('/cart', Cart::class)->name('cart');
Route::get('/checkout', Checkout::class)->name('checkout');

// Page-builder product cards link here: a link can only reach a URL, so the
// add runs on the request and the shopper lands on the cart.
Route::get('/cart/add/{product}', AddToCartController::class)
    ->middleware('throttle:30,1')
    ->name('cart.add');

Route::get('/post/{slug}', StorefrontCmsPost::class)->name('posts.show');

Route::post('/newsletter', NewsletterSubscribeController::class)
    ->middleware('throttle:10,1')
    ->name('newsletter.subscribe');

// Vodacom Lipa payment callback. Signature-verified, CSRF-exempt (see bootstrap/app.php).
Route::post('/payments/vodacom/callback', VodacomCallbackController::class)
    ->middleware('throttle:60,1')
    ->name('payments.vodacom.callback');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/my-orders', MyOrders::class)->name('orders.index');
    Route::get('/my-orders/{order}', OrderDetail::class)->name('orders.show');
    Route::get('/my-payments', Payments::class)->name('payments.index');
});

// Admin Routes (Protected by auth, panel access and a per-page permission)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/products', Products::class)->middleware('permission:products.view')->name('products');
    Route::get('/categories', Categories::class)->middleware('permission:categories.view')->name('categories');
    Route::get('/brands', Brands::class)->middleware('permission:brands.view')->name('brands');
    Route::get('/inventory', Inventory::class)->middleware('permission:inventory.view')->name('inventory');
    Route::get('/units', Units::class)->middleware('permission:units.view')->name('units');
    Route::get('/users', Users::class)->middleware('permission:users.view')->name('users');
    Route::get('/roles', Roles::class)->middleware('permission:roles.view')->name('roles');
    Route::get('/customers', Customers::class)->middleware('permission:customers.view')->name('customers');
    Route::get('/orders', AdminOrders::class)->middleware('permission:orders.view')->name('orders');
    Route::get('/reports', Reports::class)->middleware('permission:reports.view')->name('reports');
    Route::get('/cms/pages', CmsPages::class)->middleware('permission:cms.manage')->name('cms.pages');
    Route::get('/cms/pages/{page}/builder', [CmsPageBuilderController::class, 'edit'])->middleware('permission:cms.manage')->name('cms.pages.builder');
    Route::put('/cms/pages/{page}/builder', [CmsPageBuilderController::class, 'update'])->middleware('permission:cms.manage')->name('cms.pages.builder.update');
    Route::get('/cms/posts', CmsPosts::class)->middleware('permission:cms.manage')->name('cms.posts');
    Route::get('/cms/posts/{post}/builder', [CmsPageBuilderController::class, 'editPost'])->middleware('permission:cms.manage')->name('cms.posts.builder');
    Route::put('/cms/posts/{post}/builder', [CmsPageBuilderController::class, 'updatePost'])->middleware('permission:cms.manage')->name('cms.posts.builder.update');
    Route::get('/cms/menus', CmsMenus::class)->middleware('permission:cms.manage')->name('cms.menus');
    Route::get('/cms/media-library', [CmsPageBuilderController::class, 'mediaLibrary'])->middleware('permission:cms.manage')->name('cms.media-library');
    Route::get('/settings', Settings::class)->middleware('permission:settings.view')->name('settings');
    Route::post('/media/upload', MediaUploadController::class)->middleware('permission:media.manage')->name('media.upload');
    Route::get('/media', MediaBrowser::class)->middleware('permission:media.view')->name('media');

});

require __DIR__.'/auth.php';

// ---------------------------------------------------------------
// CMS pages answer at the site root (/about-us), so the catch-all is
// registered last: every real route is matched before a slug is tried.
// ---------------------------------------------------------------

// Pages published before pages moved to the root keep working.
Route::get('/pages/{slug}', fn (string $slug) => redirect()->route('pages.show', ['slug' => $slug], 301))
    ->where('slug', '[A-Za-z0-9][A-Za-z0-9_-]*');

Route::get('/{slug}', CmsPage::class)
    ->where('slug', '[A-Za-z0-9][A-Za-z0-9_-]*')
    ->name('pages.show');
