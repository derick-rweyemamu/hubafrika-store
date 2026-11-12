@php
    $isEnabled = $user->hasTwoFactorEnabled();
    $isPending = $user->hasPendingTwoFactorSetup();
    $hasEmail = filled($user->email);
@endphp

<section class="pf-card lg:col-span-2" id="two-factor-authentication">
    <div class="pf-card-head">
        <span class="pf-card-icon pf-card-icon--accent">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
        </span>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="pf-card-title">{{ __('Two-factor authentication') }}</h2>

                @if ($isEnabled)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ __('Enabled') }}
                    </span>
                @elseif ($isPending)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        {{ __('Pending confirmation') }}
                    </span>
                @else
                    <span class="pf-chip">{{ __('Disabled') }}</span>
                @endif
            </div>

            <p class="pf-card-sub">
                {{ __('Require a 6-digit code from your authenticator app when signing in. If you lose access to your app, you can recover access through your registered email account.') }}
            </p>
        </div>
    </div>

    <div class="pf-card-body space-y-6">
        {{-- Registered email recovery overview --}}
        <div class="rounded-xl border p-4" style="border-color: var(--pf-line); background-color: var(--pf-surface-alt)">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 space-y-1">
                    <p class="pf-field-label">{{ __('Account recovery email') }}</p>
                    <p class="text-sm font-semibold" style="color: var(--pf-text)">
                        {{ $hasEmail ? $user->email : __('No registered email address on this account') }}
                    </p>
                    <p class="pf-card-sub">
                        {{ __('When two-factor authentication is active and you lose access to your authenticator app, the sign-in screen sends a single-use recovery code to this registered email address.') }}
                    </p>
                </div>

                @if ($hasEmail)
                    <span @class([
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                        'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' => $user->hasVerifiedEmail(),
                        'bg-amber-500/15 text-amber-700 dark:text-amber-300' => ! $user->hasVerifiedEmail(),
                    ])>
                        {{ $user->hasVerifiedEmail() ? __('Verified email') : __('Unverified email') }}
                    </span>
                @endif
            </div>

            @if (! $hasEmail)
                <div class="mt-3">
                    <a href="{{ route('profile.edit', ['tab' => 'update']) }}" class="pf-link">
                        {{ __('Add a registered email address in Profile update') }}
                    </a>
                </div>
            @elseif (! $user->hasVerifiedEmail())
                <form method="post" action="{{ route('verification.send') }}" class="mt-3 flex flex-wrap items-center gap-3">
                    @csrf
                    <button type="submit" class="pf-btn pf-btn--ghost">
                        {{ __('Verify recovery email') }}
                    </button>
                    <span class="pf-card-sub">{{ __('Recommended so you can reliably receive recovery codes.') }}</span>
                </form>
            @endif
        </div>

        @if (! $isEnabled && ! $isPending)
            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="pf-card-sub max-w-xl">
                    {{ __('Works with standard authenticator apps including Google Authenticator, Microsoft Authenticator, Authy, 1Password, and Bitwarden.') }}
                </p>

                <form method="post" action="{{ route('profile.two-factor.store') }}">
                    @csrf
                    <button type="submit" class="pf-btn">
                        {{ __('Enable two-factor authentication') }}
                    </button>
                </form>
            </div>
        @elseif ($isPending)
            <div class="grid gap-6 lg:grid-cols-[auto_minmax(0,1fr)] lg:items-start">
                <div class="flex flex-col items-center gap-3 rounded-xl border p-4" style="border-color: var(--pf-line); background-color: var(--pf-surface-alt)">
                    <div class="overflow-hidden rounded-lg border bg-white p-2" style="border-color: var(--pf-line)">
                        {!! $twoFactorQrSvg !!}
                    </div>

                    @if (! empty($twoFactorFormattedSecret))
                        <div class="w-full max-w-[260px] text-center" x-data="{ copied: false }">
                            <p class="pf-field-label">{{ __('Setup key (manual entry)') }}</p>
                            <code class="mt-1 flex flex-wrap justify-center gap-x-1.5 gap-y-0.5 rounded-lg border px-2.5 py-2 font-mono text-xs font-semibold tracking-wider"
                                  style="border-color: var(--pf-line); background-color: var(--pf-surface); color: var(--pf-text)">
                                @foreach (explode(' ', $twoFactorFormattedSecret) as $chunk)
                                    <span class="whitespace-nowrap">{{ $chunk }}</span>
                                @endforeach
                            </code>
                            <button
                                type="button"
                                class="pf-link mt-2 text-xs"
                                x-on:click="navigator.clipboard?.writeText(@js(str_replace(' ', '', $twoFactorFormattedSecret))); copied = true; setTimeout(() => copied = false, 2000)"
                            >
                                <span x-show="!copied">{{ __('Copy setup key') }}</span>
                                <span x-show="copied" x-cloak>{{ __('Copied!') }}</span>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="min-w-0 space-y-5">
                    <div class="space-y-1">
                        <h3 class="pf-card-title">{{ __('Finish setting up your authenticator') }}</h3>
                        <p class="pf-card-sub">
                            {{ __('1. Scan the QR code (or enter the setup key) in your authenticator app. 2. Enter the 6-digit code shown in the app and your current password below.') }}
                        </p>
                    </div>

                    <form id="cancel-two-factor-setup" method="post" action="{{ route('profile.two-factor.destroy') }}">
                        @csrf
                        @method('delete')
                    </form>

                    <form method="post" action="{{ route('profile.two-factor.confirm') }}" class="space-y-4">
                        @csrf

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="two_factor_confirm_code" class="pf-field-label">{{ __('6-digit authenticator code') }}</label>
                                <input id="two_factor_confirm_code"
                                       name="code"
                                       type="text"
                                       inputmode="numeric"
                                       autocomplete="one-time-code"
                                       placeholder="123456"
                                       class="pf-input mt-1"
                                       required>
                                <x-input-error class="mt-1.5" :messages="$errors->twoFactorConfirm->get('code')" />
                            </div>

                            <div>
                                <label for="two_factor_confirm_password" class="pf-field-label">{{ __('Current password') }}</label>
                                <input id="two_factor_confirm_password"
                                       name="current_password"
                                       type="password"
                                       autocomplete="current-password"
                                       class="pf-input mt-1"
                                       required>
                                <x-input-error class="mt-1.5" :messages="$errors->twoFactorConfirm->get('current_password')" />
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit" class="pf-btn">
                                {{ __('Confirm & enable 2FA') }}
                            </button>

                            <button type="submit" form="cancel-two-factor-setup" formnovalidate class="pf-btn pf-btn--ghost">
                                {{ __('Cancel setup') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="space-y-5">
                <p class="pf-card-sub">
                    {{ __('Two-factor authentication has been active since :date. Each sign-in requires a code from your authenticator app or a single-use recovery code sent to your registered email.', [
                        'date' => $user->two_factor_confirmed_at?->format('d M Y H:i') ?? '—',
                    ]) }}
                </p>

                <div class="pf-divider"></div>

                <form method="post" action="{{ route('profile.two-factor.destroy') }}" class="space-y-4">
                    @csrf
                    @method('delete')

                    <div class="max-w-sm">
                        <label for="two_factor_disable_password" class="pf-field-label">{{ __('Current password to disable 2FA') }}</label>
                        <input id="two_factor_disable_password"
                               name="current_password"
                               type="password"
                               autocomplete="current-password"
                               class="pf-input mt-1"
                               required>
                        <x-input-error class="mt-1.5" :messages="$errors->twoFactorDisable->get('current_password')" />
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="pf-btn pf-btn--danger">
                            {{ __('Disable two-factor authentication') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</section>

