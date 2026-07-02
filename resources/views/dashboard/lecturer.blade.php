@extends('layouts.lms')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="corp-dashboard">
    <section class="corp-dash-summary">
        <div class="corp-dash-toolbar">
            <div class="corp-dash-heading">
                <h1 class="corp-dash-title">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="corp-dash-meta">{{ now()->format('l, F j, Y') }} · Lecturer workspace</p>
            </div>
            <div class="corp-dash-actions">
                <a href="{{ route('courses.index') }}" class="lms-btn-secondary lms-btn-secondary--xs">All courses</a>
                <a href="{{ route('courses.create') }}" class="lms-btn-primary lms-btn-primary--xs">New course</a>
            </div>
        </div>

        <div class="corp-kpi-grid corp-kpi-grid--2">
            <x-dashboard.kpi-card label="Active courses" :value="$stats['active_courses']" :sub="$stats['active_courses'].' of '.$stats['courses'].' programmes active'" icon="book" />
            <x-dashboard.kpi-card label="Pending reviews" :value="$stats['pending_reviews']" :sub="$stats['reviewed'].' of '.$stats['total_submissions'].' submissions reviewed'" icon="clipboard" />
        </div>
    </section>

    <div class="corp-dash-grid">
        <div class="corp-dash-primary">
            <x-dashboard.highlight-card
                :course="$featuredCourse"
                :progress="$featuredProgress"
                subtitle="Most recent module"
            />

            @include('dashboard.partials.analytics')

            <section class="corp-panel">
                <div class="corp-panel-head">
                    <div>
                        <h2 class="corp-panel-title">Teaching portfolio</h2>
                        <p class="corp-panel-desc">{{ $courses->count() }} {{ $courses->count() === 1 ? 'module' : 'modules' }} under your supervision.</p>
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
                                    <x-dashboard.course-table-row
                                        :course="$course"
                                        :progress="\App\Support\DashboardMetrics::lecturerCourseProgress($course)"
                                        :meta="$course->students_count.' students · '.$course->assignments_count.' assignments'"
                                    />
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-lms.empty-state title="Create your first course" message="Add a programme module to begin assigning work." variant="notebook">
                        <a href="{{ route('courses.create') }}" class="lms-btn-primary">Create course</a>
                    </x-lms.empty-state>
                @endif
            </section>
        </div>

        @include('dashboard.partials.aside', ['upcoming' => $upcoming, 'highlightDates' => $highlightDates])
    </div>
</div>
@endsection
