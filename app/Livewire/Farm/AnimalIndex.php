<?php

namespace App\Livewire\Farm;

use App\Models\Animal;
use App\Models\FarmRecord;
use App\Models\Pen;
use App\Services\AuditLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class AnimalIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingAnimalId = null;

    public ?int $selectedAnimalId = null;

    public string $tag_number = '';

    public string $name = '';

    public string $breed = '';

    public string $category = '';

    public string $sex = '';

    public string $birth_date = '';

    public string $acquisition_source = '';

    public string $acquired_at = '';

    public string $purchase_cost = '';

    public string $status = 'active';

    public ?int $pen_id = null;

    public string $notes = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createAnimal(): void
    {
        $this->authorize('animals.create');
        $this->resetForm();
        $this->showForm = true;
    }

    public function editAnimal(int $animalId): void
    {
        $this->authorize('animals.update');
        $animal = Animal::query()->findOrFail($animalId);
        abort_if(in_array($animal->status, ['archived', 'sold', 'deceased'], true), 409, 'This animal is not editable in Animal Management.');

        $this->editingAnimalId = $animal->id;
        $this->tag_number = $animal->tag_number;
        $this->name = $animal->name ?? '';
        $this->breed = $animal->breed ?? '';
        $this->category = $animal->category ?? '';
        $this->sex = $animal->sex;
        $this->birth_date = $animal->birth_date?->format('Y-m-d') ?? '';
        $this->acquisition_source = $animal->acquisition_source ?? '';
        $this->acquired_at = $animal->acquired_at?->format('Y-m-d') ?? '';
        $this->purchase_cost = $animal->purchase_cost ?? '';
        $this->status = $animal->status;
        $this->pen_id = $animal->pen_id;
        $this->notes = $animal->notes ?? '';
        $this->selectedAnimalId = null;
        $this->showForm = true;
    }

    public function showAnimal(int $animalId): void
    {
        $this->authorize('animals.view');
        $this->selectedAnimalId = Animal::query()->whereKey($animalId)->value('id');
        abort_if($this->selectedAnimalId === null, 404);
        $this->showForm = false;
    }

    public function saveAnimal(): void
    {
        $this->authorize($this->editingAnimalId ? 'animals.update' : 'animals.create');

        $animal = $this->editingAnimalId
            ? Animal::query()->findOrFail($this->editingAnimalId)
            : null;
        abort_if($animal && in_array($animal->status, ['archived', 'sold', 'deceased'], true), 409, 'This animal is not editable in Animal Management.');

        $breedRule = Rule::exists('farm_records', 'title')->where(fn ($query) => $query
            ->where('module', 'breeds')
            ->where('status', 'active'));
        $categoryRule = Rule::exists('farm_records', 'title')->where(fn ($query) => $query
            ->where('module', 'animal-categories')
            ->where('status', 'active'));
        $validated = $this->validate([
            'tag_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('animals', 'tag_number')->ignore($animal?->id),
            ],
            'name' => ['nullable', 'string', 'max:100'],
            'breed' => ['nullable', 'string', 'max:100', ...($animal && $this->breed === $animal->breed ? [] : [$breedRule])],
            'category' => ['nullable', 'string', 'max:100', ...($animal && $this->category === $animal->category ? [] : [$categoryRule])],
            'sex' => ['required', Rule::in(['female', 'male'])],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'acquisition_source' => ['nullable', 'string', 'max:150'],
            'acquired_at' => ['nullable', 'date', 'before_or_equal:today'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'pen_id' => ['nullable', 'integer', 'exists:pens,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $validated['purchase_cost'] = $validated['purchase_cost'] === '' ? null : $validated['purchase_cost'];

        DB::transaction(function () use ($animal, $validated): void {
            $creating = $animal === null;
            if ($validated['pen_id'] !== null) {
                $pen = Pen::query()->lockForUpdate()->findOrFail($validated['pen_id']);
                $occupied = $pen->animals()
                    ->where('status', 'active')
                    ->when($animal, fn ($query) => $query->where('animals.id', '!=', $animal->id))
                    ->count();

                if ($pen->status !== 'active') {
                    throw ValidationException::withMessages([
                        'pen_id' => 'Animals can only be assigned to an active pen.',
                    ]);
                }

                if ($occupied >= $pen->capacity) {
                    throw ValidationException::withMessages([
                        'pen_id' => 'The selected pen is at capacity.',
                    ]);
                }
            }

            $animal ??= new Animal;
            $animal->fill($validated);
            $animal->status = $validated['status'];
            $animal->updated_by = auth()->id();
            if ($creating) {
                $animal->created_by = auth()->id();
            }
            $animal->save();

            app(AuditLogger::class)->record(
                $creating ? 'animal.created' : 'animal.updated',
                $animal,
                ['tag_number' => $animal->tag_number],
            );
        });

        $this->selectedAnimalId = null;
        $this->resetForm();
        session()->flash('status', __('biik.animal_record_saved'));
    }

    public function archiveAnimal(int $animalId): void
    {
        $this->authorize('animals.delete');
        $animal = Animal::query()->findOrFail($animalId);
        abort_if($animal->status === 'archived', 409, 'This animal is already archived.');

        DB::transaction(function () use ($animal): void {
            $animal->forceFill(['status' => 'archived', 'updated_by' => auth()->id()])->save();
            app(AuditLogger::class)->record('animal.archived', $animal, [
                'tag_number' => $animal->tag_number,
            ]);
        });

        if ($this->selectedAnimalId === $animal->id) {
            $this->selectedAnimalId = null;
        }

        session()->flash('status', __('biik.animal_archived'));
    }

    public function closePanels(): void
    {
        $this->resetForm();
        $this->selectedAnimalId = null;
    }

    private function resetForm(): void
    {
        $this->reset('editingAnimalId', 'tag_number', 'name', 'breed', 'category', 'sex', 'birth_date', 'acquisition_source', 'acquired_at', 'purchase_cost', 'pen_id', 'notes');
        $this->status = 'active';
        $this->resetValidation();
        $this->showForm = false;
    }

    public function render()
    {
        $this->authorize('animals.view');

        return view('livewire.farm.animals.index', [
            'animals' => Animal::query()
                ->with('pen')
                ->when($this->search !== '', function ($query): void {
                    $query->where(function ($query): void {
                        $query->where('tag_number', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%')
                            ->orWhere('breed', 'like', '%'.$this->search.'%');
                    });
                })
                ->latest('id')
                ->paginate(10),
            'pens' => Pen::query()
                ->withCount(['animals as active_animals_count' => fn ($query) => $query->where('status', 'active')])
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'breeds' => FarmRecord::query()->where('module', 'breeds')->where('status', 'active')->orderBy('title')->pluck('title'),
            'categories' => FarmRecord::query()->where('module', 'animal-categories')->where('status', 'active')->orderBy('title')->pluck('title'),
            'selectedAnimal' => $this->selectedAnimalId
                ? Animal::query()->with(['pen', 'creator', 'updater'])->find($this->selectedAnimalId)
                : null,
        ])->layout('layouts.app', ['title' => __('biik.modules.animals.title')]);
    }
}
