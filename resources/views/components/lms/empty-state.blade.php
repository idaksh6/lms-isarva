@props([
    'title' => 'Nothing here yet',
    'message' => '',
    'variant' => 'books',
])

@php
    $icon = match ($variant) {
        'assignment', 'task', 'document' => 'clipboard',
        'analytics', 'data', 'chart' => 'chart',
        'sky', 'users' => 'users',
        'inbox' => 'inbox',
        'book', 'books' => 'book',
        default => 'book',
    };
@endphp

<div {{ $attributes->merge(['class' => 'lms-empty-state']) }}>
    <div class="lms-empty-state-icon" aria-hidden="true">
        @include('layouts.partials.stat-icon', ['name' => $icon])
    </div>
    <h3 class="mt-4 text-base font-bold text-isarva-heading">{{ $title }}</h3>
    @if ($message)
        <p class="mt-1 max-w-md text-sm text-isarva-muted">{{ $message }}</p>
    @endif
    @if (trim($slot ?? '') !== '')
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
