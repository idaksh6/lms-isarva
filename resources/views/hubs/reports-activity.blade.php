@extends('layouts.lms')

@section('title', 'Course activity report')
@section('page_title', 'Reports')

@section('content')
@php
    $kpis = $selectedCourse ? $report['kpis'] : null;
    $isSparse = $selectedCourse && (
        ($kpis['sessions_total'] ?? 0) === 0
        && ($kpis['assignments_published'] ?? 0) === 0
        && ($kpis['assessments_published'] ?? 0) === 0
    );
@endphp

<div class="corp-dashboard">
    <x-lms.module-hero
        module="reports"
        title="Course activity report"
        subtitle="Sessions, assignments, quizzes, and student participation for one course."
    >
        @if ($selectedCourse)
            <div class="lms-report-export-group">
                <a href="{{ route('reports.activity.export', ['course' => $selectedCourse->id, 'format' => 'csv']) }}" class="lms-btn-secondary lms-btn-secondary--xs">CSV</a>
                <a href="{{ route('reports.activity.export', ['course' => $selectedCourse->id, 'format' => 'xlsx']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Excel</a>
                <a href="{{ route('reports.activity.export', ['course' => $selectedCourse->id, 'format' => 'pdf']) }}" class="lms-btn-primary lms-btn-primary--xs">PDF</a>
            </div>
        @endif
    </x-lms.module-hero>

    <x-lms.report-tabs active="activity" />

    <form method="GET" action="{{ route('reports.activity') }}" class="lms-at-risk-toolbar">
        <div class="lms-at-risk-toolbar-field">
            <label for="activity-course" class="lms-field-label">Course</label>
            <select id="activity-course" name="course" class="lms-field-input mt-1.5" onchange="this.form.submit()" required>
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
                <a href="{{ route('reports.activity') }}" class="lms-btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    @if (! $selectedCourse)
        <x-lms.empty-state
            title="Select a course"
            message="Choose a course to see scheduled activities, assignments, quizzes, and student participation."
            variant="chart"
        />
    @else
        <div class="lms-activity-summary">
            <div class="lms-activity-stat">
                <span class="lms-activity-stat-value">{{ $kpis['enrolled'] }}</span>
                <span class="lms-activity-stat-label">Enrolled</span>
                <span class="lms-activity-stat-sub">{{ $kpis['active_participants'] }} active ({{ $kpis['participation_rate'] ?? 0 }}%)</span>
            </div>
            <div class="lms-activity-stat">
                <span class="lms-activity-stat-value">{{ $kpis['sessions_total'] }}</span>
                <span class="lms-activity-stat-label">Sessions</span>
                <span class="lms-activity-stat-sub">{{ $kpis['sessions_past'] }} past · {{ $kpis['sessions_upcoming'] }} upcoming</span>
            </div>
            <div class="lms-activity-stat">
                <span class="lms-activity-stat-value">{{ $kpis['assignments_published'] }}</span>
                <span class="lms-activity-stat-label">Assignments</span>
                <span class="lms-activity-stat-sub">{{ $kpis['submission_rate'] ?? 0 }}% submitted</span>
            </div>
            <div class="lms-activity-stat">
                <span class="lms-activity-stat-value">{{ $kpis['assessments_published'] }}</span>
                <span class="lms-activity-stat-label">Quizzes</span>
                <span class="lms-activity-stat-sub">{{ $kpis['quiz_completion_rate'] ?? 0 }}% completed</span>
            </div>
            <div class="lms-activity-stat">
                <span class="lms-activity-stat-value">{{ $kpis['avg_assignment_score'] !== null ? $kpis['avg_assignment_score'].'%' : '—' }}</span>
                <span class="lms-activity-stat-label">Avg score</span>
                <span class="lms-activity-stat-sub">Q&amp;A {{ $kpis['questions'] }} · Materials {{ $kpis['materials'] }}</span>
            </div>
        </div>

        <details class="lms-at-risk-legend">
            <summary>About this report</summary>
            <div class="lms-at-risk-legend-body">
                <ul>
                    <li>Attendance is not recorded in the LMS. Session counts show scheduled class activities only.</li>
                    <li>Google Form quiz scores appear after a lecturer records them on the assessment page.</li>
                </ul>
            </div>
        </details>

        @if ($isSparse)
            <section class="lms-activity-empty-board">
                <div class="lms-activity-empty-hero">
                    <div class="lms-activity-empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </div>
                    <h2 class="lms-activity-empty-title">No course activity yet</h2>
                    <p class="lms-activity-empty-text">
                        This course has {{ $kpis['enrolled'] }} enrolled {{ \Illuminate\Support\Str::plural('student', $kpis['enrolled']) }}, but no published sessions, assignments, or quizzes to report on.
                    </p>
                </div>

                <div class="lms-activity-empty-grid">
                    <div class="lms-activity-empty-tile">
                        <span class="lms-activity-empty-tile-label">Class sessions</span>
                        <p>Schedule lectures or labs to track class activities here.</p>
                        @can('update', $selectedCourse)
                            <a href="{{ route('courses.sessions.index', $selectedCourse) }}" class="lms-btn-secondary lms-btn-secondary--xs">Manage schedule</a>
                        @endcan
                    </div>
                    <div class="lms-activity-empty-tile">
                        <span class="lms-activity-empty-tile-label">Assignments</span>
                        <p>Publish assignments to see submission rates and scores.</p>
                        @can('update', $selectedCourse)
                            <a href="{{ route('courses.show', $selectedCourse) }}" class="lms-btn-secondary lms-btn-secondary--xs">Open course</a>
                        @endcan
                    </div>
                    <div class="lms-activity-empty-tile">
                        <span class="lms-activity-empty-tile-label">Quizzes</span>
                        <p>Publish manual or Google Form assessments to track completion.</p>
                        @can('update', $selectedCourse)
                            <a href="{{ route('courses.assessments.index', $selectedCourse) }}" class="lms-btn-secondary lms-btn-secondary--xs">Assessments</a>
                        @endcan
                    </div>
                </div>

                @if ($report['participation']->isNotEmpty())
                    <div class="lms-activity-empty-roster">
                        <h3 class="lms-activity-empty-roster-title">Enrolled students</h3>
                        <div class="corp-table-wrap">
                            <table class="corp-table corp-table--compact">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Assignments</th>
                                        <th>Quizzes</th>
                                        <th>Q&amp;A</th>
                                        <th>Participation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['participation'] as $row)
                                        <tr class="corp-table-row">
                                            <td class="corp-table-cell">
                                                <span class="corp-table-title">{{ $row['student']->name }}</span>
                                                <span class="corp-table-meta">{{ $row['student']->student_id ?: 'No ID' }} · {{ $row['student']->email }}</span>
                                            </td>
                                            <td class="corp-table-cell">{{ $row['assignments_submitted'] }} / {{ $row['assignments_total'] }}</td>
                                            <td class="corp-table-cell">{{ $row['quizzes_completed'] }} / {{ $row['quizzes_total'] }}</td>
                                            <td class="corp-table-cell">{{ $row['questions_asked'] }}Q / {{ $row['answers_posted'] }}A</td>
                                            <td class="corp-table-cell">{{ $row['participation_rate'] !== null ? $row['participation_rate'].'%' : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </section>
        @else
            <div class="lms-activity-sections">
                <section class="lms-activity-section">
                    <div class="lms-activity-section-head">
                        <div>
                            <h2 class="lms-activity-section-title">Class sessions</h2>
                            <p class="lms-activity-section-desc">Scheduled class activities (attendance not tracked).</p>
                        </div>
                        <span class="lms-at-risk-list-count">{{ $report['sessions']->count() }}</span>
                    </div>
                    @if ($report['sessions']->isNotEmpty())
                        <div class="corp-table-wrap">
                            <table class="corp-table corp-table--compact">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>When</th>
                                        <th>Mode</th>
                                        <th>Details</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['sessions'] as $session)
                                        <tr class="corp-table-row">
                                            <td class="corp-table-cell">
                                                <span class="corp-table-title">{{ $session->displayTitle() }}</span>
                                            </td>
                                            <td class="corp-table-cell corp-table-cell--muted">
                                                {{ $session->dateLabel() }} · {{ $session->timeRangeLabel() }}
                                            </td>
                                            <td class="corp-table-cell">{{ $session->mode?->label() }}</td>
                                            <td class="corp-table-cell corp-table-cell--muted">
                                                {{ $session->mode?->value === 'online' ? ($session->meeting_link ?: '—') : ($session->location ?: '—') }}
                                            </td>
                                            <td class="corp-table-cell">
                                                <span class="lms-activity-status {{ $session->starts_at->isPast() ? 'is-past' : 'is-upcoming' }}">
                                                    {{ $session->starts_at->isPast() ? 'Past' : 'Upcoming' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="lms-activity-section-empty">No class sessions scheduled.</div>
                    @endif
                </section>

                <section class="lms-activity-section">
                    <div class="lms-activity-section-head">
                        <div>
                            <h2 class="lms-activity-section-title">Assignments</h2>
                            <p class="lms-activity-section-desc">Published assignments and submission progress.</p>
                        </div>
                        <span class="lms-at-risk-list-count">{{ $report['assignments']->count() }}</span>
                    </div>
                    @if ($report['assignments']->isNotEmpty())
                        <div class="corp-table-wrap">
                            <table class="corp-table corp-table--compact">
                                <thead>
                                    <tr>
                                        <th>Assignment</th>
                                        <th>Due</th>
                                        <th>Submitted</th>
                                        <th>Late</th>
                                        <th>Reviewed</th>
                                        <th>Avg score</th>
                                        <th>Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['assignments'] as $row)
                                        <tr class="corp-table-row">
                                            <td class="corp-table-cell"><span class="corp-table-title">{{ $row['assignment']->title }}</span></td>
                                            <td class="corp-table-cell corp-table-cell--muted">{{ $row['assignment']->due_at?->format('M j, Y') ?? '—' }}</td>
                                            <td class="corp-table-cell">{{ $row['submitted'] }} / {{ $row['submitted'] + $row['not_submitted'] }}</td>
                                            <td class="corp-table-cell">{{ $row['late'] }}</td>
                                            <td class="corp-table-cell">{{ $row['reviewed'] }}</td>
                                            <td class="corp-table-cell">{{ $row['avg_score'] !== null ? $row['avg_score'].'%' : '—' }}</td>
                                            <td class="corp-table-cell">{{ $row['submission_rate'] !== null ? $row['submission_rate'].'%' : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="lms-activity-section-empty">No published assignments.</div>
                    @endif
                </section>

                <section class="lms-activity-section">
                    <div class="lms-activity-section-head">
                        <div>
                            <h2 class="lms-activity-section-title">Quizzes / assessments</h2>
                            <p class="lms-activity-section-desc">Completion and scores for published assessments.</p>
                        </div>
                        <span class="lms-at-risk-list-count">{{ $report['assessments']->count() }}</span>
                    </div>
                    @if ($report['assessments']->isNotEmpty())
                        <div class="corp-table-wrap">
                            <table class="corp-table corp-table--compact">
                                <thead>
                                    <tr>
                                        <th>Assessment</th>
                                        <th>Type</th>
                                        <th>Due</th>
                                        <th>Completed</th>
                                        <th>Rate</th>
                                        <th>Avg score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['assessments'] as $row)
                                        <tr class="corp-table-row">
                                            <td class="corp-table-cell"><span class="corp-table-title">{{ $row['assessment']->title }}</span></td>
                                            <td class="corp-table-cell">{{ $row['type_label'] }}</td>
                                            <td class="corp-table-cell corp-table-cell--muted">{{ $row['assessment']->due_at?->format('M j, Y') ?? '—' }}</td>
                                            <td class="corp-table-cell">{{ $row['completed'] }} / {{ $row['completed'] + $row['not_completed'] }}</td>
                                            <td class="corp-table-cell">{{ $row['completion_rate'] !== null ? $row['completion_rate'].'%' : '—' }}</td>
                                            <td class="corp-table-cell">{{ $row['avg_score'] !== null ? $row['avg_score'].'%' : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="lms-activity-section-empty">No published assessments.</div>
                    @endif
                </section>

                <section class="lms-activity-section">
                    <div class="lms-activity-section-head">
                        <div>
                            <h2 class="lms-activity-section-title">Student participation</h2>
                            <p class="lms-activity-section-desc">Assignments, quizzes, and Q&amp;A activity per enrolled student.</p>
                        </div>
                        <span class="lms-at-risk-list-count">{{ $report['participation']->count() }}</span>
                    </div>
                    @if ($report['participation']->isNotEmpty())
                        <div class="corp-table-wrap">
                            <table class="corp-table corp-table--compact">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Assignments</th>
                                        <th>Quizzes</th>
                                        <th>Q&amp;A</th>
                                        <th>Participation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['participation'] as $row)
                                        <tr class="corp-table-row">
                                            <td class="corp-table-cell">
                                                <span class="corp-table-title">{{ $row['student']->name }}</span>
                                                <span class="corp-table-meta">{{ $row['student']->student_id ?: 'No ID' }} · {{ $row['student']->email }}</span>
                                            </td>
                                            <td class="corp-table-cell">{{ $row['assignments_submitted'] }} / {{ $row['assignments_total'] }}</td>
                                            <td class="corp-table-cell">{{ $row['quizzes_completed'] }} / {{ $row['quizzes_total'] }}</td>
                                            <td class="corp-table-cell">{{ $row['questions_asked'] }}Q / {{ $row['answers_posted'] }}A</td>
                                            <td class="corp-table-cell">{{ $row['participation_rate'] !== null ? $row['participation_rate'].'%' : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="lms-activity-section-empty">No enrolled students.</div>
                    @endif
                </section>
            </div>
        @endif
    @endif
</div>
@endsection
