@extends('layouts.lms')

@section('title', 'Assign mentor')
@section('page_title', 'Mentoring')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero
        module="users"
        title="Assign a mentor"
        subtitle="Link a faculty mentor with a student. Optionally scope the relationship to a course."
    >
        <a href="{{ route('mentoring.index') }}" class="lms-btn-secondary lms-btn-secondary--xs">Back to mentoring</a>
    </x-lms.module-hero>

    <form method="POST" action="{{ route('mentoring.store') }}" class="lms-form-card">
        @csrf

        <div class="lms-form-header">
            <h2 class="lms-form-title">Mentoring relationship</h2>
            <p class="lms-form-desc">The student is notified when the assignment is created.</p>
        </div>

        @if (auth()->user()->isAdmin())
            <div class="lms-form-field">
                <label for="mentor_id" class="lms-field-label">Mentor (faculty)</label>
                <select id="mentor_id" name="mentor_id" class="lms-field-input mt-1.5" required>
                    <option value="">Select lecturer</option>
                    @foreach ($mentors as $mentor)
                        <option value="{{ $mentor->id }}" @selected((int) old('mentor_id') === $mentor->id)>{{ $mentor->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('mentor_id')" class="mt-1.5" />
            </div>
        @else
            <input type="hidden" name="mentor_id" value="{{ auth()->id() }}">
            <div class="lms-form-field">
                <span class="lms-field-label">Mentor</span>
                <p class="mt-1.5 text-sm text-isarva-heading">{{ auth()->user()->name }}</p>
            </div>
        @endif

        <div class="lms-form-field">
            <label for="mentee_id" class="lms-field-label">Student (mentee)</label>
            <select id="mentee_id" name="mentee_id" class="lms-field-input mt-1.5" required>
                <option value="">Select student</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((int) old('mentee_id') === $student->id)>
                        {{ $student->name }}@if ($student->student_id) ({{ $student->student_id }})@endif
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('mentee_id')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="course_id" class="lms-field-label">Course <span class="font-normal text-isarva-muted">(optional)</span></label>
            <select id="course_id" name="course_id" class="lms-field-input mt-1.5">
                <option value="">General mentoring</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected((int) old('course_id') === $course->id)>{{ $course->code }} — {{ $course->title }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('course_id')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="started_at" class="lms-field-label">Start date</label>
            <input id="started_at" type="datetime-local" name="started_at" value="{{ old('started_at', now()->format('Y-m-d\TH:i')) }}" class="lms-field-input mt-1.5">
        </div>

        <div class="lms-form-field">
            <label for="goals" class="lms-field-label">Goals</label>
            <textarea id="goals" name="goals" rows="4" class="lms-field-input mt-1.5" placeholder="What should this mentoring relationship focus on?">{{ old('goals') }}</textarea>
        </div>

        <div class="lms-form-actions">
            <button type="submit" class="lms-btn-primary">Create relationship</button>
            <a href="{{ route('mentoring.index') }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
