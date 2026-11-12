@php
    $isAdmin = $user->isAdmin();
    $roleLabel = $isAdmin ? __('Administrator') : __('Customer');
    $statusLabel = match (true) {
        (bool) $user->banned => __('Suspended'),
        $user->email_verified_at !== null => __('Active'),
        default => __('Pending verification'),
    };
    $initials = collect(preg_split('/\s+/', trim((string) $user->name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');
    $empty = '—';
@endphp

<div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
    {{-- Identity record pulled from the users row --}}
    <aside class="pf-card h-fit p-5">
        <div class="flex flex-col items-center text-center">
            <div class="pf-avatar pf-avatar--sm">
                <span aria-hidden="true">{{ $initials !== '' ? $initials : 'U' }}</span>
                @if (! empty($avatarUrl))
                    <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" onerror="this.remove()">
                @endif
            </div>

            <p class="pf-card-title mt-3">{{ $user->name }}</p>
            <p class="pf-card-sub mt-0.5">{{ $roleLabel }}</p>
            <span class="pf-chip mt-2">{{ $statusLabel }}</span>
        </div>

        <div class="pf-divider"></div>

        <div class="space-y-2">
            <a href="mailto:{{ $user->email }}" class="pf-identity-row">
                <span class="pf-identity-icon">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                </span>
                <span class="min-w-0">
                    <span class="pf-field-label">{{ __('Email') }}</span>
                    <span class="block truncate text-sm font-semibold" style="color: var(--pf-text)">{{ $user->email ?: $empty }}</span>
                </span>
            </a>

            @if (filled($user->phone))
                <a href="tel:{{ $user->phone }}" class="pf-identity-row">
                    <span class="pf-identity-icon">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="pf-field-label">{{ __('Phone') }}</span>
                        <span class="block truncate text-sm font-semibold" style="color: var(--pf-text)">{{ $user->phone }}</span>
                    </span>
                </a>
            @else
                <div class="pf-identity-row">
                    <span class="pf-identity-icon">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="pf-field-label">{{ __('Phone') }}</span>
                        <span class="block text-sm font-semibold pf-text-muted">{{ __('Not provided') }}</span>
                    </span>
                </div>
            @endif
        </div>

        <div class="pf-divider"></div>

        <div>
            <div class="pf-meta">
                <span class="pf-meta-label">{{ __('User ID') }}</span>
                <span class="pf-meta-value">#{{ str_pad((string) $user->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="pf-meta">
                <span class="pf-meta-label">{{ __('Account type') }}</span>
                <span class="pf-meta-value">{{ $roleLabel }}</span>
            </div>
            <div class="pf-meta">
                <span class="pf-meta-label">{{ __('Status') }}</span>
                <span class="pf-meta-value">{{ $statusLabel }}</span>
            </div>
            <div class="pf-meta">
                <span class="pf-meta-label">{{ __('Two-factor auth') }}</span>
                <a href="{{ route('profile.edit', ['tab' => 'security']) }}" class="pf-meta-value pf-link">
                    {{ $user->hasTwoFactorEnabled() ? __('Enabled') : __('Off') }}
                </a>
            </div>
            <div class="pf-meta">
                <span class="pf-meta-label">{{ __('Member since') }}</span>
                <span class="pf-meta-value">{{ $user->created_at?->format('d M Y') ?? $empty }}</span>
            </div>
        </div>
    </aside>

    <div class="min-w-0 space-y-6">
        {{-- Records at a glance --}}
        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            <div class="pf-stat">
                <p class="pf-stat-value">{{ $summary['orders'] }}</p>
                <p class="pf-stat-label">{{ __('Orders') }}</p>
            </div>
            <div class="pf-stat">
                <p class="pf-stat-value">{{ $currency }} {{ number_format($summary['spent'], 2) }}</p>
                <p class="pf-stat-label">{{ __('Total spent') }}</p>
            </div>
            <div class="pf-stat">
                <p class="pf-stat-value">{{ $summary['addresses'] }}</p>
                <p class="pf-stat-label">{{ __('Saved addresses') }}</p>
            </div>
            <div class="pf-stat">
                <p class="pf-stat-value">{{ $summary['last_order_at']?->format('d M Y') ?? $empty }}</p>
                <p class="pf-stat-label">{{ __('Last order') }}</p>
            </div>
        </div>

        {{-- Personal information --}}
        <section class="pf-card">
            <div class="pf-card-head">
                <span class="pf-card-icon">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                </span>
                <div class="min-w-0">
                    <h2 class="pf-card-title">{{ __('Personal information') }}</h2>
                    <p class="pf-card-sub">{{ __('Identity and contact details stored in the users table.') }}</p>
                </div>
                <a href="{{ route('profile.edit', ['tab' => 'update']) }}" class="pf-btn pf-btn--ghost ml-auto shrink-0">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    {{ __('Edit') }}
                </a>
            </div>

            <div class="pf-card-body grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <div class="pf-field">
                    <span class="pf-field-label">{{ __('Full name') }}</span>
                    <p class="pf-field-value">{{ $user->name ?: $empty }}</p>
                </div>
                <div class="pf-field">
                    <span class="pf-field-label">{{ __('Email') }}</span>
                    <p class="pf-field-value">{{ $user->email ?: $empty }}</p>
                </div>
                <div class="pf-field">
                    <span class="pf-field-label">{{ __('Phone') }}</span>
                    <p class="pf-field-value">{{ $user->phone ?: $empty }}</p>
                </div>
                <div class="pf-field sm:col-span-2 xl:col-span-3">
                    <span class="pf-field-label">{{ __('Address') }}</span>
                    <p class="pf-field-value">{{ $user->address ?: $empty }}</p>
                </div>
                <div class="pf-field">
                    <span class="pf-field-label">{{ __('City') }}</span>
                    <p class="pf-field-value">{{ $user->city ?: $empty }}</p>
                </div>
                <div class="pf-field">
                    <span class="pf-field-label">{{ __('State / region') }}</span>
                    <p class="pf-field-value">{{ $user->state ?: $empty }}</p>
                </div>
                <div class="pf-field">
                    <span class="pf-field-label">{{ __('Postal code') }}</span>
                    <p class="pf-field-value">{{ $user->postal_code ?: $empty }}</p>
                </div>
                <div class="pf-field">
                    <span class="pf-field-label">{{ __('Country') }}</span>
                    <p class="pf-field-value">{{ $user->country ?: $empty }}</p>
                </div>
                <div class="pf-field">
                    <span class="pf-field-label">{{ __('Email verified') }}</span>
                    <p class="pf-field-value">{{ $user->email_verified_at?->format('d M Y') ?? __('Not verified') }}</p>
                </div>
                <div class="pf-field">
                    <span class="pf-field-label">{{ __('Member since') }}</span>
                    <p class="pf-field-value">{{ $user->created_at?->format('d M Y') ?? $empty }}</p>
                </div>
            </div>
        </section>

        {{-- Purchase records --}}
        <section class="pf-card">
            <div class="pf-card-head">
                <span class="pf-card-icon pf-card-icon--accent">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12A1.125 1.125 0 0119.75 21H4.25a1.125 1.125 0 01-1.119-1.243l1.264-12A1.125 1.125 0 015.513 8.25h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                </span>
                <div class="min-w-0">
                    <h2 class="pf-card-title">{{ __('Purchase records') }}</h2>
                    <p class="pf-card-sub">
                        {{ trans_choice('{0} No orders recorded yet|{1} :count order on file|[2,*] :count orders on file', $summary['orders'], ['count' => $summary['orders']]) }}
                        @if ($summary['open_orders'] > 0)
                            &middot; {{ __(':count still in progress', ['count' => $summary['open_orders']]) }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('orders.index') }}" class="pf-link ml-auto shrink-0">{{ __('View all') }}</a>
            </div>

            <div class="pf-card-body">
                @if ($recentOrders->isEmpty())
                    <p class="pf-empty">{{ __('No orders have been placed with this account yet.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="pf-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Order') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Items') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-right">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td class="font-semibold">{{ $order->order_number }}</td>
                                        <td class="whitespace-nowrap">{{ $order->created_at?->format('d M Y') ?? $empty }}</td>
                                        <td>{{ $order->items_count }}</td>
                                        <td>
                                            <span @class([
                                                'pf-status',
                                                'pf-status--'.$order->status => in_array($order->status, \App\Models\Order::STATUSES, true),
                                            ])>{{ ucfirst($order->status) }}</span>
                                        </td>
                                        <td class="text-right whitespace-nowrap">{{ $order->currency ?: $currency }} {{ number_format((float) $order->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

        {{-- Delivery records --}}
        <section class="pf-card">
            <div class="pf-card-head">
                <span class="pf-card-icon">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                </span>
                <div class="min-w-0">
                    <h2 class="pf-card-title">{{ __('Saved addresses') }}</h2>
                    <p class="pf-card-sub">{{ __('Delivery records stored in the addresses table.') }}</p>
                </div>
            </div>

            <div class="pf-card-body space-y-2">
                @forelse ($addresses as $address)
                    <div class="pf-identity-row items-start">
                        <span class="pf-identity-icon">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="pf-field-value">{{ $address->address ?: __('Address not set') }}</p>
                            <p class="pf-card-sub">
                                {{ collect([$address->city, $address->state, $address->postal_code])->filter()->implode(', ') ?: __('No city details saved') }}
                            </p>
                            @if (filled($address->phone))
                                <p class="pf-card-sub">{{ $address->phone }}</p>
                            @endif
                        </div>
                        @if ($address->set_default)
                            <span class="pf-chip shrink-0">{{ __('Default') }}</span>
                        @endif
                    </div>
                @empty
                    <p class="pf-empty">{{ __('No delivery addresses are saved for this account.') }}</p>
                @endforelse
            </div>
        </section>

        @if ($isAdmin)
            {{-- Staff-only record --}}
            <section class="pf-card">
                <div class="pf-card-head">
                    <span class="pf-card-icon pf-card-icon--accent">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"/></svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="pf-card-title">{{ __('Staff access') }}</h2>
                        <p class="pf-card-sub">{{ __('This record can manage the store. Use it from the admin workspace.') }}</p>
                    </div>
                </div>

                <div class="pf-card-body grid gap-3 sm:grid-cols-2">
                    <div class="pf-field">
                        <span class="pf-field-label">{{ __('Role') }}</span>
                        <p class="pf-field-value">{{ $roleLabel }}</p>
                    </div>
                    <div class="pf-field">
                        <span class="pf-field-label">{{ __('Access') }}</span>
                        <p class="pf-field-value">
                            <a href="{{ route('admin.dashboard') }}" class="pf-link">{{ __('Open admin dashboard') }}</a>
                        </p>
                    </div>
                </div>
            </section>
        @endif
    </div>
</div>
