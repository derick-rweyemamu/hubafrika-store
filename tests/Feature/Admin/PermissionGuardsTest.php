<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Brands;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\Settings;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionGuardsTest extends CatalogTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function staffWith(array $permissions): User
    {
        $role = Role::findOrCreate('Limited staff', 'web');
        $role->syncPermissions($permissions);

        $staff = User::factory()->create(['is_admin' => false]);
        $staff->assignRole($role);

        return $staff;
    }

    public function test_a_view_only_user_sees_the_catalog_without_management_controls(): void
    {
        Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);

        $this->actingAs($this->staffWith(['brands.view']));

        Livewire::test(Brands::class)
            ->assertOk()
            ->assertSee('View only')
            ->assertDontSee('Add brand');
    }

    public function test_a_view_only_user_cannot_open_the_create_form(): void
    {
        $this->actingAs($this->staffWith(['brands.view']));

        Livewire::test(Brands::class)
            ->call('create')
            ->assertForbidden();

        $this->assertDatabaseCount('brands', 0);
    }

    public function test_a_view_only_user_cannot_delete_a_record(): void
    {
        $brand = Brand::query()->create(['name' => 'Acme', 'slug' => 'acme']);

        $this->actingAs($this->staffWith(['brands.view']));

        Livewire::test(Brands::class)
            ->call('delete', $brand->id)
            ->assertForbidden();

        $this->assertModelExists($brand);
    }

    public function test_a_view_only_user_can_still_sort_the_product_list(): void
    {
        $this->actingAs($this->staffWith(['products.view']));

        Livewire::test(Products::class)
            ->call('sortBy', 'unit_price')
            ->assertSet('sortField', 'unit_price')
            ->assertSet('sortDirection', 'asc');

        Livewire::test(Products::class)
            ->call('create')
            ->assertForbidden();
    }

    public function test_a_settings_reader_can_switch_tabs_but_not_change_a_setting(): void
    {
        $this->actingAs($this->staffWith(['settings.view']));

        Livewire::test(Settings::class)
            ->assertOk()
            ->assertSee('View only — changing these settings needs the')
            ->call('selectTab', 'admin-design')
            ->assertSet('activeTab', 'admin-design');

        Livewire::test(Settings::class)
            ->call('toggleMaintenanceMode')
            ->assertForbidden();

        Livewire::test(Settings::class)
            ->call('edit', 'shop_name')
            ->assertForbidden();
    }

    public function test_a_settings_reader_cannot_flip_a_shipping_or_payment_switch(): void
    {
        BusinessSetting::query()->create([
            'type' => 'free_shipping',
            'value' => '0',
            'lang' => null,
        ]);

        $this->actingAs($this->staffWith(['settings.view']));

        Livewire::test(Settings::class)
            ->call('selectTab', 'shipping-checkout')
            ->assertSee('Free shipping')
            ->assertDontSee('Toggle Free shipping')
            ->call('toggleBoolean', 'free_shipping')
            ->assertForbidden();

        $this->assertDatabaseHas('business_settings', [
            'type' => 'free_shipping',
            'value' => '0',
        ]);
    }

    public function test_a_settings_manager_can_still_change_settings(): void
    {
        BusinessSetting::query()->create([
            'type' => 'maintenance_mode',
            'value' => '0',
            'lang' => null,
        ]);

        $this->actingAs($this->staffWith(['settings.view', 'settings.manage']));

        Livewire::test(Settings::class)
            ->assertOk()
            ->assertDontSee('View only — changing these settings needs the')
            ->call('toggleMaintenanceMode')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('business_settings', [
            'type' => 'maintenance_mode',
            'value' => '1',
        ]);
    }
}
