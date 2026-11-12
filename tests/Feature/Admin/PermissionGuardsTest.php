<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Brands;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\CmsMenus;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Inventory;
use App\Livewire\Admin\OnlineUsers;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\Reports;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Units;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
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

    public function test_livewire_admin_modules_enforce_view_and_panel_permissions(): void
    {
        $this->actingAs($this->staffWith(['cms.manage']));

        foreach ([
            Brands::class,
            Categories::class,
            Inventory::class,
            Units::class,
            Products::class,
            Reports::class,
        ] as $component) {
            Livewire::test($component)->assertForbidden();
        }

        $this->actingAs($this->staffWith(['settings.view']));

        Livewire::test(Settings::class)
            ->set('settingType', 'shop_name')
            ->assertForbidden();

        $customer = User::factory()->create(['is_admin' => false]);
        $this->actingAs($customer);

        Livewire::test(Dashboard::class)->assertForbidden();
        Livewire::test(OnlineUsers::class)->assertForbidden();
    }

    public function test_admin_header_hides_settings_link_without_settings_view_permission(): void
    {
        $this->actingAs($this->staffWith(['cms.manage']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('href="'.route('admin.settings').'"', false);

        $this->actingAs($this->staffWith(['cms.manage', 'settings.view']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('admin.settings').'"', false);
    }

    public function test_media_picker_buttons_and_cross_links_respect_permissions(): void
    {
        $this->actingAs($this->staffWith(['brands.view', 'brands.manage']));

        Livewire::test(Brands::class)
            ->call('create')
            ->assertDontSee('Choose from media');

        $this->actingAs($this->staffWith(['brands.view', 'brands.manage', 'media.view']));

        Livewire::test(Brands::class)
            ->call('create')
            ->assertSee('Choose from media');

        $this->actingAs($this->staffWith(['categories.view', 'categories.manage']));

        Livewire::test(Categories::class)
            ->call('create')
            ->assertDontSee('Choose banner from media')
            ->assertDontSee('Choose icon from media')
            ->assertDontSee('Choose cover from media');

        $this->actingAs($this->staffWith(['products.view', 'products.manage']));

        Livewire::test(Products::class)
            ->call('create')
            ->assertDontSee('Choose images from media')
            ->assertDontSee('Manage categories');

        $this->actingAs($this->staffWith(['cms.manage']));

        Livewire::test(CmsMenus::class)
            ->assertDontSee('Choose from Media');
    }

    public function test_dashboard_hides_unpermitted_metrics_and_management_ctas(): void
    {
        User::factory()->create([
            'name' => 'Private Customer',
            'email' => 'private-customer@example.com',
            'is_admin' => false,
        ]);

        $this->actingAs($this->staffWith(['cms.manage']));

        Livewire::test(Dashboard::class)
            ->assertDontSee('Newest customers')
            ->assertDontSee('private-customer@example.com')
            ->assertDontSee('Low stock')
            ->assertDontSee('Your catalogue is empty')
            ->assertDontSee('Add a product')
            ->assertDontSee('Adjust stock')
            ->assertDontSee('Review orders')
            ->assertDontSee('View customers')
            ->assertDontSee('Store settings')
            ->assertSee('Edit CMS pages');

        $this->actingAs($this->staffWith(['products.view']));

        Livewire::test(Dashboard::class)
            ->assertSee('View products')
            ->assertDontSee('Add a product')
            ->assertDontSee('Newest customers');
    }

    public function test_cms_builder_routes_forbid_users_without_cms_manage_permission(): void
    {
        $this->actingAs($this->staffWith(['products.view']))
            ->get(route('admin.cms.media-library'))
            ->assertForbidden();
    }

    public function test_role_seeder_syncs_all_catalog_permissions_to_admin_role(): void
    {
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->syncPermissions(['brands.view']);

        $this->seed(RoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $catalogCount = collect(config('permissions.groups'))
            ->flatMap(static fn (array $group): array => array_keys($group))
            ->count();

        $this->assertSame($catalogCount, Permission::query()->count());
        $this->assertSame($catalogCount, $adminRole->fresh()->permissions()->count());
        $this->assertDatabaseHas('roles', ['name' => 'customer', 'guard_name' => 'web']);
    }
}
