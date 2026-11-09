<section class="pf-card">
    <div class="pf-card-head">
        <span class="pf-card-icon">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
        </span>
        <div class="min-w-0">
            <h2 class="pf-card-title">{{ __('Account update') }}</h2>
            <p class="pf-card-sub">{{ __('Use a long, random password to keep this record secure.') }}</p>
        </div>
    </div>

    <div class="pf-card-body">
        <form method="post" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            @method('put')

            <div>
                <label for="update_password_current_password" class="pf-field-label">{{ __('Current password') }}</label>
                <input id="update_password_current_password" name="current_password" type="password"
                       class="pf-input mt-1" autocomplete="current-password">
                <x-input-error class="mt-1.5" :messages="$errors->updatePassword->get('current_password')" />
            </div>

            <div>
                <label for="update_password_password" class="pf-field-label">{{ __('New password') }}</label>
                <input id="update_password_password" name="password" type="password"
                       class="pf-input mt-1" autocomplete="new-password">
                <x-input-error class="mt-1.5" :messages="$errors->updatePassword->get('password')" />
            </div>

            <div>
                <label for="update_password_password_confirmation" class="pf-field-label">{{ __('Confirm password') }}</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                       class="pf-input mt-1" autocomplete="new-password">
                <x-input-error class="mt-1.5" :messages="$errors->updatePassword->get('password_confirmation')" />
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="pf-btn">{{ __('Update password') }}</button>
            </div>
        </form>
    </div>
</section>
