@php
    $today = now();
    $start = $today->copy()->startOfMonth()->startOfWeek();
    $end = $today->copy()->endOfMonth()->endOfWeek();
    $highlights = collect($highlightDates ?? [])->flip();
    $monthLabel = $today->format('F Y');
    $deadlineCount = $highlights->count();
@endphp

<div class="quyl-card quyl-card-calendar">
    <div class="flex items-center justify-between gap-3">
        <h3 class="quyl-card-title">Deadlines</h3>
        <span class="calendar-month-badge">{{ $monthLabel }}</span>
    </div>

    <p class="calendar-subtitle">
        @if ($deadlineCount > 0)
            {{ $deadlineCount }} {{ $deadlineCount === 1 ? 'assignment' : 'assignments' }} due in {{ $today->format('F') }}.
        @else
            No assignment due dates in {{ $today->format('F') }}.
        @endif
    </p>

    <div class="calendar-body">
        <ul class="calendar-legend" aria-label="Calendar key">
            <li class="calendar-legend-item">
                <span class="calendar-legend-swatch calendar-legend-swatch--today" aria-hidden="true"></span>
                <span>Today</span>
            </li>
            <li class="calendar-legend-item">
                <span class="calendar-legend-swatch calendar-legend-swatch--due" aria-hidden="true"></span>
                <span>Due date</span>
            </li>
        </ul>

        <div class="calendar-weekdays">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <span>{{ $day }}</span>
            @endforeach
        </div>

        <div class="calendar-grid">
        @for ($date = $start->copy(); $date->lte($end); $date->addDay())
            @php
                $inMonth = $date->month === $today->month;
                $isToday = $date->isToday();
                $key = $date->format('Y-m-d');
                $hasEvent = $highlights->has($key);
            @endphp
            <div class="calendar-cell">
                <span @class([
                    'calendar-day',
                    'is-outside' => ! $inMonth,
                    'is-today' => $isToday,
                    'has-event' => $hasEvent && ! $isToday,
                ])>
                    {{ $date->day }}
                </span>
            </div>
        @endfor
        </div>
    </div>
</div>
