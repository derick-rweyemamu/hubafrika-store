<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_storefront_url_renders_the_branded_page(): void
    {
        $this->get('/definitely-not-a-page')
            ->assertNotFound()
            ->assertSee('Continue shopping')
            ->assertSee('auth-page', false)
            ->assertDontSee('admin-theme', false);
    }

    public function test_unknown_admin_url_renders_the_admin_panel_page(): void
    {
        $this->get('/admin/definitely-not-a-page')
            ->assertNotFound()
            ->assertSee('Back to dashboard')
            ->assertSee('admin-theme', false)
            ->assertDontSee('auth-page', false);
    }

    public function test_access_denied_in_the_panel_renders_the_admin_403_page(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.dashboard'))
            ->assertForbidden()
            ->assertSee('Access denied')
            ->assertSee('admin-theme', false);
    }
}
