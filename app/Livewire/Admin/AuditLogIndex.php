<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $eventFilter = '';

    public string $from = '';

    public string $to = '';

    protected function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'eventFilter' => ['nullable', 'string', 'max:150'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'eventFilter', 'from', 'to'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $this->authorize('audit-logs.view');
        $this->validate();

        $invalidDateRange = $this->from !== ''
            && $this->to !== ''
            && Carbon::parse($this->to)->lt(Carbon::parse($this->from));

        if ($invalidDateRange) {
            $this->addError('to', 'The end date must be on or after the start date.');
        }

        $query = AuditLog::query()
            ->with('actor:id,name,email')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('event', 'like', '%'.$this->search.'%')
                        ->orWhere('subject_type', 'like', '%'.$this->search.'%')
                        ->orWhere('subject_id', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->eventFilter !== '', fn ($query) => $query->where('event', $this->eventFilter))
            ->when(! $invalidDateRange && $this->from !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->from))
            ->when(! $invalidDateRange && $this->to !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->to))
            ->latest('created_at');

        $logs = $query->paginate(20);

        return view('livewire.admin.audit-logs', [
            'logs' => $logs,
            'events' => AuditLog::query()->distinct()->orderBy('event')->pluck('event'),
        ])->layout('layouts.app', ['title' => 'Audit trail']);
    }
}
