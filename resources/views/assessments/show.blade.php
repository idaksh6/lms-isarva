@extends('layouts.lms')

@php
    $isStaff = auth()->user()->isLecturer() || auth()->user()->isAdmin();
    $submitted = $attempt?->isSubmitted() ?? false;
    $isGoogleForm = $assessment->isGoogleForm();
@endphp

@section('title', $assessment->title)
@section('page_title', $assessment->course->title)

@section('content')
@php
    $course = $assessment->course->loadCount(['students', 'assignments', 'assessments']);
@endphp
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="assessments" />

    <div class="lms-page-toolbar lms-page-toolbar--actions-only">
        <div class="lms-page-toolbar-actions">
            @can('update', $assessment)
                <a href="{{ route('assessments.edit', $assessment) }}" class="lms-btn-secondary lms-btn-secondary--xs">
                    {{ $isGoogleForm ? 'Edit' : 'Edit questions' }}
                </a>
            @endcan
            @if (auth()->user()->isStudent() && $assessment->is_published)
                @if ($isGoogleForm && $assessment->external_url)
                    <a
                        href="{{ $assessment->external_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="lms-btn-primary lms-btn-primary--xs"
                    >Open Google Form</a>
                    @if ($submitted)
                        <a href="{{ route('assessments.result', $assessment) }}" class="lms-btn-secondary lms-btn-secondary--xs">View your score</a>
                    @endif
                @elseif (! $isGoogleForm)
                    @if ($submitted)
                        <a href="{{ route('assessments.result', $assessment) }}" class="lms-btn-primary lms-btn-primary--xs">View your result</a>
                    @else
                        <a href="{{ route('assessments.attempt', $assessment) }}" class="lms-btn-primary lms-btn-primary--xs">Start assessment</a>
                    @endif
                @endif
            @endif
        </div>
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
                    · Google Form · {{ $assessment->maxScore() }} marks
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
                            Ready to publish. Students will open the Google Form in a new tab. After responses come in, record scores below.
                        @elseif (! $assessment->external_url)
                            Add a Google Form URL before publishing.
                        @elseif ($assessment->maxScore() < 1)
                            Set total marks before publishing and recording scores.
                        @else
                            Open the Google Form responses, then enter each student’s score in the roster below.
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
                <x-input-error :messages="$errors->get('score')" class="mt-1.5" />
            @elseif ($assessment->is_published && ! $isGoogleForm)
                <p class="text-sm text-isarva-muted">One attempt allowed. Correct answers are not shown after submission.</p>
            @elseif ($assessment->is_published && $isGoogleForm)
                <p class="text-sm text-isarva-muted">
                    Complete the Google Form using the button above. It opens in a new tab.
                    @if ($submitted)
                        Your lecturer has recorded a score for you.
                    @endif
                </p>
            @endif
        </div>
    </section>

    @if ($isStaff && $assessment->is_published && $resultsSummary)
        <section class="lms-assignment-submissions-hub">
            <div class="lms-assignment-submissions-hub-header">
                <div>
                    <h2 class="lms-assignment-submissions-hub-title">
                        {{ $isGoogleForm ? 'Student scores' : 'Student results' }}
                    </h2>
                    <p class="lms-assignment-submissions-hub-desc">
                        @if ($isGoogleForm)
                            Check Google Form responses, enter scores below (out of {{ $assessment->maxScore() }}), then click <strong>Save all scores</strong>. Leave a field blank to skip that student.
                        @else
                            Track who submitted and their score. Individual answers stay hidden from students after submit.
                        @endif
                        @if ($resultsSummary['average'] !== null)
                            Class average: <strong class="font-semibold text-isarva-heading">{{ $resultsSummary['average'] }} / {{ $assessment->maxScore() }}</strong>
                        @endif
                    </p>
                </div>
                <span class="lms-assignment-submissions-hub-count">{{ $resultsSummary['submitted'] }}/{{ $resultsSummary['enrolled'] }}</span>
            </div>
            <div class="lms-assignment-submissions-hub-body">
                @if ($isGoogleForm)
                    <x-input-error :messages="$errors->get('scores')" class="mb-3" />
                    <form method="POST" action="{{ route('assessments.scores.bulk', $assessment) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="lms-assessment-bulk-bar">
                            <p class="text-sm text-isarva-muted">Enter scores for multiple students, then save once.</p>
                            <button type="submit" class="lms-btn-primary">Save all scores</button>
                        </div>
                        @forelse ($studentResults as $row)
                            <x-lms.assessment-result-row
                                :student="$row['student']"
                                :attempt="$row['attempt']"
                                :max-score="$assessment->maxScore()"
                                :assessment="$assessment"
                                :editable="true"
                                :bulk="true"
                            />
                        @empty
                            <div class="lms-assignment-submissions-empty">
                                <p class="text-sm font-semibold text-slate-700">No enrolled students</p>
                                <p class="mt-1 text-sm text-slate-500">Enroll students on this course to track assessment results.</p>
                            </div>
                        @endforelse
                        @if ($studentResults->isNotEmpty())
                            <div class="lms-assessment-bulk-bar lms-assessment-bulk-bar--footer">
                                <p class="text-sm text-isarva-muted">{{ $studentResults->count() }} enrolled students</p>
                                <button type="submit" class="lms-btn-primary">Save all scores</button>
                            </div>
                        @endif
                    </form>
                    @foreach ($studentResults as $row)
                        @if ($row['attempt']?->isSubmitted())
                            <form
                                id="clear-score-{{ $row['student']->id }}"
                                method="POST"
                                action="{{ route('assessments.scores.destroy', [$assessment, $row['student']]) }}"
                                class="hidden"
                            >
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    @endforeach
                @else
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
                @endif
            </div>
        </section>
    @elseif ($isStaff && ! $assessment->is_published)
        <section class="lms-panel">
            <div class="lms-panel-body">
                <p class="text-sm text-isarva-muted">
                    @if ($isGoogleForm)
                        Publish this assessment so enrolled students can open the Google Form, then record scores here.
                    @else
                        Publish this assessment to start tracking student submissions and scores.
                    @endif
                </p>
            </div>
        </section>
    @endif
</div>
@endsection
