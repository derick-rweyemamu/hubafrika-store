<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * "Online now" counters shown at the foot of the admin sidebar.
 *
 * An account is online when it made a request inside the last few minutes.
 * That fact is already recorded by the session driver the app ships with
 * (SESSION_DRIVER=database), whose `last_activity` column is refreshed on
 * every request, so no extra column or middleware is needed. Guests keep
 * sessions too but carry no `user_id`, so only signed-in accounts count.
 */
class OnlineUsers extends Component
{
    /**
     * Minutes without a request after which an account stops being online.
     */
    private const WINDOW_MINUTES = 5;

    public function render()
    {
        [$usersOnline, $staffOnline] = $this->counts();

        return view('livewire.admin.online-users', [
            'usersOnline' => $usersOnline,
            'staffOnline' => $staffOnline,
        ]);
    }

    /**
     * @return array{0: int, 1: int} Customers and staff currently online.
     */
    private function counts(): array
    {
        $onlineIds = DB::table(config('session.table', 'sessions'))
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(self::WINDOW_MINUTES)->getTimestamp())
            ->distinct()
            ->pluck('user_id');

        if ($onlineIds->isEmpty()) {
            return [0, 0];
        }

        // Anyone who can reach the panel is staff: the flag, a role, or a
        // directly granted permission. Everyone else is a customer.
        $staffOnline = User::query()
            ->whereIn('id', $onlineIds)
            ->where(function (Builder $query): void {
                $query->where('is_admin', true)
                    ->orWhereHas('roles')
                    ->orWhereHas('permissions');
            })
            ->count();

        return [$onlineIds->count() - $staffOnline, $staffOnline];
    }
}
