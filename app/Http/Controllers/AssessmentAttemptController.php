<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssessmentAttemptController extends Controller
{
    public function create(Assessment $assessment): View|RedirectResponse
    {
        if ($assessment->isGoogleForm()) {
            $this->authorize('view', $assessment);

            return redirect()
                ->route('assessments.show', $assessment)
                ->withErrors(['attempt' => 'This assessment uses a Google Form. Open the form link from the assessment page.']);
        }

        $this->authorize('attempt', $assessment);

        $assessment->load(['course', 'questions.options']);

        $attempt = $assessment->attempts()->firstOrCreate([
            'user_id' => request()->user()->id,
        ], [
            'max_score' => $assessment->maxScore(),
        ]);

        if ($attempt->isSubmitted()) {
            return redirect()->route('assessments.result', $assessment);
        }

        return view('assessments.attempt', compact('assessment', 'attempt'));
    }

    public function store(Request $request, Assessment $assessment): RedirectResponse
    {
        if ($assessment->isGoogleForm()) {
            $this->authorize('view', $assessment);

            return redirect()
                ->route('assessments.show', $assessment)
                ->withErrors(['attempt' => 'This assessment uses a Google Form and cannot be submitted in the LMS.']);
        }

        $this->authorize('attempt', $assessment);

        $assessment->load('questions.options');

        $rules = ['answers' => ['required', 'array', 'size:'.$assessment->questions->count()]];
        foreach ($assessment->questions as $question) {
            $rules['answers.'.$question->id] = ['required', 'integer', 'exists:assessment_options,id'];
        }

        $validated = $request->validate($rules);

        $attempt = $assessment->attempts()->firstOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'max_score' => $assessment->maxScore(),
        ]);

        if ($attempt->isSubmitted()) {
            return redirect()->route('assessments.result', $assessment);
        }

        DB::transaction(function () use ($assessment, $attempt, $validated): void {
            $score = 0;

            foreach ($assessment->questions as $question) {
                $optionId = (int) $validated['answers'][$question->id];
                $option = $question->options->firstWhere('id', $optionId);
                $isCorrect = $option?->is_correct ?? false;

                if ($isCorrect) {
                    $score += $assessment->marks_per_question;
                }

                $attempt->answers()->updateOrCreate(
                    ['assessment_question_id' => $question->id],
                    [
                        'assessment_option_id' => $optionId,
                        'is_correct' => $isCorrect,
                    ]
                );
            }

            $attempt->update([
                'score' => $score,
                'max_score' => $assessment->maxScore(),
                'submitted_at' => now(),
            ]);
        });

        return redirect()
            ->route('assessments.result', $assessment)
            ->with('success', 'Assessment submitted.');
    }

    public function result(Assessment $assessment): View
    {
        $this->authorize('view', $assessment);

        $attempt = $assessment->attempts()
            ->where('user_id', request()->user()->id)
            ->whereNotNull('submitted_at')
            ->firstOrFail();

        return view('assessments.result', compact('assessment', 'attempt'));
    }
}
