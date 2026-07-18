@extends('layouts.lms')

@section('title', 'Add material — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="materials" />

    <form method="POST" action="{{ route('courses.materials.store', $course) }}" enctype="multipart/form-data" class="lms-form-card">
        @csrf

        <div class="lms-form-header">
            <h2 class="lms-form-title">Add class material</h2>
            <p class="lms-form-desc">Upload a file or share an external link. Choose the category students should look under.</p>
        </div>

        <div class="lms-form-field">
            <label for="category" class="lms-field-label">Category</label>
            <select id="category" name="category" class="lms-field-input mt-1.5" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->value }}" @selected(old('category') === $category->value)>{{ $category->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="lms-form-field">
            <label for="title" class="lms-field-label">Title</label>
            <input id="title" type="text" name="title" value="{{ old('title') }}" class="lms-field-input mt-1.5" required>
            <x-input-error :messages="$errors->get('title')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="description" class="lms-field-label">Description <span class="font-normal text-isarva-muted">(optional)</span></label>
            <textarea id="description" name="description" rows="3" class="lms-field-input mt-1.5">{{ old('description') }}</textarea>
        </div>

        <div class="lms-form-field">
            <label for="external_url" class="lms-field-label">External link <span class="font-normal text-isarva-muted">(optional if uploading a file)</span></label>
            <input id="external_url" type="url" name="external_url" value="{{ old('external_url') }}" class="lms-field-input mt-1.5" placeholder="https://">
            <x-input-error :messages="$errors->get('external_url')" class="mt-1.5" />
        </div>

        <x-lms.single-file-upload
            name="file"
            label="File"
            :required="false"
            :max-upload-mb="\App\Support\UploadLimits::courseMaterialMaxMegabytes()"
            :hint="'Optional if using a link · PDF, slides, datasets · max '.\App\Support\UploadLimits::courseMaterialMaxMegabytes().' MB'"
        />

        <div class="lms-form-actions">
            <button type="submit" class="lms-btn-primary">Save material</button>
            <a href="{{ route('courses.materials.index', $course) }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
