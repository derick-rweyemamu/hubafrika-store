<?php

namespace Tests\Feature\Storefront;

class StorePageHeaderTest extends OrdersTestCase
{
    public function test_store_pages_render_the_page_header_banner_with_a_single_heading(): void
    {
        $customer = $this->customerWithCart();
        $order = $this->placedLipaOrder($customer, 'HDR0001');

        $guestPages = [
            route('shop') => [
                'title' => 'Shop',
                'breadcrumbs' => ['href="'.url('/').'"'],
            ],
            route('cart') => [
                'title' => 'Your cart',
                'breadcrumbs' => ['href="'.url('/').'"', 'href="'.route('shop').'"', '>Shop<'],
            ],
        ];

        foreach ($guestPages as $url => $expected) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertPageHeaderHtml($html, $expected['title'], $expected['breadcrumbs'], $url);
        }

        $customerPages = [
            route('checkout') => [
                'title' => 'Checkout',
                'breadcrumbs' => ['href="'.url('/').'"', 'href="'.route('shop').'"', 'href="'.route('cart').'"'],
            ],
            route('orders.index') => [
                'title' => 'My orders',
                'breadcrumbs' => ['href="'.url('/').'"'],
            ],
            route('orders.show', $order) => [
                'title' => 'Order '.$order->order_number,
                'breadcrumbs' => ['href="'.url('/').'"', 'href="'.route('orders.index').'"', '>My orders<'],
            ],
            route('payments.index') => [
                'title' => 'My payments',
                'breadcrumbs' => ['href="'.url('/').'"'],
            ],
        ];

        foreach ($customerPages as $url => $expected) {
            $html = $this->actingAs($customer)->get($url)->assertOk()->getContent();

            $this->assertPageHeaderHtml($html, $expected['title'], $expected['breadcrumbs'], $url);
        }
    }

    /**
     * @param  array<int, string>  $breadcrumbs
     */
    private function assertPageHeaderHtml(string $html, string $title, array $breadcrumbs, string $url): void
    {
        $this->assertStringContainsString('<section class="hubafrika-page-header">', $html, $url.' should render the page header section.');
        $this->assertStringContainsString('<h1 class="hubafrika-page-header-title">'.e($title).'</h1>', $html, $url.' should render the page header title.');
        $this->assertSame(1, substr_count($html, '<h1'), $url.' should render a single h1 element.');

        foreach ($breadcrumbs as $breadcrumb) {
            $this->assertStringContainsString($breadcrumb, $html, $url.' should include breadcrumb '.$breadcrumb);
        }
    }
}
