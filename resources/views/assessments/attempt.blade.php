@extends('layouts.lms')

@section('title', 'Take assessment — ' . $assessment->title)
@section('page_title', $assessment->title)

@section('content')
<div class="lms-page-stack">
    <div class="lms-page-actions">
        <a href="{{ route('assessments.show', $assessment) }}" class="lms-btn-back">← Back</a>
    </div>

    <form method="POST" action="{{ route('assessments.attempt.store', $assessment) }}" class="lms-form-card">
        @csrf

        <div class="lms-form-header">
            <h2 class="lms-form-title">{{ $assessment->title }}</h2>
            <p class="lms-form-desc">Answer all questions, then submit. You cannot change answers after submitting.</p>
        </div>

        <div class="space-y-6">
            @foreach ($assessment->questions as $index => $question)
                <fieldset class="rounded-xl border border-slate-200 p-4">
                    <legend class="px-1 text-sm font-semibold text-isarva-heading">Question {{ $index + 1 }}</legend>
                    <p class="mt-2 text-sm text-slate-700">{{ $question->prompt }}</p>
                    <div class="mt-3 space-y-2">
                        @foreach ($question->options as $option)
                            <label class="flex cursor-pointer items-start gap-2 rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50">
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" @checked(old('answers.'.$question->id) == $option->id) required>
                                <span class="text-sm text-slate-700">{{ $option->label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('answers.'.$question->id)" class="mt-2" />
                </fieldset>
            @endforeach
        </div>

        <div class="lms-form-actions mt-6">
            <button type="submit" class="lms-btn-primary" onclick="return confirm('Submit your answers? You cannot change them after this.')">Submit assessment</button>
        </div>
    </form>
</div>
@endsection
