@php
    $isAdmin = $user->isAdmin();
    $initials = collect(preg_split('/\s+/', trim((string) $user->name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');

    $statusLabel = match (true) {
        (bool) $user->banned => __('Suspended'),
        $user->email_verified_at !== null => __('Active account'),
        default => __('Unverified'),
    };
@endphp

<header class="pf-hero">
    <div class="flex flex-wrap items-center gap-5">
        <div class="pf-avatar">
            <span aria-hidden="true">{{ $initials !== '' ? $initials : 'U' }}</span>
            @if (! empty($avatarUrl))
                <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" onerror="this.remove()">
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="pf-badge pf-badge--accent">
                    {{ $isAdmin ? __('Administrator') : __('Customer') }}
                </span>
                <span class="pf-badge">#{{ str_pad((string) $user->id, 4, '0', STR_PAD_LEFT) }}</span>
                <span @class(['pf-badge', 'pf-badge--danger' => (bool) $user->banned, 'pf-badge--success' => ! $user->banned])>
                    {{ $statusLabel }}
                </span>
            </div>

            <h1 class="pf-hero-name">{{ $user->name }}</h1>

            @if (filled($user->email))
                <a href="mailto:{{ $user->email }}" class="pf-hero-mail">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    <span class="truncate">{{ $user->email }}</span>
                </a>
            @endif
        </div>

        <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:justify-end">
            <a href="{{ route('profile.edit', ['tab' => 'update']) }}" class="pf-hero-btn">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                {{ __('Edit profile') }}
            </a>

            <a href="{{ route('profile.edit', ['tab' => 'security']) }}" class="pf-hero-btn pf-hero-btn--ghost">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                {{ __('Security settings') }}
            </a>
        </div>
    </div>
</header>
