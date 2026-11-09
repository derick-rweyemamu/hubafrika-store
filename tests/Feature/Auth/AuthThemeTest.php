<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\PasswordField;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AuthThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->boolean('is_admin')->default(false);
                $table->boolean('banned')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('business_settings')) {
            Schema::create('business_settings', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('type')->unique();
                $table->longText('value')->nullable();
                $table->string('lang')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_auth_pages_use_the_palette_and_fonts_saved_in_the_admin_panel(): void
    {
        $this->storeSettings();

        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('--storefront-primary: #123456', false)
            ->assertSee('--storefront-secondary: #1A2B3C', false)
            ->assertSee('--storefront-accent: #0ABCDE', false)
            ->assertSee('font-family: Poppins, sans-serif', false)
            ->assertSee('Hubafrika Shop');
    }

    public function test_inactive_palettes_are_ignored_on_auth_pages(): void
    {
        $this->storeSettings();
        BusinessSetting::query()->where('type', 'frontend_palette_active')->update(['value' => '3']);

        BusinessSetting::create([
            'type' => 'frontend_palette_3',
            'value' => json_encode([
                'name' => 'Inactive palette',
                'primary' => '#654321',
                'secondary' => '#222222',
                'accent' => '#333333',
                'bg' => '#FFFFFF',
                'surface' => '#FFFFFF',
                'text' => '#111111',
                'bg_dark' => '#000000',
                'surface_dark' => '#111111',
                'text_dark' => '#FFFFFF',
            ]),
            'lang' => null,
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertSee('--storefront-primary: #654321', false)
            ->assertDontSee('--storefront-primary: #123456', false);
    }

    public function test_every_auth_screen_renders_with_the_themed_guest_layout(): void
    {
        $this->storeSettings();

        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
        $this->get('/reset-password/reset-token?email=customer%40example.com')->assertOk();

        $customer = User::factory()->unverified()->create();

        $this->actingAs($customer)->get('/verify-email')->assertOk();
        $this->actingAs($customer)->get('/confirm-password')->assertOk();
    }

    public function test_auth_fields_keep_their_names_and_stay_inside_the_post_forms(): void
    {
        $this->storeSettings();

        $this->get('/login')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="remember"', false)
            ->assertSee('action="'.route('login').'"', false);

        $this->get('/register')
            ->assertSee('name="name"', false)
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_password_field_toggles_between_masked_and_plain_text(): void
    {
        Livewire::test(PasswordField::class, ['name' => 'password', 'label' => 'Password'])
            ->assertSee('type="password"', false)
            ->assertSee('aria-pressed="false"', false)
            ->call('toggleVisibility')
            ->assertSet('visible', true)
            ->assertSee('type="text"', false)
            ->assertSee('aria-pressed="true"', false);
    }

    private function storeSettings(): void
    {
        foreach ([
            'site_name' => 'Hubafrika Shop',
            'frontend_palette_active' => '2',
            'font_frontend_body' => 'Poppins',
            'font_frontend_primary_header' => 'Playfair Display',
        ] as $type => $value) {
            BusinessSetting::create([
                'type' => $type,
                'value' => $value,
                'lang' => null,
            ]);
        }

        BusinessSetting::create([
            'type' => 'frontend_palette_2',
            'value' => json_encode([
                'name' => 'Active palette',
                'primary' => '#123456',
                'secondary' => '#1A2B3C',
                'accent' => '#0ABCDE',
                'bg' => '#FFFFFF',
                'surface' => '#FFFFFF',
                'text' => '#111111',
                'bg_dark' => '#000000',
                'surface_dark' => '#111111',
                'text_dark' => '#FFFFFF',
            ]),
            'lang' => null,
        ]);
    }
}
