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
        subtitle="Student-level performance by assignment — status, scores, lateness, and feedback. Leave Assignment empty to see the full course."
    >
        @if ($selectedCourse)
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
        <a href="{{ route('reports.activity') }}" class="lms-report-tab">Course activity</a>
        <a href="{{ route('reports.at-risk') }}" class="lms-report-tab">Course at-risk</a>
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
                <label for="report-assignment" class="lms-field-label">Assignment <span class="font-normal text-isarva-muted">(optional)</span></label>
                <select
                    id="report-assignment"
                    name="assignment"
                    class="lms-field-input mt-1.5"
                    @disabled(! $selectedCourse)
                >
                    <option value="">{{ $selectedCourse ? 'All assignments' : 'Select a course first' }}</option>
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
            message="Choose a course to see all published assignments, or narrow to one assignment."
            variant="chart"
        />
    @elseif ($assignments->isEmpty())
        <x-lms.empty-state
            title="No published assignments"
            message="Publish at least one assignment in this course to generate the report."
            variant="assignment"
        />
    @else
        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">
                        @if ($selectedAssignment)
                            {{ $selectedAssignment->title }}
                        @else
                            All assignments — {{ $selectedCourse->code }}
                        @endif
                    </h2>
                    <p class="corp-panel-desc">
                        {{ $selectedCourse->code }} · {{ $selectedCourse->title }}
                        @if ($selectedAssignment?->due_at)
                            · Due {{ $selectedAssignment->due_at->format('l, M j, Y g:i A') }}
                        @elseif (! $selectedAssignment)
                            · {{ $sections->count() }} published {{ \Illuminate\Support\Str::plural('assignment', $sections->count()) }}
                        @endif
                    </p>
                </div>
                <span class="corp-sidebar-badge">{{ $rows->count() }} rows</span>
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

        @forelse ($sections as $section)
            @php
                $sectionAssignment = $section['assignment'];
                $sectionRows = $section['rows'];
                $sectionKpis = $section['kpis'] ?? null;
            @endphp
            <section class="corp-panel">
                <div class="corp-panel-head">
                    <div>
                        <h2 class="corp-panel-title">{{ $sectionAssignment->title }}</h2>
                        <p class="corp-panel-desc">
                            @if ($sectionAssignment->due_at)
                                Due {{ $sectionAssignment->due_at->format('M j, Y g:i A') }} ·
                            @endif
                            {{ $sectionRows->count() }} student {{ \Illuminate\Support\Str::plural('row', $sectionRows->count()) }}
                            @if ($sectionKpis)
                                · Avg {{ $sectionKpis['avg_score'] !== null ? $sectionKpis['avg_score'].'%' : '—' }}
                            @endif
                        </p>
                    </div>
                    <span class="corp-sidebar-badge">{{ $sectionRows->count() }}</span>
                </div>

                @if ($sectionRows->isNotEmpty())
                    @include('hubs.partials.assignment-report-table', ['rows' => $sectionRows])
                @else
                    <div class="corp-panel-body">
                        <p class="text-sm text-isarva-muted">No students match the current filters for this assignment.</p>
                    </div>
                @endif
            </section>
        @empty
            <x-lms.empty-state
                title="No assignment data"
                message="No published assignments are available for this course."
                variant="assignment"
            />
        @endforelse
    @endif
</div>
@endsection
