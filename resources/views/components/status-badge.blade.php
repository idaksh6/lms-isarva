@props([
    'status',
    'tone' => null,
])

@php
    $toneClass = $tone ?? match ($status) {
        'submitted', \App\Enums\SubmissionStatus::Submitted => 'is-submitted',
        'late', \App\Enums\SubmissionStatus::Late => 'is-late',
        'needs_revision', \App\Enums\SubmissionStatus::NeedsRevision => 'is-needs-revision',
        'reviewed', \App\Enums\SubmissionStatus::Reviewed => 'is-reviewed',
        default => 'is-default',
    };

    $label = $status instanceof \App\Enums\SubmissionStatus ? $status->label() : ucfirst((string) $status);
@endphp

<span {{ $attributes->merge(['class' => 'lms-status-badge '.$toneClass]) }}>{{ $label }}</span>
