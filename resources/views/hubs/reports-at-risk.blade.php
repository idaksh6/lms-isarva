@extends('layouts.lms')

@section('title', 'Course at-risk report')
@section('page_title', 'Reports')

@section('content')
<div class="corp-dashboard">
    <x-lms.module-hero
        module="reports"
        title="Course at-risk"
        subtitle="Auto-flagged weak students with reasons, performance snapshots, and intervention tracking."
    >
        @if ($selectedCourse)
            <div class="lms-report-export-group">
                <a href="{{ route('reports.at-risk.export', ['course' => $selectedCourse->id, 'format' => 'csv']) }}" class="lms-btn-secondary lms-btn-secondary--xs">CSV</a>
                <a href="{{ route('reports.at-risk.export', ['course' => $selectedCourse->id, 'format' => 'xlsx']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Excel</a>
                <a href="{{ route('reports.at-risk.export', ['course' => $selectedCourse->id, 'format' => 'pdf']) }}" class="lms-btn-primary lms-btn-primary--xs">PDF</a>
            </div>
        @endif
    </x-lms.module-hero>

    <nav class="lms-report-tabs" aria-label="Report type">
        <a href="{{ route('reports.index') }}" class="lms-report-tab">Overview</a>
        <a href="{{ route('reports.assignments') }}" class="lms-report-tab">Individual assignment</a>
        <a href="{{ route('reports.activity') }}" class="lms-report-tab">Course activity</a>
        <a href="{{ route('reports.at-risk') }}" class="lms-report-tab is-active">Course at-risk</a>
    </nav>

    <form method="GET" action="{{ route('reports.at-risk') }}" class="lms-report-filters">
        <div class="lms-report-filters-grid">
            <div class="lms-form-field lms-report-filters-span">
                <label for="at-risk-course" class="lms-field-label">Course</label>
                <select id="at-risk-course" name="course" class="lms-field-input mt-1.5" onchange="this.form.submit()" required>
                    <option value="">Choose a course…</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected($selectedCourse?->id === $course->id)>
                            {{ $course->code }} — {{ $course->title }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="lms-report-filters-actions">
            <button type="submit" class="lms-btn-primary">Open report</button>
            @if ($selectedCourse)
                <a href="{{ route('reports.at-risk') }}" class="lms-btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    @if (! $selectedCourse)
        <x-lms.empty-state
            title="Select a course"
            message="Choose a course to identify weak students and manage support interventions."
            variant="chart"
        />
    @else
        @php $kpis = $report['kpis']; @endphp

        <div class="corp-kpi-grid">
            <x-dashboard.kpi-card label="Flagged students" :value="$kpis['flagged']" :sub="'of '.$kpis['enrolled'].' enrolled'" icon="users" />
            <x-dashboard.kpi-card label="Open cases" :value="$kpis['open_cases']" sub="Active interventions" icon="clipboard" />
            <x-dashboard.kpi-card label="Resolved cases" :value="$kpis['resolved_cases']" sub="Closed successfully" icon="chart" />
            <x-dashboard.kpi-card label="Avg risk score" :value="$kpis['avg_risk_score'] !== null ? $kpis['avg_risk_score'] : '—'" sub="Rules fired per flagged student" icon="inbox" />
        </div>

        @foreach ($report['notes'] as $note)
            <p class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-950">{{ $note }}</p>
        @endforeach

        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">Weak students</h2>
                    <p class="corp-panel-desc">
                        Flagged when a critical rule fires, or when two or more rules fire.
                        @if ($report['course_avg'] !== null)
                            Course assignment average: {{ $report['course_avg'] }}%.
                        @endif
                    </p>
                </div>
                <span class="corp-sidebar-badge">{{ $report['flagged']->count() }}</span>
            </div>

            @if ($report['flagged']->isNotEmpty())
                <div class="corp-table-wrap">
                    <table class="corp-table corp-table--compact">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Risk</th>
                                <th>Reasons</th>
                                <th>Snapshot</th>
                                <th>Case</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['flagged'] as $row)
                                @php $m = $row['metrics']; @endphp
                                <tr class="corp-table-row">
                                    <td class="corp-table-cell">
                                        <span class="corp-table-title">{{ $row['student']->name }}</span>
                                        <div class="corp-table-cell--muted text-xs">
                                            {{ $row['student']->student_id ?: '—' }}
                                            · {{ $row['student']->email }}
                                        </div>
                                    </td>
                                    <td class="corp-table-cell">
                                        <span class="font-semibold">{{ $row['risk_score'] }}</span>
                                    </td>
                                    <td class="corp-table-cell">
                                        <ul class="list-disc pl-4 text-sm space-y-0.5">
                                            @foreach ($row['reasons'] as $reason)
                                                <li>{{ $reason }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="corp-table-cell corp-table-cell--muted text-sm">
                                        Avg {{ $m['assignment_avg'] !== null ? $m['assignment_avg'].'%' : '—' }}
                                        @if ($m['avg_delta'] !== null)
                                            ({{ $m['avg_delta'] > 0 ? '+' : '' }}{{ $m['avg_delta'] }} vs course)
                                        @endif
                                        <br>
                                        Submitted {{ $m['submitted'] }}/{{ $m['published_assignments'] }}
                                        · Missing {{ $m['missing_overdue'] }}
                                        · Late {{ $m['late_count'] }}
                                        <br>
                                        Quiz {{ $m['quiz_avg'] !== null ? $m['quiz_avg'].'%' : '—' }}
                                        · Part. {{ $m['participation_rate'] !== null ? $m['participation_rate'].'%' : '—' }}
                                    </td>
                                    <td class="corp-table-cell">
                                        @if ($row['active_case'])
                                            <a href="{{ route('reports.at-risk.cases.show', $row['active_case']) }}" class="lms-btn-secondary lms-btn-secondary--xs">View case</a>
                                        @else
                                            <form method="POST" action="{{ route('reports.at-risk.cases.store') }}">
                                                @csrf
                                                <input type="hidden" name="course_id" value="{{ $selectedCourse->id }}">
                                                <input type="hidden" name="user_id" value="{{ $row['student']->id }}">
                                                <button type="submit" class="lms-btn-primary lms-btn-primary--xs">Open support</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="corp-panel-body">
                    <p class="text-sm text-isarva-muted">No students currently match the weak-student rules for this course.</p>
                </div>
            @endif
        </section>
    @endif
</div>
@endsection
