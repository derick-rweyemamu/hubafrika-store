<?php

namespace App\Livewire\Admin;

use App\Mail\NewsletterMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Subscribers extends Component
{
    use WithPagination;

    public function boot(): void
    {
        abort_unless(auth()->user()?->can('subscribers.view'), 403);
    }

    public string $activeTab = 'subscribers'; // 'subscribers', 'compose', 'campaigns'

    // --- Subscribers list filters ---
    public string $search = '';

    public string $statusFilter = 'all'; // 'all', 'active', 'unsubscribed'

    public string $sourceFilter = 'all';

    public int $perPage = 10;

    // --- Subscriber Add / Edit modal ---
    public bool $isSubscriberModalOpen = false;

    public ?int $subscriberId = null;

    public string $name = '';

    public string $email = '';

    public bool $isActive = true;

    // --- Email Composer ---
    public string $campaignSubject = '';

    public string $campaignContent = '';

    public string $testEmail = '';

    public bool $isSending = false;

    // --- Campaign Details modal ---
    public bool $isCampaignModalOpen = false;

    public ?NewsletterCampaign $selectedCampaign = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSourceFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['subscribers', 'compose', 'campaigns'], true)) {
            $this->activeTab = $tab;
        }
    }

    // =========================================================================
    // Subscriber CRUD
    // =========================================================================

    public function createSubscriber(): void
    {
        $this->authorizeManage();
        $this->resetSubscriberForm();
        $this->isSubscriberModalOpen = true;
    }

    public function editSubscriber(NewsletterSubscriber $subscriber): void
    {
        $this->authorizeManage();
        $this->subscriberId = $subscriber->id;
        $this->name = (string) $subscriber->name;
        $this->email = $subscriber->email;
        $this->isActive = $subscriber->is_active;
        $this->isSubscriberModalOpen = true;
    }

    public function saveSubscriber(): void
    {
        $this->authorizeManage();

        $validated = $this->validate([
            'name' => ['nullable', 'string', 'max:191'],
            'email' => [
                'required',
                'string',
                'email',
                'max:191',
                Rule::unique('newsletter_subscribers', 'email')->ignore($this->subscriberId),
            ],
            'isActive' => ['boolean'],
        ]);

        if ($this->subscriberId) {
            $subscriber = NewsletterSubscriber::query()->findOrFail($this->subscriberId);
            $subscriber->update([
                'name' => trim($validated['name'] ?? '') ?: null,
                'email' => strtolower(trim($validated['email'])),
                'is_active' => $validated['isActive'],
                'unsubscribed_at' => $validated['isActive'] ? null : ($subscriber->unsubscribed_at ?? now()),
            ]);
            session()->flash('success', __('Subscriber updated successfully.'));
        } else {
            NewsletterSubscriber::query()->create([
                'name' => trim($validated['name'] ?? '') ?: null,
                'email' => strtolower(trim($validated['email'])),
                'source' => 'admin',
                'is_active' => $validated['isActive'],
                'unsubscribed_at' => $validated['isActive'] ? null : now(),
            ]);
            session()->flash('success', __('Subscriber created successfully.'));
        }

        $this->isSubscriberModalOpen = false;
        $this->resetSubscriberForm();
    }

    public function toggleSubscriberStatus(NewsletterSubscriber $subscriber): void
    {
        $this->authorizeManage();

        $newStatus = ! $subscriber->is_active;
        $subscriber->update([
            'is_active' => $newStatus,
            'unsubscribed_at' => $newStatus ? null : now(),
        ]);

        session()->flash('success', $newStatus
            ? __('Subscriber has been activated.')
            : __('Subscriber has been unsubscribed.'));
    }

    public function deleteSubscriber(NewsletterSubscriber $subscriber): void
    {
        $this->authorizeManage();
        $subscriber->delete();

        session()->flash('success', __('Subscriber deleted successfully.'));
    }

    public function exportCsv(): StreamedResponse
    {
        $this->authorizeManage();

        $subscribers = NewsletterSubscriber::query()
            ->when($this->statusFilter === 'active', fn (Builder $q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'unsubscribed', fn (Builder $q) => $q->where('is_active', false))
            ->when($this->sourceFilter !== 'all', fn (Builder $q) => $q->where('source', $this->sourceFilter))
            ->search($this->search)
            ->latest('id')
            ->get();

        $filename = 'subscribers_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($subscribers): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Email', 'Name', 'Source', 'Status', 'Subscribed At', 'Unsubscribed At']);

            foreach ($subscribers as $sub) {
                fputcsv($handle, [
                    $sub->id,
                    $sub->email,
                    $sub->name ?? '',
                    $sub->source,
                    $sub->is_active ? 'Active' : 'Unsubscribed',
                    $sub->created_at?->toIso8601String() ?? '',
                    $sub->unsubscribed_at?->toIso8601String() ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function resetSubscriberForm(): void
    {
        $this->reset(['subscriberId', 'name', 'email', 'isActive']);
        $this->isActive = true;
    }

    // =========================================================================
    // Newsletter Campaign Actions
    // =========================================================================

    public function sendTestEmail(): void
    {
        $this->authorizeManage();

        $this->validate([
            'campaignSubject' => ['required', 'string', 'max:191'],
            'campaignContent' => ['required', 'string', 'min:5'],
            'testEmail' => ['required', 'string', 'email', 'max:191'],
        ]);

        $dummyCampaign = new NewsletterCampaign([
            'subject' => $this->campaignSubject,
            'content' => $this->campaignContent,
        ]);

        Mail::to(trim($this->testEmail))->send(new NewsletterMail($dummyCampaign));

        session()->flash('success', __('Test email sent to :email.', ['email' => $this->testEmail]));
    }

    public function sendCampaign(): void
    {
        $this->authorizeManage();

        $this->validate([
            'campaignSubject' => ['required', 'string', 'max:191'],
            'campaignContent' => ['required', 'string', 'min:5'],
        ]);

        $activeSubscribers = NewsletterSubscriber::query()->active()->get();

        if ($activeSubscribers->isEmpty()) {
            session()->flash('error', __('There are no active subscribers to send this email to.'));

            return;
        }

        $campaign = NewsletterCampaign::query()->create([
            'subject' => trim($this->campaignSubject),
            'content' => $this->campaignContent,
            'status' => 'sending',
            'recipients_count' => 0,
            'created_by' => auth()->id(),
        ]);

        $sentCount = 0;

        foreach ($activeSubscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new NewsletterMail($campaign, $subscriber));
                $sentCount++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $campaign->update([
            'status' => 'sent',
            'recipients_count' => $sentCount,
            'sent_at' => now(),
        ]);

        $this->reset(['campaignSubject', 'campaignContent', 'testEmail']);
        $this->activeTab = 'campaigns';

        session()->flash('success', __('Campaign ":subject" sent to :count subscriber(s).', [
            'subject' => $campaign->subject,
            'count' => $sentCount,
        ]));
    }

    public function viewCampaign(int $campaignId): void
    {
        $this->selectedCampaign = NewsletterCampaign::query()->with('author')->findOrFail($campaignId);
        $this->isCampaignModalOpen = true;
    }

    public function closeCampaignModal(): void
    {
        $this->isCampaignModalOpen = false;
        $this->selectedCampaign = null;
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()?->can('subscribers.manage'), 403);
    }

    public function render(): View
    {
        $subscribersQuery = NewsletterSubscriber::query()
            ->search($this->search)
            ->when($this->statusFilter === 'active', fn (Builder $q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'unsubscribed', fn (Builder $q) => $q->where('is_active', false))
            ->when($this->sourceFilter !== 'all', fn (Builder $q) => $q->where('source', $this->sourceFilter))
            ->latest('id');

        $subscribers = $subscribersQuery->paginate($this->perPage);

        $campaigns = NewsletterCampaign::query()
            ->with('author')
            ->latest('id')
            ->paginate(10);

        $stats = [
            'total' => NewsletterSubscriber::query()->count(),
            'active' => NewsletterSubscriber::query()->active()->count(),
            'unsubscribed' => NewsletterSubscriber::query()->where('is_active', false)->count(),
            'campaigns' => NewsletterCampaign::query()->count(),
        ];

        $sources = NewsletterSubscriber::query()
            ->distinct()
            ->pluck('source')
            ->filter()
            ->values();

        return view('livewire.admin.subscribers', [
            'subscribers' => $subscribers,
            'campaigns' => $campaigns,
            'stats' => $stats,
            'sources' => $sources,
        ])->layout('components.layouts.admin', ['title' => 'Subscribers']);
    }
}
