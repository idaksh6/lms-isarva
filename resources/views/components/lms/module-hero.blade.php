@props([
    'module' => null,
    'title' => '',
    'subtitle' => '',
    'variant' => null,
])

@if ($subtitle || trim($slot ?? '') !== '')
    <div {{ $attributes->merge(['class' => 'lms-page-toolbar'.( ! $subtitle && trim($slot ?? '') !== '' ? ' lms-page-toolbar--actions-only' : '')]) }}>
        @if ($subtitle)
            <p class="lms-page-toolbar-desc">{{ $subtitle }}</p>
        @endif
        @if (trim($slot ?? '') !== '')
            <div class="lms-page-toolbar-actions">
                {{ $slot }}
            </div>
        @endif
    </div>
@endif
