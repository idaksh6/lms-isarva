@props([
    'action',
    'title' => 'Bulk import',
    'description' => 'Download a template, fill it offline, then upload it here. Existing quiz questions are replaced.',
    'templateKind' => null,
    'warning' => null,
])

@php
    $maxMb = \App\Support\UploadLimits::bulkImportMaxMegabytes();
    $accept = '.doc,.docx,.pdf,.txt,.xlsx,.xls,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/pdf,text/plain';
@endphp

<section {{ $attributes->merge(['class' => 'lms-bulk-import-panel']) }}>
    <div class="lms-bulk-import-panel-header">
        <div>
            <p class="lms-bulk-import-kicker">Bulk import</p>
            <h2 class="lms-bulk-import-panel-title">{{ $title }}</h2>
            <p class="lms-bulk-import-panel-desc">{{ $description }}</p>
        </div>
        <a href="{{ route('imports.templates') }}" class="lms-btn-secondary lms-btn-secondary--xs shrink-0">Browse all templates</a>
    </div>

    @if ($templateKind)
        <div class="lms-bulk-import-steps">
            <div class="lms-bulk-import-step">
                <span class="lms-bulk-import-step-num" aria-hidden="true">1</span>
                <div class="lms-bulk-import-step-body">
                    <p class="lms-bulk-import-step-title">Download a template</p>
                    <p class="lms-bulk-import-step-desc">Start with Excel for fastest bulk entry, or Word if you prefer a document.</p>
                    <div class="lms-bulk-import-template-grid">
                        <a href="{{ route('imports.templates.download', ['kind' => $templateKind, 'format' => 'xlsx']) }}" class="lms-bulk-import-template-card">
                            <span class="lms-bulk-import-template-icon" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125h-7.5c-.621 0-1.125-.504-1.125-1.125m9.75 0v-1.5c0-.621-.504-1.125-1.125-1.125m0 0h-7.5"/>
                                </svg>
                            </span>
                            <span>
                                <span class="lms-bulk-import-template-name">Excel (.xlsx)</span>
                                <span class="lms-bulk-import-template-meta">Recommended</span>
                            </span>
                        </a>
                        <a href="{{ route('imports.templates.download', ['kind' => $templateKind, 'format' => 'docx']) }}" class="lms-bulk-import-template-card">
                            <span class="lms-bulk-import-template-icon" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </span>
                            <span>
                                <span class="lms-bulk-import-template-name">Word (.docx)</span>
                                <span class="lms-bulk-import-template-meta">Document format</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="lms-bulk-import-step">
                <span class="lms-bulk-import-step-num" aria-hidden="true">2</span>
                <div class="lms-bulk-import-step-body">
                    <p class="lms-bulk-import-step-title">Upload your filled file</p>
                    <p class="lms-bulk-import-step-desc">Excel, Word, PDF, or plain text · max {{ $maxMb }} MB</p>

                    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="lms-bulk-import-panel-form">
                        @csrf

                        <x-lms.single-file-upload
                            name="import_file"
                            label="Template file"
                            :hint="'Excel, Word, PDF, or TXT · max '.$maxMb.' MB'"
                            :max-upload-mb="$maxMb"
                            :accept="$accept"
                            :required="true"
                        />

                        @if ($warning)
                            <p class="lms-bulk-import-warning">{{ $warning }}</p>
                        @endif

                        <div class="lms-form-actions">
                            <button type="submit" class="lms-btn-primary">Import file</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="lms-bulk-import-panel-form">
            @csrf
            <x-lms.single-file-upload
                name="import_file"
                label="Template file"
                :hint="'Excel, Word, PDF, or TXT · max '.$maxMb.' MB'"
                :max-upload-mb="$maxMb"
                :accept="$accept"
                :required="true"
            />
            <div class="lms-form-actions">
                <button type="submit" class="lms-btn-primary">Import file</button>
            </div>
        </form>
    @endif
</section>
