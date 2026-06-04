@props(['assignment'])

<div {{ $attributes->merge(['class' => 'lms-assignment-meta-strip']) }}>
    <div class="lms-assignment-meta-item">
        <span class="lms-assignment-meta-label">Course</span>
        <span class="lms-assignment-meta-value">
            <a href="{{ route('courses.show', $assignment->course) }}" class="font-semibold text-brand-600 hover:text-brand-700">
                {{ $assignment->course->code }}
            </a>
            <span class="mt-0.5 block text-sm font-normal text-slate-500">{{ $assignment->course->title }}</span>
        </span>
    </div>

    <div class="lms-assignment-meta-item">
        <span class="lms-assignment-meta-label">Due date</span>
        <span class="lms-assignment-meta-value">
            @if ($assignment->due_at)
                <span @class(['font-semibold text-slate-900', 'text-rose-600' => $assignment->isOverdue()])>
                    {{ $assignment->due_at->format('M j, Y · g:i A') }}
                </span>
                @if ($assignment->isOverdue())
                    <span class="mt-0.5 block text-xs font-semibold text-rose-600">Past due</span>
                @endif
            @else
                <span class="text-slate-500">No due date</span>
            @endif
        </span>
    </div>

    <div class="lms-assignment-meta-item">
        <span class="lms-assignment-meta-label">Status</span>
        <span class="lms-assignment-meta-value">
            @if ($assignment->is_published)
                <span class="lms-badge bg-brand-50 text-brand-800">Published</span>
            @else
                <span class="lms-badge bg-slate-100 text-slate-600">Draft</span>
            @endif
        </span>
    </div>

    @if (auth()->user()->isLecturer() || auth()->user()->isAdmin())
        <div class="lms-assignment-meta-item">
            <span class="lms-assignment-meta-label">Submissions</span>
            <span class="lms-assignment-meta-value font-semibold text-slate-900">
                {{ $assignment->submissions_count ?? $assignment->submissions->count() }}
            </span>
        </div>
    @endif
</div>
