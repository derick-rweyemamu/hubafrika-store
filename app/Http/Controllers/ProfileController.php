<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

class ProfileController extends Controller
{
    /**
     * Contact fields that are optional on the users table and should be stored
     * as null instead of an empty string when the user clears them.
     *
     * @var list<string>
     */
    private const OPTIONAL_CONTACT_FIELDS = ['phone', 'address', 'city', 'state', 'postal_code', 'country'];

    /**
     * Display the profile: the identity record plus everything else stored
     * against this user, so a customer and the staff supporting them both see
     * the same picture of the account.
     */
    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $recentOrders = $user->orders()
            ->withCount('items')
            ->latest()
            ->take(5)
            ->get();

        return view('profile.edit', [
            'user' => $user,
            'activeTab' => $this->activeTab($request),
            'summary' => $this->purchaseSummary($user),
            'recentOrders' => $recentOrders,
            'addresses' => $user->addresses()
                ->orderByDesc('set_default')
                ->latest()
                ->get(),
            'currency' => $recentOrders->first()?->currency ?? $this->storeCurrency(),
            'avatarUrl' => $this->avatarUrl($user),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $attributes = $request->validated();

        foreach (self::OPTIONAL_CONTACT_FIELDS as $field) {
            $attributes[$field] = filled($attributes[$field] ?? null) ? $attributes[$field] : null;
        }

        $user->fill($attributes);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            $user->verification_status = 0;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     *
     * Administrator records are protected: they own the store and must be
     * re-assigned before they can ever be removed.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user->isAdmin(), 403, __('Administrator accounts cannot be deleted.'));

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Which panel is open: the explicit "?tab=" value, otherwise the panel that
     * holds the feedback the user was redirected back for.
     */
    private function activeTab(Request $request): string
    {
        $requested = (string) $request->query('tab', '');

        if (in_array($requested, ['info', 'update', 'security'], true)) {
            return $requested;
        }

        $errors = $request->session()->get('errors');

        if ($errors instanceof ViewErrorBag) {
            if ($errors->getBag('userDeletion')->isNotEmpty() || $errors->getBag('updatePassword')->isNotEmpty()) {
                return 'security';
            }

            if ($errors->isNotEmpty()) {
                return 'update';
            }
        }

        return $request->session()->get('status') === 'password-updated' ? 'security' : 'info';
    }

    /**
     * Aggregated purchase and delivery records for the account overview.
     *
     * @return array{orders: int, open_orders: int, spent: float, addresses: int, last_order_at: ?Carbon}
     */
    private function purchaseSummary(User $user): array
    {
        $lastOrderAt = $user->orders()->max('created_at');

        return [
            'orders' => $user->orders()->count(),
            'open_orders' => $user->orders()
                ->whereIn('status', ['pending', 'processing', 'shipped'])
                ->count(),
            'spent' => (float) $user->orders()->where('status', '!=', 'cancelled')->sum('total'),
            'addresses' => $user->addresses()->count(),
            'last_order_at' => filled($lastOrderAt) ? Carbon::parse($lastOrderAt) : null,
        ];
    }

    private function storeCurrency(): string
    {
        $currency = BusinessSetting::query()->where('type', 'currency_code')->value('value');

        return filled($currency) ? Str::upper((string) $currency) : 'USD';
    }

    private function avatarUrl(User $user): ?string
    {
        return $user->avatarUrl();
    }
}
