@props([
    'url',
    'label' => null,
    'provider' => null,
])

@php
    use App\Support\ExternalSubmissionLink;

    $displayLabel = $label ?: ExternalSubmissionLink::labelFromUrl($url);
    $providerLabel = $provider ?: ExternalSubmissionLink::providerLabel($url);
@endphp

<div {{ $attributes->merge(['class' => 'lms-doc-file']) }}>
    <div class="lms-doc-file-row">
        <span class="lms-doc-file-icon" aria-hidden="true">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m3.536-.536l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
            </svg>
        </span>
        <span class="min-w-0 flex-1">
            <span class="block truncate text-sm font-semibold text-isarva-heading">{{ $displayLabel }}</span>
            <span class="text-xs text-isarva-muted">{{ $providerLabel }} · external link</span>
        </span>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="lms-btn-primary text-xs sm:text-sm">
                Open in {{ $providerLabel }}
            </a>
        </div>
    </div>
    <p class="mt-2 truncate text-xs text-slate-500" title="{{ $url }}">{{ $url }}</p>
</div>
