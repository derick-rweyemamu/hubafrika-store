<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Users extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public string $status = 'all';

    public bool $isOpen = false;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $phone = '';

    public string $address = '';

    public string $country = '';

    public string $state = '';

    public string $city = '';

    public string $postalCode = '';

    public string $role = '';

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

        $user = $this->userQuery()->findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
        $this->address = $user->address ?? '';
        $this->country = $user->country ?? '';
        $this->state = $user->state ?? '';
        $this->city = $user->city ?? '';
        $this->postalCode = $user->postal_code ?? '';
        $this->role = $user->roles->pluck('name')->first() ?? '';
        $this->password = '';
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
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => [
                Rule::requiredIf($this->userId === null),
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
            'role' => ['nullable', 'string', Rule::in($this->assignableRoles())],
        ]);

        $user = $this->userId === null
            ? new User
            : $this->userQuery()->findOrFail($this->userId);

        $emailChanged = $user->exists && $user->email !== $validated['email'];

        $user->fill([
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
            $user->password = Hash::make($validated['password']);
        }

        if (! $user->exists) {
            $user->is_admin = false;
            $user->banned = false;
        }

        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->verification_status = 0;
        }

        $user->save();

        $user->syncRoles($validated['role'] === null || $validated['role'] === '' ? [] : [$validated['role']]);

        session()->flash(
            'message',
            $this->userId === null
                ? 'User account created successfully.'
                : 'User account updated successfully.',
        );
        $this->isOpen = false;
        $this->resetForm();
    }

    public function toggleSuspension(int $id): void
    {
        $this->authorizeAdmin();

        $user = $this->userQuery()->findOrFail($id);
        $user->banned = ! $user->banned;
        $user->save();

        session()->flash(
            'message',
            $user->banned
                ? 'User suspended. Their data and purchase history are retained.'
                : 'User restored successfully.',
        );
    }

    public function delete(int $id): void
    {
        $this->authorizeAdmin();

        $user = User::query()->findOrFail($id);

        if ($user->isAdmin()) {
            abort(403, 'Administrator accounts cannot be deleted.');
        }

        $user->addresses()->delete();
        $user->delete();

        if ($this->userId === $id) {
            $this->isOpen = false;
            $this->resetForm();
        }

        session()->flash('message', 'User account deleted. Their order history is retained.');
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('users.view'), 403);

        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;
        $search = mb_substr($this->search, 0, 100);

        return view('livewire.admin.users', [
            'users' => $this->userQuery()
                ->with('roles')
                ->withCount('orders')
                ->when($this->status === 'active', fn (Builder $query) => $query->where('banned', false)->whereNotNull('email_verified_at'))
                ->when($this->status === 'unverified', fn (Builder $query) => $query->where('banned', false)->whereNull('email_verified_at'))
                ->when($this->status === 'suspended', fn (Builder $query) => $query->where('banned', true))
                ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                }))
                ->orderBy('name')
                ->paginate($perPage),
            'roles' => $this->assignableRoles(),
        ])->layout('components.layouts.admin', ['title' => 'Users']);
    }

    /**
     * Accounts managed from this screen. Administrator records are excluded from
     * both admin systems so they can never be listed, edited or deleted here.
     */
    private function userQuery(): Builder
    {
        return User::query()
            ->where('is_admin', false)
            ->whereDoesntHave('roles', static fn (Builder $query) => $query->where('name', 'admin'));
    }

    private function resetForm(): void
    {
        $this->reset([
            'userId',
            'name',
            'email',
            'password',
            'phone',
            'address',
            'country',
            'state',
            'city',
            'postalCode',
            'role',
        ]);
    }

    /**
     * Roles this screen may hand out. The built-in administrator role is
     * excluded so the panel cannot mint a second super administrator.
     *
     * @return array<int, string>
     */
    private function assignableRoles(): array
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', config('permissions.super_role'))
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->can('users.manage'), 403);
    }
}
