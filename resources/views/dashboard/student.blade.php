@extends('layouts.lms')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-layout">
    <div class="dashboard-main">
        <x-dashboard.resume-card
            :course="$featuredCourse"
            :progress="$featuredProgress"
            subtitle="Pick up where you left off"
        />

        <section>
            <h2 class="quyl-section-title">Status</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-3">
                <x-dashboard.stat-ring label="Enrolled" :value="$stats['courses']" :sub="$stats['courses'].' active courses'" :percent="$stats['courses'] > 0 ? 100 : 0" tone="brand" />
                <x-dashboard.stat-ring label="Pending" :value="$stats['pending']" :sub="$stats['pending'].' assignments left'" :percent="$stats['pending_pct']" tone="orange" />
                <x-dashboard.stat-ring label="Completed" :value="$stats['completion_pct'].'%'" :sub="$stats['submitted'].' of '.$stats['total_assignments'].' submitted'" :percent="$stats['completion_pct']" tone="sky" />
            </div>
        </section>

        <section class="quyl-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="quyl-card-title">My courses</h2>
                <a href="{{ route('courses.index') }}" class="lms-text-link">View all →</a>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($courses as $index => $course)
                    @php
                        $user = auth()->user();
                        $tones = [
                            ['bg' => 'bg-brand-100', 'text' => 'text-brand-700', 'from' => 'from-brand-400', 'to' => 'to-brand-600'],
                            ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'from' => 'from-rose-400', 'to' => 'to-pink-500'],
                            ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'from' => 'from-orange-400', 'to' => 'to-amber-500'],
                        ];
                        $t = $tones[$index % count($tones)];
                        $count = $course->assignments_count ?? $course->assignments->count();
                    @endphp
                    <x-dashboard.course-row
                        :course="$course"
                        :progress="\App\Support\DashboardMetrics::studentCourseProgress($user, $course)"
                        :meta="$course->code.' · '.$count.' assignments'"
                        :bar-from="$t['from']"
                        :bar-to="$t['to']"
                    />
                @empty
                    <x-lms.empty-state title="No courses assigned yet" message="Your lecturer will enroll you in modules soon." variant="books" />
                @endforelse
            </div>
        </section>

        @if ($openAssignments->isNotEmpty())
            <section class="quyl-card">
                <h2 class="quyl-card-title">Open assignments</h2>
                <div class="mt-3 space-y-2">
                    @foreach ($openAssignments->take(5) as $assignment)
                        <a href="{{ route('assignments.show', $assignment) }}" class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2.5 hover:border-brand-200 hover:bg-brand-50/40">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-isarva-heading">{{ $assignment->title }}</p>
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
@endsection
