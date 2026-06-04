@props([
    'name',
    'streamUrl',
    'downloadUrl',
    'mime' => null,
])

@php
    use App\Support\FilePreview;

    $previewType = FilePreview::type($mime, $name);
    $canPreview = FilePreview::canPreviewInline($mime, $name);
    $officeEmbed = $previewType === 'office'
        ? FilePreview::officeEmbedUrl(url($streamUrl))
        : null;
    $isLocal = str_contains(config('app.url'), '127.0.0.1') || str_contains(config('app.url'), 'localhost');
@endphp

<div {{ $attributes->merge(['class' => 'lms-doc-file']) }} @if ($canPreview) x-data="{ open: false }" x-on:keydown.escape.window="open = false" @endif>
    <div class="lms-doc-file-row">
        <span class="lms-doc-file-icon" aria-hidden="true">
            @if ($previewType === 'pdf')
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            @else
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            @endif
        </span>
        <span class="min-w-0 flex-1">
            <span class="block truncate text-sm font-semibold text-isarva-heading">{{ $name }}</span>
            <span class="text-xs text-isarva-muted">
                @if ($previewType === 'pdf') PDF document
                @elseif ($previewType === 'image') Image
                @elseif ($previewType === 'office') Word / Office document
                @else File attachment
                @endif
            </span>
        </span>
        <div class="flex shrink-0 flex-wrap gap-2">
            @if ($canPreview)
                <button type="button" class="lms-btn-secondary text-xs sm:text-sm" @click="open = true">
                    View in app
                </button>
            @endif
            <a href="{{ $downloadUrl }}" class="lms-btn-secondary text-xs sm:text-sm">Download</a>
        </div>
    </div>

    @if ($canPreview)
        <div
            x-show="open"
            x-cloak
            class="lms-doc-modal"
            role="dialog"
            aria-modal="true"
            :aria-label="'Preview: {{ $name }}'"
        >
            <div class="lms-doc-modal-backdrop" @click="open = false"></div>
            <div class="lms-doc-modal-panel">
                <div class="lms-doc-modal-toolbar">
                    <p class="truncate text-sm font-semibold text-isarva-heading">{{ $name }}</p>
                    <div class="flex gap-2">
                        <a href="{{ $downloadUrl }}" class="lms-btn-secondary text-sm">Download</a>
                        <button type="button" class="lms-btn-secondary text-sm" @click="open = false">Close</button>
                    </div>
                </div>
                <div class="lms-doc-modal-body">
                    @if ($previewType === 'pdf')
                        <iframe src="{{ $streamUrl }}#toolbar=1&navpanes=0" title="{{ $name }}" class="lms-doc-iframe"></iframe>
                    @elseif ($previewType === 'image')
                        <img src="{{ $streamUrl }}" alt="{{ $name }}" class="lms-doc-image">
                    @elseif ($previewType === 'office')
                        @if ($isLocal)
                            <div class="lms-doc-fallback">
                                <p class="text-sm text-slate-600">In-browser Word preview works on your live server URL. On localhost, download the file or deploy to view Office documents here.</p>
                                <a href="{{ $downloadUrl }}" class="mt-4 lms-btn-primary">Download {{ $name }}</a>
                            </div>
                        @else
                            <iframe src="{{ $officeEmbed }}" title="{{ $name }}" class="lms-doc-iframe"></iframe>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
