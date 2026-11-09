<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Customers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomersTest extends CatalogTestCase
{
    public function test_admin_can_create_update_suspend_and_restore_a_customer(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Customers::class)
            ->call('create')
            ->set('name', 'Amina Customer')
            ->set('email', 'amina@example.com')
            ->set('password', 'customer-password')
            ->set('phone', '+254700123456')
            ->set('address', '1 Market Street')
            ->set('country', 'Kenya')
            ->set('city', 'Nairobi')
            ->set('postalCode', '00100')
            ->call('save')
            ->assertHasNoErrors();

        $customer = User::query()->where('email', 'amina@example.com')->firstOrFail();
        $this->assertModelExists($customer);
        $this->assertTrue(Hash::check('customer-password', $customer->password));
        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'phone' => '+254700123456',
            'address' => '1 Market Street',
            'country' => 'Kenya',
            'city' => 'Nairobi',
            'postal_code' => '00100',
            'banned' => false,
        ]);

        Livewire::test(Customers::class)
            ->call('edit', $customer->id)
            ->set('name', 'Amina Updated')
            ->set('email', 'amina.updated@example.com')
            ->set('password', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Amina Updated',
            'email' => 'amina.updated@example.com',
            'verification_status' => 0,
        ]);

        Livewire::test(Customers::class)
            ->call('toggleSuspension', $customer->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $customer->id, 'banned' => true]);
        $this->assertModelExists($customer);

        Livewire::test(Customers::class)
            ->call('toggleSuspension', $customer->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $customer->id, 'banned' => false]);
    }

    public function test_admin_can_create_update_set_default_and_delete_a_customer_address(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Customers::class)
            ->call('edit', $customer->id)
            ->call('createAddress')
            ->set('addressLine', '10 River Road')
            ->set('addressPhone', '+254711222333')
            ->set('addressPostalCode', '00100')
            ->set('addressIsDefault', true)
            ->call('saveAddress')
            ->assertHasNoErrors();

        $address = $customer->addresses()->where('address', '10 River Road')->firstOrFail();
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'user_id' => $customer->id,
            'address' => '10 River Road',
            'set_default' => true,
        ]);

        Livewire::test(Customers::class)
            ->call('edit', $customer->id)
            ->call('createAddress')
            ->set('addressLine', '25 Hill Lane')
            ->set('addressIsDefault', true)
            ->call('saveAddress')
            ->assertHasNoErrors();

        $replacementAddress = $customer->addresses()->where('address', '25 Hill Lane')->firstOrFail();
        $this->assertDatabaseHas('addresses', ['id' => $address->id, 'set_default' => false]);
        $this->assertDatabaseHas('addresses', ['id' => $replacementAddress->id, 'set_default' => true]);

        Livewire::test(Customers::class)
            ->call('edit', $customer->id)
            ->call('editAddress', $replacementAddress->id)
            ->set('addressLine', '26 Hill Lane')
            ->call('saveAddress')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('addresses', ['id' => $replacementAddress->id, 'address' => '26 Hill Lane']);

        Livewire::test(Customers::class)
            ->call('edit', $customer->id)
            ->call('deleteAddress', $replacementAddress->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('addresses', ['id' => $replacementAddress->id]);
    }

    public function test_customer_page_is_admin_only(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.customers'))
            ->assertOk()
            ->assertSee('Customers');

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.customers'))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_invoke_customer_actions(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        try {
            (new Customers)->create();
            $this->fail('A non-admin must not be allowed to create customers.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_customer_listing_excludes_administrators_from_both_admin_systems(): void
    {
        User::factory()->create([
            'name' => 'Legacy Administrator',
            'email' => 'legacy-admin@example.com',
            'is_admin' => true,
        ]);
        $roleAdministrator = User::factory()->create([
            'name' => 'Role Administrator',
            'email' => 'role-admin@example.com',
            'is_admin' => false,
        ]);
        $adminRole = Role::query()->create(['name' => 'admin', 'guard_name' => 'web']);
        $roleAdministrator->assignRole($adminRole);
        User::factory()->create([
            'name' => 'Visible Customer',
            'email' => 'customer@example.com',
            'is_admin' => false,
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Customers::class)
            ->assertSee('customer@example.com')
            ->assertDontSee('legacy-admin@example.com')
            ->assertDontSee('role-admin@example.com');
    }

    public function test_customer_creation_rejects_duplicate_email_and_short_password(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Customers::class)
            ->call('create')
            ->set('name', 'Duplicate Customer')
            ->set('email', 'existing@example.com')
            ->set('password', 'short')
            ->call('save')
            ->assertHasErrors(['email', 'password'])
            ->assertSee('The email has already been taken.')
            ->assertSee('The password field must be at least 8 characters.');

        $this->assertDatabaseMissing('users', ['name' => 'Duplicate Customer']);
    }

    public function test_customer_address_cannot_be_changed_by_another_customer_id(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $otherCustomer = User::factory()->create(['is_admin' => false]);
        $otherAddress = $otherCustomer->addresses()->create(['address' => 'Private address']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Customers::class)
            ->call('edit', $customer->id)
            ->call('deleteAddress', $otherAddress->id)
            ->assertStatus(404);

        $this->assertModelExists($otherAddress);
    }
}
