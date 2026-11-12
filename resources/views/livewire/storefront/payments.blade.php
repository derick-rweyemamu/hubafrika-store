@php
    $statusTone = [
        \App\Models\Payment::STATUS_PENDING => 'pending',
        \App\Models\Payment::STATUS_AWAITING_VERIFICATION => 'processing',
        \App\Models\Payment::STATUS_VERIFIED => 'delivered',
        \App\Models\Payment::STATUS_REJECTED => 'cancelled',
        \App\Models\Payment::STATUS_REFUNDED => 'refunded',
    ];

    $empty = '—';
@endphp

<div class="pf-page space-y-6">
    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Every payment recorded on your orders, newest first.') }}</p>

    <x-account-tabs active="payments" />

    <x-flash-toast />

    <div class="grid grid-cols-2 gap-3 sm:max-w-md">
        <div class="pf-stat">
            <p class="pf-stat-value">{{ $payments->total() }}</p>
            <p class="pf-stat-label">{{ __('Payments recorded') }}</p>
        </div>
        <div class="pf-stat">
            <p class="pf-stat-value">{{ $awaitingCount }}</p>
            <p class="pf-stat-label">{{ __('Awaiting verification') }}</p>
        </div>
    </div>

    @if ($payments->isEmpty())
        <section class="pf-card">
            <div class="pf-card-body space-y-3">
                <p class="pf-empty">{{ __('No payments have been recorded on this account yet.') }}</p>
                <p class="text-center">
                    <a href="{{ route('shop') }}" wire:navigate class="pf-btn">{{ __('Browse the shop') }}</a>
                </p>
            </div>
        </section>
    @else
        <section class="pf-card">
            <div class="pf-card-head">
                <span class="pf-card-icon">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                </span>
                <div class="min-w-0">
                    <h2 class="pf-card-title">{{ __('Payment history') }}</h2>
                    <p class="pf-card-sub">{{ __('Transaction IDs you submitted and how each one was resolved.') }}</p>
                </div>
            </div>

            <div class="pf-card-body overflow-x-auto">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Order') }}</th>
                            <th>{{ __('Method') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr wire:key="payment-{{ $payment->id }}">
                                <td class="whitespace-nowrap">
                                    {{ ($payment->submitted_at ?? $payment->created_at)?->format('d M Y') ?? $empty }}
                                </td>
                                <td class="whitespace-nowrap font-semibold">
                                    @if ($payment->order)
                                        <a href="{{ route('orders.show', $payment->order) }}" wire:navigate class="pf-link">{{ $payment->order->order_number }}</a>
                                    @else
                                        {{ $empty }}
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">{{ $payment->methodLabel() }}</td>
                                <td class="whitespace-nowrap font-mono text-xs">{{ $payment->reference ?: $empty }}</td>
                                <td>
                                    <span @class(['pf-status', 'pf-status--'.($statusTone[$payment->status] ?? 'pending')])>{{ $payment->statusLabel() }}</span>
                                </td>
                                <td class="whitespace-nowrap text-right">
                                    {{ $payment->order?->money($payment->amount) ?? number_format((float) $payment->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div>{{ $payments->links() }}</div>
    @endif
</div>
