@extends('layouts.lms')

@section('title', 'Edit ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="edit" />

    <form method="POST" action="{{ route('courses.update', $course) }}" class="lms-form-card">
        @csrf
        @method('PATCH')

        <div class="lms-form-header">
            <h2 class="lms-form-title">Course details</h2>
            <p class="lms-form-desc">Update code, title, description, and availability.</p>
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
        @if (auth()->user()->isAdmin() && $lecturers->isNotEmpty())
            <div class="lms-form-field">
                <label for="lecturer_id" class="lms-field-label">Lecturer</label>
                <select id="lecturer_id" name="lecturer_id" class="lms-field-input mt-1.5">
                    @foreach ($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}" @selected(old('lecturer_id', $course->lecturer_id) == $lecturer->id)>{{ $lecturer->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <label class="lms-form-check">
            <input type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $course->is_active))>
            <span class="text-sm font-medium text-slate-700">Course is active</span>
        </label>
        <div class="lms-form-actions">
            <button type="submit" class="lms-btn-primary">Save changes</button>
            <a href="{{ route('courses.show', $course) }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
