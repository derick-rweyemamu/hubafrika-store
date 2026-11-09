<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Customers extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public string $status = 'all';

    public bool $isOpen = false;

    public ?int $customerId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $phone = '';

    public string $address = '';

    public string $country = '';

    public string $state = '';

    public string $city = '';

    public string $postalCode = '';

    public ?int $addressId = null;

    public bool $isAddressFormOpen = false;

    public string $addressLine = '';

    public string $addressPhone = '';

    public string $addressPostalCode = '';

    public bool $addressIsDefault = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorizeAdmin();
        $this->resetValidation();
        $this->resetForm();
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->authorizeAdmin();
        $this->isOpen = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $this->authorizeAdmin();
        $this->resetValidation();

        $customer = $this->customerQuery()->findOrFail($id);

        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->email = $customer->email ?? '';
        $this->phone = $customer->phone ?? '';
        $this->address = $customer->address ?? '';
        $this->country = $customer->country ?? '';
        $this->state = $customer->state ?? '';
        $this->city = $customer->city ?? '';
        $this->postalCode = $customer->postal_code ?? '';
        $this->password = '';
        $this->resetAddressForm();
        $this->isOpen = true;
    }

    public function save(): void
    {
        $this->authorizeAdmin();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($this->customerId),
            ],
            'password' => [
                Rule::requiredIf($this->customerId === null),
                'nullable',
                'string',
                'min:8',
                'max:191',
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:300'],
            'country' => ['nullable', 'string', 'max:30'],
            'state' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:30'],
            'postalCode' => ['nullable', 'string', 'max:20'],
        ]);

        $customer = $this->customerId === null
            ? new User
            : $this->customerQuery()->findOrFail($this->customerId);
        $emailChanged = $customer->exists && $customer->email !== $validated['email'];

        $customer->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'address' => $validated['address'] ?: null,
            'country' => $validated['country'] ?: null,
            'state' => $validated['state'] ?: null,
            'city' => $validated['city'] ?: null,
            'postal_code' => $validated['postalCode'] ?: null,
        ]);

        if ($validated['password'] !== null && $validated['password'] !== '') {
            $customer->password = Hash::make($validated['password']);
        }

        if (! $customer->exists) {
            $customer->banned = false;
        }

        if ($emailChanged) {
            $customer->email_verified_at = null;
            $customer->verification_status = 0;
        }

        $customer->save();

        session()->flash(
            'message',
            $this->customerId === null
                ? 'Customer created successfully.'
                : 'Customer updated successfully.',
        );
        $this->isOpen = false;
        $this->resetForm();
    }

    public function toggleSuspension(int $id): void
    {
        $this->authorizeAdmin();

        $customer = $this->customerQuery()->findOrFail($id);
        $customer->banned = ! $customer->banned;
        $customer->save();

        session()->flash(
            'message',
            $customer->banned
                ? 'Customer suspended. Their data and purchase history are retained.'
                : 'Customer restored successfully.',
        );
    }

    public function createAddress(): void
    {
        $this->authorizeAdmin();
        $this->customerQuery()->findOrFail($this->customerId);
        $this->resetValidation();
        $this->resetAddressForm();
        $this->isAddressFormOpen = true;
    }

    public function editAddress(int $id): void
    {
        $this->authorizeAdmin();
        $customer = $this->customerQuery()->findOrFail($this->customerId);
        $address = $customer->addresses()->findOrFail($id);

        $this->resetValidation();
        $this->addressId = $address->id;
        $this->addressLine = $address->address ?? '';
        $this->addressPhone = $address->phone ?? '';
        $this->addressPostalCode = $address->postal_code ?? '';
        $this->addressIsDefault = (bool) $address->set_default;
        $this->isAddressFormOpen = true;
    }

    public function saveAddress(): void
    {
        $this->authorizeAdmin();

        $customer = $this->customerQuery()->findOrFail($this->customerId);
        $validated = $this->validate([
            'addressLine' => ['required', 'string', 'max:255'],
            'addressPhone' => ['nullable', 'string', 'max:255'],
            'addressPostalCode' => ['nullable', 'string', 'max:255'],
            'addressIsDefault' => ['boolean'],
        ]);

        DB::transaction(function () use ($customer, $validated): void {
            $address = $this->addressId === null
                ? $customer->addresses()->make()
                : $customer->addresses()->findOrFail($this->addressId);

            if ($validated['addressIsDefault']) {
                $customer->addresses()
                    ->when($address->exists, fn (Builder $query) => $query->whereKeyNot($address->id))
                    ->update(['set_default' => false]);
            }

            $address->fill([
                'address' => $validated['addressLine'],
                'phone' => $validated['addressPhone'] ?: null,
                'postal_code' => $validated['addressPostalCode'] ?: null,
                'set_default' => $validated['addressIsDefault'],
            ]);
            $address->save();
        });

        session()->flash(
            'message',
            $this->addressId === null
                ? 'Customer address added successfully.'
                : 'Customer address updated successfully.',
        );
        $this->resetAddressForm();
        $this->isAddressFormOpen = false;
    }

    public function deleteAddress(int $id): void
    {
        $this->authorizeAdmin();

        $customer = $this->customerQuery()->findOrFail($this->customerId);
        $customer->addresses()->findOrFail($id)->delete();
        $this->resetAddressForm();

        session()->flash('message', 'Customer address deleted successfully.');
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('customers.view'), 403);

        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;
        $search = mb_substr($this->search, 0, 100);

        return view('livewire.admin.customers', [
            'customers' => $this->customerQuery()
                ->withCount('addresses')
                ->when($this->status === 'active', fn (Builder $query) => $query->where('banned', false))
                ->when($this->status === 'suspended', fn (Builder $query) => $query->where('banned', true))
                ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                }))
                ->orderBy('name')
                ->paginate($perPage),
            'editingCustomer' => $this->isOpen && $this->customerId !== null
                ? $this->customerQuery()->with('addresses')->find($this->customerId)
                : null,
        ])->layout('components.layouts.admin', ['title' => 'Customers']);
    }

    private function customerQuery(): Builder
    {
        return User::query()
            ->where('is_admin', false)
            ->whereDoesntHave('roles', static fn (Builder $query) => $query->where('name', 'admin'));
    }

    private function resetForm(): void
    {
        $this->reset([
            'customerId',
            'name',
            'email',
            'password',
            'phone',
            'address',
            'country',
            'state',
            'city',
            'postalCode',
        ]);
        $this->resetAddressForm();
    }

    private function resetAddressForm(): void
    {
        $this->reset([
            'addressId',
            'addressLine',
            'addressPhone',
            'addressPostalCode',
            'addressIsDefault',
            'isAddressFormOpen',
        ]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->can('customers.manage'), 403);
    }
}
