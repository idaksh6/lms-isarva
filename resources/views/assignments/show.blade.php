@extends('layouts.lms')

@section('title', $assignment->title)
@section('heading', $assignment->title)
@section('subheading', $assignment->course->code . ' · ' . $assignment->course->title)

@section('content')
<div class="lms-page-actions">
    <a href="{{ route('courses.show', $assignment->course) }}" class="lms-btn-back">← Course</a>
    @can('update', $assignment)
        <a href="{{ route('assignments.edit', $assignment) }}" class="lms-btn-secondary">Edit</a>
    @endcan
    @if (auth()->user()->isStudent() && $assignment->is_published && ! $userSubmission)
        <a href="{{ route('assignments.submit', $assignment) }}" class="lms-btn-primary">Submit your work</a>
    @endif
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
        <section class="lms-card">
            @if ($assignment->due_at)
                <p class="mb-4 text-sm font-medium {{ $assignment->isOverdue() ? 'text-rose-600' : 'text-slate-500' }}">
                    Due: {{ $assignment->due_at->format('l, F j, Y \a\t g:i A') }}
                </p>
            @endif
            @if ($assignment->instructions)
                <div class="prose prose-slate max-w-none text-slate-700 whitespace-pre-wrap">{{ $assignment->instructions }}</div>
            @else
                <p class="text-slate-500">No additional instructions.</p>
            @endif
            @if ($assignment->attachments->isNotEmpty())
                <div class="lms-attachment-list">
                    <p class="text-xs font-semibold uppercase tracking-wide text-isarva-muted">Resources</p>
                    @foreach ($assignment->attachments as $attachment)
                        <a href="{{ $attachment->url() }}" target="_blank" class="lms-attachment-link">
                            <svg class="h-4 w-4 shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                            {{ $attachment->name }}
                        </a>
                    @endforeach
                </div>
            @elseif ($assignment->attachment_path)
                <div class="lms-attachment-list">
                    <a href="{{ asset('storage/'.$assignment->attachment_path) }}" target="_blank" class="lms-attachment-link">
                        {{ $assignment->attachment_name }}
                    </a>
                </div>
            @endif
        </section>

        @if ($userSubmission)
            <section class="lms-card border-brand-200 bg-brand-50/30">
                <h2 class="font-bold text-slate-900">Your submission</h2>
                <p class="mt-2 text-sm text-slate-600">Submitted {{ $userSubmission->submitted_at->format('M j, Y g:i A') }}</p>
                <x-status-badge :status="$userSubmission->status" class="mt-2" />
                <a href="{{ route('submissions.show', $userSubmission) }}" class="mt-3 inline-block text-sm font-semibold text-brand-600">View details →</a>
            </section>
        @endif
    </div>

    @if (auth()->user()->isLecturer() || auth()->user()->isAdmin())
        <section class="lms-card">
            <h2 class="mb-4 text-lg font-bold">Submissions ({{ $assignment->submissions->count() }})</h2>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse ($assignment->submissions as $submission)
                    <a href="{{ route('submissions.show', $submission) }}" class="block rounded-xl border border-slate-100 p-3 hover:bg-slate-50">
                        <p class="font-semibold text-slate-900">{{ $submission->student->name }}</p>
                        <p class="text-xs text-slate-500">{{ $submission->submitted_at->diffForHumans() }}</p>
                        <x-status-badge :status="$submission->status" class="mt-1" />
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No submissions yet.</p>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
