<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Roles;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesTest extends CatalogTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_create_a_role_with_permissions(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Roles::class)
            ->call('create')
            ->set('name', 'Shop assistant')
            ->set('selectedPermissions', ['products.view', 'orders.view'])
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::query()->where('name', 'Shop assistant')->firstOrFail();

        $this->assertSame(
            ['orders.view', 'products.view'],
            $role->permissions->pluck('name')->sort()->values()->all(),
        );
        $this->assertDatabaseCount('roles', 1);
    }

    public function test_admin_can_change_which_permissions_a_role_grants(): void
    {
        $role = Role::findOrCreate('Manager', 'web');
        $role->syncPermissions(['orders.view']);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Roles::class)
            ->call('edit', $role->id)
            ->assertSet('name', 'Manager')
            ->assertSet('selectedPermissions', ['orders.view'])
            ->set('selectedPermissions', ['inventory.manage', 'inventory.view'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(
            ['inventory.manage', 'inventory.view'],
            $role->refresh()->permissions->pluck('name')->sort()->values()->all(),
        );
    }

    public function test_toggle_group_selects_and_clears_a_whole_group(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Roles::class)
            ->call('create')
            ->call('toggleGroup', 'Content')
            ->assertSet('selectedPermissions', ['cms.manage'])
            ->call('toggleGroup', 'Content')
            ->assertSet('selectedPermissions', []);
    }

    public function test_a_role_name_must_be_unique(): void
    {
        Role::findOrCreate('Manager', 'web');

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Roles::class)
            ->call('create')
            ->set('name', 'Manager')
            ->call('save')
            ->assertHasErrors('name');

        $this->assertSame(1, Role::query()->where('name', 'Manager')->count());
    }

    public function test_an_unassigned_role_can_be_deleted(): void
    {
        $role = Role::findOrCreate('Unused', 'web');

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Roles::class)
            ->call('delete', $role->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_a_role_still_assigned_to_an_account_cannot_be_deleted(): void
    {
        $role = Role::findOrCreate('Support', 'web');
        User::factory()->create(['is_admin' => false])->assignRole($role);

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Roles::class)
            ->call('delete', $role->id)
            ->assertSee('Move them to another role');

        $this->assertModelExists($role);
    }

    public function test_the_built_in_administrator_role_cannot_be_deleted(): void
    {
        $adminRole = Role::findOrCreate('admin', 'web');

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Roles::class)
            ->call('delete', $adminRole->id)
            ->assertSee('built in');

        $this->assertModelExists($adminRole);
    }

    public function test_a_staff_account_can_only_open_the_pages_its_role_grants(): void
    {
        $role = Role::findOrCreate('Content editor', 'web');
        $role->syncPermissions(['cms.manage']);

        $staff = User::factory()->create(['is_admin' => false]);
        $staff->assignRole($role);

        $this->actingAs($staff)->get(route('admin.cms.pages'))->assertOk();
        $this->actingAs($staff)->get(route('admin.products'))->assertForbidden();
    }

    public function test_an_account_without_any_permission_cannot_open_the_panel(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_the_roles_page_is_gated_by_its_permission(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.roles'))
            ->assertOk()
            ->assertSee('Bundle permissions into a role')
            ->assertSee('Add role');

        $role = Role::findOrCreate('Role reader', 'web');
        $role->syncPermissions(['roles.view']);

        $reader = User::factory()->create(['is_admin' => false]);
        $reader->assignRole($role);

        $this->actingAs($reader)->get(route('admin.roles'))->assertOk();

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.roles'))
            ->assertForbidden();
    }
}
