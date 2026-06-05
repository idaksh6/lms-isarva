@props([
    'submission',
    'prominent' => false,
])

@php
    $status = $submission->status;
    $statusTone = match ($status) {
        \App\Enums\SubmissionStatus::Submitted => 'is-submitted',
        \App\Enums\SubmissionStatus::Late => 'is-late',
        \App\Enums\SubmissionStatus::NeedsRevision => 'is-needs-revision',
        \App\Enums\SubmissionStatus::Reviewed => 'is-reviewed',
        default => 'is-submitted',
    };
@endphp

<article @class([
    'lms-submission-card',
    'lms-submission-card--row' => $prominent,
])>
    <div class="lms-submission-card-main">
        <span class="lms-student-avatar lms-student-avatar--lg">{{ strtoupper(substr($submission->student->name, 0, 1)) }}</span>
        <div class="lms-submission-card-body min-w-0 flex-1">
            <p class="lms-submission-card-name">{{ $submission->student->name }}</p>
            <p class="lms-submission-card-meta">
                @if ($submission->student->student_id)
                    <span>ID {{ $submission->student->student_id }}</span>
                    <span class="lms-submission-card-dot" aria-hidden="true">·</span>
                @endif
                <span>{{ $submission->assignment->course->code }}</span>
            </p>
            <p class="lms-submission-card-assignment">{{ $submission->assignment->title }}</p>
            <p class="lms-submission-card-date">{{ $submission->submitted_at->format('M j, Y · g:i A') }}</p>
        </div>
    </div>

    <div class="lms-submission-card-actions">
        <div class="lms-submission-card-meta-row">
            <x-status-badge :status="$submission->status" :tone="$statusTone" />
            @if ($submission->isGraded())
                <x-lms.grade-badge :score="$submission->score" :letter="$submission->letter_grade" size="sm" />
            @endif
        </div>
        <a href="{{ route('submissions.show', $submission) }}" class="lms-btn-primary w-full justify-center text-sm">
            View submission
        </a>
    </div>
</article>
