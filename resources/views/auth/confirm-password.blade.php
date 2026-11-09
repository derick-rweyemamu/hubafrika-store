<x-guest-layout :title="__('Confirm password')" :subtitle="__('This is a secure area. Please confirm your password before continuing.')">
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
        @csrf

        <!-- Password -->
        <livewire:auth.password-field
            name="password"
            :label="__('Password')"
            autocomplete="current-password"
            :autofocus="true"
        />

        <button type="submit" class="auth-btn">
            {{ __('Confirm') }}
        </button>
    </form>
</x-guest-layout>
