@props([
    'course',
    'meta' => '',
    'progress' => 0,
])

<a href="{{ route('courses.show', $course) }}" {{ $attributes->merge(['class' => 'dashboard-course-row group']) }}>
    <div class="dashboard-course-row-thumb">
        <x-dashboard.course-cover :course="$course" :animated="false" variant="corporate" />
    </div>
    <div class="min-w-0 flex-1">
        <p class="font-semibold text-isarva-heading group-hover:text-brand-600">{{ $course->title }}</p>
        <p class="text-xs text-isarva-muted">{{ $meta }}</p>
        <div class="mt-2 flex items-center gap-2">
            <div class="dashboard-course-row-track" role="presentation">
                <div class="dashboard-course-row-fill" style="width: {{ min(100, max(0, (int) $progress)) }}%"></div>
            </div>
            <span class="text-[10px] font-bold text-isarva-muted">{{ min(100, max(0, (int) $progress)) }}%</span>
        </div>
    </div>
    <span class="dashboard-course-row-arrow" aria-hidden="true">→</span>
</a>
