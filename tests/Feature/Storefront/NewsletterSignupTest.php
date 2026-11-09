<?php

namespace Tests\Feature\Storefront;

use Tests\Feature\Admin\CatalogTestCase;

class NewsletterSignupTest extends CatalogTestCase
{
    public function test_the_footer_signup_stores_a_normalised_email(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => '  Reader@Example.COM '])
            ->assertRedirect()
            ->assertSessionHas('newsletter_status');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'reader@example.com',
            'source' => 'footer',
        ]);
    }

    public function test_signing_up_twice_keeps_a_single_subscriber(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'reader@example.com'])->assertRedirect();
        $this->post(route('newsletter.subscribe'), ['email' => 'READER@example.com'])->assertRedirect();

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_an_invalid_address_is_rejected(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }
}
