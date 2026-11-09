<x-guest-layout :title="__('Create account')" :subtitle="__('Join the store to save favourites and follow every order.')">
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <x-auth.field
            name="name"
            :label="__('Full name')"
            :value="old('name')"
            autocomplete="name"
            :autofocus="true"
        />

        <!-- Email Address -->
        <x-auth.field
            name="email"
            :label="__('Email')"
            type="email"
            :value="old('email')"
            autocomplete="username"
        />

        <!-- Password -->
        <livewire:auth.password-field
            name="password"
            :label="__('Password')"
            autocomplete="new-password"
        />

        <!-- Confirm Password -->
        <livewire:auth.password-field
            name="password_confirmation"
            :label="__('Confirm password')"
            autocomplete="new-password"
        />

        <button type="submit" class="auth-btn">
            {{ __('Create account') }}
        </button>
    </form>

    <x-slot:footer>
        <span class="auth-muted">{{ __('Already registered?') }}</span>
        <a class="auth-link font-semibold" href="{{ route('login') }}">{{ __('Sign in') }}</a>
    </x-slot:footer>
</x-guest-layout>
