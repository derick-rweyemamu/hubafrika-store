<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Storefront\CartSession;
use Illuminate\Http\RedirectResponse;

class AddToCartController extends Controller
{
    /**
     * Add a product straight from a page-builder card. Links inside the CMS
     * document can only reach a URL, so the add runs on the request itself and
     * the shopper lands on the cart that now holds the product.
     */
    public function __invoke(Product $product): RedirectResponse
    {
        abort_unless($product->published && $product->approved, 404);

        // A product with options needs a choice the card cannot offer.
        if ($product->variant_product) {
            return redirect()
                ->route('shop')
                ->with('message', "Choose an option for {$product->name} on the shop page.");
        }

        $result = CartSession::add($product);

        if ($result['error'] !== null) {
            return redirect()->route('shop')->with('message', $result['error']);
        }

        return redirect()
            ->route('cart')
            ->with('message', "{$product->name} added to your cart.");
    }
}
