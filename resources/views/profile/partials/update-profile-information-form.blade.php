@php
    $initials = collect(preg_split('/\s+/', trim((string) $user->name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');
@endphp

<div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
    <aside class="pf-card h-fit p-5">
        <div class="flex items-center gap-3">
            <div class="pf-avatar pf-avatar--sm">
                <span aria-hidden="true">{{ $initials !== '' ? $initials : 'U' }}</span>
                @if (! empty($avatarUrl))
                    <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" onerror="this.remove()">
                @endif
            </div>
            <div class="min-w-0">
                <p class="pf-card-title truncate">{{ $user->name }}</p>
                <p class="pf-card-sub">{{ $user->isAdmin() ? __('Administrator') : __('Customer') }}</p>
            </div>
        </div>

        <p class="pf-card-sub mt-4">
            {{ __('These details are the record staff and couriers use to reach you. Changing your email address marks it unverified until you confirm it again.') }}
        </p>

        <div class="pf-divider"></div>

        <div class="pf-meta">
            <span class="pf-meta-label">{{ __('Email status') }}</span>
            <span class="pf-meta-value">{{ $user->email_verified_at ? __('Verified') : __('Unverified') }}</span>
        </div>
        <div class="pf-meta">
            <span class="pf-meta-label">{{ __('Last updated') }}</span>
            <span class="pf-meta-value">{{ $user->updated_at?->format('d M Y') ?? '—' }}</span>
        </div>
    </aside>

    <section class="pf-card">
        <div class="pf-card-head">
            <span class="pf-card-icon">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
            </span>
            <div class="min-w-0">
                <h2 class="pf-card-title">{{ __('Profile update') }}</h2>
                <p class="pf-card-sub">{{ __('Update the identity and contact records stored against your account.') }}</p>
            </div>
        </div>

        <div class="pf-card-body">
            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('patch')

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="pf-field-label">{{ __('Full name') }}</label>
                        <input id="name" name="name" type="text" class="pf-input mt-1" required autofocus autocomplete="name"
                               value="{{ old('name', $user->name) }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <label for="email" class="pf-field-label">{{ __('Email address') }}</label>
                        <input id="email" name="email" type="email" class="pf-input mt-1" required autocomplete="username"
                               value="{{ old('email', $user->email) }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <label for="phone" class="pf-field-label">{{ __('Phone number') }}</label>
                        <input id="phone" name="phone" type="text" class="pf-input mt-1" autocomplete="tel"
                               value="{{ old('phone', $user->phone) }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('phone')" />
                    </div>

                    <div>
                        <label for="postal_code" class="pf-field-label">{{ __('Postal code') }}</label>
                        <input id="postal_code" name="postal_code" type="text" class="pf-input mt-1" autocomplete="postal-code"
                               value="{{ old('postal_code', $user->postal_code) }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('postal_code')" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="pf-field-label">{{ __('Address') }}</label>
                        <input id="address" name="address" type="text" class="pf-input mt-1" autocomplete="street-address"
                               value="{{ old('address', $user->address) }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('address')" />
                    </div>

                    <div>
                        <label for="city" class="pf-field-label">{{ __('City') }}</label>
                        <input id="city" name="city" type="text" class="pf-input mt-1" autocomplete="address-level2"
                               value="{{ old('city', $user->city) }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('city')" />
                    </div>

                    <div>
                        <label for="state" class="pf-field-label">{{ __('State / region') }}</label>
                        <input id="state" name="state" type="text" class="pf-input mt-1" autocomplete="address-level1"
                               value="{{ old('state', $user->state) }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('state')" />
                    </div>

                    <div>
                        <label for="country" class="pf-field-label">{{ __('Country') }}</label>
                        <input id="country" name="country" type="text" class="pf-input mt-1" autocomplete="country-name"
                               value="{{ old('country', $user->country) }}">
                        <x-input-error class="mt-1.5" :messages="$errors->get('country')" />
                    </div>
                </div>

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="pf-field">
                        <p class="pf-field-value">{{ __('Your email address is unverified.') }}</p>
                        <button form="send-verification" class="pf-link mt-1">
                            {{ __('Re-send the verification email.') }}
                        </button>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="pf-btn">{{ __('Save changes') }}</button>
                    <a href="{{ route('profile.edit') }}" class="pf-btn pf-btn--ghost">{{ __('Back to overview') }}</a>
                </div>
            </form>
        </div>
    </section>
</div>
