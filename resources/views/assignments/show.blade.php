@extends('layouts.lms')

@php
    $isStaff = auth()->user()->isLecturer() || auth()->user()->isAdmin();
@endphp

@section('title', $assignment->title)
@section('page_title', $assignment->course->title)

@section('content')
@php
    $course = $assignment->course->loadMissing('lecturer')->loadCount(['students', 'assignments', 'assessments']);
@endphp
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="assignment" />

    <div class="lms-page-toolbar">
        <p class="lms-page-toolbar-desc">{{ $assignment->title }}</p>
        <div class="lms-page-toolbar-actions">
            @can('update', $assignment)
                <a href="{{ route('assignments.edit', $assignment) }}" class="lms-btn-secondary lms-btn-secondary--xs">Edit assignment</a>
            @endcan
            @if (auth()->user()->isStudent() && $assignment->is_published && ! $userSubmission)
                <a href="{{ route('assignments.submit', $assignment) }}" class="lms-btn-primary lms-btn-primary--xs">Submit your work</a>
            @endif
        </div>
    </div>

    <x-lms.assignment-hero :assignment="$assignment" :course="$course" />

    @if ($isStaff)
        <div class="lms-assignment-staff-layout">
            <x-lms.assignment-meta-strip :assignment="$assignment" />

            <section class="lms-panel">
                <div class="lms-panel-header">
                    <h2 class="lms-panel-title">Instructions</h2>
                </div>
                <div class="lms-panel-body">
                    @if ($assignment->instructions)
                        <div class="lms-prose whitespace-pre-wrap">{{ $assignment->instructions }}</div>
                    @else
                        <p class="text-sm text-isarva-muted">No additional instructions for this assignment.</p>
                    @endif
                </div>
            </section>

            @if ($assignment->attachments->isNotEmpty() || $assignment->attachment_path)
                <section class="lms-panel">
                    <div class="lms-panel-header">
                        <h2 class="lms-panel-title">Resources</h2>
                        <span class="lms-panel-count">{{ $assignment->attachments->count() ?: 1 }}</span>
                    </div>
                    <div class="lms-panel-body space-y-3">
                        @foreach ($assignment->attachments as $attachment)
                            <x-lms.document-viewer
                                :name="$attachment->name"
                                :stream-url="route('media.assignment-attachment', $attachment)"
                                :download-url="route('media.assignment-attachment.download', $attachment)"
                                :mime="$attachment->mime"
                            />
                        @endforeach
                        @if ($assignment->attachment_path && $assignment->attachments->isEmpty())
                            @php $legacyPath = asset('storage/'.$assignment->attachment_path); @endphp
                            <x-lms.document-viewer
                                :name="$assignment->attachment_name ?? 'Course resource'"
                                :stream-url="$legacyPath"
                                :download-url="$legacyPath"
                            />
                        @endif
                    </div>
                </section>
            @endif

            @if ($assignment->acceptsExternalLink() && $assignment->drop_folder_url)
                <section class="lms-panel lms-panel--highlight">
                    <div class="lms-panel-header">
                        <h2 class="lms-panel-title">Shared upload folder</h2>
                    </div>
                    <div class="lms-panel-body space-y-3">
                        <p class="text-sm text-isarva-muted">
                            Students upload their bundle here, then paste the <strong class="font-semibold text-isarva-heading">share link to their file</strong> when submitting.
                        </p>
                        <a href="{{ $assignment->drop_folder_url }}" target="_blank" rel="noopener noreferrer" class="lms-btn-secondary inline-flex">
                            Open shared folder
                        </a>
                    </div>
                </section>
            @endif

            <section class="lms-assignment-submissions-hub">
                <div class="lms-assignment-submissions-hub-header">
                    <div>
                        <h2 class="lms-assignment-submissions-hub-title">Student submissions</h2>
                        <p class="lms-assignment-submissions-hub-desc">Review work, open files, and mark submissions as reviewed.</p>
                    </div>
                    <span class="lms-assignment-submissions-hub-count">{{ $assignment->submissions_count }}</span>
                </div>
                <div class="lms-assignment-submissions-hub-body">
                    @forelse ($assignment->submissions as $submission)
                        <x-lms.submission-list-item :submission="$submission" prominent />
                    @empty
                        <div class="lms-assignment-submissions-empty">
                            <p class="text-sm font-semibold text-slate-700">No submissions yet</p>
                            <p class="mt-1 text-sm text-slate-500">When students submit work, it will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    @else
        <div class="lms-page-grid-3">
            <div class="lms-page-grid-3-main">
                <section class="lms-panel">
                    <div class="lms-panel-header">
                        <h2 class="lms-panel-title">Instructions</h2>
                    </div>
                    <div class="lms-panel-body">
                        @if ($assignment->instructions)
                            <div class="lms-prose whitespace-pre-wrap">{{ $assignment->instructions }}</div>
                        @else
                            <p class="text-sm text-isarva-muted">No additional instructions for this assignment.</p>
                        @endif
                    </div>
                </section>

                @if ($assignment->attachments->isNotEmpty() || $assignment->attachment_path)
                    <section class="lms-panel">
                        <div class="lms-panel-header">
                            <h2 class="lms-panel-title">Resources</h2>
                            <span class="lms-panel-count">{{ $assignment->attachments->count() ?: 1 }}</span>
                        </div>
                        <div class="lms-panel-body space-y-3">
                            @foreach ($assignment->attachments as $attachment)
                                <x-lms.document-viewer
                                    :name="$attachment->name"
                                    :stream-url="route('media.assignment-attachment', $attachment)"
                                    :download-url="route('media.assignment-attachment.download', $attachment)"
                                    :mime="$attachment->mime"
                                />
                            @endforeach
                            @if ($assignment->attachment_path && $assignment->attachments->isEmpty())
                                @php $legacyPath = asset('storage/'.$assignment->attachment_path); @endphp
                                <x-lms.document-viewer
                                    :name="$assignment->attachment_name ?? 'Course resource'"
                                    :stream-url="$legacyPath"
                                    :download-url="$legacyPath"
                                />
                            @endif
                        </div>
                    </section>
                @endif

                @if ($assignment->acceptsExternalLink() && $assignment->drop_folder_url && ! $userSubmission)
                    <section class="lms-panel lms-panel--highlight">
                        <div class="lms-panel-header">
                            <h2 class="lms-panel-title">Shared upload folder</h2>
                        </div>
                        <div class="lms-panel-body space-y-3">
                            <p class="text-sm text-isarva-muted">
                                Step 1: upload your zip to the shared folder. Step 2: submit the file share link on the submit page.
                            </p>
                            <a href="{{ $assignment->drop_folder_url }}" target="_blank" rel="noopener noreferrer" class="lms-btn-secondary inline-flex">
                                Open shared folder
                            </a>
                        </div>
                    </section>
                @endif

                @if ($userSubmission)
                    <section class="lms-panel lms-panel--highlight">
                        <div class="lms-panel-header">
                            <h2 class="lms-panel-title">Your submission</h2>
                            <x-status-badge :status="$userSubmission->status" />
                        </div>
                        <div class="lms-panel-body space-y-4">
                            <p class="text-sm text-isarva-muted">
                                Submitted {{ $userSubmission->submitted_at->format('l, F j, Y · g:i A') }}
                            </p>
                            @if ($userSubmission->isExternalLink())
                                <x-lms.external-link-card
                                    :url="$userSubmission->external_url"
                                    :label="$userSubmission->external_label"
                                />
                            @else
                                <x-lms.document-viewer
                                    :name="$userSubmission->file_name"
                                    :stream-url="route('media.submission', $userSubmission)"
                                    :download-url="route('media.submission.download', $userSubmission)"
                                />
                            @endif
                            <a href="{{ route('submissions.show', $userSubmission) }}" class="lms-btn-primary text-sm">View full submission</a>
                        </div>
                    </section>
                @endif
            </div>

            <aside class="lms-page-grid-3-side">
                <section class="lms-panel">
                    <div class="lms-panel-header">
                        <h2 class="lms-panel-title">At a glance</h2>
                    </div>
                    <div class="lms-panel-body">
                        <x-lms.assignment-glance :assignment="$assignment" :user-submission="$userSubmission" />
                    </div>
                </section>
            </aside>
        </div>
    @endif
</div>
@endsection
