@extends('layouts.lms')

@section('title', 'Edit ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="edit" />

    <div class="lms-form-card">
    <form method="POST" action="{{ route('courses.update', $course) }}" id="course-edit-form">
        @csrf
        @method('PATCH')

        <div class="lms-form-header">
            <h2 class="lms-form-title">Course details</h2>
            <p class="lms-form-desc">Update code, title, description, and semester. Publishing is managed from the course list.</p>
        </div>

        <div class="lms-form-field">
            <label for="code" class="lms-field-label">Course code</label>
            <input id="code" type="text" name="code" value="{{ old('code', $course->code) }}" class="lms-field-input mt-1.5" required>
        </div>
        <div class="lms-form-field">
            <label for="title" class="lms-field-label">Title</label>
            <input id="title" type="text" name="title" value="{{ old('title', $course->title) }}" class="lms-field-input mt-1.5" required>
        </div>
        <div class="lms-form-field">
            <label for="description" class="lms-field-label">Description</label>
            <textarea id="description" name="description" rows="4" class="lms-field-input mt-1.5">{{ old('description', $course->description) }}</textarea>
        </div>
        <div class="lms-form-field">
            <label for="semester" class="lms-field-label">Semester <span class="font-normal text-isarva-muted">(e.g. 2026-S1 — used for timetable import filter)</span></label>
            <input id="semester" type="text" name="semester" value="{{ old('semester', $course->semester) }}" class="lms-field-input mt-1.5" placeholder="2026-S1">
        </div>
        @if (auth()->user()->isAdmin() && $lecturers->isNotEmpty())
            <div class="lms-form-field">
                <label for="lecturer_id" class="lms-field-label">Lecturer</label>
                <select id="lecturer_id" name="lecturer_id" class="lms-field-input mt-1.5">
                    @foreach ($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}" @selected(old('lecturer_id', $course->lecturer_id) == $lecturer->id)>{{ $lecturer->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('lecturer_id')" class="mt-1.5" />
            </div>
        @endif
        @if (! $course->is_active)
            <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-950">
                This course is disabled. Publish it from the <a href="{{ route('courses.index') }}" class="font-semibold underline">course list</a> when students should see it.
            </p>
        @endif
    </form>

    <div class="lms-form-actions">
        <button type="submit" form="course-edit-form" class="lms-btn-primary">Save changes</button>
        <a href="{{ route('courses.show', $course) }}" class="lms-btn-secondary">Cancel</a>
        @can('delete', $course)
            <form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('Delete or archive this course? Courses with submissions are archived only.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="lms-btn-danger">Delete course</button>
            </form>
        @endcan
    </div>
    </div>
</div>
@endsection
