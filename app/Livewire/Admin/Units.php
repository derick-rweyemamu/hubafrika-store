<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Units extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $isOpen = false;

    public ?int $unitId = null;

    public string $name = '';

    public function updatingSearch(): void
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

    public function edit(int $id): void
    {
        $this->authorizeAdmin();
        $this->resetValidation();

        $unit = Unit::query()->findOrFail($id);
        $this->unitId = $unit->id;
        $this->name = $unit->name;
        $this->isOpen = true;
    }

    public function save(): void
    {
        $this->authorizeAdmin();

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('units', 'name')->ignore($this->unitId),
            ],
        ], [
            'name.unique' => 'A unit with that name already exists.',
        ]);

        $unit = $this->unitId === null
            ? new Unit
            : Unit::query()->findOrFail($this->unitId);
        $unit->name = $validated['name'];
        $unit->save();

        session()->flash('message', $this->unitId === null
            ? 'Unit created successfully.'
            : 'Unit updated successfully.');
        $this->isOpen = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $this->authorizeAdmin();

        $unit = Unit::query()->findOrFail($id);

        DB::transaction(function () use ($unit): void {
            Product::query()->where('unit', $unit->id)->update(['unit' => null]);
            $unit->delete();
        });

        session()->flash('message', 'Unit deleted. Products that used it now show no unit.');
    }

    public function render()
    {
        $perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;

        return view('livewire.admin.units', [
            'units' => Unit::query()
                ->withCount('products')
                ->when($this->search !== '', fn (Builder $query) => $query->where('name', 'like', '%'.mb_substr($this->search, 0, 100).'%'))
                ->orderBy('name')
                ->paginate($perPage),
        ])->layout('components.layouts.admin', ['title' => 'Units']);
    }

    private function resetForm(): void
    {
        $this->reset(['unitId', 'name']);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->can('units.manage'), 403);
    }
}
