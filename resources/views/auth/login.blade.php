<x-guest-layout :title="__('Welcome')" :subtitle="__('Sign in to track orders, save favourites and check out faster.')">
    <!-- Session Status -->
    <x-flash-toast />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <x-auth.field
            name="email"
            :label="__('Email')"
            type="email"
            :value="old('email')"
            autocomplete="username"
            :autofocus="true"
        />

        <!-- Password -->
        <livewire:auth.password-field
            name="password"
            :label="__('Password')"
            autocomplete="current-password"
        />

        <!-- Remember Me -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
                <input id="remember_me" type="checkbox" name="remember" class="auth-checkbox">
                <span class="auth-muted text-sm">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-link text-sm" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="auth-btn">
            {{ __('Log in') }}
        </button>
    </form>

    <x-slot:footer>
        <span class="auth-muted">{{ __('New to the store?') }}</span>
        <a class="auth-link font-semibold" href="{{ route('register') }}">{{ __('Create an account') }}</a>
    </x-slot:footer>
</x-guest-layout>
