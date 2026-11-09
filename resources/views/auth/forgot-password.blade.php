<x-guest-layout :title="__('Reset password')" :subtitle="__('Enter your email and we will send you a secure reset link.')">
    <!-- Session Status -->
    <x-flash-toast />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
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

        <p class="auth-muted text-center text-xs leading-relaxed">
            {{ __('We will email a link that lets you choose a new password. The link expires after 60 minutes.') }}
        </p>

        <button type="submit" class="auth-btn">
            {{ __('Email reset link') }}
        </button>
    </form>

    <x-slot:footer>
        <a class="auth-link font-semibold" href="{{ route('login') }}">{{ __('Back to sign in') }}</a>
    </x-slot:footer>
</x-guest-layout>
