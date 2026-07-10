@props([
    'name' => 'attachments',
    'label' => 'Attachments',
    'hint' => null,
    'maxFiles' => \App\Support\UploadLimits::ASSIGNMENT_ATTACHMENT_MAX_COUNT,
    'maxSizeMb' => \App\Support\UploadLimits::assignmentAttachmentMaxMegabytes(),
    'required' => false,
])

@php
    $hint ??= 'PDF, notebooks, datasets — up to '.$maxFiles.' files, '.$maxSizeMb.' MB each';
@endphp

<div
    {{ $attributes->merge(['class' => 'lms-form-field']) }}
    x-data="lmsFileUpload({ maxFiles: {{ (int) $maxFiles }}, maxSizeMb: {{ (int) $maxSizeMb }} })"
>
    <span class="lms-field-label">{{ $label }}</span>
    @if ($hint)
        <p class="lms-field-hint">{{ $hint }}</p>
    @endif

    <div
        x-show="files.length < maxFiles"
        class="lms-file-upload-zone"
        :class="{ 'lms-file-upload-zone--drag': dragging }"
        @click="$refs.input.click()"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop="onDrop($event)"
    >
        <input
            type="file"
            x-ref="input"
            name="{{ $name }}[]"
            multiple
            class="sr-only"
            @if ($required) required @endif
            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.txt,.md,.ipynb,.zip,.rar,.7z,.png,.jpg,.jpeg,.gif,.json,.py,.r,.sql"
            @change="onSelect($event)"
        >
        <span class="lms-file-upload-icon" aria-hidden="true">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
            </svg>
        </span>
        <span class="lms-file-upload-title">Drop files here or <span class="text-brand-600">browse</span></span>
        <span class="lms-file-upload-meta">Up to {{ $maxFiles }} files · {{ $maxSizeMb }} MB each</span>
    </div>

    <p x-show="files.length >= maxFiles" x-cloak class="lms-field-hint">Maximum of {{ $maxFiles }} files attached. Remove one to add another.</p>

    <ul x-show="files.length > 0" x-cloak class="lms-file-upload-list">
        <template x-for="(file, index) in files" :key="index">
            <li class="lms-file-upload-item">
                <span class="lms-file-upload-item-icon" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-isarva-heading" x-text="file.name"></span>
                    <span class="text-xs text-isarva-muted" x-text="file.size"></span>
                </span>
                <button type="button" class="lms-file-upload-remove" @click.stop="removeAt(index)" aria-label="Remove file">×</button>
            </li>
        </template>
    </ul>

    <x-input-error :messages="collect($errors->getMessages())->filter(fn ($_, $key) => $key === $name || str_starts_with($key, $name.'.'))->flatten()->unique()->values()->all()" class="mt-1.5" />
</div>
