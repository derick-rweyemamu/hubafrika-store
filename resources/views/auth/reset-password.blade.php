<x-guest-layout :title="__('Choose a password')" :subtitle="__('Pick a new password for your account, then sign in again.')">
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <x-auth.field
            name="email"
            :label="__('Email')"
            type="email"
            :value="old('email', $request->email)"
            autocomplete="username"
            :autofocus="true"
        />

        <!-- Password -->
        <livewire:auth.password-field
            name="password"
            :label="__('New password')"
            autocomplete="new-password"
        />

        <!-- Confirm Password -->
        <livewire:auth.password-field
            name="password_confirmation"
            :label="__('Confirm password')"
            autocomplete="new-password"
        />

        <button type="submit" class="auth-btn">
            {{ __('Reset password') }}
        </button>
    </form>

    <x-slot:footer>
        <a class="auth-link font-semibold" href="{{ route('login') }}">{{ __('Back to sign in') }}</a>
    </x-slot:footer>
</x-guest-layout>
