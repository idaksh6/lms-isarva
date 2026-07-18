@extends('layouts.lms')

@php
    $isStaff = auth()->user()->isLecturer() || auth()->user()->isAdmin();
    $submitted = $attempt?->isSubmitted() ?? false;
@endphp

@section('title', $assessment->title)
@section('page_title', $assessment->title)

@section('content')
<div class="lms-page-stack">
    <div class="lms-page-actions">
        <a href="{{ route('courses.assessments.index', $assessment->course) }}" class="lms-btn-back">← Back to assessments</a>
        @can('update', $assessment)
            <a href="{{ route('assessments.edit', $assessment) }}" class="lms-btn-secondary">Edit questions</a>
        @endcan
        @if (auth()->user()->isStudent() && $assessment->is_published)
            @if ($submitted)
                <a href="{{ route('assessments.result', $assessment) }}" class="lms-btn-primary">View your result</a>
            @else
                <a href="{{ route('assessments.attempt', $assessment) }}" class="lms-btn-primary">Start assessment</a>
            @endif
        @endif
    </div>

    <section class="lms-panel">
        <div class="lms-panel-header">
            <h2 class="lms-panel-title">{{ $assessment->title }}</h2>
            @if (! $assessment->is_published)
                <span class="lms-panel-count">Draft</span>
            @endif
        </div>
        <div class="lms-panel-body space-y-4">
            <p class="text-sm text-isarva-muted">
                {{ $assessment->course->code }} · {{ $assessment->question_count }} questions · {{ $assessment->maxScore() }} marks
                @if ($assessment->due_at)
                    · Due {{ $assessment->due_at->format('l, F j \a\t g:i A') }}
                @endif
            </p>
            @if ($assessment->instructions)
                <div class="lms-prose whitespace-pre-wrap text-sm text-slate-700">{{ $assessment->instructions }}</div>
            @endif
            @if ($isStaff)
                <p class="text-sm text-isarva-muted">
                    {{ $assessment->questions->count() }} / {{ $assessment->question_count }} questions configured.
                    @if ($assessment->isReadyToPublish() && ! $assessment->is_published)
                        Ready to publish.
                    @endif
                </p>
                @if ($assessment->isReadyToPublish() && ! $assessment->is_published)
                    <form method="POST" action="{{ route('assessments.publish', $assessment) }}">
                        @csrf
                        <button type="submit" class="lms-btn-primary">Publish to students</button>
                    </form>
                @endif
            @elseif ($assessment->is_published)
                <p class="text-sm text-isarva-muted">One attempt allowed. Correct answers are not shown after submission.</p>
            @endif
        </div>
    </section>

    @if ($isStaff && $assessment->is_published && $resultsSummary)
        <section class="lms-assignment-submissions-hub">
            <div class="lms-assignment-submissions-hub-header">
                <div>
                    <h2 class="lms-assignment-submissions-hub-title">Student results</h2>
                    <p class="lms-assignment-submissions-hub-desc">
                        Track who submitted and their score. Individual answers stay hidden from students after submit.
                        @if ($resultsSummary['average'] !== null)
                            Class average: <strong class="font-semibold text-isarva-heading">{{ $resultsSummary['average'] }} / {{ $assessment->maxScore() }}</strong>
                        @endif
                    </p>
                </div>
                <span class="lms-assignment-submissions-hub-count">{{ $resultsSummary['submitted'] }}/{{ $resultsSummary['enrolled'] }}</span>
            </div>
            <div class="lms-assignment-submissions-hub-body">
                @forelse ($studentResults as $row)
                    <x-lms.assessment-result-row
                        :student="$row['student']"
                        :attempt="$row['attempt']"
                        :max-score="$assessment->maxScore()"
                    />
                @empty
                    <div class="lms-assignment-submissions-empty">
                        <p class="text-sm font-semibold text-slate-700">No enrolled students</p>
                        <p class="mt-1 text-sm text-slate-500">Enroll students on this course to track assessment results.</p>
                    </div>
                @endforelse
            </div>
        </section>
    @elseif ($isStaff && ! $assessment->is_published)
        <section class="lms-panel">
            <div class="lms-panel-body">
                <p class="text-sm text-isarva-muted">Publish this assessment to start tracking student submissions and scores.</p>
            </div>
        </section>
    @endif
</div>
@endsection
