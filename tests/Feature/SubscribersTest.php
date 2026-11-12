<?php

namespace Tests\Feature;

use App\Livewire\Admin\Subscribers;
use App\Mail\NewsletterMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SubscribersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createAdminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->assignRole($role);

        return $admin;
    }

    public function test_guest_can_subscribe_to_newsletter_via_form(): void
    {
        $response = $this->post(route('newsletter.subscribe'), [
            'email' => 'solarfan@example.com',
            'name' => 'Solar Fan',
        ]);

        $response->assertSessionHas('newsletter_status');
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'solarfan@example.com',
            'name' => 'Solar Fan',
            'is_active' => true,
        ]);
    }

    public function test_resubscribing_reactivates_inactive_subscriber(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'existing@example.com',
            'is_active' => false,
            'unsubscribed_at' => now()->subDays(5),
        ]);

        $this->post(route('newsletter.subscribe'), [
            'email' => 'existing@example.com',
        ]);

        $subscriber->refresh();
        $this->assertTrue($subscriber->is_active);
        $this->assertNull($subscriber->unsubscribed_at);
    }

    public function test_subscriber_can_unsubscribe_via_signed_link(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'leave@example.com',
            'is_active' => true,
        ]);

        $signedUrl = URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $subscriber->id]);

        $response = $this->get($signedUrl);
        $response->assertOk();
        $response->assertSee('You have been unsubscribed');

        $subscriber->refresh();
        $this->assertFalse($subscriber->is_active);
        $this->assertNotNull($subscriber->unsubscribed_at);
    }

    public function test_unsigned_unsubscribe_request_is_rejected(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'tamper@example.com',
            'is_active' => true,
        ]);

        $unsignedUrl = route('newsletter.unsubscribe', ['subscriber' => $subscriber->id]);

        $this->get($unsignedUrl)->assertForbidden();
    }

    public function test_unauthorized_user_cannot_view_subscribers_admin_module(): void
    {
        $regularUser = User::factory()->create(['is_admin' => false]);
        $this->actingAs($regularUser);

        Livewire::test(Subscribers::class)->assertForbidden();
    }

    public function test_admin_can_view_subscribers_and_switch_tabs(): void
    {
        $this->actingAs($this->createAdminUser());

        NewsletterSubscriber::query()->create([
            'email' => 'fan1@example.com',
            'name' => 'Fan One',
            'is_active' => true,
        ]);

        Livewire::test(Subscribers::class)
            ->assertSee('fan1@example.com')
            ->assertSee('Fan One')
            ->call('setTab', 'compose')
            ->assertSet('activeTab', 'compose')
            ->call('setTab', 'campaigns')
            ->assertSet('activeTab', 'campaigns');
    }

    public function test_admin_can_create_update_toggle_and_delete_subscriber(): void
    {
        $this->actingAs($this->createAdminUser());

        Livewire::test(Subscribers::class)
            ->call('createSubscriber')
            ->set('email', 'newsub@example.com')
            ->set('name', 'New Subscriber')
            ->set('isActive', true)
            ->call('saveSubscriber')
            ->assertHasNoErrors();

        $sub = NewsletterSubscriber::query()->where('email', 'newsub@example.com')->firstOrFail();
        $this->assertTrue($sub->is_active);

        // Edit
        Livewire::test(Subscribers::class)
            ->call('editSubscriber', $sub->id)
            ->set('name', 'Updated Name')
            ->call('saveSubscriber')
            ->assertHasNoErrors();

        $this->assertSame('Updated Name', $sub->fresh()->name);

        // Toggle status
        Livewire::test(Subscribers::class)
            ->call('toggleSubscriberStatus', $sub->id);

        $this->assertFalse($sub->fresh()->is_active);

        // Delete
        Livewire::test(Subscribers::class)
            ->call('deleteSubscriber', $sub->id);

        $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $sub->id]);
    }

    public function test_admin_can_send_test_email(): void
    {
        Mail::fake();
        $this->actingAs($this->createAdminUser());

        Livewire::test(Subscribers::class)
            ->set('campaignSubject', 'Test Subject')
            ->set('campaignContent', 'Test Content Body')
            ->set('testEmail', 'tester@example.com')
            ->call('sendTestEmail')
            ->assertHasNoErrors();

        Mail::assertSent(NewsletterMail::class, function ($mail) {
            return $mail->hasTo('tester@example.com') && $mail->campaign->subject === 'Test Subject';
        });
    }

    public function test_admin_can_broadcast_campaign_to_all_active_subscribers(): void
    {
        Mail::fake();
        $this->actingAs($this->createAdminUser());

        NewsletterSubscriber::query()->create(['email' => 'sub1@example.com', 'is_active' => true]);
        NewsletterSubscriber::query()->create(['email' => 'sub2@example.com', 'is_active' => true]);
        NewsletterSubscriber::query()->create(['email' => 'inactive@example.com', 'is_active' => false]);

        Livewire::test(Subscribers::class)
            ->set('campaignSubject', 'Black Friday Solar Deals')
            ->set('campaignContent', '<p>Huge discounts on all hybrid inverters this week!</p>')
            ->call('sendCampaign')
            ->assertHasNoErrors()
            ->assertSet('activeTab', 'campaigns');

        $campaign = NewsletterCampaign::query()->firstOrFail();
        $this->assertSame('Black Friday Solar Deals', $campaign->subject);
        $this->assertSame('sent', $campaign->status);
        $this->assertSame(2, $campaign->recipients_count);
        $this->assertNotNull($campaign->sent_at);

        Mail::assertSent(NewsletterMail::class, 2);
    }
}
