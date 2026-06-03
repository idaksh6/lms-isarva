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

<section class="lms-panel">
    <div class="lms-panel-header">
        <h2 class="lms-panel-title">Assignments</h2>
        <span class="lms-panel-count">{{ $assignmentCount }}</span>
    </div>
    <div class="lms-panel-body space-y-3">
        @forelse ($course->assignments as $assignment)
            @php
                $submission = $submissionsByAssignment[$assignment->id] ?? null;
            @endphp
            <div class="lms-assignment-row">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="lms-assignment-row-icon" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <a href="{{ route('assignments.show', $assignment) }}" class="font-semibold text-isarva-heading hover:text-brand-600">
                            {{ $assignment->title }}
                        </a>
                        @if ($assignment->due_at)
                            <p class="mt-0.5 text-sm text-isarva-muted">Due {{ $assignment->due_at->format('M j, Y g:i A') }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2 sm:justify-end">
                    @if ($submission)
                        <x-status-badge :status="$submission->status" />
                        <a href="{{ route('submissions.show', $submission) }}" class="text-sm font-semibold text-brand-600">View submission</a>
                    @elseif (auth()->user()->isStudent() && $assignment->is_published)
                        <a href="{{ route('assignments.submit', $assignment) }}" class="lms-btn-primary">Submit work</a>
                    @elseif (! $assignment->is_published)
                        <span class="lms-badge bg-slate-100 text-slate-600">Draft</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="lms-empty-panel">
                <p class="text-sm font-medium text-isarva-muted">No assignments posted yet.</p>
                @can('update', $course)
                    <a href="{{ route('courses.assignments.create', $course) }}" class="mt-3 lms-btn-primary">Create first assignment</a>
                @endcan
            </div>
        @endforelse
    </div>
</section>
</div>
@endsection
