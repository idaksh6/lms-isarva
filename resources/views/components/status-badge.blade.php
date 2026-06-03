@props(['status'])

@php
    $classes = match ($status) {
        'submitted', \App\Enums\SubmissionStatus::Submitted => 'bg-blue-100 text-blue-800',
        'late', \App\Enums\SubmissionStatus::Late => 'bg-amber-100 text-amber-800',
        'reviewed', \App\Enums\SubmissionStatus::Reviewed => 'bg-emerald-100 text-emerald-800',
        default => 'bg-slate-100 text-slate-700',
    };
    $label = $status instanceof \App\Enums\SubmissionStatus ? $status->label() : ucfirst((string) $status);
@endphp

<span {{ $attributes->merge(['class' => 'lms-badge '.$classes]) }}>{{ $label }}</span>
