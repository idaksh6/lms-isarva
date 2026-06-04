@extends('layouts.lms')

@php
    use App\Support\FilePreview;

    $previewType = FilePreview::type(null, $submission->file_name);
@endphp

@section('title', 'Submission')
@section('page_title', 'Submission')

@section('content')
<div class="lms-page-stack">
    <div class="lms-page-actions">
        <a href="{{ route('assignments.show', $submission->assignment) }}" class="lms-btn-back">← Back to assignment</a>
        @if ((auth()->user()->isLecturer() || auth()->user()->isAdmin()) && $submission->status !== \App\Enums\SubmissionStatus::Reviewed)
            <form method="POST" action="{{ route('submissions.reviewed', $submission) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="lms-btn-primary">Mark as reviewed</button>
            </form>
        @endif
    </div>

    <div class="lms-page-grid-3">
        <section class="lms-panel min-w-0 xl:col-span-1">
            <div class="lms-panel-header">
                <h2 class="lms-panel-title">Details</h2>
                <x-status-badge :status="$submission->status" />
            </div>
            <div class="lms-panel-body">
                <dl class="lms-meta-list">
                    <div class="lms-meta-list-row">
                        <dt>Student</dt>
                        <dd>
                            <span class="lms-meta-list-value">{{ $submission->student->name }}</span>
                            @if ($submission->student->student_id)
                                <span class="lms-meta-list-sub">ID {{ $submission->student->student_id }}</span>
                            @endif
                            @if ($submission->student->email)
                                <span class="lms-meta-list-sub">{{ $submission->student->email }}</span>
                            @endif
                        </dd>
                    </div>

                    <div class="lms-meta-list-row">
                        <dt>Assignment</dt>
                        <dd>
                            <a href="{{ route('assignments.show', $submission->assignment) }}" class="lms-meta-list-link">
                                {{ $submission->assignment->title }}
                            </a>
                            <span class="lms-meta-list-sub">
                                <a href="{{ route('courses.show', $submission->assignment->course) }}" class="text-brand-600 hover:text-brand-700">
                                    {{ $submission->assignment->course->code }}
                                </a>
                                · {{ $submission->assignment->course->title }}
                            </span>
                        </dd>
                    </div>

                    <div class="lms-meta-list-row">
                        <dt>Submitted</dt>
                        <dd>
                            <time datetime="{{ $submission->submitted_at->toIso8601String() }}" class="lms-meta-list-date">
                                {{ $submission->submitted_at->format('l, F j, Y') }}
                            </time>
                            <span class="lms-meta-list-sub">{{ $submission->submitted_at->format('g:i A') }}</span>
                        </dd>
                    </div>

                    @if ($submission->notes)
                        <div class="lms-meta-list-row">
                            <dt>Student notes</dt>
                            <dd>
                                <div class="lms-meta-list-note whitespace-pre-wrap">{{ $submission->notes }}</div>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </section>

        <section class="lms-panel min-w-0 xl:col-span-2">
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
