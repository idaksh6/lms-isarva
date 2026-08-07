@extends('layouts.lms')

@section('title', 'Reports')
@section('page_title', 'Reports')

@section('content')
@php
    $totalSubmissions = array_sum($statusBreakdown);

    $statusMeta = [
        'submitted' => ['label' => 'Submitted', 'icon' => 'inbox'],
        'late' => ['label' => 'Late', 'icon' => 'clipboard'],
        'needs_revision' => ['label' => 'Needs revision', 'icon' => 'clipboard'],
        'reviewed' => ['label' => 'Reviewed', 'icon' => 'chart'],
    ];
@endphp

<div class="corp-dashboard">
    <x-lms.module-hero module="reports" title="Reports & analytics" subtitle="Overview of courses, submissions, and review progress across your programme.">
        <a href="{{ route('reports.export') }}" class="lms-btn-primary lms-btn-primary--xs">Download CSV report</a>
    </x-lms.module-hero>

    <nav class="lms-report-tabs" aria-label="Report type">
        <a href="{{ route('reports.index') }}" class="lms-report-tab is-active">Overview</a>
        <a href="{{ route('reports.assignments') }}" class="lms-report-tab">Individual assignment</a>
    </nav>

    <div class="corp-kpi-grid">
        @foreach ($statusBreakdown as $key => $count)
            @php
                $meta = $statusMeta[$key] ?? ['label' => ucfirst($key), 'icon' => 'chart'];
                $pct = $totalSubmissions > 0 ? round(($count / $totalSubmissions) * 100).'% of total' : 'No submissions yet';
            @endphp
            <x-dashboard.kpi-card
                :label="$meta['label']"
                :value="$count"
                :sub="$pct"
                :icon="$meta['icon']"
            />
        @endforeach
    </div>

    <section class="corp-panel">
        <div class="corp-panel-head">
            <div>
                <h2 class="corp-panel-title">Course breakdown</h2>
                <p class="corp-panel-desc">Enrolments and assignments per programme.</p>
            </div>
            <span class="corp-sidebar-badge">{{ $courseBreakdown->count() }} courses</span>
        </div>
        @if ($courseBreakdown->isNotEmpty())
            <div class="corp-table-wrap">
                <table class="corp-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Course</th>
                            <th>Students</th>
                            <th>Assignments</th>
                            <th><span class="sr-only">Action</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courseBreakdown as $course)
                            <tr class="corp-table-row group">
                                <td class="corp-table-cell corp-table-cell--code">
                                    <span class="corp-code-badge">{{ $course->code }}</span>
                                </td>
                                <td class="corp-table-cell">
                                    <a href="{{ route('courses.show', $course) }}" class="corp-table-link">
                                        <span class="corp-table-title">{{ $course->title }}</span>
                                    </a>
                                </td>
                                <td class="corp-table-cell corp-table-cell--muted">{{ $course->students_count }}</td>
                                <td class="corp-table-cell corp-table-cell--muted">{{ $course->assignments_count }}</td>
                                <td class="corp-table-cell corp-table-cell--action">
                                    <a href="{{ route('courses.show', $course) }}" class="corp-table-action">Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-lms.empty-state title="No courses yet" message="Course stats will appear when programmes are created." variant="book" />
        @endif
    </section>

    <section class="corp-panel">
        <div class="corp-panel-head">
            <div>
                <h2 class="corp-panel-title">Recent submissions</h2>
                <p class="corp-panel-desc">Latest student uploads across all courses.</p>
            </div>
            <a href="{{ route('submissions.index') }}" class="corp-panel-link">View all</a>
        </div>
        @if ($recentSubmissions->isNotEmpty())
            <div class="corp-table-wrap">
                <table class="corp-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Assignment</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th class="corp-table-col--md">Grade</th>
                            <th><span class="sr-only">Action</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentSubmissions as $submission)
                            <tr class="corp-table-row group">
                                <td class="corp-table-cell">
                                    <span class="corp-table-title">{{ $submission->student->name }}</span>
                                    @if ($submission->student->student_id)
                                        <span class="corp-table-meta">{{ $submission->student->student_id }}</span>
                                    @endif
                                </td>
                                <td class="corp-table-cell">
                                    <span class="corp-table-title">{{ $submission->assignment->title }}</span>
                                    <span class="corp-table-meta">{{ $submission->assignment->course->code }}</span>
                                </td>
                                <td class="corp-table-cell corp-table-cell--muted">
                                    {{ $submission->submitted_at->format('M j, Y') }}
                                </td>
                                <td class="corp-table-cell">
                                    <x-status-badge :status="$submission->status" />
                                </td>
                                <td class="corp-table-cell corp-table-col--md">
                                    @if ($submission->isGraded())
                                        <x-lms.grade-badge :score="$submission->score" :letter="$submission->letter_grade" size="sm" />
                                    @else
                                        <span class="text-xs text-isarva-muted">—</span>
                                    @endif
                                </td>
                                <td class="corp-table-cell corp-table-cell--action">
                                    <a href="{{ route('submissions.show', $submission) }}" class="corp-table-action">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-lms.empty-state title="No submissions yet" message="Submissions will appear here when students upload work." variant="inbox" />
        @endif
    </section>
</div>
@endsection
