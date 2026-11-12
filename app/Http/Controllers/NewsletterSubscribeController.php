<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Stores the storefront newsletter signup, handles unsubscriptions,
 * and confirms subscriber status.
 */
class NewsletterSubscribeController extends Controller
{
    /**
     * Handle incoming subscription from footer or builder widgets.
     */
    public function __invoke(Request $request): RedirectResponse|JsonResponse
    {
        return $this->subscribe($request);
    }

    /**
     * Subscribe an email address to the newsletter.
     */
    public function subscribe(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:191'],
            'name' => ['nullable', 'string', 'max:191'],
            'source' => ['nullable', 'string', 'max:50'],
        ]);

        $subscriber = NewsletterSubscriber::query()->firstOrNew([
            'email' => strtolower(trim($validated['email'])),
        ]);

        if (! empty($validated['name'])) {
            $subscriber->name = trim($validated['name']);
        }

        $subscriber->source = $validated['source'] ?? ($subscriber->source ?: 'footer');
        $subscriber->is_active = true;
        $subscriber->unsubscribed_at = null;
        $subscriber->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Thanks for subscribing. We will keep you posted.'),
            ]);
        }

        return back()->with('newsletter_status', __('Thanks for subscribing. We will keep you posted.'));
    }

    /**
     * Unsubscribe a subscriber using a signed URL.
     */
    public function unsubscribe(Request $request, NewsletterSubscriber $subscriber): View
    {
        abort_unless($request->hasValidSignature(), 403, 'Invalid or expired unsubscribe link.');

        $subscriber->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);

        return view('newsletter.unsubscribed', [
            'email' => $subscriber->email,
        ]);
    }
}
