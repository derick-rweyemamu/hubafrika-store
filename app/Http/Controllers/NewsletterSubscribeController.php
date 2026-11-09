<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Stores the storefront footer newsletter signup, then returns the visitor to
 * the page they signed up from with a confirmation message.
 */
class NewsletterSubscribeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:191'],
        ]);

        NewsletterSubscriber::query()->firstOrCreate([
            'email' => strtolower(trim($validated['email'])),
        ], [
            'source' => 'footer',
        ]);

        return back()->with('newsletter_status', __('Thanks for subscribing. We will keep you posted.'));
    }
}
