@props(['submission'])

@php
    $statusTone = match ($submission->status) {
        \App\Enums\SubmissionStatus::Submitted => 'is-submitted',
        \App\Enums\SubmissionStatus::Late => 'is-late',
        \App\Enums\SubmissionStatus::NeedsRevision => 'is-needs-revision',
        \App\Enums\SubmissionStatus::Reviewed => 'is-reviewed',
        default => 'is-submitted',
    };
@endphp

<tr {{ $attributes->merge(['class' => 'corp-table-row group']) }}>
    <td class="corp-table-cell">
        <span class="corp-table-title">{{ $submission->student->name }}</span>
        @if ($submission->student->student_id)
            <span class="corp-table-meta">{{ $submission->student->student_id }}</span>
        @endif
    </td>
    <td class="corp-table-cell">
        <span class="corp-table-title">{{ $submission->assignment->title }}</span>
        <span class="corp-table-meta">{{ $submission->assignment->course->code }}</span>
    </td>
    <td class="corp-table-cell corp-table-cell--muted">
        {{ $submission->submitted_at->format('M j, Y') }}
        <span class="corp-table-meta">{{ $submission->submitted_at->format('g:i A') }}</span>
    </td>
    <td class="corp-table-cell">
        <x-status-badge :status="$submission->status" :tone="$statusTone" />
    </td>
    <td class="corp-table-cell corp-table-col--md">
        @if ($submission->isGraded())
            <x-lms.grade-badge :score="$submission->score" :letter="$submission->letter_grade" size="sm" />
        @else
            <span class="text-xs text-isarva-muted">—</span>
        @endif
    </td>
    <td class="corp-table-cell corp-table-cell--action">
        <a href="{{ route('submissions.show', $submission) }}" class="corp-table-action">View</a>
    </td>
</tr>
