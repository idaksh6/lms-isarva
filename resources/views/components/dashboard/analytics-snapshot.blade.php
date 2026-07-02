@props(['segments' => []])

@php
    $total = collect($segments)->sum('value');
    $isCompact = count($segments) === 3;
@endphp

<div
    class="corp-chart-snapshot {{ $isCompact ? 'corp-chart-snapshot--compact' : '' }}"
    role="list"
    aria-label="Review snapshot"
>
    @foreach ($segments as $segment)
        @php
            $tip = $total > 0 && $segment['value'] > 0
                ? $segment['label'].': '.$segment['value'].' ('.$segment['pct'].'%)'
                : $segment['label'].': '.$segment['value'];
        @endphp
        <div
            class="corp-chart-tile corp-chart-tile--{{ $segment['tone'] }} {{ $segment['value'] === 0 ? 'is-empty' : '' }} group"
            role="listitem"
            title="{{ $tip }}"
        >
            <span class="corp-chart-tile-val">{{ $segment['value'] }}</span>
            <span class="corp-chart-tile-label">{{ $segment['label'] }}</span>
            @if ($total > 0 && $segment['value'] > 0)
                <span class="corp-chart-tile-tip" role="tooltip">{{ $segment['pct'] }}%</span>
            @endif
        </div>
    @endforeach
</div>
