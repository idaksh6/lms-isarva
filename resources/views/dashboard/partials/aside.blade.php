@props([
    'upcoming' => collect(),
    'highlightDates' => collect(),
])

<aside class="corp-dash-sidebar">
    @include('dashboard.partials.calendar', ['highlightDates' => $highlightDates])

    <section class="corp-sidebar-panel">
        <div class="corp-sidebar-panel-head">
            <h3 class="corp-sidebar-panel-title">Upcoming deadlines</h3>
            <p class="corp-sidebar-panel-desc">Assignments due in the next period.</p>
        </div>
        <ul class="corp-deadline-list">
            @forelse ($upcoming as $item)
                <li>
                    <a href="{{ route('assignments.show', $item) }}" class="corp-deadline-item">
                        <div class="corp-deadline-date">
                            <span class="corp-deadline-day">{{ $item->due_at->format('d') }}</span>
                            <span class="corp-deadline-month">{{ $item->due_at->format('M') }}</span>
                        </div>
                        <div class="corp-deadline-body">
                            <p class="corp-deadline-title">{{ $item->title }}</p>
                            <p class="corp-deadline-meta">{{ $item->course->code }}</p>
                        </div>
                    </a>
                </li>
            @empty
                <li class="corp-deadline-empty">No upcoming deadlines scheduled.</li>
            @endforelse
        </ul>
    </section>

    @include('dashboard.partials.quick-links')
</aside>
