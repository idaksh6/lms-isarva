@props([
    'action',
    'title' => 'Bulk import',
    'description' => 'Upload a filled Word (.doc/.docx), PDF, or .txt template.',
    'templateKind' => null,
])

<div class="mb-6 space-y-3 rounded-xl border border-isarva-border bg-slate-50 p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-isarva-heading">{{ $title }}</h3>
            <p class="mt-1 text-xs text-isarva-muted">{{ $description }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($templateKind)
                <a href="{{ route('imports.templates.download', ['kind' => $templateKind, 'format' => 'docx']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Download template</a>
            @endif
            <a href="{{ route('imports.templates') }}" class="lms-btn-secondary lms-btn-secondary--xs">All templates &amp; macro</a>
        </div>
    </div>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="flex flex-col gap-3 sm:flex-row sm:items-end">
        @csrf
        <div class="min-w-0 flex-1">
            <label for="import_file" class="lms-field-label">Template file</label>
            <input
                id="import_file"
                type="file"
                name="import_file"
                accept=".doc,.docx,.pdf,.txt,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf,text/plain"
                class="lms-field-input mt-1.5"
                required
            >
            <x-input-error :messages="$errors->get('import_file')" class="mt-1.5" />
        </div>
        <button type="submit" class="lms-btn-primary lms-btn-primary--xs shrink-0">Import</button>
    </form>
</div>
