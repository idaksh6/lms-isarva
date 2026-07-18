@extends('layouts.lms')

@section('title', 'Result — ' . $assessment->title)
@section('page_title', $assessment->title)

@section('content')
<div class="lms-page-stack">
    <div class="lms-page-actions">
        <a href="{{ route('courses.assessments.index', $assessment->course) }}" class="lms-btn-back">← Back to assessments</a>
    </div>

    <section class="lms-panel">
        <div class="lms-panel-header">
            <h2 class="lms-panel-title">Your result</h2>
        </div>
        <div class="lms-panel-body space-y-3">
            <p class="text-sm text-isarva-muted">{{ $assessment->title }}</p>
            <p class="text-3xl font-bold text-isarva-heading">{{ $attempt->score }} / {{ $attempt->max_score }}</p>
            <p class="text-sm text-isarva-muted">Submitted {{ $attempt->submitted_at->format('M j, Y g:i A') }}</p>
            <p class="text-sm text-isarva-muted">Individual question answers are not shown after submission.</p>
        </div>
    </section>
</div>
@endsection
