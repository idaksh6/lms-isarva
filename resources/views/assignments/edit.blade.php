@extends('layouts.lms')

@section('title', 'Edit assignment')
@section('heading', 'Edit assignment')
@section('subheading', $assignment->title)

@section('content')
<div class="lms-page-actions">
    <a href="{{ route('assignments.show', $assignment) }}" class="lms-btn-back">← Back to assignment</a>
</div>

<div class="lms-form-card">
<form method="POST" action="{{ route('assignments.update', $assignment) }}" enctype="multipart/form-data" id="assignment-edit-form">
    @csrf
    @method('PATCH')

    <div class="lms-form-header">
        <h2 class="lms-form-title">Update assignment</h2>
        <p class="lms-form-desc">Change details or add more resource files (up to {{ \App\Support\UploadLimits::ASSIGNMENT_ATTACHMENT_MAX_COUNT }} total).</p>
    </div>

    <div class="lms-form-field">
        <label for="title" class="lms-field-label">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $assignment->title) }}" class="lms-field-input mt-1.5" required>
    </div>

    <div class="lms-form-field">
        <label for="instructions" class="lms-field-label">Instructions</label>
        <textarea id="instructions" name="instructions" rows="6" class="lms-field-input mt-1.5">{{ old('instructions', $assignment->instructions) }}</textarea>
    </div>

    <div class="lms-form-field">
        <label for="due_at" class="lms-field-label">Due date</label>
        <input id="due_at" type="datetime-local" name="due_at"
               value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\TH:i')) }}"
               class="lms-field-input mt-1.5">
    </div>

    <x-lms.assignment-delivery-fields :assignment="$assignment" />

    @if ($assignment->attachments->isNotEmpty())
        <div class="lms-form-field">
            <span class="lms-field-label">Current files</span>
            <div class="lms-attachment-list">
                @foreach ($assignment->attachments as $attachment)
                    <a href="{{ $attachment->url() }}" target="_blank" class="lms-attachment-link">
                        <svg class="h-4 w-4 shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        {{ $attachment->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @elseif ($assignment->attachment_path)
        <div class="lms-form-field">
            <span class="lms-field-label">Current file</span>
            <a href="{{ asset('storage/'.$assignment->attachment_path) }}" target="_blank" class="lms-attachment-link">
                {{ $assignment->attachment_name }}
            </a>
        </div>
    @endif

    @php
        $slotsLeft = max(0, \App\Support\UploadLimits::ASSIGNMENT_ATTACHMENT_MAX_COUNT - $assignment->attachments->count());
    @endphp
    @if ($slotsLeft > 0)
        <x-lms.file-upload
            :label="$assignment->attachments->isEmpty() ? 'Attachments' : 'Add more files'"
            :hint="'You can add up to '.$slotsLeft.' more file(s). 10 MB each.'"
            :max-files="$slotsLeft"
        />
    @endif

    <label class="lms-form-check">
        <input type="checkbox" name="is_published" value="1" id="is_published" @checked(old('is_published', $assignment->is_published))>
        <span class="text-sm font-semibold text-isarva-heading">Published — visible to students</span>
    </label>
</form>

<div class="lms-form-actions">
    <button type="submit" form="assignment-edit-form" class="lms-btn-primary">Save changes</button>
    <a href="{{ route('assignments.show', $assignment) }}" class="lms-btn-secondary">Cancel</a>
    @can('delete', $assignment)
        <form method="POST" action="{{ route('assignments.destroy', $assignment) }}" class="ml-auto" onsubmit="return confirm('Delete this assignment and all submissions?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="lms-btn-danger">Delete assignment</button>
        </form>
    @endcan
</div>
</div>
@endsection
