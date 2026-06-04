@props([
    'assignment',
    'userSubmission' => null,
])

<dl class="lms-assignment-glance">
    <div class="lms-assignment-glance-row">
        <dt>Course</dt>
        <dd>
            <a href="{{ route('courses.show', $assignment->course) }}" class="lms-meta-list-link">
                {{ $assignment->course->code }}
            </a>
            <span class="lms-meta-list-sub">{{ $assignment->course->title }}</span>
        </dd>
    </div>
    <div class="lms-assignment-glance-row">
        <dt>Due date</dt>
        <dd>
            @if ($assignment->due_at)
                <span @class(['lms-meta-list-value text-base', 'text-rose-600' => $assignment->isOverdue()])>
                    {{ $assignment->due_at->format('M j, Y · g:i A') }}
                </span>
                @if ($assignment->isOverdue())
                    <span class="mt-1 block text-xs font-semibold text-rose-600">Past due</span>
                @endif
            @else
                <span class="text-sm text-slate-500">No due date</span>
            @endif
        </dd>
    </div>
    <div class="lms-assignment-glance-row">
        <dt>Status</dt>
        <dd>
            @if ($assignment->is_published)
                <span class="lms-badge bg-brand-50 text-brand-800">Published</span>
            @else
                <span class="lms-badge bg-slate-100 text-slate-600">Draft</span>
            @endif
        </dd>
    </div>
    @if (auth()->user()->isStudent())
        <div class="lms-assignment-glance-row">
            <dt>Your work</dt>
            <dd>
                @if ($userSubmission)
                    <x-status-badge :status="$userSubmission->status" />
                @elseif ($assignment->is_published)
                    <span class="text-sm font-medium text-amber-700">Not submitted yet</span>
                @else
                    <span class="text-sm text-slate-500">—</span>
                @endif
            </dd>
        </div>
    @endif
</dl>

@if (auth()->user()->isStudent() && $assignment->is_published && ! $userSubmission)
    <a href="{{ route('assignments.submit', $assignment) }}" class="lms-btn-primary mt-5 w-full justify-center">Submit your work</a>
@endif
