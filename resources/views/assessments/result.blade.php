@extends('layouts.lms')

@section('title', 'Result — ' . $assessment->title)
@section('page_title', $assessment->course->title)

@section('content')
@php
    $course = $assessment->course->loadMissing('lecturer')->loadCount(['students', 'assignments', 'assessments']);
    $pct = $attempt->max_score > 0
        ? (int) round(($attempt->score / $attempt->max_score) * 100)
        : 0;
@endphp
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="assessments" />

    <div class="lms-page-toolbar">
        <p class="lms-page-toolbar-desc">Your submitted score for this assessment.</p>
        <div class="lms-page-toolbar-actions">
            <a href="{{ route('assessments.show', $assessment) }}" class="lms-btn-secondary lms-btn-secondary--xs">Back to assessment</a>
        </div>
    </div>

    <section class="lms-form-card">
        <div class="lms-form-header">
            <h2 class="lms-form-title">Your result</h2>
            <p class="lms-form-desc">{{ $assessment->title }}</p>
        </div>

        <div class="space-y-3">
            <p class="lms-result-score">{{ $attempt->score }} / {{ $attempt->max_score }}</p>
            <p class="text-sm text-isarva-muted">{{ $pct }}% · Submitted {{ $attempt->submitted_at->format('M j, Y g:i A') }}</p>
            @if ($assessment->isGoogleForm())
                <p class="text-sm text-isarva-muted">Score recorded by your lecturer from the Google Form responses.</p>
            @else
                <p class="text-sm text-isarva-muted">Individual question answers are not shown after submission.</p>
            @endif
        </div>
    </section>
</div>
@endsection
