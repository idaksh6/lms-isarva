@extends('layouts.lms')

@section('title', 'Course at-risk report')
@section('page_title', 'Reports')

@section('content')
@php
    $kpis = $selectedCourse ? $report['kpis'] : null;
    $flagged = $selectedCourse ? $report['flagged'] : collect();
@endphp

<div class="corp-dashboard">
    <x-lms.module-hero
        module="reports"
        title="Course at-risk"
        subtitle="Find struggling students, open support cases, and track interventions."
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

    <form method="GET" action="{{ route('reports.at-risk') }}" class="lms-at-risk-toolbar">
        <div class="lms-at-risk-toolbar-field">
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
        <div class="lms-at-risk-toolbar-actions">
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
        <div class="lms-at-risk-summary">
            <div class="lms-at-risk-stat">
                <span class="lms-at-risk-stat-value {{ $kpis['flagged'] > 0 ? 'is-alert' : 'is-ok' }}">{{ $kpis['flagged'] }}</span>
                <span class="lms-at-risk-stat-label">Flagged</span>
                <span class="lms-at-risk-stat-sub">of {{ $kpis['enrolled'] }} enrolled</span>
            </div>
            <div class="lms-at-risk-stat">
                <span class="lms-at-risk-stat-value">{{ $kpis['open_cases'] }}</span>
                <span class="lms-at-risk-stat-label">Open cases</span>
                <span class="lms-at-risk-stat-sub">Active support</span>
            </div>
            <div class="lms-at-risk-stat">
                <span class="lms-at-risk-stat-value">{{ $kpis['resolved_cases'] }}</span>
                <span class="lms-at-risk-stat-label">Resolved</span>
                <span class="lms-at-risk-stat-sub">Closed cases</span>
            </div>
            <div class="lms-at-risk-stat">
                <span class="lms-at-risk-stat-value">{{ $kpis['avg_risk_score'] !== null ? $kpis['avg_risk_score'] : '—' }}</span>
                <span class="lms-at-risk-stat-label">Avg risk</span>
                <span class="lms-at-risk-stat-sub">Rules fired</span>
            </div>
            @if ($report['course_avg'] !== null)
                <div class="lms-at-risk-stat">
                    <span class="lms-at-risk-stat-value">{{ $report['course_avg'] }}%</span>
                    <span class="lms-at-risk-stat-label">Course avg</span>
                    <span class="lms-at-risk-stat-sub">Assignments</span>
                </div>
            @endif
        </div>

        <details class="lms-at-risk-legend">
            <summary>How students are flagged</summary>
            <div class="lms-at-risk-legend-body">
                <p>A student is flagged if <strong>any critical rule</strong> fires, or if <strong>two or more</strong> other rules fire.</p>
                <ul>
                    <li><span class="lms-at-risk-chip is-critical">Critical</span> Assignment average under 60%, or 2+ missing overdue assignments</li>
                    <li><span class="lms-at-risk-chip">Other</span> Chronic late, stuck in revision, low quiz score, low participation</li>
                    <li>Snapshot compares current scores to the course average (score history is not stored yet). Attendance is not tracked.</li>
                </ul>
            </div>
        </details>

        <section class="lms-at-risk-list">
            <div class="lms-at-risk-list-head">
                <div>
                    <h2 class="lms-at-risk-list-title">Weak students</h2>
                    <p class="lms-at-risk-list-desc">
                        @if ($flagged->isNotEmpty())
                            Review reasons and open a support case to log mentoring, extra classes, and strategies.
                        @else
                            Nobody matches the weak-student rules right now.
                        @endif
                    </p>
                </div>
                <span class="lms-at-risk-list-count">{{ $flagged->count() }}</span>
            </div>

            @if ($flagged->isNotEmpty())
                <div class="lms-at-risk-cards">
                    @foreach ($flagged as $row)
                        @php
                            $m = $row['metrics'];
                            $riskLevel = $row['risk_score'] >= 3 ? 'high' : ($row['risk_score'] >= 2 ? 'medium' : 'low');
                        @endphp
                        <article class="lms-at-risk-card">
                            <div class="lms-at-risk-card-top">
                                <div class="lms-at-risk-card-identity">
                                    <span class="lms-student-avatar lms-student-avatar--lg">{{ strtoupper(substr($row['student']->name, 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <h3 class="lms-at-risk-card-name">{{ $row['student']->name }}</h3>
                                        <p class="lms-at-risk-card-meta">
                                            {{ $row['student']->student_id ?: 'No student ID' }}
                                            · {{ $row['student']->email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="lms-at-risk-card-risk is-{{ $riskLevel }}" title="Rules fired">
                                    <span class="lms-at-risk-card-risk-score">{{ $row['risk_score'] }}</span>
                                    <span class="lms-at-risk-card-risk-label">Risk</span>
                                </div>
                            </div>

                            <div class="lms-at-risk-card-reasons">
                                @foreach ($row['reasons'] as $reason)
                                    <span class="lms-at-risk-reason">{{ $reason }}</span>
                                @endforeach
                            </div>

                            <div class="lms-at-risk-metrics">
                                <div class="lms-at-risk-metric">
                                    <span class="lms-at-risk-metric-label">Avg</span>
                                    <span class="lms-at-risk-metric-value">
                                        {{ $m['assignment_avg'] !== null ? $m['assignment_avg'].'%' : '—' }}
                                        @if ($m['avg_delta'] !== null)
                                            <em class="{{ $m['avg_delta'] < 0 ? 'is-down' : 'is-up' }}">{{ $m['avg_delta'] > 0 ? '+' : '' }}{{ $m['avg_delta'] }}</em>
                                        @endif
                                    </span>
                                </div>
                                <div class="lms-at-risk-metric">
                                    <span class="lms-at-risk-metric-label">Submitted</span>
                                    <span class="lms-at-risk-metric-value">{{ $m['submitted'] }}/{{ $m['published_assignments'] }}</span>
                                </div>
                                <div class="lms-at-risk-metric">
                                    <span class="lms-at-risk-metric-label">Missing</span>
                                    <span class="lms-at-risk-metric-value">{{ $m['missing_overdue'] }}</span>
                                </div>
                                <div class="lms-at-risk-metric">
                                    <span class="lms-at-risk-metric-label">Late</span>
                                    <span class="lms-at-risk-metric-value">{{ $m['late_count'] }}</span>
                                </div>
                                <div class="lms-at-risk-metric">
                                    <span class="lms-at-risk-metric-label">Quiz</span>
                                    <span class="lms-at-risk-metric-value">{{ $m['quiz_avg'] !== null ? $m['quiz_avg'].'%' : '—' }}</span>
                                </div>
                                <div class="lms-at-risk-metric">
                                    <span class="lms-at-risk-metric-label">Participation</span>
                                    <span class="lms-at-risk-metric-value">{{ $m['participation_rate'] !== null ? $m['participation_rate'].'%' : '—' }}</span>
                                </div>
                            </div>

                            <div class="lms-at-risk-card-actions">
                                @if ($row['active_case'])
                                    <span class="lms-at-risk-case-pill">Case open</span>
                                    <a href="{{ route('reports.at-risk.cases.show', $row['active_case']) }}" class="lms-btn-primary lms-btn-primary--xs">View case</a>
                                @else
                                    <form method="POST" action="{{ route('reports.at-risk.cases.store') }}">
                                        @csrf
                                        <input type="hidden" name="course_id" value="{{ $selectedCourse->id }}">
                                        <input type="hidden" name="user_id" value="{{ $row['student']->id }}">
                                        <button type="submit" class="lms-btn-primary lms-btn-primary--xs">Open support</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="lms-at-risk-empty">
                    <div class="lms-at-risk-empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <h3 class="lms-at-risk-empty-title">No weak students flagged</h3>
                    <p class="lms-at-risk-empty-text">
                        Enrolled students are currently within the course thresholds. This list updates as grades, submissions, and participation change.
                    </p>
                </div>
            @endif
        </section>
    @endif
</div>
@endsection
