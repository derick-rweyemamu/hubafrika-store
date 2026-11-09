<x-guest-layout :title="__('Verify email')" :subtitle="__('One quick step before we open up your account.')">
    <div class="space-y-5">
        <x-flash-toast />

        <p class="auth-muted text-center text-sm leading-relaxed">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you did not receive the email, we will gladly send you another.') }}
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <button type="submit" class="auth-btn">
                {{ __('Resend verification email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf

            <button type="submit" class="auth-link text-sm font-semibold">
                {{ __('Log out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
