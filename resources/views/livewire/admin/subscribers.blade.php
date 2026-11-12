@php
    $canManage = auth()->user()?->can('subscribers.manage') ?? false;
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Newsletter Subscribers</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage email subscribers, compose marketing updates, and track email campaigns.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            @if ($canManage)
                <button type="button" wire:click="exportCsv"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Export CSV
                </button>
                <button type="button" wire:click="createSubscriber"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Subscriber
                </button>
                <button type="button" wire:click="setTab('compose')"
                        class="admin-primary-bg inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:opacity-95">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    Compose Email
                </button>
            @endif
        </div>
    </div>

    <x-flash-toast />

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800 dark:border-red-900/40 dark:bg-red-950/40 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Subscribers</div>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Active Audience</div>
            <div class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Unsubscribed</div>
            <div class="mt-2 text-2xl font-bold text-gray-600 dark:text-gray-300">{{ number_format($stats['unsubscribed']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-wider text-sky-600 dark:text-sky-400">Campaigns Sent</div>
            <div class="mt-2 text-2xl font-bold text-sky-700 dark:text-sky-400">{{ number_format($stats['campaigns']) }}</div>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-800">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button type="button" wire:click="setTab('subscribers')"
                    @class([
                        'inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-semibold transition',
                        'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' => $activeTab === 'subscribers',
                        'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $activeTab !== 'subscribers',
                    ])>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                Subscribers ({{ $stats['total'] }})
            </button>
            <button type="button" wire:click="setTab('compose')"
                    @class([
                        'inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-semibold transition',
                        'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' => $activeTab === 'compose',
                        'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $activeTab !== 'compose',
                    ])>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
                Compose Email
            </button>
            <button type="button" wire:click="setTab('campaigns')"
                    @class([
                        'inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-semibold transition',
                        'border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' => $activeTab === 'campaigns',
                        'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $activeTab !== 'campaigns',
                    ])>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Campaign History ({{ $stats['campaigns'] }})
            </button>
        </nav>
    </div>

    {{-- TAB 1: SUBSCRIBERS LIST --}}
    @if ($activeTab === 'subscribers')
        <div class="flex flex-col gap-3 sm:flex-row">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by email or name..."
                   class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 sm:max-w-sm">

            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                Status
                <select wire:model.live="statusFilter" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="all">All statuses</option>
                    <option value="active">Active only</option>
                    <option value="unsubscribed">Unsubscribed only</option>
                </select>
            </label>

            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                Source
                <select wire:model.live="sourceFilter" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="all">All sources</option>
                    @foreach ($sources as $source)
                        <option value="{{ $source }}">{{ ucfirst($source) }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                Show
                <select wire:model.live="perPage" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </label>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/70">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subscriber</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Source</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date Subscribed</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($subscribers as $sub)
                            <tr wire:key="subscriber-{{ $sub->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $sub->email }}</div>
                                    @if ($sub->name)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $sub->name }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ ucfirst($sub->source) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' => $sub->is_active,
                                        'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400' => ! $sub->is_active,
                                    ])>
                                        {{ $sub->is_active ? 'Active' : 'Unsubscribed' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <div>{{ $sub->created_at?->format('M d, Y') ?? '—' }}</div>
                                    <div class="text-xs text-gray-400">{{ $sub->created_at?->format('h:i A') }}</div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if ($canManage)
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" wire:click="toggleSubscriberStatus({{ $sub->id }})"
                                                    title="{{ $sub->is_active ? 'Unsubscribe' : 'Activate' }}"
                                                    class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                                @if ($sub->is_active)
                                                    <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                @else
                                                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                @endif
                                            </button>
                                            <button type="button" wire:click="editSubscriber({{ $sub->id }})"
                                                    title="Edit Subscriber"
                                                    class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>
                                            <button type="button" wire:click="deleteSubscriber({{ $sub->id }})"
                                                    wire:confirm="Are you sure you want to remove this subscriber?"
                                                    title="Delete Subscriber"
                                                    class="rounded-lg p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/40">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No subscribers found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($subscribers->hasPages())
                <div class="border-t border-gray-200 p-4 dark:border-gray-800">
                    {{ $subscribers->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- TAB 2: COMPOSE EMAIL --}}
    @if ($activeTab === 'compose')
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <form wire:submit.prevent="sendCampaign" class="space-y-6">
                {{-- Recipient count banner --}}
                <div class="flex items-center justify-between rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        <span>Target Audience: <strong>{{ number_format($stats['active']) }} active subscriber(s)</strong> will receive this email.</span>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">All Active</span>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1.5">
                        Email Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="campaignSubject" placeholder="e.g. Special Offer: 20% off all Solar Inverters this week!"
                           class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                    @error('campaignSubject')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1.5">
                        Email Body Content <span class="text-red-500">*</span>
                    </label>
                    <x-rich-editor wire:model="campaignContent" placeholder="Write your newsletter content here..." min-height="240px" />
                    @error('campaignContent')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                        An automatic footer with store details and an unsubscribe link will be attached to every outgoing email.
                    </p>
                </div>

                {{-- Test Email Sandbox Box --}}
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/40">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Send Test Email First</h4>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        Preview formatting in your own inbox before broadcasting to all subscribers.
                    </p>
                    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                        <input type="email" wire:model="testEmail" placeholder="your-email@example.com"
                               class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 sm:max-w-sm">
                        <button type="button" wire:click="sendTestEmail"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            Send Test
                        </button>
                    </div>
                    @error('testEmail')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" wire:click="setTab('subscribers')"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        Cancel
                    </button>
                    @if ($canManage)
                        <button type="submit"
                                wire:confirm="Are you ready to send this campaign to {{ number_format($stats['active']) }} active subscriber(s)?"
                                class="admin-primary-bg inline-flex items-center gap-2 rounded-lg px-6 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:opacity-95">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            <span>Send Campaign to All Subscribers</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    @endif

    {{-- TAB 3: CAMPAIGN HISTORY --}}
    @if ($activeTab === 'campaigns')
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/70">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subject</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Recipients</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sent By</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sent Date</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($campaigns as $campaign)
                            <tr wire:key="campaign-{{ $campaign->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-5 py-4 font-medium text-gray-900 dark:text-gray-100">
                                    {{ $campaign->subject }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 dark:text-emerald-400">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                        {{ number_format($campaign->recipients_count) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $campaign->author?->name ?? 'Admin' }}
                                </td>
                                <td class="px-5 py-4">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' => $campaign->status === 'sent',
                                        'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200' => $campaign->status === 'sending',
                                        'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' => $campaign->status === 'draft',
                                    ])>
                                        {{ ucfirst($campaign->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $campaign->sent_at?->format('M d, Y h:i A') ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" wire:click="viewCampaign({{ $campaign->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        View Content
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No email campaigns sent yet. Click "Compose Email" to broadcast your first newsletter!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($campaigns->hasPages())
                <div class="border-t border-gray-200 p-4 dark:border-gray-800">
                    {{ $campaigns->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- MODAL: Add / Edit Subscriber --}}
    @if ($isSubscriberModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $subscriberId ? 'Edit Subscriber' : 'Add New Subscriber' }}
                    </h3>
                    <button type="button" wire:click="$set('isSubscriberModalOpen', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveSubscriber" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" wire:model="email" placeholder="customer@example.com" required
                               class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Subscriber Name (Optional)</label>
                        <input type="text" wire:model="name" placeholder="John Doe"
                               class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="isActive" wire:model="isActive" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="isActive" class="text-sm text-gray-700 dark:text-gray-300 font-medium">Active Subscriber (receives broadcasts)</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" wire:click="$set('isSubscriberModalOpen', false)"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            Cancel
                        </button>
                        <button type="submit"
                                class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-xs hover:opacity-95">
                            {{ $subscriberId ? 'Update' : 'Add Subscriber' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL: View Campaign Details --}}
    @if ($isCampaignModalOpen && $selectedCampaign)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs">
            <div class="w-full max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ $selectedCampaign->subject }}
                        </h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Sent to {{ number_format($selectedCampaign->recipients_count) }} recipient(s) on {{ $selectedCampaign->sent_at?->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeCampaignModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 max-h-[60vh] overflow-y-auto rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-950 prose dark:prose-invert max-w-none text-sm">
                    {!! \App\Support\RichText::toHtml($selectedCampaign->content) !!}
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800 mt-4">
                    <button type="button" wire:click="closeCampaignModal"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

