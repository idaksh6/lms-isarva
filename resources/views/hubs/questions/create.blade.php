@extends('layouts.lms')

@section('title', 'Ask a question')
@section('page_title', 'Ask a question')

@section('content')
<div class="lms-page-stack corp-qa-page">
    <x-lms.module-hero module="questions" title="Ask a question" subtitle="Share your question with the community. Include enough detail so others can help you effectively." />

    <form method="POST" action="{{ route('questions.store') }}" class="lms-form-card">
        @csrf
        <div class="lms-form-header">
            <h2 class="lms-form-title">Question details</h2>
            <p class="lms-form-desc">Your name and posting time will be shown on the thread.</p>
        </div>

        <div class="lms-form-field">
            <label for="course_id" class="lms-field-label">Related course (optional)</label>
            <select id="course_id" name="course_id" class="lms-field-input mt-1.5">
                <option value="">General — not tied to a specific course</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->code }} — {{ $course->title }}</option>
                @endforeach
            </select>
            @error('course_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lms-form-field">
            <label for="title" class="lms-field-label">Subject</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" class="lms-field-input mt-1.5" required maxlength="255" placeholder="Summarize your question in one line">
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lms-form-field">
            <label for="body" class="lms-field-label">Question</label>
            <textarea id="body" name="body" rows="6" class="lms-field-input mt-1.5" required maxlength="10000" placeholder="Describe your question with as much context as possible…">{{ old('body') }}</textarea>
            @error('body')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lms-form-actions">
            <a href="{{ route('questions.index') }}" class="lms-btn-secondary">Cancel</a>
            <button type="submit" class="lms-btn-primary">Post question</button>
        </div>
    </form>
</div>
@endsection
