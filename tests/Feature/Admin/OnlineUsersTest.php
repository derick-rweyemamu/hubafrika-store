<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\OnlineUsers;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

class OnlineUsersTest extends CatalogTestCase
{
    public function test_it_separates_signed_in_customers_from_staff_and_ignores_idle_sessions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $idle = User::factory()->create(['is_admin' => false]);

        $this->recordSession($customer);
        $this->recordSession($admin);
        $this->recordSession($idle, minutesAgo: 30);
        $this->recordSession(null);

        $this->actingAs($admin);

        Livewire::test(OnlineUsers::class)
            ->assertOk()
            ->assertViewHas('usersOnline', 1)
            ->assertViewHas('staffOnline', 1);
    }

    public function test_role_holders_without_the_admin_flag_count_as_staff(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $agent = User::factory()->create(['is_admin' => false]);
        $agent->assignRole(Role::findOrCreate('Agent', 'web'));

        $this->recordSession($admin);
        $this->recordSession($agent);
        $this->recordSession(User::factory()->create(['is_admin' => false]));

        $this->actingAs($admin);

        Livewire::test(OnlineUsers::class)
            ->assertViewHas('usersOnline', 1)
            ->assertViewHas('staffOnline', 2);
    }

    public function test_the_admin_sidebar_shows_the_counters(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->recordSession($admin);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Users online')
            ->assertSee('Staff online');
    }

    private function recordSession(?User $user, int $minutesAgo = 0): void
    {
        DB::table('sessions')->insert([
            'id' => Str::random(40),
            'user_id' => $user?->getKey(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'payload' => '',
            'last_activity' => now()->subMinutes($minutesAgo)->getTimestamp(),
        ]);
    }
}
