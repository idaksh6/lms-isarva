@extends('layouts.lms')

@section('title', 'New assessment — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="assessments" />

    <form method="POST" action="{{ route('courses.assessments.store', $course) }}" class="lms-form-card">
        @csrf

        <div class="lms-form-header">
            <h2 class="lms-form-title">Assessment setup</h2>
            <p class="lms-form-desc">Create the quiz shell first, then add all questions before publishing to students.</p>
        </div>

        <div class="lms-form-field">
            <label for="title" class="lms-field-label">Title</label>
            <input id="title" type="text" name="title" value="{{ old('title') }}" class="lms-field-input mt-1.5" required>
            <x-input-error :messages="$errors->get('title')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="instructions" class="lms-field-label">Instructions</label>
            <textarea id="instructions" name="instructions" rows="4" class="lms-field-input mt-1.5">{{ old('instructions') }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="lms-form-field">
                <label for="question_count" class="lms-field-label">Number of questions</label>
                <input id="question_count" type="number" name="question_count" value="{{ old('question_count', 15) }}" min="1" max="50" class="lms-field-input mt-1.5" required>
            </div>
            <div class="lms-form-field">
                <label for="marks_per_question" class="lms-field-label">Marks per question</label>
                <input id="marks_per_question" type="number" name="marks_per_question" value="{{ old('marks_per_question', 2) }}" min="1" max="10" class="lms-field-input mt-1.5" required>
            </div>
        </div>

        <div class="lms-form-field">
            <label for="due_at" class="lms-field-label">Due date & time <span class="font-normal text-isarva-muted">(optional)</span></label>
            <input id="due_at" type="datetime-local" name="due_at" value="{{ old('due_at') }}" class="lms-field-input mt-1.5">
        </div>

        <div class="lms-form-actions">
            <button type="submit" class="lms-btn-primary">Create & add questions</button>
            <a href="{{ route('courses.assessments.index', $course) }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
