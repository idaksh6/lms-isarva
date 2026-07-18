@props([
    'course',
    'active' => null,
])

@php
    $active = $active ?? match (true) {
        request()->routeIs('courses.edit') => 'edit',
        request()->routeIs('courses.enrollments.*') => 'enrollments',
        request()->routeIs('courses.assignments.create') => 'assignment',
        request()->routeIs('courses.assessments.*', 'assessments.*') => 'assessments',
        request()->routeIs('courses.materials.*', 'course-materials.*') => 'materials',
        request()->routeIs('courses.sessions.*', 'class-sessions.*') => 'sessions',
        default => 'show',
    };

    $course->loadMissing('lecturer');

    $studentCount = $course->students_count
        ?? ($course->relationLoaded('students') ? $course->students->count() : $course->students()->count());

    $assignmentCount = $course->assignments_count
        ?? ($course->relationLoaded('assignments') ? $course->assignments->count() : $course->assignments()->count());
@endphp

<article {{ $attributes->merge(['class' => 'lms-course-hero']) }}>
    <div class="lms-course-hero-header">
        <div class="lms-course-hero-copy">
            <p class="lms-hero-eyebrow">{{ $course->code }}</p>
            @if ($active === 'show')
                <h2 class="lms-hero-title">{{ $course->title }}</h2>
            @else
                <h2 class="lms-hero-title">
                    <a href="{{ route('courses.show', $course) }}" class="text-inherit hover:text-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/40 rounded-sm">
                        {{ $course->title }}
                    </a>
                </h2>
            @endif

            <div class="lms-course-hero-details">
                <span class="lms-hero-pill">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 19.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                    {{ $course->lecturer->name }}
                </span>
                <span class="lms-hero-pill">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-5.216-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                    {{ $studentCount }} {{ $studentCount === 1 ? 'student' : 'students' }}
                </span>
                <span class="lms-hero-pill">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    {{ $assignmentCount }} {{ $assignmentCount === 1 ? 'assignment' : 'assignments' }}
                </span>
            </div>
        </div>
    </div>

    @can('update', $course)
        <nav class="lms-course-hero-actions" aria-label="Course actions">
            <a href="{{ route('courses.edit', $course) }}"
               @class([
                   'lms-course-hero-tab',
                   'lms-course-hero-tab--active' => $active === 'edit',
               ])
               @if ($active === 'edit') aria-current="page" @endif>
                Edit course
            </a>
            <a href="{{ route('courses.enrollments.edit', $course) }}"
               @class([
                   'lms-course-hero-tab',
                   'lms-course-hero-tab--active' => $active === 'enrollments',
               ])
               @if ($active === 'enrollments') aria-current="page" @endif>
                Manage students
            </a>
            <a href="{{ route('courses.materials.index', $course) }}"
               @class([
                   'lms-course-hero-tab',
                   'lms-course-hero-tab--active' => $active === 'materials',
               ])
               @if ($active === 'materials') aria-current="page" @endif>
                Class materials
            </a>
            <a href="{{ route('courses.assessments.index', $course) }}"
               @class([
                   'lms-course-hero-tab',
                   'lms-course-hero-tab--active' => $active === 'assessments',
               ])
               @if ($active === 'assessments') aria-current="page" @endif>
                Assessments
            </a>
            <a href="{{ route('courses.assignments.create', $course) }}"
               @class([
                   'lms-course-hero-tab',
                   'lms-course-hero-tab--active' => $active === 'assignment',
               ])
               @if ($active === 'assignment') aria-current="page" @endif>
                New assignment
            </a>
            <a href="{{ route('courses.sessions.index', $course) }}"
               @class([
                   'lms-course-hero-tab',
                   'lms-course-hero-tab--active' => $active === 'sessions',
               ])
               @if ($active === 'sessions') aria-current="page" @endif>
                Class schedule
            </a>
        </nav>
    @endcan
</article>
