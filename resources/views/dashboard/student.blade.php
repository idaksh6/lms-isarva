@extends('layouts.lms')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="corp-dashboard">
    <section class="corp-dash-summary">
        <div class="corp-dash-toolbar">
            <div class="corp-dash-heading">
                <h1 class="corp-dash-title">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="corp-dash-meta">{{ now()->format('l, F j, Y') }} · Student workspace</p>
            </div>
            <div class="corp-dash-actions">
                <a href="{{ route('courses.index') }}" class="lms-btn-secondary lms-btn-secondary--xs">All courses</a>
                <a href="{{ route('assignments.index') }}" class="lms-btn-primary lms-btn-primary--xs">Assignments</a>
            </div>
        </div>

        <div class="corp-kpi-grid corp-kpi-grid--3">
            <x-dashboard.kpi-card label="Enrolled courses" :value="$stats['courses']" :sub="$stats['courses'].' active programmes'" icon="book" />
            <x-dashboard.kpi-card label="Pending work" :value="$stats['pending']" :sub="$stats['pending'].' assignments remaining'" icon="clipboard" />
            <x-dashboard.kpi-card label="Completion rate" :value="$stats['completion_pct'].'%'" :sub="$stats['submitted'].' of '.$stats['total_assignments'].' submitted'" icon="chart" />
        </div>
    </section>

    <div class="corp-dash-grid">
        <div class="corp-dash-primary">
            <x-dashboard.highlight-card
                :course="$featuredCourse"
                :progress="$featuredProgress"
                subtitle="Current priority"
            />

            @include('dashboard.partials.analytics')

            <section class="corp-panel">
                <div class="corp-panel-head">
                    <div>
                        <h2 class="corp-panel-title">My courses</h2>
                        <p class="corp-panel-desc">Enrolled programmes and progress summary.</p>
                    </div>
                    <a href="{{ route('courses.index') }}" class="corp-panel-link">View all</a>
                </div>
                @if ($courses->isNotEmpty())
                    <div class="corp-table-wrap">
                        <table class="corp-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Course</th>
                                    <th>Progress</th>
                                    <th><span class="sr-only">Action</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($courses as $course)
                                    @php
                                        $count = $course->assignments_count ?? $course->assignments->count();
                                    @endphp
                                    <x-dashboard.course-table-row
                                        :course="$course"
                                        :progress="\App\Support\DashboardMetrics::studentCourseProgress(auth()->user(), $course)"
                                        :meta="$count.' assignments'"
                                    />
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-lms.empty-state title="No courses assigned yet" message="Your programme administrator will enroll you in modules." variant="books" />
                @endif
            </section>

            @if ($openAssignments->isNotEmpty())
                <section class="corp-panel">
                    <div class="corp-panel-head">
                        <div>
                            <h2 class="corp-panel-title">Open assignments</h2>
                            <p class="corp-panel-desc">Work items requiring your attention.</p>
                        </div>
                        <a href="{{ route('assignments.index') }}" class="corp-panel-link">View all</a>
                    </div>
                    <div class="corp-table-wrap">
                        <table class="corp-table">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Course</th>
                                    <th>Due date</th>
                                    <th>Status</th>
                                    <th><span class="sr-only">Action</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($openAssignments->take(8) as $assignment)
                                    <tr class="corp-table-row">
                                        <td class="corp-table-cell">
                                            <a href="{{ route('assignments.show', $assignment) }}" class="corp-table-link">
                                                <span class="corp-table-title">{{ $assignment->title }}</span>
                                            </a>
                                        </td>
                                        <td class="corp-table-cell">
                                            <span class="corp-code-badge">{{ $assignment->course->code }}</span>
                                        </td>
                                        <td class="corp-table-cell corp-table-cell--muted">
                                            {{ $assignment->due_at?->format('M j, Y') ?? '—' }}
                                        </td>
                                        <td class="corp-table-cell">
                                            @if ($assignment->due_at && $assignment->isOverdue())
                                                <span class="corp-status corp-status--danger">Overdue</span>
                                            @elseif ($assignment->due_at)
                                                <span class="corp-status corp-status--warning">Due soon</span>
                                            @else
                                                <span class="corp-status">Open</span>
                                            @endif
                                        </td>
                                        <td class="corp-table-cell corp-table-cell--action">
                                            <a href="{{ route('assignments.show', $assignment) }}" class="corp-table-action">Review</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>

        @include('dashboard.partials.aside', ['upcoming' => $upcoming, 'highlightDates' => $highlightDates])
    </div>
</div>
@endsection
