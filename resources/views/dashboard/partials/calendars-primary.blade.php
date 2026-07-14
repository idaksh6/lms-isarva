@php
    $monthLabel = now()->format('F Y');
    $sessionCount = count($sessionEventsByDate ?? []);
    $dueCount = count($dueEventsByDate ?? []);
@endphp

<div class="corp-dash-calendars">
    <section class="corp-panel corp-dash-cal-panel corp-dash-cal-panel--sessions">
        <div class="corp-panel-head corp-dash-cal-head">
            <div>
                <h2 class="corp-panel-title">Class schedule</h2>
                <p class="corp-panel-desc">{{ $monthLabel }} · Online and offline sessions</p>
            </div>
            @if ($sessionCount > 0)
                <span class="corp-sidebar-badge">{{ $sessionCount }} class days</span>
            @endif
        </div>

        <div class="corp-dash-cal-stack">
            <div class="corp-dash-cal-calendar">
                <x-lms.calendar-grid
                    class="corp-calendar--dash"
                    variant="sessions"
                    date-param="session_date"
                    :events-by-date="$sessionEventsByDate ?? []"
                    :month="now()->month"
                    :year="now()->year"
                />
            </div>

            <div class="corp-dash-cal-side">
                <p class="corp-cal-col-label">Upcoming classes</p>
                <ul class="corp-deadline-list corp-deadline-list--dash">
                    @forelse ($upcomingSessions ?? [] as $session)
                        <li>
                            <a href="{{ route('calendar.index', ['session_date' => $session->starts_at->format('Y-m-d')]) }}#calendar-sessions" @class([
                                'corp-deadline-item',
                                'corp-deadline-item--online' => $session->mode === \App\Enums\SessionDeliveryMode::Online,
                                'corp-deadline-item--offline' => $session->mode === \App\Enums\SessionDeliveryMode::Offline,
                            ])>
                                <div @class([
                                    'corp-deadline-date',
                                    'corp-deadline-date--online' => $session->mode === \App\Enums\SessionDeliveryMode::Online,
                                    'corp-deadline-date--offline' => $session->mode === \App\Enums\SessionDeliveryMode::Offline,
                                ])>
                                    <span class="corp-deadline-day">{{ $session->starts_at->format('d') }}</span>
                                    <span class="corp-deadline-month">{{ $session->starts_at->format('M') }}</span>
                                </div>
                                <div class="corp-deadline-body">
                                    <p class="corp-deadline-title">{{ $session->displayTitle() }}</p>
                                    <p class="corp-deadline-meta">
                                        {{ $session->course->code }} · {{ $session->timeRangeLabel() }} ·
                                        <span @class([
                                            'corp-deadline-mode',
                                            'corp-deadline-mode--online' => $session->mode === \App\Enums\SessionDeliveryMode::Online,
                                            'corp-deadline-mode--offline' => $session->mode === \App\Enums\SessionDeliveryMode::Offline,
                                        ])>{{ $session->mode->label() }}</span>
                                    </p>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="corp-deadline-empty">No upcoming classes this month.</li>
                    @endforelse
                </ul>
                <a href="{{ route('calendar.index') }}#calendar-sessions" class="corp-dash-cal-link">Open class calendar</a>
            </div>
        </div>
    </section>

    <section class="corp-panel corp-dash-cal-panel corp-dash-cal-panel--due">
        <div class="corp-panel-head corp-dash-cal-head">
            <div>
                <h2 class="corp-panel-title">Assignment due dates</h2>
                <p class="corp-panel-desc">{{ $monthLabel }} · Published assignment deadlines</p>
            </div>
            @if ($dueCount > 0)
                <span class="corp-sidebar-badge corp-sidebar-badge--due">{{ $dueCount }} due days</span>
            @endif
        </div>

        <div class="corp-dash-cal-stack">
            <div class="corp-dash-cal-calendar">
                <x-lms.calendar-grid
                    class="corp-calendar--dash"
                    variant="due"
                    date-param="due_date"
                    :events-by-date="$dueEventsByDate ?? []"
                    :month="now()->month"
                    :year="now()->year"
                />
            </div>

            <div class="corp-dash-cal-side">
                <p class="corp-cal-col-label">Upcoming deadlines</p>
                <ul class="corp-deadline-list corp-deadline-list--dash">
                    @forelse ($upcoming ?? [] as $item)
                        <li>
                            <a href="{{ route('calendar.index', ['due_date' => $item->due_at->format('Y-m-d')]) }}#calendar-due-dates" class="corp-deadline-item corp-deadline-item--due">
                                <div class="corp-deadline-date corp-deadline-date--due">
                                    <span class="corp-deadline-day">{{ $item->due_at->format('d') }}</span>
                                    <span class="corp-deadline-month">{{ $item->due_at->format('M') }}</span>
                                </div>
                                <div class="corp-deadline-body corp-deadline-body--due">
                                    <p class="corp-deadline-title">{{ $item->title }}</p>
                                    <p class="corp-deadline-meta corp-deadline-meta--due">
                                        {{ $item->course->code }} · <span class="corp-deadline-mode corp-deadline-mode--due">Due {{ $item->due_at->format('M j, g:i A') }}</span>
                                    </p>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="corp-deadline-empty">No upcoming deadlines scheduled.</li>
                    @endforelse
                </ul>
                <a href="{{ route('calendar.index') }}#calendar-due-dates" class="corp-dash-cal-link">Open due date calendar</a>
            </div>
        </div>
    </section>
</div>
