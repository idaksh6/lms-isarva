@php
    $monthLabel = now()->format('F Y');
    $deadlineCount = collect($highlightDates ?? [])->count();
@endphp

<section class="corp-sidebar-panel corp-sidebar-panel--calendar">
    <div class="corp-sidebar-panel-head">
        <div>
            <h3 class="corp-sidebar-panel-title">Calendar</h3>
            <p class="corp-sidebar-panel-desc">{{ $monthLabel }}</p>
        </div>
        @if ($deadlineCount > 0)
            <span class="corp-sidebar-badge">{{ $deadlineCount }} due</span>
        @endif
    </div>

    <x-lms.calendar-grid :highlight-dates="$highlightDates ?? []" />
</section>
