@extends('layouts.lms')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="corp-dashboard">
    <section class="corp-dash-summary">
        <div class="corp-dash-toolbar">
            <div class="corp-dash-heading">
                <h1 class="corp-dash-title">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="corp-dash-meta">{{ now()->format('l, F j, Y') }} · Platform overview · Administrator workspace</p>
            </div>
            <div class="corp-dash-actions">
                <a href="{{ route('admin.users.bulk-import') }}" class="lms-btn-secondary lms-btn-secondary--xs">Import students</a>
                <a href="{{ route('admin.users.create') }}" class="lms-btn-secondary lms-btn-secondary--xs">Add user</a>
                <a href="{{ route('courses.create') }}" class="lms-btn-primary lms-btn-primary--xs">Create course</a>
            </div>
        </div>

        <div class="corp-kpi-grid">
            <x-dashboard.kpi-card label="Students" :value="$stats['students']" :sub="$stats['students'].' of '.$stats['total_users'].' users'" icon="users" />
            <x-dashboard.kpi-card label="Lecturers" :value="$stats['lecturers']" :sub="$stats['lecturers'].' teaching staff'" icon="lecturer" />
            <x-dashboard.kpi-card label="Active courses" :value="$stats['active_courses']" :sub="$stats['active_courses'].' of '.$stats['courses'].' programmes'" icon="book" />
            <x-dashboard.kpi-card label="Published tasks" :value="$stats['published_assignments']" :sub="$stats['pending_reviews'].' awaiting review'" icon="clipboard" />
        </div>
    </section>

    <div class="corp-dash-grid">
        <div class="corp-dash-primary">
            <x-dashboard.highlight-card
                :course="$featuredCourse"
                :progress="$featuredProgress"
                subtitle="Featured programme"
            />

            @include('dashboard.partials.analytics')

            <section class="corp-panel">
                <div class="corp-panel-head">
                    <div>
                        <h2 class="corp-panel-title">Recent programmes</h2>
                        <p class="corp-panel-desc">Courses with active enrolments and assignments.</p>
                    </div>
                    <a href="{{ route('courses.index') }}" class="corp-panel-link">View all</a>
                </div>
                @if ($recentCourses->isNotEmpty())
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
                                @foreach ($recentCourses as $course)
                                    <x-dashboard.course-table-row
                                        :course="$course"
                                        :progress="\App\Support\DashboardMetrics::adminCourseProgress($course)"
                                        :meta="$course->lecturer->name.' · '.$course->students_count.' students · '.$course->assignments_count.' tasks'"
                                    />
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-lms.empty-state title="No courses yet" message="Create a programme module to see it listed here." variant="books">
                        <a href="{{ route('courses.create') }}" class="lms-btn-primary">Create course</a>
                    </x-lms.empty-state>
                @endif
            </section>
        </div>

        @include('dashboard.partials.aside', ['upcoming' => $upcoming, 'highlightDates' => $highlightDates])
    </div>
</div>
@endsection
