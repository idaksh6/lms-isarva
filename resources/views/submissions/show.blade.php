@extends('layouts.lms')

@php
    use App\Support\FilePreview;
    use App\Support\GradeHelper;

    $previewType = FilePreview::type(null, $submission->file_name);
    $canReview = auth()->user()->can('review', $submission);
    $isStudent = auth()->user()->isStudent();
@endphp

@section('title', 'Submission')
@section('page_title', 'Submission')

@section('content')
<div class="lms-page-stack">
    <div class="lms-page-actions">
        <a href="{{ route('assignments.show', $submission->assignment) }}" class="lms-btn-back">← Back to assignment</a>
        @if ($submission->canResubmit() && $isStudent)
            <a href="{{ route('assignments.submit', $submission->assignment) }}" class="lms-btn-primary">Resubmit work</a>
        @endif
    </div>

    <div class="lms-page-grid-submission">
        <section class="lms-panel min-w-0">
            <div class="lms-panel-header">
                <h2 class="lms-panel-title">Details</h2>
                <x-status-badge :status="$submission->status" />
            </div>
            <div class="lms-panel-body space-y-4">
                @if ($submission->isGraded())
                    <div class="lms-grade-summary">
                        <x-lms.grade-badge :score="$submission->score" :letter="$submission->letter_grade" />
                        <p class="text-sm text-slate-600">{{ GradeHelper::labelForLetter($submission->letter_grade) }}</p>
                    </div>
                @endif

                <dl class="lms-meta-list">
                    <div class="lms-meta-list-row">
                        <dt>Student</dt>
                        <dd>
                            <span class="lms-meta-list-value">{{ $submission->student->name }}</span>
                            @if ($submission->student->student_id)
                                <span class="lms-meta-list-sub">ID {{ $submission->student->student_id }}</span>
                            @endif
                        </dd>
                    </div>

                    <div class="lms-meta-list-row">
                        <dt>Assignment</dt>
                        <dd>
                            <a href="{{ route('assignments.show', $submission->assignment) }}" class="lms-meta-list-link">
                                {{ $submission->assignment->title }}
                            </a>
                            <span class="lms-meta-list-sub">{{ $submission->assignment->course->code }}</span>
                        </dd>
                    </div>

                    <div class="lms-meta-list-row">
                        <dt>Submitted</dt>
                        <dd>
                            <time datetime="{{ $submission->submitted_at->toIso8601String() }}" class="lms-meta-list-date">
                                {{ $submission->submitted_at->format('M j, Y g:i A') }}
                            </time>
                        </dd>
                    </div>

                    @if ($submission->reviewed_at)
                        <div class="lms-meta-list-row">
                            <dt>Reviewed</dt>
                            <dd>
                                {{ $submission->reviewed_at->format('M j, Y') }}
                                @if ($submission->reviewer)
                                    <span class="lms-meta-list-sub">by {{ $submission->reviewer->name }}</span>
                                @endif
                            </dd>
                        </div>
                    @endif

                    @if ($submission->notes)
                        <div class="lms-meta-list-row">
                            <dt>Student notes</dt>
                            <dd><div class="lms-meta-list-note whitespace-pre-wrap">{{ $submission->notes }}</div></dd>
                        </div>
                    @endif

                    @if ($submission->feedback && ($isStudent || $canReview))
                        <div class="lms-meta-list-row">
                            <dt>Lecturer feedback</dt>
                            <dd><div class="lms-feedback-box whitespace-pre-wrap">{{ $submission->feedback }}</div></dd>
                        </div>
                    @endif
                </dl>

                @if ($canReview)
                    <form method="POST" action="{{ route('submissions.review', $submission) }}" class="lms-review-form space-y-4 border-t border-slate-100 pt-4">
                        @csrf
                        @method('PATCH')
                        <h3 class="text-sm font-bold text-isarva-heading">Review & grade</h3>
                        <div class="lms-form-field">
                            <label for="score" class="lms-field-label">Score (0–100)</label>
                            <input id="score" type="number" name="score" min="0" max="100" step="0.5" value="{{ old('score', $submission->score) }}" class="lms-field-input mt-1.5">
                        </div>
                        <div class="lms-form-field">
                            <label for="feedback" class="lms-field-label">Feedback for student</label>
                            <textarea id="feedback" name="feedback" rows="4" class="lms-field-input mt-1.5" placeholder="What went well? What to improve?">{{ old('feedback', $submission->feedback) }}</textarea>
                        </div>
                        <div class="lms-review-actions">
                            <button type="submit" name="action" value="grade" class="lms-btn-primary">Post grade</button>
                            <button type="submit" name="action" value="revision" class="lms-btn-secondary">Request revision</button>
                            <button type="submit" name="action" value="reviewed" class="lms-btn-secondary">Mark reviewed</button>
                        </div>
                    </form>
                @endif
            </div>
        </section>

        <section class="lms-panel min-w-0">
            <div class="lms-panel-header">
                <h2 class="lms-panel-title">Submitted file</h2>
            </div>
            <div class="lms-panel-body space-y-4">
                <x-lms.document-viewer
                    :name="$submission->file_name"
                    :stream-url="route('media.submission', $submission)"
                    :download-url="route('media.submission.download', $submission)"
                />

                <div class="lms-doc-inline-preview">
                    <p class="mb-2 text-[0.6875rem] font-semibold uppercase tracking-wider text-slate-500">Quick preview</p>
                    @if ($previewType === 'pdf')
                        <iframe src="{{ route('media.submission', $submission) }}#toolbar=1" title="{{ $submission->file_name }}" class="lms-doc-iframe lms-doc-iframe--inline"></iframe>
                    @elseif ($previewType === 'image')
                        <img src="{{ route('media.submission', $submission) }}" alt="{{ $submission->file_name }}" class="lms-doc-image lms-doc-image--inline">
                    @else
                        <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                            Use <strong class="font-semibold text-slate-700">View in app</strong> above for Word documents, or download the file.
                        </p>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
