@php
    // Read straight from the session table, so the numbers refresh on their own
    // while the panel stays open. `mini` is the collapsed (icon-only) sidebar.
    $rows = [
        ['label' => 'Users online', 'count' => $usersOnline, 'dot' => 'bg-emerald-500'],
        ['label' => 'Staff online', 'count' => $staffOnline, 'dot' => 'bg-sky-500'],
    ];
@endphp

<div wire:poll.30s class="shrink-0 border-t border-gray-200 px-2 py-2.5 dark:border-gray-800">
    <p x-show="!mini" class="px-1.5 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
        Online now
    </p>

    @foreach ($rows as $row)
        <div class="flex items-center gap-2 rounded-lg px-1.5 py-1.5 text-sm" :class="mini ? 'justify-center' : 'justify-between'">
            <span class="flex min-w-0 items-center gap-2" title="{{ $row['label'] }}">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="absolute inline-flex h-full w-full rounded-full {{ $row['dot'] }} opacity-60"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full {{ $row['dot'] }}"></span>
                </span>
                <span x-show="!mini" class="truncate text-gray-600 dark:text-gray-400">{{ $row['label'] }}</span>
            </span>

            <span x-show="!mini"
                  class="admin-primary-soft-bg admin-primary-text shrink-0 rounded-md px-1.5 py-0.5 text-xs font-semibold">
                {{ number_format($row['count']) }}
            </span>

            <span x-cloak x-show="mini" class="text-xs font-semibold">{{ number_format($row['count']) }}</span>
        </div>
    @endforeach
</div>
