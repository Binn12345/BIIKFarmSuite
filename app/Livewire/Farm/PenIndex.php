<?php

namespace App\Livewire\Farm;

use App\Models\Pen;
use App\Services\AuditLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class PenIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingPenId = null;

    public ?int $selectedPenId = null;

    public string $name = '';

    public string $capacity = '';

    public string $location = '';

    public string $status = 'active';

    public string $notes = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createPen(): void
    {
        $this->authorize('pens.create');
        $this->resetForm();
        $this->showForm = true;
    }

    public function editPen(int $penId): void
    {
        $this->authorize('pens.update');
        $pen = Pen::query()->findOrFail($penId);

        $this->editingPenId = $pen->id;
        $this->name = $pen->name;
        $this->capacity = (string) $pen->capacity;
        $this->location = $pen->location ?? '';
        $this->status = $pen->status;
        $this->notes = $pen->notes ?? '';
        $this->selectedPenId = null;
        $this->showForm = true;
    }

    public function showPen(int $penId): void
    {
        $this->authorize('pens.view');
        $this->selectedPenId = Pen::query()->whereKey($penId)->value('id');
        abort_if($this->selectedPenId === null, 404);
        $this->showForm = false;
    }

    public function savePen(): void
    {
        $this->authorize($this->editingPenId ? 'pens.update' : 'pens.create');

        $pen = $this->editingPenId
            ? Pen::query()->findOrFail($this->editingPenId)
            : null;

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('pens', 'name')->ignore($pen?->id),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'maintenance'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($pen, $validated): void {
            $creating = $pen === null;
            if (! $creating) {
                $pen = Pen::query()->lockForUpdate()->findOrFail($pen->id);
                if ($validated['capacity'] < $pen->animals()->where('status', 'active')->count()) {
                    throw ValidationException::withMessages([
                        'capacity' => 'Capacity cannot be lower than the number of active animals currently assigned.',
                    ]);
                }
            }
            $pen ??= new Pen;
            $pen->fill($validated);
            $pen->updated_by = auth()->id();
            if ($creating) {
                $pen->created_by = auth()->id();
            }
            $pen->save();

            app(AuditLogger::class)->record(
                $creating ? 'pen.created' : 'pen.updated',
                $pen,
                ['name' => $pen->name],
            );
        });

        $this->selectedPenId = null;
        $this->resetForm();
        session()->flash('status', 'Pen record saved.');
    }

    public function deletePen(int $penId): void
    {
        $this->authorize('pens.delete');
        DB::transaction(function () use ($penId): void {
            $pen = Pen::query()->lockForUpdate()->findOrFail($penId);
            if ($pen->animals()->exists()) {
                throw ValidationException::withMessages([
                    'pen' => 'A pen with animal records cannot be deleted. Reassign the animals first.',
                ]);
            }

            app(AuditLogger::class)->record('pen.deleted', $pen, ['name' => $pen->name]);
            $pen->delete();
        });

        if ($this->selectedPenId === $penId) {
            $this->selectedPenId = null;
        }

        session()->flash('status', 'Pen deleted.');
    }

    public function closePanels(): void
    {
        $this->resetForm();
        $this->selectedPenId = null;
    }

    private function resetForm(): void
    {
        $this->reset('editingPenId', 'name', 'capacity', 'location', 'notes');
        $this->status = 'active';
        $this->resetValidation();
        $this->showForm = false;
    }

    public function render()
    {
        $this->authorize('pens.view');

        return view('livewire.farm.pens.index', [
            'pens' => Pen::query()
                ->withCount(['animals as active_animals_count' => fn ($query) => $query->where('status', 'active')])
                ->when($this->search !== '', function ($query): void {
                    $query->where(function ($query): void {
                        $query->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('location', 'like', '%'.$this->search.'%');
                    });
                })
                ->orderBy('name')
                ->paginate(10),
            'selectedPen' => $this->selectedPenId
                ? Pen::query()->with(['animals' => fn ($query) => $query->orderBy('tag_number')])->find($this->selectedPenId)
                : null,
        ])->layout('layouts.app', ['title' => 'Pens Management']);
    }
}
