@extends('layouts.lms')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-page">
    <div class="dashboard-layout">
        <div class="dashboard-main">
            <header class="dashboard-welcome">
                <div>
                    <p class="dashboard-welcome-eyebrow">Student dashboard</p>
                    <h2 class="dashboard-welcome-title">Hi, {{ auth()->user()->name }}</h2>
                    <p class="dashboard-welcome-desc">Pick up assignments and track progress across your modules.</p>
                </div>
                <a href="{{ route('courses.index') }}" class="lms-btn-primary">All courses</a>
            </header>

            <x-dashboard.resume-card
                :course="$featuredCourse"
                :progress="$featuredProgress"
                subtitle="Pick up where you left off"
            />

            <section class="dashboard-section">
                <div class="dashboard-section-head">
                    <h2 class="dashboard-section-title">Status</h2>
                </div>
                <div class="dashboard-stats-grid dashboard-stats-grid--3">
                    <x-dashboard.stat-ring label="Enrolled" :value="$stats['courses']" :sub="$stats['courses'].' active courses'" :percent="$stats['courses'] > 0 ? 100 : 0" tone="brand" />
                    <x-dashboard.stat-ring label="Pending" :value="$stats['pending']" :sub="$stats['pending'].' assignments left'" :percent="$stats['pending_pct']" tone="orange" />
                    <x-dashboard.stat-ring label="Completed" :value="$stats['completion_pct'].'%'" :sub="$stats['submitted'].' of '.$stats['total_assignments'].' submitted'" :percent="$stats['completion_pct']" tone="sky" />
                </div>
            </section>

            <section class="dashboard-section dashboard-panel">
                <div class="dashboard-section-head">
                    <div>
                        <h2 class="dashboard-section-title-lg">My courses</h2>
                        <p class="dashboard-section-desc">Your enrolled programmes this term.</p>
                    </div>
                    <a href="{{ route('courses.index') }}" class="lms-text-link">View all →</a>
                </div>

                <div class="dashboard-course-grid">
                    @forelse ($courses as $course)
                        @php
                            $count = $course->assignments_count ?? $course->assignments->count();
                        @endphp
                        <x-dashboard.course-card
                            :course="$course"
                            :progress="\App\Support\DashboardMetrics::studentCourseProgress(auth()->user(), $course)"
                            :meta="$course->code.' · '.$count.' assignments'"
                        />
                    @empty
                        <div class="dashboard-course-grid-empty">
                            <x-lms.empty-state title="No courses assigned yet" message="Your lecturer will enroll you in modules soon." variant="books" />
                        </div>
                    @endforelse
                </div>
            </section>

            @if ($openAssignments->isNotEmpty())
                <section class="dashboard-section dashboard-panel">
                    <h2 class="dashboard-section-title-lg">Open assignments</h2>
                    <div class="mt-3 space-y-2">
                        @foreach ($openAssignments->take(5) as $assignment)
                            <a href="{{ route('assignments.show', $assignment) }}" class="dashboard-assignment-pill">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-isarva-heading">{{ $assignment->title }}</p>
                                    <p class="text-xs text-isarva-muted">{{ $assignment->course->code }}</p>
                                </div>
                                @if ($assignment->due_at)
                                    <span class="shrink-0 text-xs font-semibold {{ $assignment->isOverdue() ? 'text-rose-600' : 'text-amber-600' }}">
                                        {{ $assignment->isOverdue() ? 'Overdue' : 'Due' }} {{ $assignment->due_at->format('M j') }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        @include('dashboard.partials.aside', ['upcoming' => $upcoming, 'highlightDates' => $highlightDates])
    </div>
</div>
@endsection
