@extends('layouts.lms')

@php
    $isStaff = auth()->user()->isLecturer() || auth()->user()->isAdmin();
    $submitted = $attempt?->isSubmitted() ?? false;
    $isGoogleForm = $assessment->isGoogleForm();
@endphp

@section('title', $assessment->title)
@section('page_title', $assessment->title)

@section('content')
<div class="lms-page-stack">
    <div class="lms-page-actions">
        <a href="{{ route('courses.assessments.index', $assessment->course) }}" class="lms-btn-back">← Back to assessments</a>
        @can('update', $assessment)
            <a href="{{ route('assessments.edit', $assessment) }}" class="lms-btn-secondary">
                {{ $isGoogleForm ? 'Edit' : 'Edit questions' }}
            </a>
        @endcan
        @if (auth()->user()->isStudent() && $assessment->is_published)
            @if ($isGoogleForm && $assessment->external_url)
                <a
                    href="{{ $assessment->external_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="lms-btn-primary"
                >Open Google Form</a>
            @elseif (! $isGoogleForm)
                @if ($submitted)
                    <a href="{{ route('assessments.result', $assessment) }}" class="lms-btn-primary">View your result</a>
                @else
                    <a href="{{ route('assessments.attempt', $assessment) }}" class="lms-btn-primary">Start assessment</a>
                @endif
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
                {{ $assessment->course->code }}
                @if ($isGoogleForm)
                    · Google Form
                @else
                    · {{ $assessment->question_count }} questions · {{ $assessment->maxScore() }} marks
                @endif
                @if ($assessment->due_at)
                    · Due {{ $assessment->due_at->format('l, F j \a\t g:i A') }}
                @endif
            </p>
            @if ($assessment->instructions)
                <div class="lms-prose whitespace-pre-wrap text-sm text-slate-700">{{ $assessment->instructions }}</div>
            @endif
            @if ($isGoogleForm && $assessment->external_url && $isStaff)
                <p class="text-sm">
                    <a
                        href="{{ $assessment->external_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-medium text-isarva-accent underline"
                    >Preview Google Form</a>
                </p>
            @endif
            @if ($isStaff)
                @if ($isGoogleForm)
                    <p class="text-sm text-isarva-muted">
                        @if ($assessment->isReadyToPublish() && ! $assessment->is_published)
                            Ready to publish. Students will open the Google Form in a new tab.
                        @elseif (! $assessment->external_url)
                            Add a Google Form URL before publishing.
                        @else
                            Scores are collected in Google Forms, not in the LMS.
                        @endif
                    </p>
                @else
                    <p class="text-sm text-isarva-muted">
                        {{ $assessment->questions->count() }} / {{ $assessment->question_count }} questions configured.
                        @if ($assessment->isReadyToPublish() && ! $assessment->is_published)
                            Ready to publish.
                        @endif
                    </p>
                @endif
                @if ($assessment->isReadyToPublish() && ! $assessment->is_published)
                    <form method="POST" action="{{ route('assessments.publish', $assessment) }}">
                        @csrf
                        <button type="submit" class="lms-btn-primary">Publish to students</button>
                    </form>
                @endif
                <x-input-error :messages="$errors->get('publish')" class="mt-1.5" />
            @elseif ($assessment->is_published && ! $isGoogleForm)
                <p class="text-sm text-isarva-muted">One attempt allowed. Correct answers are not shown after submission.</p>
            @elseif ($assessment->is_published && $isGoogleForm)
                <p class="text-sm text-isarva-muted">Complete the Google Form using the button above. It opens in a new tab.</p>
            @endif
        </div>
    </section>

    @if ($isStaff && ! $isGoogleForm && $assessment->is_published && $resultsSummary)
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
                <p class="text-sm text-isarva-muted">
                    @if ($isGoogleForm)
                        Publish this assessment so enrolled students can open the Google Form.
                    @else
                        Publish this assessment to start tracking student submissions and scores.
                    @endif
                </p>
            </div>
        </section>
    @endif
</div>
@endsection
