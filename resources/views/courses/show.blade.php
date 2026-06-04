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
