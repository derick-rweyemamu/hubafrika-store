<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings;
use App\Livewire\Storefront\Checkout;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RecaptchaV3Test extends TestCase
{
    use RefreshDatabase;

    protected function enableRecaptcha(float $minScore = 0.5): void
    {
        foreach ([
            'recaptcha_enabled' => '1',
            'recaptcha_site_key' => 'test-site-key',
            'recaptcha_secret_key' => 'test-secret-key',
            'recaptcha_min_score' => (string) $minScore,
        ] as $type => $value) {
            BusinessSetting::query()->updateOrCreate(['type' => $type], ['value' => $value, 'lang' => null]);
        }
    }

    public function test_recaptcha_is_bypassed_when_disabled_or_unconfigured(): void
    {
        BusinessSetting::query()->updateOrCreate(['type' => 'recaptcha_enabled'], ['value' => '0']);

        $service = app(RecaptchaService::class);
        $this->assertFalse($service->isEnabled());
        $this->assertTrue($service->verify(null, 'register'));
    }

    public function test_recaptcha_service_validates_score_and_action(): void
    {
        $this->enableRecaptcha(0.5);
        $service = app(RecaptchaService::class);

        $this->assertTrue($service->isEnabled());
        $this->assertFalse($service->verify('', 'register'));

        Http::fake([
            RecaptchaService::VERIFY_URL => Http::sequence()
                ->push(['success' => true, 'score' => 0.9, 'action' => 'register'])
                ->push(['success' => true, 'score' => 0.2, 'action' => 'register'])
                ->push(['success' => true, 'score' => 0.9, 'action' => 'login'])
                ->push(['success' => false, 'error-codes' => ['invalid-input-response']]),
        ]);

        $this->assertTrue($service->verify('good-token', 'register'));
        $this->assertFalse($service->verify('low-score-token', 'register'));
        $this->assertFalse($service->verify('wrong-action-token', 'register'));
        $this->assertFalse($service->verify('bad-token', 'register'));
    }

    public function test_registration_is_protected_by_recaptcha_when_enabled(): void
    {
        $this->enableRecaptcha();

        Http::fake([
            RecaptchaService::VERIFY_URL => Http::sequence()
                ->push(['success' => true, 'score' => 0.1, 'action' => 'register'])
                ->push(['success' => true, 'score' => 0.8, 'action' => 'register']),
        ]);

        $blocked = $this->post('/register', [
            'name' => 'Bot User',
            'email' => 'bot@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'g-recaptcha-response' => 'low-score',
        ]);
        $blocked->assertSessionHasErrors('g-recaptcha-response');
        $this->assertDatabaseMissing('users', ['email' => 'bot@example.com']);

        $allowed = $this->post('/register', [
            'name' => 'Real User',
            'email' => 'real@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'g-recaptcha-response' => 'good-token',
        ]);
        $allowed->assertRedirect(route('home', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'real@example.com']);
    }

    public function test_login_and_password_reset_are_protected_by_recaptcha_when_enabled(): void
    {
        $this->enableRecaptcha();
        $user = User::factory()->create(['email' => 'shopper@example.com']);

        Http::fake([
            RecaptchaService::VERIFY_URL => Http::response(['success' => true, 'score' => 0.1, 'action' => 'login']),
        ]);

        $loginBlocked = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'low-score',
        ]);
        $loginBlocked->assertSessionHasErrors('g-recaptcha-response');
        $this->assertGuest();

        $resetBlocked = $this->post('/forgot-password', [
            'email' => $user->email,
            'g-recaptcha-response' => '',
        ]);
        $resetBlocked->assertSessionHasErrors('g-recaptcha-response');
    }

    public function test_newsletter_and_contact_forms_are_protected_by_recaptcha_when_enabled(): void
    {
        $this->enableRecaptcha();

        Http::fake([
            RecaptchaService::VERIFY_URL => Http::sequence()
                ->push(['success' => true, 'score' => 0.1, 'action' => 'newsletter'])
                ->push(['success' => true, 'score' => 0.9, 'action' => 'newsletter'])
                ->push(['success' => true, 'score' => 0.1, 'action' => 'contact'])
                ->push(['success' => true, 'score' => 0.8, 'action' => 'contact']),
        ]);

        $this->postJson(route('newsletter.subscribe'), [
            'email' => 'spam@example.com',
            'g-recaptcha-response' => 'low-score',
        ])->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');

        $this->postJson(route('newsletter.subscribe'), [
            'email' => 'human@example.com',
            'g-recaptcha-response' => 'valid-token',
        ])->assertOk()->assertJson(['success' => true]);

        $this->postJson(route('contact.submit'), [
            'name' => 'Spammer',
            'email' => 'spam@example.com',
            'message' => 'Buy cheap links',
            'g-recaptcha-response' => 'low-score',
        ])->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');

        $this->postJson(route('contact.submit'), [
            'name' => 'Amina',
            'email' => 'amina@example.com',
            'subject' => 'Solar Kit',
            'message' => 'Please share a quote.',
            'g-recaptcha-response' => 'valid-token',
        ])->assertOk()->assertJson(['success' => true]);
    }

    public function test_checkout_is_protected_by_recaptcha_when_enabled(): void
    {
        $this->enableRecaptcha();
        BusinessSetting::query()->updateOrCreate(['type' => 'currency_code'], ['value' => 'TZS']);
        BusinessSetting::query()->updateOrCreate(['type' => 'cash_on_delivery'], ['value' => '1']);

        Category::query()->forceCreate(['id' => 1, 'name' => 'Solar', 'slug' => 'solar']);
        $product = Product::query()->create([
            'name' => 'Solar Panel',
            'slug' => 'solar-panel',
            'category_id' => 1,
            'unit_price' => 100000,
            'current_stock' => 10,
            'published' => true,
            'approved' => true,
        ]);

        $customer = User::factory()->create(['is_admin' => false]);
        $customer->carts()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 0,
            'tax' => 0,
        ]);

        Http::fake([
            RecaptchaService::VERIFY_URL => Http::sequence()
                ->push(['success' => true, 'score' => 0.1, 'action' => 'checkout'])
                ->push(['success' => true, 'score' => 0.9, 'action' => 'checkout']),
        ]);

        $this->actingAs($customer);

        Livewire::test(Checkout::class)
            ->set('shippingAddress', 'Arusha')
            ->call('placeOrder', 'low-score')
            ->assertHasErrors('checkout');

        Livewire::test(Checkout::class)
            ->set('shippingAddress', 'Arusha')
            ->call('placeOrder', 'valid-token')
            ->assertHasNoErrors();
    }

    public function test_admin_can_view_and_update_recaptcha_settings_in_dashboard(): void
    {
        $this->enableRecaptcha(0.5);
        Permission::findOrCreate('settings.view', 'web');
        Permission::findOrCreate('settings.manage', 'web');
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->givePermissionTo(['settings.view', 'settings.manage']);

        $this->actingAs($admin);

        Livewire::test(Settings::class)
            ->call('selectTab', 'security')
            ->assertSee('Google reCAPTCHA v3')
            ->call('edit', 'recaptcha_min_score')
            ->set('value', '0.7')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('0.7', BusinessSetting::query()->where('type', 'recaptcha_min_score')->value('value'));
    }
}
