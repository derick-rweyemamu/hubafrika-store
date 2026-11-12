<?php

namespace App\Livewire\Storefront;

use App\Models\Payment;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class MyOrders extends Component
{
    use WithPagination;

    public function render()
    {
        $customer = auth()->user();
        abort_unless($customer instanceof User, 403);

        return view('livewire.storefront.my-orders', [
            'orders' => $customer->orders()
                ->with(['items', 'payments'])
                ->latest()
                ->paginate(10),
            'lipaAccount' => Payment::lipaAccount(),
        ])->layout('components.layouts.storefront', [
            'title' => 'My orders',
            'navigationArea' => 'store',
            'pageHeader' => [
                'title' => 'My orders',
            ],
        ]);
    }
}
