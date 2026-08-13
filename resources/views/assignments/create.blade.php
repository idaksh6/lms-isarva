@extends('layouts.lms')

@section('title', 'New assignment — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="assignment" />

    <x-lms.bulk-import-panel
        :action="route('courses.assignments.import', $course)"
        title="Import assignments"
        description="Use an Excel or Word template to create several assignments for this course at once."
        template-kind="assignments"
    />

    <form method="POST" action="{{ route('courses.assignments.store', $course) }}" enctype="multipart/form-data" class="lms-form-card">
        @csrf

        <div class="lms-form-header">
            <h2 class="lms-form-title">Assignment details</h2>
            <p class="lms-form-desc">Set instructions, due date, and optional resources for students.</p>
        </div>

        <div class="lms-form-field">
            <label for="title" class="lms-field-label">Title</label>
            <input id="title" type="text" name="title" value="{{ old('title') }}" class="lms-field-input mt-1.5" required>
            <x-input-error :messages="$errors->get('title')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="instructions" class="lms-field-label">Instructions</label>
            <textarea id="instructions" name="instructions" rows="6" class="lms-field-input mt-1.5" placeholder="What should students deliver?">{{ old('instructions') }}</textarea>
        </div>

        <div class="lms-form-field">
            <label for="due_at" class="lms-field-label">Due date & time</label>
            <input id="due_at" type="datetime-local" name="due_at" value="{{ old('due_at') }}" class="lms-field-input mt-1.5">
        </div>

        <x-lms.assignment-delivery-fields />

        <x-lms.file-upload />

        <label class="lms-form-check">
            <input type="checkbox" name="is_published" value="1" id="is_published" @checked(old('is_published', true))>
            <span>
                <span class="block text-sm font-semibold text-isarva-heading">Visible to students immediately</span>
                <span class="block text-xs text-isarva-muted">Uncheck to save as a draft until you publish.</span>
            </span>
        </label>

        <div class="lms-form-actions">
            <button type="submit" class="lms-btn-primary">Publish assignment</button>
            <a href="{{ route('courses.show', $course) }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
