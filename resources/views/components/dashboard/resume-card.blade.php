@props([
    'course' => null,
    'progress' => 0,
    'subtitle' => null,
])

@if ($course)
    @php
        $pct = min(100, max(0, (int) $progress));
    @endphp
    <a href="{{ route('courses.show', $course) }}" class="dashboard-featured group">
        <div class="dashboard-featured-thumb">
            <x-dashboard.course-cover :course="$course" />
            <span class="dashboard-featured-code">{{ $course->code }}</span>
        </div>
        <div class="dashboard-featured-body">
            <p class="dashboard-featured-eyebrow">{{ $subtitle ?? 'Continue learning' }}</p>
            <h2 class="dashboard-featured-title">{{ $course->title }}</h2>
            <p class="dashboard-featured-meta">
                @if ($course->relationLoaded('lecturer') && $course->lecturer)
                    {{ $course->lecturer->name }} ·
                @endif
                {{ $pct }}% progress
            </p>
            <div class="dashboard-featured-track" role="presentation">
                <div class="dashboard-featured-fill" style="width: {{ $pct }}%"></div>
            </div>
        </div>
        <span class="dashboard-featured-cta">
            Open
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
        </span>
    </a>
@endif
