@props([
    'date' => null,
    'highlightDates' => [],
])

@php
    $viewDate = ($date instanceof \Carbon\Carbon ? $date : now())->copy()->startOfMonth();
    $start = $viewDate->copy()->startOfMonth()->startOfWeek();
    $end = $viewDate->copy()->endOfMonth()->endOfWeek();
    $highlights = collect($highlightDates)->flip();
    $deadlineCount = $highlights->count();
@endphp

<div {{ $attributes->merge(['class' => 'corp-calendar']) }}>
    <ul class="corp-calendar-legend" aria-label="Calendar key">
        <li><span class="corp-calendar-key corp-calendar-key--today"></span> Today</li>
        <li><span class="corp-calendar-key corp-calendar-key--due"></span> Due date</li>
        @if ($deadlineCount > 0)
            <li class="ml-auto text-xs font-medium text-isarva-muted">{{ $deadlineCount }} due this month</li>
        @endif
    </ul>

    <div class="corp-calendar-weekdays">
        @foreach (['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $day)
            <span>{{ $day }}</span>
        @endforeach
    </div>

    <div class="corp-calendar-grid">
        @for ($cell = $start->copy(); $cell->lte($end); $cell->addDay())
            @php
                $inMonth = $cell->month === $viewDate->month;
                $isToday = $cell->isToday();
                $key = $cell->format('Y-m-d');
                $hasEvent = $highlights->has($key);
            @endphp
            <div class="corp-calendar-cell">
                <span @class([
                    'corp-calendar-day',
                    'is-outside' => ! $inMonth,
                    'is-today' => $isToday,
                    'has-event' => $hasEvent && ! $isToday,
                ])>
                    {{ $cell->day }}
                </span>
            </div>
        @endfor
    </div>
</div>
