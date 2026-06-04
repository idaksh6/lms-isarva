@props([
    'course',
    'meta' => '',
    'progress' => 0,
])

@php
    $pct = min(100, max(0, (int) $progress));
@endphp

<a href="{{ route('courses.show', $course) }}" {{ $attributes->merge(['class' => 'dashboard-course-card group']) }}>
    <div class="dashboard-course-card-thumb">
        <x-dashboard.course-cover :course="$course" />
        <span class="dashboard-course-card-code">{{ $course->code }}</span>
    </div>
    <div class="dashboard-course-card-body">
        <div class="dashboard-course-card-top">
            <h3 class="dashboard-course-card-title">{{ $course->title }}</h3>
            <span class="dashboard-course-card-pct">{{ $pct }}%</span>
        </div>
        <p class="dashboard-course-card-meta">{{ $meta }}</p>
        <div class="dashboard-course-card-track" role="presentation">
            <div class="dashboard-course-card-fill" style="width: {{ $pct }}%"></div>
        </div>
    </div>
    <span class="dashboard-course-card-arrow" aria-hidden="true">→</span>
</a>
