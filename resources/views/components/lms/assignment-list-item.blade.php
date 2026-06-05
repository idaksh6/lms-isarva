@props([
    'assignment',
    'submission' => null,
    'showCourse' => false,
])

<a href="{{ route('assignments.show', $assignment) }}" {{ $attributes->merge(['class' => 'lms-assignment-card group']) }}>
    <div class="lms-assignment-card-visual">
        <x-lms.illustration variant="assignment" class="lms-banner--assignment-card" />
    </div>
    <div class="lms-assignment-card-body">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h3 class="lms-assignment-card-title">{{ $assignment->title }}</h3>
                @if ($showCourse && $assignment->relationLoaded('course'))
                    <p class="mt-0.5 text-xs font-semibold uppercase tracking-wide text-brand-600">{{ $assignment->course->code }}</p>
                @endif
                @if ($assignment->due_at)
                    <p class="mt-1 text-sm text-isarva-muted">
                        Due {{ $assignment->due_at->format('M j, Y · g:i A') }}
                    </p>
                @else
                    <p class="mt-1 text-sm text-isarva-muted">No due date</p>
                @endif
            </div>
            @if (! $assignment->is_published)
                <span class="lms-badge shrink-0 bg-slate-100 text-slate-600">Draft</span>
            @endif
        </div>

        <div class="lms-assignment-card-footer">
            @if ($submission)
                <x-status-badge :status="$submission->status" />
                <span class="text-sm font-semibold text-brand-600 group-hover:text-brand-700">View submission →</span>
            @elseif (auth()->user()->isStudent() && $assignment->is_published)
                <span class="lms-assignment-card-cta">Submit work →</span>
            @else
                <span class="lms-assignment-card-cta">Open assignment →</span>
            @endif
        </div>
    </div>
</a>
