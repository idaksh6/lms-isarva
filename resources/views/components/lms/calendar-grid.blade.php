@props([
    'date' => null,
    'eventsByDate' => [],
    'highlightDates' => [],
    'selectedDate' => null,
    'month' => null,
    'year' => null,
    'variant' => 'all',
    'dateParam' => 'date',
    'queryParams' => [],
])

@php
    $viewDate = ($date instanceof \Carbon\Carbon ? $date : now())->copy()->startOfMonth();
    $start = $viewDate->copy()->startOfMonth()->startOfWeek();
    $end = $viewDate->copy()->endOfMonth()->endOfWeek();
    $events = collect($eventsByDate);

    if ($events->isEmpty() && ! empty($highlightDates)) {
        foreach ($highlightDates as $day) {
            if ($variant === 'sessions') {
                $events->put($day, ['online' => true, 'offline' => false]);
            } else {
                $events->put($day, ['due' => true]);
            }
        }
    }

    $selectedKey = $selectedDate instanceof \Carbon\Carbon ? $selectedDate->format('Y-m-d') : null;
    $dueCount = $events->filter(fn ($e) => $e['due'] ?? false)->count();
    $onlineCount = $events->filter(fn ($e) => $e['online'] ?? false)->count();
    $offlineCount = $events->filter(fn ($e) => $e['offline'] ?? false)->count();
    $sessionCount = $onlineCount + $offlineCount;
    $baseQuery = array_filter(array_merge([
        'month' => $month ?? $viewDate->month,
        'year' => $year ?? $viewDate->year,
    ], $queryParams));
@endphp

<div {{ $attributes->merge(['class' => 'corp-calendar']) }}>
    <ul class="corp-calendar-legend" aria-label="Calendar key">
        <li class="corp-calendar-legend-today"><span class="corp-calendar-key corp-calendar-key--today"></span> Today</li>

        @if ($variant === 'due' || $variant === 'all')
            <li class="corp-calendar-legend-due"><span class="corp-calendar-key corp-calendar-key--due"></span> Due date</li>
        @endif

        @if ($variant === 'sessions' || $variant === 'all')
            <li class="corp-calendar-legend-online"><span class="corp-calendar-key corp-calendar-key--online"></span> Online class</li>
            <li class="corp-calendar-legend-offline"><span class="corp-calendar-key corp-calendar-key--offline"></span> Offline class</li>
        @endif

        @if ($variant === 'due' && $dueCount > 0)
            <li class="corp-calendar-legend-stat">{{ $dueCount }} due</li>
        @elseif ($variant === 'sessions' && $sessionCount > 0)
            <li class="corp-calendar-legend-stat">{{ $sessionCount }} classes</li>
        @elseif ($variant === 'all' && $dueCount + $sessionCount > 0)
            <li class="corp-calendar-legend-stat">{{ $dueCount }} due · {{ $sessionCount }} classes</li>
        @endif
    </ul>

    <div class="corp-calendar-weekdays">
        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
            <span>{{ $day }}</span>
        @endforeach
    </div>

    <div class="corp-calendar-grid">
        @for ($cell = $start->copy(); $cell->lte($end); $cell->addDay())
            @php
                $inMonth = $cell->month === $viewDate->month;
                $isToday = $cell->isToday();
                $key = $cell->format('Y-m-d');
                $dayEvents = $events->get($key, []);
                $hasDue = $dayEvents['due'] ?? false;
                $hasOnline = $dayEvents['online'] ?? false;
                $hasOffline = $dayEvents['offline'] ?? false;
                $isSelected = $selectedKey === $key;

                if ($variant === 'due') {
                    $hasEvents = $hasDue;
                    $dayClasses = [
                        'has-due' => $hasDue && ! $isToday && ! $isSelected,
                    ];
                } elseif ($variant === 'sessions') {
                    $typeCount = ($hasOnline ? 1 : 0) + ($hasOffline ? 1 : 0);
                    $hasEvents = $typeCount > 0;
                    $singleOnline = $hasOnline && ! $hasOffline;
                    $singleOffline = $hasOffline && ! $hasOnline;
                    $dayClasses = [
                        'has-online' => $singleOnline && ! $isToday && ! $isSelected,
                        'has-offline' => $singleOffline && ! $isToday && ! $isSelected,
                        'has-mixed' => $typeCount > 1 && ! $isToday && ! $isSelected,
                    ];
                } else {
                    $typeCount = ($hasDue ? 1 : 0) + ($hasOnline ? 1 : 0) + ($hasOffline ? 1 : 0);
                    $hasEvents = $typeCount > 0;
                    $singleDue = $hasDue && ! $hasOnline && ! $hasOffline;
                    $singleOnline = $hasOnline && ! $hasOffline && ! $hasDue;
                    $singleOffline = $hasOffline && ! $hasOnline && ! $hasDue;
                    $dayClasses = [
                        'has-due' => $singleDue && ! $isToday && ! $isSelected,
                        'has-online' => $singleOnline && ! $isToday && ! $isSelected,
                        'has-offline' => $singleOffline && ! $isToday && ! $isSelected,
                        'has-mixed' => $typeCount > 1 && ! $isToday && ! $isSelected,
                    ];
                }

                $sectionHash = match ($dateParam) {
                    'due_date' => '#calendar-due-dates',
                    'session_date' => '#calendar-sessions',
                    default => '',
                };
                $dayUrl = route('calendar.index', array_merge($baseQuery, [$dateParam => $key])).$sectionHash;
            @endphp
            <div class="corp-calendar-cell">
                <a href="{{ $dayUrl }}"
                   @class(array_merge([
                       'corp-calendar-day',
                       'is-outside' => ! $inMonth,
                       'is-today' => $isToday && ! $isSelected,
                       'is-selected' => $isSelected,
                   ], $dayClasses))
                   aria-label="{{ $cell->format('F j, Y') }}{{ $hasEvents ? ' — has events' : '' }}">
                    <span class="corp-calendar-day-num">{{ $cell->day }}</span>
                    @if ($hasEvents)
                        <span class="corp-calendar-dots" aria-hidden="true">
                            @if (($variant === 'due' || $variant === 'all') && $hasDue)
                                <span class="corp-calendar-dot corp-calendar-dot--due"></span>
                            @endif
                            @if (($variant === 'sessions' || $variant === 'all') && $hasOnline)
                                <span class="corp-calendar-dot corp-calendar-dot--online"></span>
                            @endif
                            @if (($variant === 'sessions' || $variant === 'all') && $hasOffline)
                                <span class="corp-calendar-dot corp-calendar-dot--offline"></span>
                            @endif
                        </span>
                    @endif
                </a>
            </div>
        @endfor
    </div>
</div>
