@php
    $reportsUrl = auth()->user()->isStudent() ? null : route('reports.index');
@endphp

<div class="corp-charts-row">
    <section class="corp-panel corp-panel--chart">
        <div class="corp-panel-head corp-panel-head--compact">
            <div>
                <h2 class="corp-panel-title">Weekly activity</h2>
                <p class="corp-panel-desc">Submissions grouped by week, based on submitted date.</p>
            </div>
            @if ($reportsUrl)
                <a href="{{ $reportsUrl }}" class="corp-panel-link">Reports</a>
            @endif
        </div>
        <div class="corp-chart-body">
            <x-dashboard.analytics-activity :items="$analytics['activity']" />
        </div>
    </section>
    <section class="corp-panel corp-panel--chart">
        <div class="corp-panel-head corp-panel-head--compact">
            <h2 class="corp-panel-title">Review snapshot</h2>
        </div>
        <div class="corp-chart-body">
            <x-dashboard.analytics-snapshot :segments="$analytics['status']" />
        </div>
    </section>
</div>

