@extends('layouts.lms')

@php
    use App\Enums\AssessmentType;

    $selectedType = old('type', AssessmentType::Manual->value);
@endphp

@section('title', 'New assessment — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="assessments" />

    <form
        method="POST"
        action="{{ route('courses.assessments.store', $course) }}"
        class="lms-form-card"
        x-data="{ type: @js($selectedType) }"
    >
        @csrf

        <div class="lms-form-header">
            <h2 class="lms-form-title">Assessment setup</h2>
            <p class="lms-form-desc" x-show="type === 'manual'">
                Create the quiz shell first, then add all questions before publishing to students.
            </p>
            <p class="lms-form-desc" x-show="type === 'google_form'" x-cloak>
                Add a Google Form link and total marks. Students open the form in a new tab; you record their scores in the LMS afterwards.
            </p>
        </div>

        <div class="lms-form-field">
            <span class="lms-field-label">Assessment type</span>
            <div class="mt-1.5 flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input
                        type="radio"
                        name="type"
                        value="{{ AssessmentType::Manual->value }}"
                        class="border-slate-300 text-isarva-accent focus:ring-isarva-accent"
                        x-model="type"
                        @checked($selectedType === AssessmentType::Manual->value)
                    >
                    Manual (in-LMS quiz)
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input
                        type="radio"
                        name="type"
                        value="{{ AssessmentType::GoogleForm->value }}"
                        class="border-slate-300 text-isarva-accent focus:ring-isarva-accent"
                        x-model="type"
                        @checked($selectedType === AssessmentType::GoogleForm->value)
                    >
                    Google Form
                </label>
            </div>
            <x-input-error :messages="$errors->get('type')" class="mt-1.5" />
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

        <div class="lms-form-field" x-show="type === 'google_form'" x-cloak>
            <label for="external_url" class="lms-field-label">Google Form URL</label>
            <input
                id="external_url"
                type="url"
                name="external_url"
                value="{{ old('external_url') }}"
                class="lms-field-input mt-1.5"
                placeholder="https://docs.google.com/forms/d/e/..."
                :required="type === 'google_form'"
            >
            <p class="mt-1.5 text-xs text-isarva-muted">Students will open this link in a new tab after you publish.</p>
            <x-input-error :messages="$errors->get('external_url')" class="mt-1.5" />
        </div>

        <div class="lms-form-field" x-show="type === 'google_form'" x-cloak>
            <label for="max_score" class="lms-field-label">Total marks</label>
            <input
                id="max_score"
                type="number"
                name="max_score"
                value="{{ old('max_score', 100) }}"
                min="1"
                max="1000"
                class="lms-field-input mt-1.5"
                :required="type === 'google_form'"
            >
            <p class="mt-1.5 text-xs text-isarva-muted">Used when you record each student’s Google Form score in the LMS.</p>
            <x-input-error :messages="$errors->get('max_score')" class="mt-1.5" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2" x-show="type === 'manual'">
            <div class="lms-form-field">
                <label for="question_count" class="lms-field-label">Number of questions</label>
                <input
                    id="question_count"
                    type="number"
                    name="question_count"
                    value="{{ old('question_count', 15) }}"
                    min="1"
                    max="50"
                    class="lms-field-input mt-1.5"
                    :required="type === 'manual'"
                >
            </div>
            <div class="lms-form-field">
                <label for="marks_per_question" class="lms-field-label">Marks per question</label>
                <input
                    id="marks_per_question"
                    type="number"
                    name="marks_per_question"
                    value="{{ old('marks_per_question', 2) }}"
                    min="1"
                    max="10"
                    class="lms-field-input mt-1.5"
                    :required="type === 'manual'"
                >
            </div>
        </div>

        <div class="lms-form-field">
            <label for="due_at" class="lms-field-label">Due date & time <span class="font-normal text-isarva-muted">(optional)</span></label>
            <input id="due_at" type="datetime-local" name="due_at" value="{{ old('due_at') }}" class="lms-field-input mt-1.5">
        </div>

        <div class="lms-form-actions">
            <button type="submit" class="lms-btn-primary" x-text="type === 'google_form' ? 'Create assessment' : 'Create & add questions'"></button>
            <a href="{{ route('courses.assessments.index', $course) }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
