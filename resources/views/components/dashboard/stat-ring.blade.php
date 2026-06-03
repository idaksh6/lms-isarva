@props([
    'label',
    'value',
    'sub' => null,
    'percent' => 0,
    'tone' => 'brand',
    'icon' => null,
])

@php
    $pct = min(100, max(0, (int) $percent));
    $iconName = $icon ?? match ($tone) {
        'orange' => 'clipboard',
        'rose' => 'lecturer',
        'sky' => 'users',
        default => 'book',
    };
    $toneSurface = match ($tone) {
        'orange' => 'border-orange-300 bg-orange-100',
        'rose' => 'border-rose-300 bg-rose-100',
        'sky' => 'border-sky-300 bg-sky-100',
        default => 'border-emerald-300 bg-emerald-100',
    };
@endphp

<div {{ $attributes->merge(['class' => 'quyl-stat-card quyl-stat-card--'.$tone.' '.$toneSurface]) }}>
    <div class="quyl-stat-card-head">
        <span class="quyl-stat-card-icon" aria-hidden="true">
            @include('layouts.partials.stat-icon', ['name' => $iconName])
        </span>
        <span class="quyl-stat-card-pct">{{ $pct }}%</span>
    </div>

    <p class="quyl-stat-card-value">{{ $value }}</p>
    <p class="quyl-stat-card-label">{{ $label }}</p>
    @if ($sub)
        <p class="quyl-stat-card-sub">{{ $sub }}</p>
    @endif

    <div class="quyl-stat-card-track" role="presentation">
        <div class="quyl-stat-card-fill" style="width: {{ $pct }}%"></div>
    </div>
</div>
