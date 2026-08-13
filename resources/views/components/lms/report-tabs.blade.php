@props(['active' => 'overview'])

<nav class="lms-report-tabs" aria-label="Report type">
    <a href="{{ route('reports.index') }}" @class(['lms-report-tab', 'is-active' => $active === 'overview'])>Overview</a>
    <a href="{{ route('reports.assignments') }}" @class(['lms-report-tab', 'is-active' => $active === 'assignments'])>Individual assignment</a>
    <a href="{{ route('reports.activity') }}" @class(['lms-report-tab', 'is-active' => $active === 'activity'])>Course activity</a>
    <a href="{{ route('reports.at-risk') }}" @class(['lms-report-tab', 'is-active' => $active === 'at-risk'])>Course at-risk</a>
    <a href="{{ route('mentoring.report') }}" @class(['lms-report-tab', 'is-active' => $active === 'mentoring'])>Mentoring</a>
</nav>
