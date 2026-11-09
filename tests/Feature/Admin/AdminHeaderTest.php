<?php

namespace Tests\Feature\Admin;

use App\Models\User;

class AdminHeaderTest extends CatalogTestCase
{
    public function test_the_header_links_to_settings_with_an_icon_button(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $response = $this->get(route('admin.settings'));

        $response->assertOk()
            ->assertSee('aria-label="Settings"', false)
            ->assertSee('href="'.route('admin.settings').'"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_the_header_offers_an_account_menu_with_profile_and_logout(): void
    {
        $admin = User::factory()->create([
            'name' => 'Ada Admin',
            'email' => 'ada@example.com',
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.settings'));

        $response->assertOk()
            ->assertSee('admin-user-menu', false)
            ->assertSee('aria-haspopup="true"', false)
            ->assertSee('Ada Admin')
            ->assertSee('ada@example.com')
            ->assertSee('View profile')
            ->assertSee('href="'.route('profile.edit').'"', false)
            ->assertSee('Logout')
            ->assertSee('action="'.route('logout').'"', false);
    }
}
