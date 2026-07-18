@props([
    'student',
    'attempt' => null,
    'maxScore',
])

@php
    $submitted = $attempt?->isSubmitted() ?? false;
    $inProgress = $attempt && ! $submitted;
@endphp

<article class="lms-assessment-result-row">
    <div class="lms-assessment-result-main">
        <span class="lms-student-avatar lms-student-avatar--lg">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
        <div class="min-w-0 flex-1">
            <p class="lms-assessment-result-name">{{ $student->name }}</p>
            <p class="lms-assessment-result-meta">
                @if ($student->student_id)
                    <span>ID {{ $student->student_id }}</span>
                @else
                    <span>{{ $student->email }}</span>
                @endif
            </p>
        </div>
    </div>

    <div class="lms-assessment-result-status">
        @if ($submitted)
            <span class="lms-badge bg-emerald-50 text-emerald-700">Submitted</span>
            <p class="lms-assessment-result-score">{{ $attempt->score }} / {{ $attempt->max_score ?: $maxScore }}</p>
            <p class="lms-assessment-result-date">{{ $attempt->submitted_at->format('M j, Y · g:i A') }}</p>
        @elseif ($inProgress)
            <span class="lms-badge bg-amber-50 text-amber-800">In progress</span>
            <p class="lms-assessment-result-date text-isarva-muted">Started but not submitted</p>
        @else
            <span class="lms-badge bg-slate-100 text-slate-600">Not started</span>
        @endif
    </div>
</article>
