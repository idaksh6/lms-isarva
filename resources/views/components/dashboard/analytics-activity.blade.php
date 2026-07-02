@props(['items' => []])

@php
    $max = max(1, collect($items)->max('value') ?? 0);
@endphp

@if (count($items) > 0)
    <div class="corp-chart-bars" role="list" aria-label="Weekly submission activity">
        @foreach ($items as $item)
            @php
                $height = $item['value'] > 0
                    ? max(8, (int) round(($item['value'] / $max) * 100))
                    : 8;
                $countLabel = $item['value'] === 1 ? '1 submission' : $item['value'].' submissions';
            @endphp
            <div class="corp-chart-bar group" role="listitem">
                <div class="corp-chart-bar-track">
                    <div class="corp-chart-bar-fill {{ $item['value'] > 0 ? '' : 'corp-chart-bar-fill--empty' }}" style="height: {{ $height }}%"></div>
                    <span class="corp-chart-tooltip" role="tooltip">{{ $countLabel }}</span>
                </div>
                <span class="corp-chart-bar-label">{{ $item['label'] }}</span>
            </div>
        @endforeach
    </div>
@else
    <p class="corp-chart-empty">No activity yet.</p>
@endif
