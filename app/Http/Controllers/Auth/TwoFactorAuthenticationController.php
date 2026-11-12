<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Auth\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Begin two-factor setup for the authenticated user by generating a pending secret.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.edit', ['tab' => 'security']);
        }

        $user->forceFill([
            'two_factor_secret' => Totp::generateSecret(),
            'two_factor_confirmed_at' => null,
            'two_factor_last_totp_step' => null,
        ])->save();

        return redirect()
            ->route('profile.edit', ['tab' => 'security'])
            ->with('status', 'two-factor-setup-started');
    }

    /**
     * Confirm two-factor setup once the user proves possession via a valid TOTP code.
     */
    public function confirm(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (blank($user->two_factor_secret)) {
            return redirect()
                ->route('profile.edit', ['tab' => 'security'])
                ->with('error', __('Start two-factor setup before confirming a verification code.'));
        }

        $validated = $request->validateWithBag('twoFactorConfirm', [
            'current_password' => ['required', 'current_password'],
            'code' => ['required', 'string'],
        ]);

        $step = Totp::verify(
            (string) $user->two_factor_secret,
            (string) $validated['code'],
            lastUsedStep: $user->two_factor_last_totp_step,
        );

        if ($step === null) {
            throw ValidationException::withMessages([
                'code' => __('The authenticator code is invalid or has expired.'),
            ])->errorBag('twoFactorConfirm')->redirectTo(route('profile.edit', ['tab' => 'security']));
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_last_totp_step' => $step,
            'two_factor_email_code_hash' => null,
            'two_factor_email_code_expires_at' => null,
        ])->save();

        return redirect()
            ->route('profile.edit', ['tab' => 'security'])
            ->with('status', 'two-factor-enabled');
    }

    /**
     * Disable active two-factor authentication or cancel a pending setup.
     */
    public function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $wasEnabled = $user->hasTwoFactorEnabled();

        if ($wasEnabled) {
            $request->validateWithBag('twoFactorDisable', [
                'current_password' => ['required', 'current_password'],
            ]);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_totp_step' => null,
            'two_factor_email_code_hash' => null,
            'two_factor_email_code_expires_at' => null,
        ])->save();

        return redirect()
            ->route('profile.edit', ['tab' => 'security'])
            ->with('status', $wasEnabled ? 'two-factor-disabled' : 'two-factor-setup-cancelled');
    }
}
