@extends('layouts.lms')

@php
    $assignmentCount = $course->assignments->count();
    $assessmentCount = $course->assessments->count();
    $materialCount = $course->materials->count();
    $aboutText = trim((string) $course->description);
    $showAbout = $aboutText !== '' && strcasecmp($aboutText, trim($course->title)) !== 0;
@endphp

@section('title', $course->title)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="show" />

@if ($showAbout)
    <section class="lms-panel mb-6">
        <div class="lms-panel-header">
            <h2 class="lms-panel-title">About this course</h2>
        </div>
        <div class="lms-panel-body">
            <p class="text-sm leading-relaxed text-slate-700">{{ $course->description }}</p>
        </div>
    </section>
@endif

@if ($upcomingSessions->isNotEmpty())
    <section class="lms-panel mb-6">
        <div class="lms-panel-header">
            <div class="flex items-center gap-2">
                <h2 class="lms-panel-title">Upcoming classes</h2>
                <span class="lms-panel-count">{{ $upcomingSessions->count() }}</span>
            </div>
            <a href="{{ route('calendar.index') }}" class="lms-btn-secondary lms-btn-secondary--sm">Open calendar</a>
        </div>
        <div class="lms-panel-body p-0">
            <ul class="corp-cal-cards corp-cal-cards--flush">
                @foreach ($upcomingSessions as $session)
                    <li>
                        <div @class([
                            'corp-cal-card corp-cal-card--flat',
                            'corp-cal-card--online' => $session->mode === \App\Enums\SessionDeliveryMode::Online,
                            'corp-cal-card--offline' => $session->mode === \App\Enums\SessionDeliveryMode::Offline,
                        ])>
                            <div @class([
                                'corp-cal-card-date',
                                'corp-cal-card-date--online' => $session->mode === \App\Enums\SessionDeliveryMode::Online,
                                'corp-cal-card-date--offline' => $session->mode === \App\Enums\SessionDeliveryMode::Offline,
                            ])>
                                <span class="corp-cal-card-date-day">{{ $session->starts_at->format('d') }}</span>
                                <span class="corp-cal-card-date-month">{{ $session->starts_at->format('M') }}</span>
                            </div>
                            <div class="corp-cal-card-body">
                                <div class="corp-cal-card-top">
                                    <p class="corp-cal-card-title">{{ $session->displayTitle() }}</p>
                                    <span @class([
                                        'corp-schedule-badge corp-schedule-badge--sm',
                                        'corp-schedule-badge--online' => $session->mode === \App\Enums\SessionDeliveryMode::Online,
                                        'corp-schedule-badge--offline' => $session->mode === \App\Enums\SessionDeliveryMode::Offline,
                                    ])>{{ $session->mode->label() }}</span>
                                </div>
                                <p class="corp-cal-card-meta">{{ $session->timeRangeLabel() }}@if ($session->location) · {{ $session->location }}@endif</p>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif

@if ($materialCount > 0 || auth()->user()->can('update', $course))
    <section class="lms-panel mb-6">
        <div class="lms-panel-header">
            <h2 class="lms-panel-title">Class materials</h2>
            <span class="lms-panel-count">{{ $materialCount }}</span>
            <a href="{{ route('courses.materials.index', $course) }}" class="lms-btn-secondary lms-btn-secondary--sm">View all</a>
        </div>
        @if ($materialCount > 0)
            <div class="lms-panel-body">
                <ul class="divide-y divide-slate-100">
                    @foreach ($course->materials->take(5) as $material)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
                            <span class="font-medium text-isarva-heading">{{ $material->title }}</span>
                            <span class="text-isarva-muted">{{ $material->category->label() }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="lms-panel-body">
                <p class="text-sm text-isarva-muted">No materials yet.</p>
            </div>
        @endif
    </section>
@endif

<section id="assessments" class="lms-panel mb-6">
    <div class="lms-panel-header">
        <h2 class="lms-panel-title">Assessments</h2>
        <span class="lms-panel-count">{{ $assessmentCount }}</span>
        <a href="{{ route('courses.assessments.index', $course) }}" class="lms-btn-secondary lms-btn-secondary--sm">View all</a>
    </div>
    <div class="lms-panel-body">
        @forelse ($course->assessments as $assessment)
            @php $attempt = $attemptsByAssessment[$assessment->id] ?? null; @endphp
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 px-4 py-3 last:mb-0">
                <div>
                    <a href="{{ route('assessments.show', $assessment) }}" class="text-sm font-semibold text-isarva-heading hover:text-brand-700">{{ $assessment->title }}</a>
                    <p class="mt-1 text-xs text-isarva-muted">
                        {{ $assessment->maxScore() }} marks
                        @if ($assessment->due_at)
                            · Due {{ $assessment->due_at->format('M j') }}
                        @endif
                    </p>
                </div>
                @if (auth()->user()->isStudent() && $assessment->is_published)
                    @if ($attempt && $attempt->isSubmitted())
                        <a href="{{ route('assessments.result', $assessment) }}" class="lms-btn-secondary lms-btn-secondary--xs">Result</a>
                    @else
                        <a href="{{ route('assessments.attempt', $assessment) }}" class="lms-btn-primary lms-btn-primary--xs">Take quiz</a>
                    @endif
                @endif
            </div>
        @empty
            <div class="lms-empty-panel">
                <p class="text-sm font-medium text-isarva-muted">No assessments posted yet.</p>
            </div>
        @endforelse
    </div>
</section>

<section class="lms-panel">
    <div class="lms-panel-header">
        <h2 class="lms-panel-title">Assignments</h2>
        <span class="lms-panel-count">{{ $assignmentCount }}</span>
    </div>
    <div class="lms-panel-body">
        <div @class(['lms-assignment-grid', 'lms-assignment-grid--single' => $assignmentCount === 0])>
        @forelse ($course->assignments as $assignment)
            @php
                $submission = $submissionsByAssignment[$assignment->id] ?? null;
            @endphp
            <x-lms.assignment-list-item :assignment="$assignment" :submission="$submission" />
        @empty
            <div class="lms-empty-panel">
                <p class="text-sm font-medium text-isarva-muted">No assignments posted yet.</p>
                @can('update', $course)
                    <a href="{{ route('courses.assignments.create', $course) }}" class="mt-3 lms-btn-primary">Create first assignment</a>
                @endcan
            </div>
        @endforelse
        </div>
    </div>
</section>
</div>
@endsection
