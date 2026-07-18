@extends('layouts.lms')

@php
    $isStaff = auth()->user()->isLecturer() || auth()->user()->isAdmin();
@endphp

@section('title', 'Assessments — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="assessments" />

    <section class="lms-panel lms-panel--list">
        <div class="lms-panel-header">
            <div class="lms-panel-heading">
                <h2 class="lms-panel-title">Assessments</h2>
                <span class="lms-panel-count">{{ $assessments->count() }}</span>
            </div>
            @can('create', \App\Models\Assessment::class)
                @can('update', $course)
                    <a href="{{ route('courses.assessments.create', $course) }}" class="lms-btn-primary lms-btn-primary--sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        New assessment
                    </a>
                @endcan
            @endcan
        </div>
        <div class="lms-panel-body p-0">
            @forelse ($assessments as $assessment)
                @php
                    $attempt = $attemptsByAssessment[$assessment->id] ?? null;
                    $submitted = $attempt && $attempt->isSubmitted();
                @endphp
                <div class="lms-assessment-index-row">
                    <div class="lms-assessment-index-main min-w-0">
                        <a href="{{ route('assessments.show', $assessment) }}" class="lms-assessment-index-title">{{ $assessment->title }}</a>
                        <p class="lms-assessment-index-meta">
                            {{ $assessment->question_count }} questions · {{ $assessment->maxScore() }} marks
                            @if ($assessment->due_at)
                                · Due {{ $assessment->due_at->format('M j, g:i A') }}
                            @endif
                            @if (! $assessment->is_published)
                                · <span class="font-medium text-amber-700">Draft</span>
                            @endif
                        </p>
                        @if ($isStaff && $assessment->is_published)
                            <p class="lms-assessment-index-track">
                                <span class="lms-assessment-index-track-label">Submissions</span>
                                <span class="lms-assessment-index-track-value">{{ $assessment->submitted_count ?? 0 }} / {{ $enrolledCount }} students</span>
                            </p>
                        @endif
                    </div>
                    <div class="lms-assessment-index-actions">
                        @if (auth()->user()->isStudent())
                            @if ($submitted)
                                <a href="{{ route('assessments.result', $assessment) }}" class="lms-btn-secondary lms-btn-secondary--xs">View result</a>
                            @elseif ($assessment->is_published)
                                <a href="{{ route('assessments.attempt', $assessment) }}" class="lms-btn-primary lms-btn-primary--xs">Take assessment</a>
                            @endif
                        @else
                            @if ($assessment->is_published)
                                <a href="{{ route('assessments.show', $assessment) }}" class="lms-btn-primary lms-btn-primary--xs">View results</a>
                            @endif
                            @can('update', $assessment)
                                <a href="{{ route('assessments.edit', $assessment) }}" class="lms-btn-secondary lms-btn-secondary--xs">Edit questions</a>
                            @endcan
                        @endif
                    </div>
                </div>
            @empty
                <div class="lms-empty-panel py-12">
                    <p class="text-sm font-medium text-isarva-muted">No assessments yet.</p>
                    @can('update', $course)
                        <a href="{{ route('courses.assessments.create', $course) }}" class="mt-3 lms-btn-primary">Create first assessment</a>
                    @endcan
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
