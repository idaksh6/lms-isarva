@props([
    'course',
    'progress' => 0,
])

@php
    $enrolled = $course->students_count ?? null;
    $assignments = $course->assignments_count ?? null;
    $completionPct = min(100, max(0, (int) $progress));
    $activityPct = $assignments !== null ? min(100, $assignments * 20) : ($enrolled > 0 ? 35 : 10);
    $lecturerInitial = $course->relationLoaded('lecturer') && $course->lecturer
        ? strtoupper(substr($course->lecturer->name, 0, 1))
        : null;
@endphp

<a href="{{ route('courses.show', $course) }}" {{ $attributes->merge(['class' => 'lms-course-card group']) }}>
    <div class="lms-course-card-cover">
        <x-dashboard.course-cover :course="$course" :large="true" :animated="false" variant="corporate" />
        <div class="lms-course-card-cover-badges">
            @if ($enrolled !== null)
                <span class="lms-course-card-enrolled ml-auto">{{ $enrolled }} enrolled</span>
            @endif
        </div>
        @if (isset($course->is_active) && ! $course->is_active)
            <span class="lms-course-card-draft">Inactive</span>
        @endif
    </div>

    <div class="lms-course-card-body">
        <div class="lms-course-card-head">
            <h3 class="lms-course-card-title">{{ $course->title }}</h3>

            @if (($course->relationLoaded('lecturer') && $course->lecturer) || $assignments !== null)
                <div class="lms-course-card-subrow">
                    @if ($course->relationLoaded('lecturer') && $course->lecturer)
                        <div class="lms-course-card-lecturer">
                            <span class="lms-course-card-lecturer-avatar">{{ $lecturerInitial }}</span>
                            <span class="lms-course-card-meta">{{ $course->lecturer->name }}</span>
                        </div>
                    @else
                        <span class="lms-course-card-meta">Course details</span>
                    @endif

                    @if ($assignments !== null)
                        <span class="lms-course-card-tag">
                            {{ $assignments }} {{ $assignments === 1 ? 'assignment' : 'assignments' }}
                        </span>
                    @elseif ($course->relationLoaded('lecturer') && $course->lecturer)
                        <span class="lms-course-card-tag lms-course-card-tag--muted">Instructor</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="lms-course-card-metrics">
            <div class="lms-course-card-metric">
                <x-lms.mini-ring label="Progress" :value="$completionPct" tone="brand" />
            </div>
            <div class="lms-course-card-metric">
                <x-lms.mini-ring label="Activity" :value="$activityPct" tone="slate" />
            </div>
        </div>
    </div>

    <div class="lms-course-card-footer">
        <span class="lms-course-card-action">
            Open course
            <span aria-hidden="true">→</span>
        </span>
    </div>
</a>
