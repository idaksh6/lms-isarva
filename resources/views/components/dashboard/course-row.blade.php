@props([
    'course',
    'meta' => '',
    'progress' => 0,
    'variant' => null,
    'barFrom' => 'from-brand-400',
    'barTo' => 'to-brand-600',
])

@php
    use App\Support\CourseIllustration;

    $motif = ($variant && in_array($variant, CourseIllustration::VARIANTS, true))
        ? $variant
        : CourseIllustration::variantFor($course->code);
@endphp

<a href="{{ route('courses.show', $course) }}" {{ $attributes->merge(['class' => 'quyl-course-row group']) }}>
    <div class="quyl-course-row-cover">
        <x-lms.illustration :variant="$motif" />
    </div>
    <div class="min-w-0 flex-1">
        <p class="font-semibold text-isarva-heading group-hover:text-brand-600">{{ $course->title }}</p>
        <p class="text-xs text-isarva-muted">{{ $meta }}</p>
        <div class="mt-2 flex items-center gap-2">
            <div class="h-1.5 max-w-xs flex-1 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-gradient-to-r {{ $barFrom }} {{ $barTo }} transition-all" style="width: {{ min(100, max(0, (int) $progress)) }}%"></div>
            </div>
            <span class="text-[10px] font-bold text-isarva-muted">{{ min(100, max(0, (int) $progress)) }}%</span>
        </div>
    </div>
    <span class="text-sm font-semibold text-brand-600">Open →</span>
</a>
