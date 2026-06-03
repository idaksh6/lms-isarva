@props([
    'upcoming' => collect(),
    'highlightDates' => collect(),
])

<aside class="dashboard-aside">
    @include('dashboard.partials.calendar', ['highlightDates' => $highlightDates])

    <div class="quyl-card">
        <h3 class="quyl-card-title text-sm">Up next</h3>
        <p class="mt-1.5 text-xs text-isarva-muted">Assignments due soon.</p>
        <ul class="mt-3 space-y-3">
            @forelse ($upcoming as $item)
                <li>
                    <a href="{{ route('assignments.show', $item) }}" class="group flex gap-3 rounded-lg p-1 transition hover:bg-slate-50">
                        <div class="quyl-upcoming-date">
                            <span class="text-base font-bold leading-none text-isarva-heading">{{ $item->due_at->format('d') }}</span>
                            <span class="text-[10px] font-semibold uppercase text-isarva-muted">{{ $item->due_at->format('M') }}</span>
                        </div>
                        <div class="min-w-0 flex-1 border-l border-isarva-border pl-3">
                            <p class="truncate text-sm font-semibold text-isarva-heading group-hover:text-brand-600">{{ $item->title }}</p>
                            <p class="mt-0.5 flex items-center gap-1.5 text-xs text-isarva-muted">
                                <span class="h-1.5 w-1.5 rounded-full bg-accent-500"></span>
                                {{ $item->course->code }}
                            </p>
                        </div>
                    </a>
                </li>
            @empty
                <li class="py-4 text-center text-sm text-isarva-muted">No upcoming deadlines.</li>
            @endforelse
        </ul>
    </div>
</aside>
