<?php

namespace Tests\Feature;

use App\Mail\TwoFactorRecoveryCodeMail;
use App\Models\BusinessSetting;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const RFC_SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ'; // "12345678901234567890"

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_profile_security_tab_shows_two_factor_disabled_and_registered_recovery_email(): void
    {
        $user = User::factory()->create(['email' => 'amina@example.com']);

        $this->actingAs($user)
            ->get('/profile?tab=security')
            ->assertOk()
            ->assertSee('Two-factor authentication')
            ->assertSee('Disabled')
            ->assertSee('amina@example.com')
            ->assertSee('Enable two-factor authentication');
    }

    public function test_user_can_initiate_setup_and_confirm_two_factor_on_profile(): void
    {
        BusinessSetting::query()->create(['type' => 'site_name', 'value' => 'Hubafrika Shop']);

        $user = User::factory()->create(['email' => 'amina@example.com']);

        $this->actingAs($user)
            ->post(route('profile.two-factor.store'))
            ->assertRedirect(route('profile.edit', ['tab' => 'security']))
            ->assertSessionHas('status', 'two-factor-setup-started');

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertFalse($user->hasTwoFactorEnabled());

        $this->actingAs($user)
            ->get('/profile?tab=security')
            ->assertOk()
            ->assertSee('Pending confirmation')
            ->assertSee('<svg', false)
            ->assertSee('Confirm &amp; enable 2FA', false);

        // Switch to deterministic secret so the test verifies known RFC 6238 vector "287082" at t = 59.
        $user->forceFill(['two_factor_secret' => self::RFC_SECRET])->save();
        Carbon::setTestNow(Carbon::createFromTimestamp(59));

        $this->actingAs($user)
            ->post(route('profile.two-factor.confirm'), [
                'current_password' => 'password',
                'code' => '000000',
            ])
            ->assertSessionHasErrorsIn('twoFactorConfirm', 'code');

        $this->assertFalse($user->refresh()->hasTwoFactorEnabled());

        $this->actingAs($user)
            ->post(route('profile.two-factor.confirm'), [
                'current_password' => 'wrong-password',
                'code' => '287082',
            ])
            ->assertSessionHasErrorsIn('twoFactorConfirm', 'current_password');

        $this->actingAs($user)
            ->post(route('profile.two-factor.confirm'), [
                'current_password' => 'password',
                'code' => '287082',
            ])
            ->assertRedirect(route('profile.edit', ['tab' => 'security']))
            ->assertSessionHas('status', 'two-factor-enabled');

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertSame(1, $user->two_factor_last_totp_step);
    }

    public function test_user_can_disable_active_two_factor_with_current_password(): void
    {
        $user = User::factory()->withTwoFactor(self::RFC_SECRET)->create();

        $this->actingAs($user)
            ->from('/profile?tab=security')
            ->delete(route('profile.two-factor.destroy'), [
                'current_password' => 'wrong-password',
            ])
            ->assertSessionHasErrorsIn('twoFactorDisable', 'current_password')
            ->assertRedirect('/profile?tab=security');

        $this->assertTrue($user->refresh()->hasTwoFactorEnabled());

        $this->actingAs($user)
            ->delete(route('profile.two-factor.destroy'), [
                'current_password' => 'password',
            ])
            ->assertRedirect(route('profile.edit', ['tab' => 'security']))
            ->assertSessionHas('status', 'two-factor-disabled');

        $user->refresh();
        $this->assertFalse($user->hasTwoFactorEnabled());
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_login_with_two_factor_enabled_requires_challenge_and_blocks_totp_replay(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1111111109)); // step 37037036 -> RFC code 081804

        $user = User::factory()->withTwoFactor(self::RFC_SECRET)->create(['is_admin' => false]);

        $category = Category::query()->create(['name' => 'Solar', 'slug' => 'solar']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Hybrid Inverter',
            'slug' => 'hybrid-inverter',
            'unit_price' => 500,
            'current_stock' => 5,
            'published' => true,
        ]);
        Cart::query()->create([
            'temp_user_id' => 'guest-cart-token-2fa',
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 500,
        ]);

        // Visiting protected route records url.intended cleanly before redirecting to /login.
        $this->get('/profile')->assertRedirect(route('login'));

        $this->withSession(['storefront.cart_token' => 'guest-cart-token-2fa'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
                'remember' => '1',
            ])
            ->assertRedirect(route('two-factor.login'));

        $this->assertGuest();

        $this->get('/profile')->assertRedirect(route('login'));

        $this->post(route('two-factor.login.store'), ['code' => '999999'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();

        $response = $this->post(route('two-factor.login.store'), ['code' => '081804']);
        $response->assertRedirect('/profile');

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, Cart::query()->where('user_id', $user->id)->count());
        $this->assertSame(37037036, $user->refresh()->two_factor_last_totp_step);

        // Logout and attempt to replay the exact same TOTP code in the same 30s time step.
        $this->post('/logout');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('two-factor.login'));

        $this->post(route('two-factor.login.store'), ['code' => '081804'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_lost_app_recovery_sends_single_use_code_to_registered_email_and_authenticates(): void
    {
        Mail::fake();

        $user = User::factory()->withTwoFactor(self::RFC_SECRET)->create([
            'email' => 'registered.customer@example.com',
            'is_admin' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.login'));

        $this->get(route('two-factor.login', ['mode' => 'recovery']))
            ->assertOk()
            ->assertSee('re***@example.com')
            ->assertDontSee('registered.customer@example.com');

        $this->post(route('two-factor.recovery.email.send'))
            ->assertRedirect(route('two-factor.login', ['mode' => 'recovery']))
            ->assertSessionHas('status', 'two-factor-recovery-sent');

        $capturedCode = null;
        Mail::assertSent(TwoFactorRecoveryCodeMail::class, function (TwoFactorRecoveryCodeMail $mail) use ($user, &$capturedCode): bool {
            $capturedCode = $mail->code;

            return $mail->hasTo($user->email);
        });

        $this->assertNotNull($capturedCode);

        $this->post(route('two-factor.recovery.email.verify'), ['recovery_code' => '0000-0000'])
            ->assertRedirect(route('two-factor.login', ['mode' => 'recovery']))
            ->assertSessionHasErrors('recovery_code');

        $this->assertGuest();

        $formattedInput = substr($capturedCode, 0, 4).'-'.substr($capturedCode, 4, 4);

        $this->post(route('two-factor.recovery.email.verify'), ['recovery_code' => $formattedInput])
            ->assertRedirect(route('home', absolute: false))
            ->assertSessionHas('status', 'two-factor-recovered');

        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNull($user->two_factor_email_code_hash);
        $this->assertNull($user->two_factor_email_code_expires_at);

        // Single-use: logging out and trying the same recovery code again must fail.
        $this->post('/logout');
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->post(route('two-factor.recovery.email.verify'), ['recovery_code' => $capturedCode])
            ->assertRedirect(route('two-factor.login', ['mode' => 'recovery']))
            ->assertSessionHasErrors('recovery_code');

        $this->assertGuest();
    }

    public function test_expired_email_recovery_code_is_rejected(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2026, 11, 11, 12, 0, 0));

        $user = User::factory()->withTwoFactor(self::RFC_SECRET)->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post(route('two-factor.recovery.email.send'));

        $capturedCode = null;
        Mail::assertSent(TwoFactorRecoveryCodeMail::class, function (TwoFactorRecoveryCodeMail $mail) use (&$capturedCode): bool {
            $capturedCode = $mail->code;

            return true;
        });

        Carbon::setTestNow(Carbon::create(2026, 11, 11, 12, 16, 0));

        $this->post(route('two-factor.recovery.email.verify'), ['recovery_code' => (string) $capturedCode])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_email_recovery_send_and_verify_are_rate_limited(): void
    {
        Mail::fake();

        $user = User::factory()->withTwoFactor(self::RFC_SECRET)->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('two-factor.recovery.email.send'))
                ->assertSessionHasNoErrors();
        }

        $this->post(route('two-factor.recovery.email.send'))
            ->assertRedirect(route('two-factor.login', ['mode' => 'recovery']))
            ->assertSessionHasErrors('recovery_code');

        Mail::assertSentTimes(TwoFactorRecoveryCodeMail::class, 3);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('two-factor.recovery.email.verify'), ['recovery_code' => '99999999'])
                ->assertSessionHasErrors('recovery_code');
        }

        $this->post(route('two-factor.recovery.email.verify'), ['recovery_code' => '99999999'])
            ->assertRedirect(route('two-factor.login', ['mode' => 'recovery']))
            ->assertSessionHasErrors('recovery_code');
    }

    public function test_recovery_mail_escapes_html_in_user_name(): void
    {
        $user = User::factory()->make([
            'name' => "O'Reilly <script>alert('xss')</script>",
            'email' => 'safe@example.com',
        ]);

        $rendered = (new TwoFactorRecoveryCodeMail($user, '12345678', 15))->render();

        $this->assertStringContainsString('&lt;script&gt;', $rendered);
        $this->assertStringNotContainsString("<script>alert('xss')</script>", $rendered);
        $this->assertStringContainsString('1234-5678', $rendered);
    }

    public function test_unauthenticated_requests_cannot_manage_two_factor_or_bypass_challenge(): void
    {
        $this->post(route('profile.two-factor.store'))->assertRedirect(route('login'));
        $this->post(route('profile.two-factor.confirm'))->assertRedirect(route('login'));
        $this->delete(route('profile.two-factor.destroy'))->assertRedirect(route('login'));

        $this->get(route('two-factor.login'))->assertRedirect(route('login'));
        $this->post(route('two-factor.recovery.email.send'))->assertRedirect(route('login'));
    }

    public function test_admin_with_two_factor_enabled_redirects_to_admin_dashboard_after_verification(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1111111109)); // 081804

        $admin = User::factory()->withTwoFactor(self::RFC_SECRET)->create(['is_admin' => true]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.login'));

        $this->assertGuest();

        $this->post(route('two-factor.login.store'), ['code' => '081804'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_pending_unconfirmed_two_factor_setup_does_not_block_login_and_can_be_cancelled(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.two-factor.store'))
            ->assertSessionHas('status', 'two-factor-setup-started');

        $this->assertTrue($user->refresh()->hasPendingTwoFactorSetup());

        $this->actingAs($user)
            ->delete(route('profile.two-factor.destroy'))
            ->assertRedirect(route('profile.edit', ['tab' => 'security']))
            ->assertSessionHas('status', 'two-factor-setup-cancelled');

        $this->assertNull($user->refresh()->two_factor_secret);

        // Even if pending secret exists, logging in still succeeds without TOTP challenge until confirmed.
        $this->actingAs($user)->post(route('profile.two-factor.store'));
        $this->post('/logout');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('home', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_without_registered_email_receives_clear_error_on_recovery_request(): void
    {
        $user = User::factory()->withTwoFactor(self::RFC_SECRET)->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.login'));

        $user->forceFill(['email' => null])->save();

        $this->get(route('two-factor.login', ['mode' => 'recovery']))
            ->assertOk()
            ->assertSee('does not have a registered email address');

        $this->post(route('two-factor.recovery.email.send'))
            ->assertRedirect(route('two-factor.login', ['mode' => 'recovery']))
            ->assertSessionHasErrors('recovery_code');
    }
}
