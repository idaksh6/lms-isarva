@extends('layouts.lms')

@php
    $course = $assessment->course;
    $existing = $assessment->questions->keyBy('position');
    $isGoogleForm = $assessment->isGoogleForm();
@endphp

@section('title', 'Edit assessment — ' . $assessment->title)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course->load('lecturer')->loadCount(['students', 'assignments', 'assessments'])" active="assessments" />

    <form method="POST" action="{{ route('assessments.update', $assessment) }}" class="lms-form-card">
        @csrf
        @method('PATCH')

        <div class="lms-form-header">
            @if ($isGoogleForm)
                <h2 class="lms-form-title">Google Form assessment</h2>
                <p class="lms-form-desc">Update the title, instructions, due date, and form link. Students open the link in a new tab.</p>
            @else
                <h2 class="lms-form-title">Quiz questions</h2>
                <p class="lms-form-desc">Add {{ $assessment->question_count }} questions with multiple choice options. Mark the correct answer for each.</p>
            @endif
        </div>

        <div class="lms-form-field">
            <label for="title" class="lms-field-label">Title</label>
            <input id="title" type="text" name="title" value="{{ old('title', $assessment->title) }}" class="lms-field-input mt-1.5" required>
        </div>

        <div class="lms-form-field">
            <label for="instructions" class="lms-field-label">Instructions</label>
            <textarea id="instructions" name="instructions" rows="3" class="lms-field-input mt-1.5">{{ old('instructions', $assessment->instructions) }}</textarea>
        </div>

        <div class="lms-form-field">
            <label for="due_at" class="lms-field-label">Due date & time</label>
            <input id="due_at" type="datetime-local" name="due_at" value="{{ old('due_at', $assessment->due_at?->format('Y-m-d\TH:i')) }}" class="lms-field-input mt-1.5">
        </div>

        @if ($isGoogleForm)
            <div class="lms-form-field">
                <label for="external_url" class="lms-field-label">Google Form URL</label>
                <input
                    id="external_url"
                    type="url"
                    name="external_url"
                    value="{{ old('external_url', $assessment->external_url) }}"
                    class="lms-field-input mt-1.5"
                    placeholder="https://docs.google.com/forms/d/e/..."
                    required
                >
                <x-input-error :messages="$errors->get('external_url')" class="mt-1.5" />
            </div>
        @else
            <div class="space-y-6">
                @for ($i = 1; $i <= $assessment->question_count; $i++)
                    @php
                        $question = $existing->get($i);
                        $oldQuestion = old('questions.'.($i - 1));
                        $prompt = $oldQuestion['prompt'] ?? $question?->prompt ?? '';
                        $options = $oldQuestion['options'] ?? $question?->options->pluck('label')->values()->all() ?? ['', '', '', ''];
                        while (count($options) < 4) { $options[] = ''; }
                        $correct = (int) ($oldQuestion['correct'] ?? ($question ? $question->options->search(fn ($o) => $o->is_correct) + 1 : 1));
                    @endphp
                    <fieldset class="rounded-xl border border-slate-200 p-4">
                        <legend class="px-1 text-sm font-semibold text-isarva-heading">Question {{ $i }}</legend>
                        <div class="lms-form-field mt-2">
                            <label class="lms-field-label">Prompt</label>
                            <textarea name="questions[{{ $i - 1 }}][prompt]" rows="2" class="lms-field-input mt-1.5" required>{{ $prompt }}</textarea>
                            <x-input-error :messages="$errors->get('questions.'.($i - 1).'.prompt')" class="mt-1.5" />
                        </div>
                        <div class="mt-3 space-y-2">
                            @for ($o = 0; $o < 4; $o++)
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="questions[{{ $i - 1 }}][correct]" value="{{ $o + 1 }}" @checked($correct === $o + 1) required>
                                    <input type="text" name="questions[{{ $i - 1 }}][options][{{ $o }}][label]" value="{{ $options[$o] ?? '' }}" class="lms-field-input flex-1" placeholder="Option {{ $o + 1 }}" required>
                                </div>
                            @endfor
                        </div>
                    </fieldset>
                @endfor
            </div>

            <x-input-error :messages="$errors->get('questions')" class="mt-4" />
        @endif

        <x-input-error :messages="$errors->get('publish')" class="mt-4" />

        <div class="lms-form-actions mt-6">
            <button type="submit" class="lms-btn-primary">{{ $isGoogleForm ? 'Save changes' : 'Save questions' }}</button>
            <a href="{{ route('assessments.show', $assessment) }}" class="lms-btn-secondary">Preview</a>
        </div>
    </form>

    @if ($assessment->isReadyToPublish() && ! $assessment->is_published)
        <form method="POST" action="{{ route('assessments.publish', $assessment) }}" class="mt-4">
            @csrf
            <button type="submit" class="lms-btn-primary">Publish to students</button>
        </form>
    @endif
</div>
@endsection
