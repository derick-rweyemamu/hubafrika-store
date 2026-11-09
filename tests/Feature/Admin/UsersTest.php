<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Users;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UsersTest extends CatalogTestCase
{
    public function test_admin_can_create_update_suspend_and_delete_a_user(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Users::class)
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

        $user = User::query()->where('email', 'amina@example.com')->firstOrFail();
        $this->assertModelExists($user);
        $this->assertTrue(Hash::check('customer-password', $user->password));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_admin' => false,
            'phone' => '+254700123456',
            'address' => '1 Market Street',
            'country' => 'Kenya',
            'city' => 'Nairobi',
            'postal_code' => '00100',
            'banned' => false,
        ]);

        Livewire::test(Users::class)
            ->call('edit', $user->id)
            ->set('name', 'Amina Updated')
            ->set('email', 'amina.updated@example.com')
            ->set('password', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Amina Updated',
            'email' => 'amina.updated@example.com',
            'verification_status' => 0,
        ]);

        Livewire::test(Users::class)
            ->call('toggleSuspension', $user->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'banned' => true]);

        Livewire::test(Users::class)
            ->call('toggleSuspension', $user->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'banned' => false]);

        $user->addresses()->create(['address' => '9 Solar Avenue']);

        Livewire::test(Users::class)
            ->call('delete', $user->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('addresses', ['user_id' => $user->id]);
    }

    public function test_user_deletion_keeps_the_order_history(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $order = $user->orders()->create([
            'order_number' => 'ORD-000111',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'currency' => 'USD',
            'subtotal' => 50,
            'total' => 50,
            'shipping_address' => '12 Solar Street',
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Users::class)
            ->call('delete', $user->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_users_listing_excludes_administrator_accounts(): void
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
        $roleAdministrator->assignRole(Role::query()->create(['name' => 'admin', 'guard_name' => 'web']));
        User::factory()->create([
            'name' => 'Visible User',
            'email' => 'user@example.com',
            'is_admin' => false,
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Users::class)
            ->assertSee('user@example.com')
            ->assertDontSee('legacy-admin@example.com')
            ->assertDontSee('role-admin@example.com');
    }

    public function test_admin_can_assign_a_role_to_a_user(): void
    {
        Role::query()->create(['name' => 'Manager', 'guard_name' => 'web']);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Users::class)
            ->call('create')
            ->set('name', 'Staff One')
            ->set('email', 'staff@example.com')
            ->set('password', 'staff-password')
            ->set('role', 'Manager')
            ->call('save')
            ->assertHasNoErrors();

        $staff = User::query()->where('email', 'staff@example.com')->firstOrFail();

        $this->assertTrue($staff->hasRole('Manager'));
    }

    public function test_users_page_is_admin_only(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Users')
            ->assertSee('Add user');

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.users'))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_invoke_user_actions(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        try {
            (new Users)->create();
            $this->fail('A non-admin must not be allowed to create users.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_administrator_accounts_cannot_be_deleted_from_the_users_screen(): void
    {
        $this->actingAs($admin = User::factory()->create(['is_admin' => true]));
        $roleAdministrator = User::factory()->create(['is_admin' => false]);
        $roleAdministrator->assignRole(Role::query()->create(['name' => 'admin', 'guard_name' => 'web']));

        foreach ([$admin->id, $roleAdministrator->id] as $protectedId) {
            try {
                (new Users)->delete($protectedId);
                $this->fail('The administrator account must not be deletable.');
            } catch (HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
            }
        }

        $this->assertModelExists($admin);
        $this->assertModelExists($roleAdministrator);
    }

    public function test_administrator_accounts_cannot_be_edited_from_the_users_screen(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(Users::class)
            ->call('edit', $admin->id)
            ->assertStatus(404);
    }

    public function test_user_creation_rejects_duplicate_email_and_short_password(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Users::class)
            ->call('create')
            ->set('name', 'Duplicate User')
            ->set('email', 'existing@example.com')
            ->set('password', 'short')
            ->call('save')
            ->assertHasErrors(['email', 'password'])
            ->assertSee('The email has already been taken.')
            ->assertSee('The password field must be at least 8 characters.');

        $this->assertDatabaseMissing('users', ['name' => 'Duplicate User']);
    }

    public function test_users_listing_can_be_searched_and_filtered_by_status(): void
    {
        User::factory()->create(['name' => 'Amina Active', 'email' => 'amina@example.com']);
        User::factory()->create(['name' => 'Bakari Unverified', 'email' => 'bakari@example.com', 'email_verified_at' => null]);
        User::factory()->create(['name' => 'Chidi Suspended', 'email' => 'chidi@example.com', 'banned' => true]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Users::class)
            ->set('search', 'bakari')
            ->assertSee('Bakari Unverified')
            ->assertDontSee('Amina Active')
            ->set('search', '')
            ->set('status', 'suspended')
            ->assertSee('Chidi Suspended')
            ->assertDontSee('Amina Active')
            ->set('status', 'unverified')
            ->assertSee('Bakari Unverified')
            ->assertDontSee('Chidi Suspended');
    }
}
