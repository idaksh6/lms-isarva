@extends('layouts.lms')

@section('title', 'Edit material — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="materials" />

    <div class="lms-form-card">
        <form method="POST" action="{{ route('course-materials.update', $material) }}" enctype="multipart/form-data" id="material-edit-form">
            @csrf
            @method('PATCH')

            <div class="lms-form-header">
                <h2 class="lms-form-title">Edit material</h2>
                <p class="lms-form-desc">Update the title, category, link, or replace the uploaded file.</p>
            </div>

            <div class="lms-form-field">
                <label for="category" class="lms-field-label">Category</label>
                <select id="category" name="category" class="lms-field-input mt-1.5" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->value }}" @selected(old('category', $material->category->value) === $category->value)>{{ $category->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lms-form-field">
                <label for="title" class="lms-field-label">Title</label>
                <input id="title" type="text" name="title" value="{{ old('title', $material->title) }}" class="lms-field-input mt-1.5" required>
            </div>

            <div class="lms-form-field">
                <label for="description" class="lms-field-label">Description</label>
                <textarea id="description" name="description" rows="3" class="lms-field-input mt-1.5">{{ old('description', $material->description) }}</textarea>
            </div>

            <div class="lms-form-field">
                <label for="external_url" class="lms-field-label">External link</label>
                <input id="external_url" type="url" name="external_url" value="{{ old('external_url', $material->external_url) }}" class="lms-field-input mt-1.5">
                <x-input-error :messages="$errors->get('external_url')" class="mt-1.5" />
            </div>

            @if ($material->hasFile())
                <p class="text-sm text-isarva-muted">Current file: <strong class="text-isarva-heading">{{ $material->file_name }}</strong></p>
            @endif

            <x-lms.single-file-upload
                name="file"
                label="Replace file"
                :required="false"
                :max-upload-mb="\App\Support\UploadLimits::courseMaterialMaxMegabytes()"
                :hint="'Optional · max '.\App\Support\UploadLimits::courseMaterialMaxMegabytes().' MB'"
            />
        </form>

        <div class="lms-form-actions">
            <button type="submit" form="material-edit-form" class="lms-btn-primary">Save changes</button>
            <a href="{{ route('courses.materials.index', $course) }}" class="lms-btn-secondary">Cancel</a>
            @can('delete', $material)
                <form method="POST" action="{{ route('course-materials.destroy', $material) }}" onsubmit="return confirm('Remove this material?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="lms-btn-danger">Delete</button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
