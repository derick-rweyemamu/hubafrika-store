<?php

namespace App\Livewire\Storefront;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Payments extends Component
{
    use WithPagination;

    public function render(): View
    {
        $customer = auth()->user();
        abort_unless($customer instanceof User, 403);

        return view('livewire.storefront.payments', [
            'payments' => Payment::query()
                ->with('order:id,order_number,currency,total')
                ->whereHas('order', fn ($query) => $query->where('user_id', $customer->id))
                ->latest('id')
                ->paginate(10),
            'awaitingCount' => Payment::query()
                ->whereHas('order', fn ($query) => $query->where('user_id', $customer->id))
                ->where('status', Payment::STATUS_AWAITING_VERIFICATION)
                ->count(),
        ])->layout('components.layouts.storefront', ['title' => 'My payments', 'navigationArea' => 'store']);
    }
}
