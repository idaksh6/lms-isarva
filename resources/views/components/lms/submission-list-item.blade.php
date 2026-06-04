@props([
    'submission',
    'prominent' => false,
])

<div @class([
    'lms-submission-card',
    'lms-submission-card--row' => $prominent,
])>
    <div class="lms-submission-card-main">
        <span class="lms-student-avatar lms-student-avatar--lg">{{ strtoupper(substr($submission->student->name, 0, 1)) }}</span>
        <span class="min-w-0 flex-1">
            <span class="block truncate text-base font-semibold text-slate-900">{{ $submission->student->name }}</span>
            @if ($submission->student->student_id)
                <span class="block truncate text-sm text-slate-500">ID {{ $submission->student->student_id }}</span>
            @endif
            <span class="block text-sm text-slate-500">{{ $submission->submitted_at->format('M j, Y · g:i A') }}</span>
        </span>
    </div>
    <div class="lms-submission-card-actions">
        <x-status-badge :status="$submission->status" />
        <a href="{{ route('submissions.show', $submission) }}" @class([
            'shrink-0 text-sm justify-center',
            'lms-btn-primary' => $prominent,
            'lms-btn-secondary' => ! $prominent,
        ])>
            View submission
        </a>
    </div>
</div>
