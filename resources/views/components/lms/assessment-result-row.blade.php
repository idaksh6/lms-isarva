@props([
    'student',
    'attempt' => null,
    'maxScore',
    'assessment' => null,
    'editable' => false,
    'bulk' => false,
])

@php
    $submitted = $attempt?->isSubmitted() ?? false;
    $inProgress = $attempt && ! $submitted;
    $oldScores = old('scores', []);
    $inputValue = array_key_exists((string) $student->id, $oldScores)
        ? $oldScores[(string) $student->id]
        : ($attempt?->score);
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
        @if ($editable && $assessment && $bulk)
            <div class="lms-assessment-score-form">
                <label class="sr-only" for="score-{{ $student->id }}">Score for {{ $student->name }}</label>
                <div class="lms-assessment-score-form-row">
                    <input
                        id="score-{{ $student->id }}"
                        type="number"
                        name="scores[{{ $student->id }}]"
                        min="0"
                        max="{{ $maxScore }}"
                        step="1"
                        value="{{ $inputValue }}"
                        class="lms-field-input lms-assessment-score-input"
                        placeholder="—"
                    >
                    <span class="lms-assessment-score-max">/ {{ $maxScore }}</span>
                </div>
            </div>
            @if ($submitted)
                <p class="lms-assessment-result-date">
                    Recorded {{ $attempt->submitted_at->format('M j, Y · g:i A') }}
                </p>
                <button
                    type="submit"
                    form="clear-score-{{ $student->id }}"
                    class="lms-btn-secondary lms-btn-secondary--xs"
                >Clear</button>
            @else
                <span class="lms-badge bg-slate-100 text-slate-600">No score yet</span>
            @endif
        @elseif ($editable && $assessment)
            <form
                method="POST"
                action="{{ route('assessments.scores.update', [$assessment, $student]) }}"
                class="lms-assessment-score-form"
            >
                @csrf
                @method('PUT')
                <label class="sr-only" for="score-{{ $student->id }}">Score for {{ $student->name }}</label>
                <div class="lms-assessment-score-form-row">
                    <input
                        id="score-{{ $student->id }}"
                        type="number"
                        name="score"
                        min="0"
                        max="{{ $maxScore }}"
                        step="1"
                        value="{{ old('score', $attempt?->score) }}"
                        class="lms-field-input lms-assessment-score-input"
                        placeholder="0"
                        required
                    >
                    <span class="lms-assessment-score-max">/ {{ $maxScore }}</span>
                    <button type="submit" class="lms-btn-primary lms-btn-primary--xs">
                        {{ $submitted ? 'Update' : 'Save score' }}
                    </button>
                </div>
            </form>
            @if ($submitted)
                <p class="lms-assessment-result-date">
                    Recorded {{ $attempt->submitted_at->format('M j, Y · g:i A') }}
                </p>
                <form method="POST" action="{{ route('assessments.scores.destroy', [$assessment, $student]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Clear</button>
                </form>
            @else
                <span class="lms-badge bg-slate-100 text-slate-600">No score yet</span>
            @endif
        @elseif ($submitted)
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
