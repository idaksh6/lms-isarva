@extends('layouts.lms')

@php
    $assignmentCount = $course->assignments->count();
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
