<?php

namespace App\Livewire\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Roles extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $isOpen = false;

    public ?int $roleId = null;

    public string $name = '';

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Select every permission in a group, or clear the group when all of its
     * permissions are already selected.
     */
    public function toggleGroup(string $group): void
    {
        $this->authorizeRoleManagement();

        $names = array_keys(config('permissions.groups')[$group] ?? []);

        if ($names === []) {
            return;
        }

        $selected = array_intersect($this->selectedPermissions, $names);

        $this->selectedPermissions = count($selected) === count($names)
            ? array_values(array_diff($this->selectedPermissions, $names))
            : array_values(array_unique([...$this->selectedPermissions, ...$names]));
    }

    public function create(): void
    {
        $this->authorizeRoleManagement();
        $this->resetValidation();
        $this->resetForm();
        $this->isOpen = true;
    }

    public function edit(int $id): void
    {
        $this->authorizeRoleManagement();
        $this->resetValidation();

        $role = Role::query()->findOrFail($id);

        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions
            ->pluck('name')
            ->sort()
            ->values()
            ->all();
        $this->isOpen = true;
    }

    public function save(): void
    {
        $this->authorizeRoleManagement();
        $this->name = trim($this->name);

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:125',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($this->roleId),
            ],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string', Rule::in($this->permissionNames())],
        ]);

        $role = $this->roleId === null
            ? new Role(['guard_name' => 'web'])
            : Role::query()->findOrFail($this->roleId);

        // The super role keeps its name: administrator checks look it up by name.
        if ($role->name !== $this->superRole()) {
            $role->name = $validated['name'];
        }

        $role->save();
        $role->syncPermissions($this->selectedPermissions);

        session()->flash(
            'message',
            $this->roleId === null ? 'Role created successfully.' : 'Role updated successfully.',
        );
        $this->isOpen = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $this->authorizeRoleManagement();

        $role = Role::query()->findOrFail($id);

        if ($role->name === $this->superRole()) {
            session()->flash('error', 'The '.$this->superRole().' role is built in and cannot be deleted.');

            return;
        }

        $assignedUsers = $role->users()->count();

        if ($assignedUsers > 0) {
            session()->flash(
                'error',
                "This role is still assigned to {$assignedUsers} ".($assignedUsers === 1 ? 'account' : 'accounts').'. Move them to another role before deleting it.',
            );

            return;
        }

        $role->syncPermissions([]);
        $role->delete();

        session()->flash('message', 'Role deleted successfully.');
    }

    public function render()
    {
        abort_unless(auth()->user()?->can('roles.view'), 403);

        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;
        $search = mb_substr(trim($this->search), 0, 100);

        return view('livewire.admin.roles', [
            'roles' => Role::query()
                ->withCount(['permissions', 'users'])
                ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'))
                ->orderBy('name')
                ->paginate($perPage),
            'permissionGroups' => config('permissions.groups'),
            'superRole' => $this->superRole(),
            'canManage' => auth()->user()?->can('roles.manage') ?? false,
        ])->layout('components.layouts.admin', ['title' => 'Roles']);
    }

    private function resetForm(): void
    {
        $this->reset(['roleId', 'name', 'selectedPermissions']);
    }

    private function superRole(): string
    {
        return (string) config('permissions.super_role');
    }

    /**
     * Every permission the panel can grant, taken from the catalog so a stale
     * database row can never sneak an unknown ability into a role.
     *
     * @return array<int, string>
     */
    private function permissionNames(): array
    {
        return collect(config('permissions.groups'))
            ->flatMap(static fn (array $group): array => array_keys($group))
            ->values()
            ->all();
    }

    private function authorizeRoleManagement(): void
    {
        abort_unless(auth()->user()?->can('roles.manage'), 403);
    }
}
