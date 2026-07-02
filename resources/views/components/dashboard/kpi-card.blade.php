@props([
    'label',
    'value',
    'sub' => null,
    'icon' => 'chart',
])

<div {{ $attributes->merge(['class' => 'corp-kpi']) }}>
    <span class="corp-kpi-icon" aria-hidden="true">
        @include('layouts.partials.stat-icon', ['name' => $icon])
    </span>
    <div class="corp-kpi-content">
        <div class="corp-kpi-primary">
            <p class="corp-kpi-value">{{ $value }}</p>
            <p class="corp-kpi-label">{{ $label }}</p>
        </div>
        @if ($sub)
            <p class="corp-kpi-sub">{{ $sub }}</p>
        @endif
    </div>
</div>
