@extends('layouts.lms')

@section('title', 'Create course')
@section('page_title', 'Create course')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="courses" subtitle="Add a new module for the Data Science programme." />

    <form method="POST" action="{{ route('courses.store') }}" class="lms-card w-full space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-slate-700">Course code</label>
            <input type="text" name="code" value="{{ old('code') }}" class="lms-field-input mt-1.5" placeholder="e.g. DS501" required>
            <x-input-error :messages="$errors->get('code')" class="mt-1" />
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700">Title</label>
            <input type="text" name="title" value="{{ old('title') }}" class="lms-field-input mt-1.5" required>
            <x-input-error :messages="$errors->get('title')" class="mt-1" />
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700">Description</label>
            <textarea name="description" rows="4" class="lms-field-input mt-1.5">{{ old('description') }}</textarea>
        </div>
        @if (auth()->user()->isAdmin() && $lecturers->isNotEmpty())
            <div>
                <label class="block text-sm font-semibold text-slate-700">Lecturer</label>
                <select name="lecturer_id" class="lms-field-input mt-1.5">
                    @foreach ($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}" @selected(old('lecturer_id') == $lecturer->id)>{{ $lecturer->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="lms-btn-primary">Create course</button>
            <a href="{{ route('courses.index') }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
