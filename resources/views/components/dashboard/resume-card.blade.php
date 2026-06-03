@props([
    'course' => null,
    'progress' => 0,
    'subtitle' => null,
])

@php
    use App\Support\CourseIllustration;

    $motif = $course ? CourseIllustration::variantFor($course->code) : 'books';
@endphp

@if ($course)
    <a href="{{ route('courses.show', $course) }}" class="quyl-resume-card group">
        <div class="quyl-resume-card-art">
            <x-lms.illustration :variant="$motif" />
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-isarva-muted">{{ $subtitle ?? 'Continue learning' }}</p>
            <p class="truncate text-sm font-bold text-isarva-heading group-hover:text-brand-600">{{ $course->title }}</p>
            <p class="text-xs text-isarva-muted">{{ $course->code }}@if($course->relationLoaded('lecturer') && $course->lecturer) · {{ $course->lecturer->name }}@endif</p>
            <div class="mt-2 flex items-center gap-2">
                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-600 transition-all" style="width: {{ min(100, max(0, (int) $progress)) }}%"></div>
                </div>
                <span class="text-[11px] font-bold text-brand-600">{{ min(100, max(0, (int) $progress)) }}%</span>
            </div>
        </div>
        <span class="quyl-resume-btn">
            Open
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        </span>
    </a>
@endif
