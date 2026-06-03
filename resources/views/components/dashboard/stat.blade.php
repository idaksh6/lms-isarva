@props([
    'label',
    'value',
    'icon' => 'chart',
    'tone' => 'brand', // brand | orange | slate | violet | sky
])

@php
    $tones = [
        'brand' => [
            'accent' => 'dash-stat-accent-brand',
            'icon' => 'bg-brand-100 text-brand-700',
        ],
        'orange' => [
            'accent' => 'dash-stat-accent-orange',
            'icon' => 'bg-orange-100 text-orange-600',
        ],
        'slate' => [
            'accent' => 'dash-stat-accent-slate',
            'icon' => 'bg-slate-100 text-slate-600',
        ],
        'violet' => [
            'accent' => 'dash-stat-accent-violet',
            'icon' => 'bg-violet-100 text-violet-600',
        ],
        'sky' => [
            'accent' => 'dash-stat-accent-sky',
            'icon' => 'bg-sky-100 text-sky-600',
        ],
    ];
    $t = $tones[$tone] ?? $tones['brand'];
@endphp

<div {{ $attributes->merge(['class' => 'dash-stat '.$t['accent']]) }}>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-isarva-muted">{{ $label }}</p>
            <p class="mt-1.5 text-2xl font-bold tracking-tight text-isarva-heading sm:text-[1.65rem]">{{ $value }}</p>
        </div>
        <span class="flex h-10 w-10 items-center justify-center rounded-lg {{ $t['icon'] }}">
            @include('layouts.partials.stat-icon', ['name' => $icon])
        </span>
    </div>
</div>
