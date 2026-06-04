@extends('layouts.lms')

@section('title', 'Submit work')
@section('page_title', 'Submit work')

@section('content')
<div class="lms-page-stack">
    <div class="lms-page-actions">
        <a href="{{ route('assignments.show', $assignment) }}" class="lms-btn-back">← Back to assignment</a>
    </div>

    <div class="lms-submit-context">
        <x-lms.illustration variant="assignment" class="lms-banner--submit-context" />
        <div class="lms-submit-context-copy">
            <p class="text-xs font-bold uppercase tracking-widest text-brand-700">{{ $assignment->course->code }}</p>
            <h2 class="text-lg font-bold text-isarva-heading">{{ $assignment->title }}</h2>
            @if ($assignment->due_at)
                <p class="mt-1 text-sm {{ $assignment->isOverdue() ? 'font-semibold text-rose-600' : 'text-isarva-muted' }}">
                    Due {{ $assignment->due_at->format('M j, Y · g:i A') }}
                </p>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('assignments.submissions.store', $assignment) }}" enctype="multipart/form-data" class="lms-form-card">
        @csrf

        <div class="lms-form-header">
            <h2 class="lms-form-title">Upload your submission</h2>
            <p class="lms-form-desc">Add your file and optional notes for your lecturer.</p>
        </div>

        <x-lms.single-file-upload :max-upload-mb="$maxUploadMb" />

        <div class="lms-form-field">
            <label for="notes" class="lms-field-label">Notes (optional)</label>
            <textarea id="notes" name="notes" rows="4" class="lms-field-input mt-1.5" placeholder="Link to repo, comments for your lecturer...">{{ old('notes') }}</textarea>
        </div>

        <div class="lms-form-actions">
            <button type="submit" class="lms-btn-primary">Submit assignment</button>
            <a href="{{ route('assignments.show', $assignment) }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
