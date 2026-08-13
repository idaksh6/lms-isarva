@props([
    'action',
    'title' => 'Bulk import',
    'description' => 'Upload a filled Excel (.xlsx/.xls), Word (.doc/.docx), PDF, or .txt template.',
    'templateKind' => null,
])

<section {{ $attributes->merge(['class' => 'lms-bulk-import-panel']) }}>
    <div class="lms-bulk-import-panel-head">
        <div>
            <h2 class="lms-bulk-import-panel-title">{{ $title }}</h2>
            <p class="lms-bulk-import-panel-desc">{{ $description }}</p>
        </div>
        <div class="lms-bulk-import-panel-actions">
            @if ($templateKind)
                <a href="{{ route('imports.templates.download', ['kind' => $templateKind, 'format' => 'xlsx']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Excel template</a>
                <a href="{{ route('imports.templates.download', ['kind' => $templateKind, 'format' => 'docx']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Word template</a>
            @endif
            <a href="{{ route('imports.templates') }}" class="lms-btn-secondary lms-btn-secondary--xs">All templates</a>
        </div>
    </div>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="lms-bulk-import-panel-form">
        @csrf
        <div class="min-w-0 flex-1">
            <label for="import_file" class="lms-field-label">Template file</label>
            <input
                id="import_file"
                type="file"
                name="import_file"
                accept=".doc,.docx,.pdf,.txt,.xlsx,.xls,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/pdf,text/plain"
                class="lms-field-input mt-1.5"
                required
            >
            <x-input-error :messages="$errors->get('import_file')" class="mt-1.5" />
        </div>
        <button type="submit" class="lms-btn-primary shrink-0">Import</button>
    </form>
</section>
