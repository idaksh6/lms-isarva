@extends('layouts.lms')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-page">
    <div class="dashboard-layout">
        <div class="dashboard-main">
            <header class="dashboard-welcome">
                <div>
                    <p class="dashboard-welcome-eyebrow">Admin dashboard</p>
                    <h2 class="dashboard-welcome-title">Platform at a glance</h2>
                    <p class="dashboard-welcome-desc">Monitor programmes, people, and submissions across ISARVA LMS.</p>
                </div>
                <div class="dashboard-welcome-actions">
                    <a href="{{ route('admin.users.create') }}" class="lms-btn-primary">Add user</a>
                    <a href="{{ route('courses.create') }}" class="lms-btn-primary">Create course</a>
                </div>
            </header>

            <x-dashboard.resume-card
                :course="$featuredCourse"
                :progress="$featuredProgress"
                subtitle="Featured programme"
            />

            <section class="dashboard-section">
                <div class="dashboard-section-head">
                    <h2 class="dashboard-section-title">Overview</h2>
                </div>
                <div class="dashboard-stats-grid">
                    <x-dashboard.stat-ring label="Students" :value="$stats['students']" :sub="$stats['students'].' of '.$stats['total_users'].' users'" :percent="$stats['students_pct']" tone="sky" />
                    <x-dashboard.stat-ring label="Lecturers" :value="$stats['lecturers']" :sub="$stats['lecturers'].' teaching staff'" :percent="$stats['lecturers_pct']" tone="rose" />
                    <x-dashboard.stat-ring label="Active courses" :value="$stats['active_courses']" :sub="$stats['active_courses'].' of '.$stats['courses'].' programmes'" :percent="$stats['active_courses_pct']" tone="brand" />
                    <x-dashboard.stat-ring label="Published tasks" :value="$stats['published_assignments']" :sub="$stats['pending_reviews'].' awaiting review'" :percent="$stats['published_pct']" tone="orange" />
                </div>
            </section>

            <section class="dashboard-section dashboard-panel">
                <div class="dashboard-section-head">
                    <div>
                        <h2 class="dashboard-section-title-lg">Recent courses</h2>
                        <p class="dashboard-section-desc">Programmes with live enrolments and assignments.</p>
                    </div>
                    <a href="{{ route('courses.index') }}" class="lms-text-link">View all →</a>
                </div>

                <div class="dashboard-course-grid">
                    @forelse ($recentCourses as $course)
                        <x-dashboard.course-card
                            :course="$course"
                            :progress="\App\Support\DashboardMetrics::adminCourseProgress($course)"
                            :meta="$course->lecturer->name.' · '.$course->students_count.' students · '.$course->assignments_count.' tasks'"
                        />
                    @empty
                        <div class="dashboard-course-grid-empty">
                            <x-lms.empty-state title="No courses yet" message="Create a programme module to see it here." variant="books">
                                <a href="{{ route('courses.create') }}" class="lms-btn-primary">Create course</a>
                            </x-lms.empty-state>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        @include('dashboard.partials.aside', ['upcoming' => $upcoming, 'highlightDates' => $highlightDates])
    </div>
</div>
@endsection
