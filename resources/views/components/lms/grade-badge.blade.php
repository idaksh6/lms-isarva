@props(['score' => null, 'letter' => null, 'size' => 'md'])

@php
    use App\Support\GradeHelper;
    $letter = $letter ?? GradeHelper::letterFromScore($score !== null ? (float) $score : null);
    $colorClass = GradeHelper::colorClass($letter);
    $sizeClass = $size === 'sm' ? 'lms-grade-badge--sm' : 'lms-grade-badge--md';
@endphp

<div {{ $attributes->merge(['class' => 'lms-grade-badge '.$colorClass.' '.$sizeClass]) }}>
    @if ($letter)
        <span class="lms-grade-badge-letter">{{ $letter }}</span>
        @if ($score !== null)
            <span class="lms-grade-badge-score">{{ number_format((float) $score, 0) }}%</span>
        @endif
    @else
        <span class="lms-grade-badge-empty">—</span>
    @endif
</div>
