@extends('layouts.lms')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-layout">
    <div class="dashboard-main">
        <x-dashboard.resume-card
            :course="$featuredCourse"
            :progress="$featuredProgress"
            subtitle="Platform overview"
        />

        <section>
            <h2 class="quyl-section-title">Overview</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <x-dashboard.stat-ring label="Students" :value="$stats['students']" :sub="$stats['students'].' of '.$stats['total_users'].' users'" :percent="$stats['students_pct']" tone="sky" />
                <x-dashboard.stat-ring label="Lecturers" :value="$stats['lecturers']" :sub="$stats['lecturers'].' teaching staff'" :percent="$stats['lecturers_pct']" tone="rose" />
                <x-dashboard.stat-ring label="Active courses" :value="$stats['active_courses']" :sub="$stats['active_courses'].' of '.$stats['courses'].' programmes'" :percent="$stats['active_courses_pct']" tone="brand" />
                <x-dashboard.stat-ring label="Published tasks" :value="$stats['published_assignments']" :sub="$stats['pending_reviews'].' awaiting review'" :percent="$stats['published_pct']" tone="orange" />
            </div>
        </section>

        <section class="quyl-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="quyl-card-title">Recent courses</h2>
                <a href="{{ route('courses.index') }}" class="lms-text-link">View all →</a>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($recentCourses as $course)
                    <x-dashboard.course-row
                        :course="$course"
                        :progress="\App\Support\DashboardMetrics::adminCourseProgress($course)"
                        :meta="$course->code.' · '.$course->lecturer->name.' · '.$course->students_count.' students'"
                        bar-from="from-sky-400"
                        bar-to="to-brand-500"
                    />
                @empty
                    <x-lms.empty-state title="No courses yet" message="Create a programme module to see it here." variant="books">
                        <a href="{{ route('courses.create') }}" class="lms-btn-primary">Create course <span aria-hidden="true">→</span></a>
                    </x-lms.empty-state>
                @endforelse
            </div>

            <div class="mt-4 flex flex-wrap gap-2 border-t border-isarva-border pt-4">
                <a href="{{ route('admin.users.create') }}" class="lms-btn-primary">Add user <span aria-hidden="true">→</span></a>
                <a href="{{ route('courses.create') }}" class="lms-btn-primary">Create course <span aria-hidden="true">→</span></a>
            </div>
        </section>
    </div>

    @include('dashboard.partials.aside', ['upcoming' => $upcoming, 'highlightDates' => $highlightDates])
</div>
@endsection
