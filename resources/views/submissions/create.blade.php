@extends('layouts.lms')

@section('title', 'Submit work')
@section('page_title', 'Submit work')

@section('content')
<div class="lms-page-stack">
    <div class="lms-page-actions">
        <a href="{{ route('assignments.show', $assignment) }}" class="lms-btn-back">← Back to assignment</a>
    </div>

    @if (! empty($resubmit))
        <div class="lms-resubmit-banner">
            <p class="text-sm font-semibold">
                Your lecturer requested a revision.
                @if ($assignment->acceptsExternalLink() && ! $assignment->acceptsFileUpload())
                    Paste an updated cloud share link below.
                @else
                    Upload an improved file or share link below.
                @endif
            </p>
        </div>
    @endif

    <div class="lms-submit-context">
        <div class="lms-submit-context-accent" aria-hidden="true"></div>
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

    @if ($assignment->drop_folder_url)
        <section class="lms-panel lms-panel--highlight">
            <div class="lms-panel-header">
                <h2 class="lms-panel-title">Step 1 — Upload to shared folder</h2>
            </div>
            <div class="lms-panel-body space-y-3">
                <p class="text-sm text-isarva-muted">
                    Upload your zip or project bundle to the lecturer's shared folder first. When done, copy the <strong class="font-semibold text-isarva-heading">share link to your file</strong> (not the folder link) and paste it in the form below.
                </p>
                <a href="{{ $assignment->drop_folder_url }}" target="_blank" rel="noopener noreferrer" class="lms-btn-secondary inline-flex">
                    Open shared upload folder
                </a>
            </div>
        </section>
    @endif

    <form method="POST" action="{{ route('assignments.submissions.store', $assignment) }}" enctype="multipart/form-data" class="lms-form-card">
        @csrf

        <div class="lms-form-header">
            @if ($assignment->acceptsExternalLink() && ! $assignment->acceptsFileUpload())
                <h2 class="lms-form-title">Step 2 — Submit your file link</h2>
                <p class="lms-form-desc">Paste the Google Drive, Dropbox, or OneDrive share link to your uploaded file.</p>
            @elseif ($assignment->acceptsExternalLink() && $assignment->acceptsFileUpload())
                <h2 class="lms-form-title">Submit your work</h2>
                <p class="lms-form-desc">Upload a file directly, or paste a cloud share link — whichever you prefer.</p>
            @else
                <h2 class="lms-form-title">Upload your submission</h2>
                <p class="lms-form-desc">Add your file and optional notes for your lecturer.</p>
            @endif
        </div>

        @if ($assignment->acceptsFileUpload())
            <x-lms.single-file-upload :max-upload-mb="$maxUploadMb" :required="! $assignment->acceptsExternalLink()" />
        @endif

        @if ($assignment->acceptsExternalLink())
            @if ($assignment->acceptsFileUpload())
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase tracking-wider">
                        <span class="bg-white px-3 text-slate-500">Or submit a cloud link</span>
                    </div>
                </div>
            @endif

            <div class="lms-form-field">
                <label for="external_url" class="lms-field-label">Cloud share link</label>
                <input
                    id="external_url"
                    type="url"
                    name="external_url"
                    value="{{ old('external_url') }}"
                    class="lms-field-input mt-1.5"
                    placeholder="https://drive.google.com/file/d/.../view?usp=sharing"
                    @if (! $assignment->acceptsFileUpload()) required @endif
                >
                <p class="mt-1.5 text-xs text-isarva-muted">Google Drive, Dropbox, and OneDrive links are supported.</p>
                <x-input-error :messages="$errors->get('external_url')" class="mt-1.5" />
            </div>

            <div class="lms-form-field">
                <label for="external_label" class="lms-field-label">File name (optional)</label>
                <input
                    id="external_label"
                    type="text"
                    name="external_label"
                    value="{{ old('external_label') }}"
                    class="lms-field-input mt-1.5"
                    placeholder="e.g. capstone_bundle.zip"
                >
                <x-input-error :messages="$errors->get('external_label')" class="mt-1.5" />
            </div>
        @endif

        <div class="lms-form-field">
            <label for="notes" class="lms-field-label">Notes (optional)</label>
            <textarea id="notes" name="notes" rows="4" class="lms-field-input mt-1.5" placeholder="Repo link, version notes, comments for your lecturer...">{{ old('notes') }}</textarea>
        </div>

        <div class="lms-form-actions">
            <button type="submit" class="lms-btn-primary">Submit assignment</button>
            <a href="{{ route('assignments.show', $assignment) }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
