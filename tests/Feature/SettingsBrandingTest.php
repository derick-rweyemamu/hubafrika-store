<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings;
use App\Models\BusinessSetting;
use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsBrandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The migrations own the base schema, so only build it by hand when
        // this class runs against a database that has none.
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->unique();
            $table->longText('value')->nullable();
            $table->string('lang')->nullable();
            $table->timestamps();
        });
    }

    /**
     * The landing page resolves the CMS page with the "home" slug, so one has
     * to exist before the storefront header can be rendered.
     */
    private function seedHomePage(): void
    {
        CmsPage::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => json_encode([
                'version' => 1,
                'published' => true,
                'blocks' => [],
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    public function test_user_with_admin_role_can_access_admin_settings(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));

        $this->actingAs($user)
            ->get(route('admin.settings'))
            ->assertOk();
    }

    public function test_user_without_admin_role_or_legacy_flag_is_forbidden_from_admin_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.settings'))
            ->assertForbidden();
    }

    public function test_legacy_admin_flag_still_allows_admin_settings_access(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.settings'))
            ->assertOk();
    }

    public function test_admin_can_upload_and_replace_business_logo_without_deleting_its_setting(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->seedHomePage();

        Livewire::test(Settings::class)
            ->assertSee('Brand assets')
            ->assertSee('Business logo')
            ->assertSee('Dark-mode logo')
            ->assertSee('Favicon')
            ->assertSee('Upload image')
            ->assertDontSee('Delete')
            ->assertDontSee('Add setting')
            ->call('edit', 'business_logo')
            ->assertSee('Choose image')
            ->set('upload', UploadedFile::fake()->image('logo.png', 120, 60))
            ->call('save')
            ->assertRedirect(route('admin.settings'));

        $setting = BusinessSetting::query()->where('type', 'business_logo')->firstOrFail();
        $this->assertNull($setting->value);

        $firstLogo = $setting->getFirstMedia('branding');
        $this->assertNotNull($firstLogo);
        $this->assertSame('logo.png', $firstLogo->file_name);
        $this->assertSame('branding', $firstLogo->library());
        $this->assertSame('business-logo', $firstLogo->folder);
        Storage::disk('public')->assertExists($firstLogo->getPathRelativeToRoot());
        $firstLogoId = $firstLogo->getKey();
        $firstLogoPath = $firstLogo->getPathRelativeToRoot();

        $logoUrl = $firstLogo->getUrl();
        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertSee($logoUrl, false);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee($logoUrl, false);

        Livewire::test(Settings::class)
            ->call('edit', 'business_logo')
            ->set('upload', UploadedFile::fake()->image('replacement.png', 120, 60))
            ->call('save')
            ->assertRedirect(route('admin.settings'));

        $replacementLogo = BusinessSetting::query()
            ->where('type', 'business_logo')
            ->firstOrFail()
            ->getFirstMedia('branding');
        $this->assertNotNull($replacementLogo);
        $this->assertSame('replacement.png', $replacementLogo->file_name);
        $this->assertNotSame($firstLogoId, $replacementLogo->getKey());
        Storage::disk('public')->assertMissing($firstLogoPath);
        Storage::disk('public')->assertExists($replacementLogo->getPathRelativeToRoot());
        $this->assertDatabaseHas('business_settings', [
            'type' => 'business_logo',
            'value' => null,
        ]);
    }

    public function test_invalid_image_upload_does_not_create_a_business_logo_setting(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Settings::class)
            ->call('edit', 'business_logo')
            ->set('upload', UploadedFile::fake()->createWithContent('logo.svg', '<svg></svg>'))
            ->call('save')
            ->assertHasErrors(['upload']);

        $this->assertDatabaseMissing('business_settings', ['type' => 'business_logo']);
        Storage::disk('public')->assertDirectoryEmpty('business-branding');
    }

    public function test_favicon_upload_requires_a_square_png(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(Settings::class)
            ->call('edit', 'favicon')
            ->set('upload', UploadedFile::fake()->image('favicon.png', 32, 16))
            ->call('save')
            ->assertHasErrors(['upload']);

        $this->assertDatabaseMissing('business_settings', ['type' => 'favicon']);
    }

    public function test_admin_can_upload_a_favicon_that_is_rendered_in_the_page_head(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->seedHomePage();

        Livewire::test(Settings::class)
            ->call('edit', 'favicon')
            ->set('upload', UploadedFile::fake()->image('favicon.png', 32, 32))
            ->call('save')
            ->assertRedirect(route('admin.settings'));

        $favicon = BusinessSetting::query()
            ->where('type', 'favicon')
            ->firstOrFail()
            ->getFirstMedia('branding');
        $this->assertNotNull($favicon);
        $this->assertSame('favicon.png', $favicon->file_name);
        Storage::disk('public')->assertExists($favicon->getPathRelativeToRoot());

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($favicon->getUrl(), false);
    }

    public function test_guest_login_page_renders_with_business_asset_data(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Log in');
    }

    public function test_existing_settings_can_be_edited_but_not_created_or_deleted(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $setting = BusinessSetting::create([
            'type' => 'site_name',
            'value' => 'My Shop',
            'lang' => null,
        ]);
        BusinessSetting::create([
            'type' => 'backend_palette_1',
            'value' => json_encode([
                'name' => 'Indigo',
                'primary' => '#4F46E5',
                'secondary' => '#64748B',
                'accent' => '#F59E0B',
                'bg' => '#F9FAFB',
                'surface' => '#FFFFFF',
                'text' => '#111827',
                'bg_dark' => '#030712',
                'surface_dark' => '#111827',
                'text_dark' => '#F3F4F6',
            ], JSON_THROW_ON_ERROR),
            'lang' => null,
        ]);
        $this->actingAs($user);

        Livewire::test(Settings::class)
            ->assertSee('Edit Site name')
            ->assertDontSee('Delete')
            ->call('selectTab', 'admin-design')
            ->assertSee('Edit Indigo')
            ->assertDontSee('Delete')
            ->set('settingType', 'site_motto')
            ->set('value', 'Forged new setting')
            ->call('save')
            ->assertForbidden();

        Livewire::test(Settings::class)
            ->call('edit', 'site_name')
            ->set('value', 'Updated Shop')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('business_settings', [
            'id' => $setting->id,
            'type' => 'site_name',
            'value' => 'Updated Shop',
        ]);
        $this->assertDatabaseMissing('business_settings', ['type' => 'site_motto']);
    }

    public function test_backend_font_settings_render_as_editable_font_preview_cards(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        BusinessSetting::create([
            'type' => 'font_backend_primary_header',
            'value' => 'Poppins',
            'lang' => null,
        ]);
        BusinessSetting::create([
            'type' => 'font_backend_sub_header',
            'value' => 'Inter',
            'lang' => null,
        ]);
        BusinessSetting::create([
            'type' => 'font_backend_accent',
            'value' => 'Playfair Display',
            'lang' => null,
        ]);
        BusinessSetting::create([
            'type' => 'font_backend_body',
            'value' => 'Inter',
            'lang' => null,
        ]);

        Livewire::test(Settings::class)
            ->call('selectTab', 'admin-design')
            ->assertSee('font-setting-card-font_backend_primary_header', false)
            ->assertSee('font-setting-card-font_backend_body', false)
            ->assertSee('Page and section titles')
            ->assertSee('Highlights and quotes')
            ->assertSee('The quick brown fox jumps over the lazy dog.')
            ->assertSee('style="font-family: Poppins, sans-serif"', false)
            ->assertDontSee('min-w-full divide-y')
            ->call('edit', 'font_backend_primary_header')
            ->assertSet('value', 'Poppins');
    }

    public function test_frontend_font_sizes_render_as_editable_size_preview_cards(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        BusinessSetting::create([
            'type' => 'font_size_base',
            'value' => '1rem',
            'lang' => null,
        ]);
        BusinessSetting::create([
            'type' => 'font_size_heading',
            'value' => '2rem',
            'lang' => null,
        ]);
        BusinessSetting::create([
            'type' => 'font_size_sub',
            'value' => '1.25rem',
            'lang' => null,
        ]);
        BusinessSetting::create([
            'type' => 'font_size_accent',
            'value' => '1.5rem',
            'lang' => null,
        ]);
        BusinessSetting::create([
            'type' => 'font_size_small',
            'value' => '0.875rem',
            'lang' => null,
        ]);

        Livewire::test(Settings::class)
            ->call('selectTab', 'storefront-design')
            ->assertSee('font-size-card-font_size_base', false)
            ->assertSee('font-size-card-font_size_heading', false)
            ->assertSee('Default paragraph and interface text')
            ->assertSee('Page and section titles')
            ->assertSee('style="font-size: 2rem"', false)
            ->assertDontSee('min-w-full divide-y')
            ->call('edit', 'font_size_heading')
            ->assertSet('value', '2rem');
    }

    public function test_general_settings_render_as_editable_cards_instead_of_a_table(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        BusinessSetting::create([
            'type' => 'site_name',
            'value' => 'Hubafrika Shop',
            'lang' => null,
        ]);
        BusinessSetting::create([
            'type' => 'maintenance_mode',
            'value' => '0',
            'lang' => null,
        ]);

        Livewire::test(Settings::class)
            ->assertSee('general-setting-card-site_name', false)
            ->assertSee('Hubafrika Shop')
            ->assertSee('aria-checked="false"', false)
            ->assertSee('Store is open to customers')
            ->assertDontSee('min-w-full divide-y')
            ->assertDontSee('general-setting-card-maintenance_mode', false)
            ->call('edit', 'site_name')
            ->assertSet('value', 'Hubafrika Shop');
    }

    public function test_admin_can_toggle_maintenance_mode_from_the_general_tab_header(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        BusinessSetting::create([
            'type' => 'maintenance_mode',
            'value' => '0',
            'lang' => null,
        ]);

        Livewire::test(Settings::class)
            ->assertSee('Maintenance mode')
            ->assertSee('aria-checked="false"', false)
            ->assertDontSee('Store status')
            ->call('toggleMaintenanceMode')
            ->assertSee('aria-checked="true"', false)
            ->assertSee('Store is unavailable to customers')
            ->assertSee('role="status"', false)
            ->assertSee('Maintenance mode enabled.')
            ->assertSee('fixed inset-x-3 top-3', false)
            ->assertDontSee('settings-toast-progress', false);

        $this->assertDatabaseHas('business_settings', [
            'type' => 'maintenance_mode',
            'value' => '1',
        ]);
    }

    public function test_shipping_and_payment_settings_render_as_cards_instead_of_a_table(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        foreach ([
            'flat_rate_shipping_cost' => '5000',
            'free_shipping' => '0',
            'cash_on_delivery' => '1',
            'lipa_enabled' => '1',
            'lipa_number' => '0754000111',
            'lipa_business_name' => '',
            'lipa_webhook_secret' => 'callback-secret-value',
        ] as $type => $value) {
            BusinessSetting::create(['type' => $type, 'value' => $value, 'lang' => null]);
        }

        Livewire::test(Settings::class)
            ->call('selectTab', 'shipping-checkout')
            ->assertSee('commerce-setting-card-flat_rate_shipping_cost', false)
            ->assertSee('commerce-setting-card-free_shipping', false)
            ->assertSee('commerce-setting-card-cash_on_delivery', false)
            ->assertSee('commerce-setting-card-lipa_enabled', false)
            ->assertSee('commerce-setting-card-lipa_webhook_secret', false)
            ->assertSee('Flat-rate shipping cost')
            ->assertSee('Free shipping')
            ->assertSee('Cash on delivery')
            ->assertSee('Added once per order at checkout.')
            ->assertSee('aria-checked="true"', false)
            ->assertSee('aria-checked="false"', false)
            ->assertDontSee('callback-secret-value')
            ->assertSee('••••••••')
            ->assertSee('Not set')
            ->assertDontSee('min-w-full divide-y')
            ->call('edit', 'flat_rate_shipping_cost')
            ->assertSet('value', '5000');
    }

    public function test_admin_can_toggle_a_shipping_switch_without_leaving_the_tab(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        BusinessSetting::create([
            'type' => 'free_shipping',
            'value' => '0',
            'lang' => null,
        ]);

        Livewire::test(Settings::class)
            ->call('selectTab', 'shipping-checkout')
            ->assertSee('aria-checked="false"', false)
            ->call('toggleBoolean', 'free_shipping')
            ->assertSet('activeTab', 'shipping-checkout')
            ->assertSee('aria-checked="true"', false)
            ->assertSee('Free shipping enabled.');

        $this->assertDatabaseHas('business_settings', [
            'type' => 'free_shipping',
            'value' => '1',
        ]);

        Livewire::test(Settings::class)
            ->call('toggleBoolean', 'free_shipping')
            ->assertSee('aria-checked="false"', false)
            ->assertSee('Free shipping disabled.');

        $this->assertDatabaseHas('business_settings', [
            'type' => 'free_shipping',
            'value' => '0',
        ]);
    }

    public function test_switches_only_accept_a_known_boolean_setting(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        BusinessSetting::create([
            'type' => 'flat_rate_shipping_cost',
            'value' => '5000',
            'lang' => null,
        ]);

        Livewire::test(Settings::class)
            ->call('toggleBoolean', 'flat_rate_shipping_cost')
            ->assertNotFound();

        Livewire::test(Settings::class)
            ->call('toggleBoolean', 'unknown_setting')
            ->assertNotFound();

        $this->assertDatabaseHas('business_settings', [
            'type' => 'flat_rate_shipping_cost',
            'value' => '5000',
        ]);
    }
}
