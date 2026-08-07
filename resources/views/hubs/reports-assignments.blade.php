@extends('layouts.lms')

@php
    use App\Enums\SubmissionStatus;
@endphp

@section('title', 'Individual assignment report')
@section('page_title', 'Reports')

@section('content')
<div class="corp-dashboard">
    <x-lms.module-hero
        module="reports"
        title="Individual assignment report"
        subtitle="Student-level performance for one assignment — status, scores, lateness, and feedback."
    >
        @if ($selectedAssignment)
            <div class="lms-report-export-group">
                <a
                    href="{{ route('reports.assignments.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
                    class="lms-btn-secondary lms-btn-secondary--xs"
                >CSV</a>
                <a
                    href="{{ route('reports.assignments.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}"
                    class="lms-btn-secondary lms-btn-secondary--xs"
                >Excel</a>
                <a
                    href="{{ route('reports.assignments.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                    class="lms-btn-primary lms-btn-primary--xs"
                >PDF</a>
            </div>
        @endif
    </x-lms.module-hero>

    <nav class="lms-report-tabs" aria-label="Report type">
        <a href="{{ route('reports.index') }}" class="lms-report-tab">Overview</a>
        <a href="{{ route('reports.assignments') }}" class="lms-report-tab is-active">Individual assignment</a>
    </nav>

    <form method="GET" action="{{ route('reports.assignments') }}" class="lms-report-filters">
        <div class="lms-report-filters-grid">
            <div class="lms-form-field">
                <label for="report-course" class="lms-field-label">Course</label>
                <select id="report-course" name="course" class="lms-field-input mt-1.5" onchange="this.form.submit()" required>
                    <option value="">Choose a course…</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected(($filters['course'] ?? null) == $course->id)>
                            {{ $course->code }} — {{ $course->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lms-form-field">
                <label for="report-assignment" class="lms-field-label">Assignment</label>
                <select
                    id="report-assignment"
                    name="assignment"
                    class="lms-field-input mt-1.5"
                    @disabled(! $selectedCourse)
                    onchange="this.form.submit()"
                >
                    <option value="">{{ $selectedCourse ? 'Choose an assignment…' : 'Select a course first' }}</option>
                    @foreach ($assignments as $assignment)
                        <option value="{{ $assignment->id }}" @selected(($filters['assignment'] ?? null) == $assignment->id)>
                            {{ $assignment->title }}
                            @if ($assignment->due_at)
                                (due {{ $assignment->due_at->format('M j') }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lms-form-field">
                <label for="report-status" class="lms-field-label">Status</label>
                <select id="report-status" name="status" class="lms-field-input mt-1.5">
                    <option value="">All statuses</option>
                    <option value="not_submitted" @selected(($filters['status'] ?? null) === 'not_submitted')>Not submitted</option>
                    @foreach (SubmissionStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lms-form-field">
                <label for="report-graded" class="lms-field-label">Graded</label>
                <select id="report-graded" name="graded" class="lms-field-input mt-1.5">
                    <option value="">All</option>
                    <option value="graded" @selected(($filters['graded'] ?? null) === 'graded')>Graded</option>
                    <option value="ungraded" @selected(($filters['graded'] ?? null) === 'ungraded')>Submitted, ungraded</option>
                </select>
            </div>

            <div class="lms-form-field lms-report-filters-span">
                <label for="report-q" class="lms-field-label">Student search</label>
                <input
                    id="report-q"
                    type="search"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    class="lms-field-input mt-1.5"
                    placeholder="Name, student ID, or email"
                >
            </div>

            <div class="lms-form-field">
                <label for="report-from" class="lms-field-label">Submitted from</label>
                <input id="report-from" type="date" name="submitted_from" value="{{ $filters['submitted_from'] ?? '' }}" class="lms-field-input mt-1.5">
            </div>

            <div class="lms-form-field">
                <label for="report-to" class="lms-field-label">Submitted to</label>
                <input id="report-to" type="date" name="submitted_to" value="{{ $filters['submitted_to'] ?? '' }}" class="lms-field-input mt-1.5">
            </div>

            <div class="lms-form-field">
                <label for="report-score-min" class="lms-field-label">Min score %</label>
                <input id="report-score-min" type="number" name="score_min" min="0" max="100" step="0.1" value="{{ $filters['score_min'] ?? '' }}" class="lms-field-input mt-1.5">
            </div>

            <div class="lms-form-field">
                <label for="report-score-max" class="lms-field-label">Max score %</label>
                <input id="report-score-max" type="number" name="score_max" min="0" max="100" step="0.1" value="{{ $filters['score_max'] ?? '' }}" class="lms-field-input mt-1.5">
            </div>
        </div>

        <div class="lms-report-filters-actions">
            <button type="submit" class="lms-btn-primary">Apply filters</button>
            @if ($selectedCourse || $selectedAssignment || ($filters['q'] ?? null) || ($filters['status'] ?? null))
                <a href="{{ route('reports.assignments') }}" class="lms-btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    @if (! $selectedCourse)
        <x-lms.empty-state
            title="Select a course"
            message="Choose a course, then an assignment, to see every enrolled student’s performance."
            variant="chart"
        />
    @elseif (! $selectedAssignment)
        <x-lms.empty-state
            title="Select an assignment"
            message="Pick a published assignment to open the student performance report."
            variant="assignment"
        />
    @else
        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">{{ $selectedAssignment->title }}</h2>
                    <p class="corp-panel-desc">
                        {{ $selectedCourse->code }} · {{ $selectedCourse->title }}
                        @if ($selectedAssignment->due_at)
                            · Due {{ $selectedAssignment->due_at->format('l, M j, Y g:i A') }}
                        @endif
                    </p>
                </div>
                <span class="corp-sidebar-badge">{{ $rows->count() }} shown</span>
            </div>
        </section>

        @if ($kpis)
            <div class="corp-kpi-grid">
                <x-dashboard.kpi-card label="Enrolled" :value="$kpis['enrolled']" :sub="'Submission rate '.($kpis['submission_rate'] ?? 0).'%'" icon="users" />
                <x-dashboard.kpi-card label="Submitted" :value="$kpis['submitted']" :sub="$kpis['not_submitted'].' not submitted'" icon="inbox" />
                <x-dashboard.kpi-card label="Late" :value="$kpis['late']" :sub="'On-time '.($kpis['on_time_rate'] ?? 0).'%'" icon="clipboard" />
                <x-dashboard.kpi-card label="Reviewed" :value="$kpis['reviewed']" :sub="'Graded rate '.($kpis['graded_rate'] ?? 0).'%'" icon="chart" />
                <x-dashboard.kpi-card
                    label="Avg score"
                    :value="$kpis['avg_score'] !== null ? $kpis['avg_score'].'%' : '—'"
                    :sub="'Median '.($kpis['median_score'] !== null ? $kpis['median_score'].'%' : '—').' · Min '.($kpis['min_score'] ?? '—').' · Max '.($kpis['max_score'] ?? '—')"
                    icon="chart"
                />
            </div>
        @endif

        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">Student performance</h2>
                    <p class="corp-panel-desc">One row per enrolled student, including those who have not submitted.</p>
                </div>
            </div>

            @if ($rows->isNotEmpty())
                <div class="corp-table-wrap">
                    <table class="corp-table corp-table--compact">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Days late</th>
                                <th>Score</th>
                                <th>Feedback</th>
                                <th>Source</th>
                                <th><span class="sr-only">Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @php
                                    /** @var \App\Models\User $student */
                                    $student = $row['student'];
                                    $submission = $row['submission'];
                                    $daysLate = $row['days_late'];
                                @endphp
                                <tr class="corp-table-row group">
                                    <td class="corp-table-cell">
                                        <span class="corp-table-title">{{ $student->name }}</span>
                                        <span class="corp-table-meta">
                                            {{ $student->student_id ?: 'No ID' }}
                                            · {{ $student->email }}
                                            @unless ($student->is_active)
                                                · Inactive
                                            @endunless
                                        </span>
                                    </td>
                                    <td class="corp-table-cell">
                                        @if ($submission)
                                            <x-status-badge :status="$submission->status" />
                                        @else
                                            <span class="lms-report-status-missing">Not submitted</span>
                                        @endif
                                    </td>
                                    <td class="corp-table-cell corp-table-cell--muted">
                                        {{ $row['submitted_at']?->format('M j, Y g:i A') ?? '—' }}
                                    </td>
                                    <td class="corp-table-cell corp-table-cell--muted">
                                        @if ($daysLate === null)
                                            —
                                        @elseif ($daysLate > 0)
                                            <span class="text-amber-700 font-semibold">+{{ $daysLate }}d</span>
                                        @elseif ($daysLate < 0)
                                            <span class="text-emerald-700">{{ $daysLate }}d</span>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="corp-table-cell">
                                        @if ($row['is_graded'])
                                            <x-lms.grade-badge :score="$row['score']" :letter="$row['letter']" size="sm" />
                                        @else
                                            <span class="text-xs text-isarva-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="corp-table-cell corp-table-cell--muted">
                                        <span class="lms-report-feedback" title="{{ $row['feedback'] }}">
                                            {{ $row['feedback'] ? \Illuminate\Support\Str::limit($row['feedback'], 48) : '—' }}
                                        </span>
                                        @if ($row['reviewed_at'])
                                            <span class="corp-table-meta">
                                                Reviewed {{ $row['reviewed_at']->format('M j') }}
                                                @if ($row['reviewer_name'])
                                                    by {{ $row['reviewer_name'] }}
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="corp-table-cell corp-table-cell--muted">
                                        {{ $row['source_label'] ?? '—' }}
                                        @if ($row['file_or_link'])
                                            <span class="corp-table-meta">{{ \Illuminate\Support\Str::limit($row['file_or_link'], 28) }}</span>
                                        @endif
                                    </td>
                                    <td class="corp-table-cell corp-table-cell--action">
                                        @if ($submission)
                                            <a href="{{ route('submissions.show', $submission) }}" class="corp-table-action">View</a>
                                        @else
                                            <span class="text-xs text-isarva-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-lms.empty-state
                    title="No students match these filters"
                    message="Try clearing status, graded, date, or score filters."
                    variant="users"
                />
            @endif
        </section>
    @endif
</div>
@endsection
