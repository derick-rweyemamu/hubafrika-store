<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Tests\Feature\Admin\CatalogTestCase;

class ProfileTest extends CatalogTestCase
{
    public function test_customer_profile_shows_identity_and_purchase_records(): void
    {
        $user = User::factory()->create([
            'name' => 'Amina Customer',
            'phone' => '+255 700 000 000',
            'city' => 'Dar es Salaam',
        ]);

        Address::query()->create([
            'user_id' => $user->id,
            'address' => '45 Market Road',
            'city' => 'Dar es Salaam',
            'set_default' => true,
        ]);

        $this->createOrder($user, ['total' => 250.50]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()
            ->assertSee('Amina Customer')
            ->assertSee('Profile info')
            ->assertSee('Personal information')
            ->assertSee('Purchase records')
            ->assertSee('45 Market Road')
            ->assertSee('250.50')
            ->assertSee('Saved addresses');
    }

    public function test_admin_profile_exposes_the_staff_record(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/profile');

        $response->assertOk()
            ->assertSee('Administrator')
            ->assertSee('Staff access')
            ->assertSee('Open admin dashboard')
            ->assertSee(route('admin.dashboard'), false);
    }

    public function test_administrator_accounts_cannot_be_deleted_from_the_profile(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->delete('/profile', ['password' => 'password'])
            ->assertForbidden();

        $this->assertModelExists($admin);
    }

    public function test_administrator_profile_shows_a_protected_account_notice(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/profile?tab=security')
            ->assertOk()
            ->assertSee('Protected account')
            ->assertDontSee('Delete account');
    }

    public function test_profile_update_tab_renders_the_contact_record_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile?tab=update');

        $response->assertOk()
            ->assertSee('Profile update')
            ->assertSee('name="phone"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="postal_code"', false)
            ->assertSee('name="country"', false)
            ->assertDontSee('Personal information');
    }

    public function test_profile_information_and_contact_records_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+255 712 345 678',
            'address' => '9 Solar Avenue',
            'city' => 'Arusha',
            'state' => 'Arusha',
            'postal_code' => '23103',
            'country' => 'Tanzania',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('+255 712 345 678', $user->phone);
        $this->assertSame('9 Solar Avenue', $user->address);
        $this->assertSame('Arusha', $user->city);
        $this->assertSame('23103', $user->postal_code);
        $this->assertSame('Tanzania', $user->country);
        $this->assertNull($user->email_verified_at);
        $this->assertSame(0, $user->verification_status);
    }

    public function test_optional_contact_fields_are_stored_as_null_when_cleared(): void
    {
        $user = User::factory()->create([
            'phone' => '+255 700 111 222',
            'address' => '1 Old Road',
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '',
            'address' => '',
        ]);

        $response->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertNull($user->phone);
        $this->assertNull($user->address);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_the_security_panel_renders_the_password_and_deletion_forms(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile?tab=security')
            ->assertOk()
            ->assertSee('Current password')
            ->assertSee('Delete account')
            ->assertDontSee('Personal information');
    }

    public function test_the_security_panel_opens_after_a_password_update(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['status' => 'password-updated'])
            ->get('/profile')
            ->assertOk()
            ->assertSee('Current password')
            ->assertDontSee('Personal information');
    }

    public function test_the_account_deletion_panel_reports_a_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->delete('/profile', ['password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/profile')
            ->delete('/profile', ['password' => 'wrong-password']);

        $response->assertSessionHasErrorsIn('userDeletion', 'password')->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createOrder(User $user, array $attributes = []): Order
    {
        return Order::query()->create(array_merge([
            'order_number' => 'ORD-'.fake()->unique()->numerify('######'),
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'currency' => 'USD',
            'subtotal' => 100,
            'total' => 100,
            'shipping_address' => '12 Solar Street',
        ], $attributes));
    }
}
