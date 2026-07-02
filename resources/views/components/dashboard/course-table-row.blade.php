@props([
    'course',
    'meta' => '',
    'progress' => 0,
])

@php
    $pct = min(100, max(0, (int) $progress));
@endphp

<tr {{ $attributes->merge(['class' => 'corp-table-row group']) }}>
    <td class="corp-table-cell corp-table-cell--code">
        <span class="corp-code-badge">{{ $course->code }}</span>
    </td>
    <td class="corp-table-cell">
        <a href="{{ route('courses.show', $course) }}" class="corp-table-link">
            <span class="corp-table-title">{{ $course->title }}</span>
            @if ($meta)
                <span class="corp-table-meta">{{ $meta }}</span>
            @endif
        </a>
    </td>
    <td class="corp-table-cell corp-table-cell--progress">
        <div class="corp-progress-inline">
            <div class="corp-progress-track corp-progress-track--sm" role="presentation">
                <div class="corp-progress-fill" style="width: {{ $pct }}%"></div>
            </div>
            <span class="corp-progress-pct">{{ $pct }}%</span>
        </div>
    </td>
    <td class="corp-table-cell corp-table-cell--action">
        <a href="{{ route('courses.show', $course) }}" class="corp-table-action">Open</a>
    </td>
</tr>
