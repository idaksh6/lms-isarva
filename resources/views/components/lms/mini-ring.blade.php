@props(['label', 'value' => 0, 'tone' => 'brand'])

@php
    $pct = min(100, max(0, (int) $value));
    $circumference = 2 * 3.14159 * 18;
    $offset = $circumference * (1 - $pct / 100);
    $colors = match ($tone) {
        'orange' => ['ring' => 'text-orange-500', 'trail' => 'text-orange-200'],
        'sky' => ['ring' => 'text-sky-500', 'trail' => 'text-sky-200'],
        default => ['ring' => 'text-brand-600', 'trail' => 'text-brand-200'],
    };
@endphp

<div class="lms-mini-ring">
    <div class="lms-mini-ring-chart">
    <svg class="h-11 w-11 -rotate-90" viewBox="0 0 44 44" aria-hidden="true">
        <circle cx="22" cy="22" r="18" fill="none" stroke-width="4" class="{{ $colors['trail'] }}" stroke="currentColor"/>
        <circle cx="22" cy="22" r="18" fill="none" stroke-width="4" stroke-linecap="round"
                class="{{ $colors['ring'] }}" stroke="currentColor"
                stroke-dasharray="{{ $circumference }}"
                stroke-dashoffset="{{ $offset }}"/>
    </svg>
    <span class="lms-mini-ring-value">{{ $pct }}%</span>
    </div>
    <span class="lms-mini-ring-label">{{ $label }}</span>
</div>
