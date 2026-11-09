<x-app-layout>
    <div class="pf-page mx-auto w-full max-w-[1200px] px-5 py-8">
        <x-flash-toast />

        @include('profile.partials.hero')

        <x-account-tabs :active="$activeTab" />

        <div class="mt-6">
            @if ($activeTab === 'update')
                @include('profile.partials.update-profile-information-form')
            @elseif ($activeTab === 'security')
                <div class="grid gap-6 lg:grid-cols-2">
                    @include('profile.partials.update-password-form')
                    @include('profile.partials.delete-user-form')
                </div>
            @else
                @include('profile.partials.account-overview')
            @endif
        </div>
    </div>
</x-app-layout>
