@extends('layouts.lms')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-page">
    <div class="dashboard-layout">
        <div class="dashboard-main">
            <header class="dashboard-welcome">
                <div>
                    <p class="dashboard-welcome-eyebrow">Lecturer dashboard</p>
                    <h2 class="dashboard-welcome-title">Welcome back, {{ auth()->user()->name }}</h2>
                    <p class="dashboard-welcome-desc">Track your modules, reviews, and upcoming deadlines.</p>
                </div>
                <a href="{{ route('courses.create') }}" class="lms-btn-primary">New course</a>
            </header>

            <x-dashboard.resume-card
                :course="$featuredCourse"
                :progress="$featuredProgress"
                subtitle="Your latest course"
            />

            <section class="dashboard-section">
                <div class="dashboard-section-head">
                    <h2 class="dashboard-section-title">Status</h2>
                </div>
                <div class="dashboard-stats-grid dashboard-stats-grid--2">
                    <x-dashboard.stat-ring label="Active courses" :value="$stats['active_courses']" :sub="$stats['active_courses'].' of '.$stats['courses'].' courses'" :percent="$stats['active_courses_pct']" tone="brand" />
                    <x-dashboard.stat-ring label="To review" :value="$stats['pending_reviews']" :sub="$stats['reviewed'].' of '.$stats['total_submissions'].' reviewed'" :percent="$stats['reviewed_pct']" tone="orange" />
                </div>
            </section>

            <section class="dashboard-section dashboard-panel">
                <div class="dashboard-section-head">
                    <div>
                        <h2 class="dashboard-section-title-lg">My courses</h2>
                        <p class="dashboard-section-desc">{{ $courses->count() }} {{ $courses->count() === 1 ? 'module' : 'modules' }} you teach.</p>
                    </div>
                    <a href="{{ route('courses.index') }}" class="lms-text-link">View all →</a>
                </div>

                <div class="dashboard-course-grid">
                    @forelse ($courses as $course)
                        <x-dashboard.course-card
                            :course="$course"
                            :progress="\App\Support\DashboardMetrics::lecturerCourseProgress($course)"
                            :meta="$course->students_count.' students · '.$course->assignments_count.' assignments'"
                        />
                    @empty
                        <div class="dashboard-course-grid-empty">
                            <x-lms.empty-state title="Create your first course" message="Add a module to start assigning work to students." variant="notebook">
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
