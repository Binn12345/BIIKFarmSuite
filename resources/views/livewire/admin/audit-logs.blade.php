<div class="min-h-screen bg-cream-50">
    <header class="border-b border-forest-950/10 bg-white">
        <div class="site-container flex min-h-20 items-center justify-between gap-5">
            <a href="{{ route('admin.dashboard') }}" class="brand-mark"><span class="grid h-10 w-10 place-items-center rounded-xl bg-forest-900 text-sm font-extrabold text-white">B</span><span class="font-extrabold">BIIK <span class="font-normal">Audit trail</span></span></a>
            <a href="{{ route('admin.dashboard') }}" class="button button-outline !px-5 !py-2.5">Admin dashboard</a>
        </div>
    </header>
    <section class="site-container py-12 sm:py-16">
        <p class="eyebrow">Restricted administrator access</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-forest-950 sm:text-4xl">Audit trail</h1>
        <p class="mt-3 leading-7 text-forest-800/75">Security and access changes are recorded without passwords, tokens, or session secrets.</p>
        @error('to') <p role="alert" class="mt-4 text-sm text-red-800">{{ $message }}</p> @enderror
        <div class="mt-7 grid gap-4 rounded-2xl border border-forest-950/10 bg-white p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div><label for="audit-search" class="form-label">Search</label><input id="audit-search" type="search" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Event or subject"></div>
            <div><label for="audit-event" class="form-label">Event</label><select id="audit-event" wire:model.live="eventFilter" class="form-input"><option value="">All events</option>@foreach ($events as $event)<option value="{{ $event }}">{{ $event }}</option>@endforeach</select></div>
            <div><label for="audit-from" class="form-label">From date</label><input id="audit-from" type="date" wire:model.live="from" class="form-input"></div>
            <div><label for="audit-to" class="form-label">To date</label><input id="audit-to" type="date" wire:model.live="to" class="form-input"></div>
        </div>
        <div class="mt-5 overflow-hidden rounded-2xl border border-forest-950/10 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-left text-sm">
                    <thead class="bg-cream-100 text-xs uppercase tracking-wider text-forest-800"><tr><th class="px-5 py-4">Time</th><th class="px-5 py-4">Actor</th><th class="px-5 py-4">Event</th><th class="px-5 py-4">Subject</th><th class="px-5 py-4">Details</th><th class="px-5 py-4">Request</th></tr></thead>
                    <tbody class="divide-y divide-forest-950/10">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-5 py-4">{{ $log->actor?->name ?? 'System / guest' }}</td>
                                <td class="px-5 py-4 font-semibold text-forest-950">{{ $log->event }}</td>
                                <td class="px-5 py-4">{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '—' }}</td>
                                <td class="max-w-xs px-5 py-4 text-xs text-forest-800/75">{{ json_encode($log->metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</td>
                                <td class="px-5 py-4 text-xs text-forest-800/70">{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-forest-800/70">No audit entries match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-forest-950/10 px-5 py-4">{{ $logs->links() }}</div>
        </div>
    </section>
</div>
