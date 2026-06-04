@props(['assignment', 'course'])

@php
    $isStaff = auth()->user()->isLecturer() || auth()->user()->isAdmin();
@endphp

<header class="lms-assignment-header">
    <div class="lms-assignment-header-accent" aria-hidden="true"></div>
    <div class="lms-assignment-header-body">
        <div class="lms-assignment-header-copy">
            <p class="lms-assignment-header-eyebrow">
                <a href="{{ route('courses.show', $course) }}" class="lms-assignment-header-course-link">
                    {{ $course->code }}
                </a>
                <span aria-hidden="true">·</span>
                <span class="text-slate-500">{{ $course->title }}</span>
            </p>
            <h1 class="lms-assignment-header-title">{{ $assignment->title }}</h1>
        </div>

        @unless ($isStaff)
            <div class="lms-assignment-header-aside">
                @if ($assignment->due_at)
                    <span @class([
                        'lms-assignment-header-due',
                        'lms-assignment-header-due--overdue' => $assignment->isOverdue(),
                    ])>
                        Due {{ $assignment->due_at->format('M j, Y · g:i A') }}
                    </span>
                @endif
                @if (! $assignment->is_published)
                    <span class="lms-badge bg-slate-100 text-slate-600">Draft</span>
                @endif
            </div>
        @endunless
    </div>
</header>
