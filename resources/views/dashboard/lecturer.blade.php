@extends('layouts.lms')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-layout">
    <div class="dashboard-main">
        <x-dashboard.resume-card
            :course="$featuredCourse"
            :progress="$featuredProgress"
            subtitle="Your latest course"
        />

        <section>
            <h2 class="quyl-section-title">Status</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <x-dashboard.stat-ring label="Active courses" :value="$stats['active_courses']" :sub="$stats['active_courses'].' of '.$stats['courses'].' courses'" :percent="$stats['active_courses_pct']" tone="brand" />
                <x-dashboard.stat-ring label="To review" :value="$stats['pending_reviews']" :sub="$stats['reviewed'].' of '.$stats['total_submissions'].' reviewed'" :percent="$stats['reviewed_pct']" tone="orange" />
            </div>
        </section>

        <section class="quyl-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="quyl-card-title">My courses</h2>
                <a href="{{ route('courses.create') }}" class="lms-btn-primary lms-btn-primary--xs">New course <span aria-hidden="true">→</span></a>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($courses as $index => $course)
                    @php
                        $tones = [
                            ['bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'from' => 'from-violet-400', 'to' => 'to-brand-500'],
                            ['bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'from' => 'from-sky-400', 'to' => 'to-cyan-500'],
                            ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'from' => 'from-orange-400', 'to' => 'to-amber-500'],
                        ];
                        $t = $tones[$index % count($tones)];
                    @endphp
                    <x-dashboard.course-row
                        :course="$course"
                        :progress="\App\Support\DashboardMetrics::lecturerCourseProgress($course)"
                        :meta="$course->students_count.' students · '.$course->assignments_count.' assignments'"
                        :bar-from="$t['from']"
                        :bar-to="$t['to']"
                    />
                @empty
                    <x-lms.empty-state title="Create your first course" message="Add a module to start assigning work to students." variant="notebook">
                        <a href="{{ route('courses.create') }}" class="lms-btn-primary">Create course <span aria-hidden="true">→</span></a>
                    </x-lms.empty-state>
                @endforelse
            </div>
        </section>
    </div>

    @include('dashboard.partials.aside', ['upcoming' => $upcoming, 'highlightDates' => $highlightDates])
</div>
@endsection
