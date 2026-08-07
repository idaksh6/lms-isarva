@extends('layouts.lms')

@section('title', 'Course activity report')
@section('page_title', 'Reports')

@section('content')
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

    <nav class="lms-report-tabs" aria-label="Report type">
        <a href="{{ route('reports.index') }}" class="lms-report-tab">Overview</a>
        <a href="{{ route('reports.assignments') }}" class="lms-report-tab">Individual assignment</a>
        <a href="{{ route('reports.activity') }}" class="lms-report-tab is-active">Course activity</a>
    </nav>

    <form method="GET" action="{{ route('reports.activity') }}" class="lms-report-filters">
        <div class="lms-report-filters-grid">
            <div class="lms-form-field lms-report-filters-span">
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
        </div>
        <div class="lms-report-filters-actions">
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
        @php $kpis = $report['kpis']; @endphp

        <div class="corp-kpi-grid">
            <x-dashboard.kpi-card label="Enrolled" :value="$kpis['enrolled']" :sub="'Active participants '.$kpis['active_participants'].' ('.($kpis['participation_rate'] ?? 0).'%)'" icon="users" />
            <x-dashboard.kpi-card label="Sessions" :value="$kpis['sessions_total']" :sub="$kpis['sessions_past'].' past · '.$kpis['sessions_upcoming'].' upcoming'" icon="book" />
            <x-dashboard.kpi-card label="Assignments" :value="$kpis['assignments_published']" :sub="'Submission rate '.($kpis['submission_rate'] ?? 0).'%'" icon="clipboard" />
            <x-dashboard.kpi-card label="Quizzes" :value="$kpis['assessments_published']" :sub="'Completion '.($kpis['quiz_completion_rate'] ?? 0).'%'" icon="chart" />
            <x-dashboard.kpi-card label="Avg assignment score" :value="$kpis['avg_assignment_score'] !== null ? $kpis['avg_assignment_score'].'%' : '—'" :sub="'Q&A '.$kpis['questions'].' · Materials '.$kpis['materials']" icon="inbox" />
        </div>

        @foreach ($report['notes'] as $note)
            <p class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-950">{{ $note }}</p>
        @endforeach

        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">Class sessions / activities</h2>
                    <p class="corp-panel-desc">Scheduled sessions (attendance is not tracked in the LMS).</p>
                </div>
                <span class="corp-sidebar-badge">{{ $report['sessions']->count() }}</span>
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
                                        {{ $session->starts_at->isPast() ? 'Past' : 'Upcoming' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="corp-panel-body"><p class="text-sm text-isarva-muted">No class sessions scheduled for this course.</p></div>
            @endif
        </section>

        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">Assignments</h2>
                    <p class="corp-panel-desc">Published assignments and submission progress.</p>
                </div>
                <span class="corp-sidebar-badge">{{ $report['assignments']->count() }}</span>
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
                <div class="corp-panel-body"><p class="text-sm text-isarva-muted">No published assignments.</p></div>
            @endif
        </section>

        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">Quizzes / assessments</h2>
                    <p class="corp-panel-desc">Published quizzes and completion (manual quizzes include LMS scores).</p>
                </div>
                <span class="corp-sidebar-badge">{{ $report['assessments']->count() }}</span>
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
                <div class="corp-panel-body"><p class="text-sm text-isarva-muted">No published assessments.</p></div>
            @endif
        </section>

        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">Student participation</h2>
                    <p class="corp-panel-desc">Assignments, quizzes, and Q&amp;A activity per enrolled student.</p>
                </div>
                <span class="corp-sidebar-badge">{{ $report['participation']->count() }}</span>
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
                <div class="corp-panel-body"><p class="text-sm text-isarva-muted">No enrolled students.</p></div>
            @endif
        </section>
    @endif
</div>
@endsection
