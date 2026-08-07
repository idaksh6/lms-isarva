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
    $canPublish = ! $course->is_active && auth()->user()?->can('update', $course);
@endphp

<article {{ $attributes->merge(['class' => 'lms-course-card group']) }}>
    <a href="{{ route('courses.show', $course) }}" class="lms-course-card-link">
        <div class="lms-course-card-cover">
            <x-dashboard.course-cover :course="$course" :large="true" :animated="false" variant="corporate" />
            <div class="lms-course-card-cover-badges">
                @if ($enrolled !== null)
                    <span class="lms-course-card-enrolled ml-auto">{{ $enrolled }} enrolled</span>
                @endif
            </div>
            @if (! $course->is_active)
                <span class="lms-course-card-draft">Not published</span>
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
    </a>

    <div class="lms-course-card-footer">
        @if ($canPublish)
            <form
                method="POST"
                action="{{ route('courses.publish', $course) }}"
                class="lms-course-card-publish"
                x-data
                x-on:submit.prevent="
                    Swal.fire({
                        title: {{ \Illuminate\Support\Js::from('Publish “'.$course->title.'” to students?') }},
                        text: 'Once enabled, this course cannot be disabled again from Edit course.',
                        icon: 'question',
                        showCancelButton: true,
                        focusCancel: true,
                        confirmButtonText: 'Yes, publish',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#64748b',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $el.submit();
                        }
                    })
                "
            >
                @csrf
                <button type="submit" class="lms-course-card-publish-btn">
                    Enable &amp; publish to students
                </button>
            </form>
        @else
            <a href="{{ route('courses.show', $course) }}" class="lms-course-card-action">
                Open course
                <span aria-hidden="true">→</span>
            </a>
        @endif
    </div>
</article>
