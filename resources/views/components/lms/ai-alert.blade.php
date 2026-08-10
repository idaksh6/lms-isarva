@props([
    'title',
    'message',
    'tone' => 'error', // error|warning|info|success
    'actionLabel' => null,
    'actionUrl' => null,
])

@php
    $toneClass = match ($tone) {
        'warning' => 'lms-ai-alert--warning',
        'info' => 'lms-ai-alert--info',
        'success' => 'lms-ai-alert--success',
        default => 'lms-ai-alert--error',
    };
@endphp

<div {{ $attributes->class(['lms-ai-alert', $toneClass]) }} role="alert">
    <div class="lms-ai-alert-icon" aria-hidden="true">
        @if ($tone === 'warning')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
        @elseif ($tone === 'info')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
        @elseif ($tone === 'success')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        @else
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
        @endif
    </div>
    <div class="lms-ai-alert-body">
        <p class="lms-ai-alert-title">{{ $title }}</p>
        <p class="lms-ai-alert-message">{{ $message }}</p>
        @if ($actionLabel && $actionUrl)
            <a
                href="{{ $actionUrl }}"
                @if (str_starts_with($actionUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif
                class="lms-ai-alert-action"
            >{{ $actionLabel }}</a>
        @endif
        {{ $slot }}
    </div>
</div>
