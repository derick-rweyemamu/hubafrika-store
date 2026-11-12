<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorRecoveryCodeMail;
use App\Models\User;
use App\Support\Auth\Totp;
use App\Support\Storefront\CartSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorAuthenticatedSessionController extends Controller
{
    private const SESSION_KEY = 'two_factor.login';

    private const EMAIL_RECOVERY_TTL_MINUTES = 15;

    /**
     * Display the two-factor challenge and email-recovery screen.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $challenge = $this->resolveChallenge($request);

        if ($challenge === null) {
            return redirect()->route('login');
        }

        [$user] = $challenge;
        $mode = $request->query('mode') === 'recovery' ? 'recovery' : 'totp';

        return view('auth.two-factor-challenge', [
            'mode' => $mode,
            'maskedEmail' => $user->maskedRecoveryEmail(),
            'hasRecoveryEmail' => filled($user->email),
        ]);
    }

    /**
     * Verify a 6-digit authenticator code and complete login.
     */
    public function store(Request $request): RedirectResponse
    {
        $challenge = $this->resolveChallenge($request);

        if ($challenge === null) {
            return redirect()->route('login');
        }

        [$user, $remember] = $challenge;
        $throttleKey = $this->throttleKey('verify', $user, $request);

        $this->ensureNotRateLimited($throttleKey, 'code');

        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $step = Totp::verify(
            (string) $user->two_factor_secret,
            (string) $validated['code'],
            lastUsedStep: $user->two_factor_last_totp_step,
        );

        if ($step === null) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'code' => __('The two-factor authentication code is invalid or has already been used.'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        $user->forceFill([
            'two_factor_last_totp_step' => $step,
            'two_factor_email_code_hash' => null,
            'two_factor_email_code_expires_at' => null,
        ])->save();

        return $this->completeLogin($request, $user, $remember);
    }

    /**
     * Send a single-use recovery code strictly to the user's registered email address.
     */
    public function sendEmailRecovery(Request $request): RedirectResponse
    {
        $challenge = $this->resolveChallenge($request);

        if ($challenge === null) {
            return redirect()->route('login');
        }

        [$user] = $challenge;

        if (blank($user->email)) {
            return redirect()
                ->route('two-factor.login', ['mode' => 'recovery'])
                ->withErrors(['recovery_code' => __('No registered email address is available on this account.')]);
        }

        $throttleKey = $this->throttleKey('email-send', $user, $request);

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return redirect()
                ->route('two-factor.login', ['mode' => 'recovery'])
                ->withErrors([
                    'recovery_code' => trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ]),
                ]);
        }

        RateLimiter::hit($throttleKey, self::EMAIL_RECOVERY_TTL_MINUTES * 60);

        $code = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

        $user->forceFill([
            'two_factor_email_code_hash' => Hash::make($code),
            'two_factor_email_code_expires_at' => now()->addMinutes(self::EMAIL_RECOVERY_TTL_MINUTES),
        ])->save();

        // Keep the pending sign-in window alive as long as the email recovery code is valid.
        $currentExpiry = (int) $request->session()->get(self::SESSION_KEY.'.expires_at', 0);
        $request->session()->put(
            self::SESSION_KEY.'.expires_at',
            max($currentExpiry, now()->addMinutes(self::EMAIL_RECOVERY_TTL_MINUTES)->getTimestamp()),
        );

        Mail::to($user->email)->send(new TwoFactorRecoveryCodeMail($user, $code, self::EMAIL_RECOVERY_TTL_MINUTES));

        return redirect()
            ->route('two-factor.login', ['mode' => 'recovery'])
            ->with('status', 'two-factor-recovery-sent');
    }

    /**
     * Verify a registered-email recovery code and complete login.
     */
    public function verifyEmailRecovery(Request $request): RedirectResponse
    {
        $challenge = $this->resolveChallenge($request);

        if ($challenge === null) {
            return redirect()->route('login');
        }

        [$user, $remember] = $challenge;
        $throttleKey = $this->throttleKey('email-verify', $user, $request);

        $this->ensureNotRateLimited(
            $throttleKey,
            'recovery_code',
            route('two-factor.login', ['mode' => 'recovery']),
        );

        $validated = $request->validate([
            'recovery_code' => ['required', 'string'],
        ]);

        $normalized = preg_replace('/[\s-]+/', '', trim((string) $validated['recovery_code'])) ?? '';
        $isExpired = $user->two_factor_email_code_expires_at === null || $user->two_factor_email_code_expires_at->isPast();
        $hasHash = filled($user->two_factor_email_code_hash);

        if ($normalized === '' || ! $hasHash || $isExpired || ! Hash::check($normalized, (string) $user->two_factor_email_code_hash)) {
            RateLimiter::hit($throttleKey, 900);

            return redirect()
                ->route('two-factor.login', ['mode' => 'recovery'])
                ->withErrors(['recovery_code' => __('The email recovery code is invalid or has expired.')]);
        }

        RateLimiter::clear($throttleKey);
        $user->clearTwoFactorEmailRecoveryCode();

        return $this->completeLogin($request, $user, $remember, recoveredViaEmail: true);
    }

    /**
     * Resolve the pending two-factor user from session, guarding expiration and banned accounts.
     *
     * @return array{0: User, 1: bool}|null
     */
    private function resolveChallenge(Request $request): ?array
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! is_array($pending) || ! isset($pending['id'], $pending['expires_at'])) {
            return null;
        }

        if ((int) $pending['expires_at'] < now()->getTimestamp()) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        $user = User::query()->find($pending['id']);

        if (! $user instanceof User || (bool) $user->banned || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        return [$user, (bool) ($pending['remember'] ?? false)];
    }

    /**
     * Finalize authentication after two-factor or email recovery succeeds.
     */
    private function completeLogin(
        Request $request,
        User $user,
        bool $remember,
        bool $recoveredViaEmail = false,
    ): RedirectResponse {
        $request->session()->forget(self::SESSION_KEY);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Anything collected as a guest follows the customer into the account.
        CartSession::claim($user);

        if ($user->is_admin) {
            $response = redirect()->route('admin.dashboard');
        } else {
            $response = redirect()->intended(route('home', absolute: false));
        }

        if ($recoveredViaEmail) {
            $response->with('status', 'two-factor-recovered');
        }

        return $response;
    }

    private function ensureNotRateLimited(string $throttleKey, string $errorField, ?string $redirectTo = null): void
    {
        if (! RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($throttleKey);

        $exception = ValidationException::withMessages([
            $errorField => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);

        if ($redirectTo !== null) {
            $exception->redirectTo($redirectTo);
        }

        throw $exception;
    }

    private function throttleKey(string $scope, User $user, Request $request): string
    {
        return "2fa:{$scope}|{$user->getAuthIdentifier()}|{$request->ip()}";
    }
}
