<?php

namespace App\Support\BulkImport;

use App\Models\Assessment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssessmentQuestionImporter
{
    /**
     * Replace assessment MCQs with imported questions and sync question_count.
     *
     * @param  list<array{prompt: string, options: list<array{label: string}>, correct: int}>  $questions
     */
    public function replace(Assessment $assessment, array $questions): int
    {
        if ($assessment->isGoogleForm()) {
            throw ValidationException::withMessages([
                'import_file' => 'Google Form assessments do not store in-LMS quiz questions.',
            ]);
        }

        $count = count($questions);
        if ($count < 1 || $count > 50) {
            throw ValidationException::withMessages([
                'import_file' => 'Import between 1 and 50 questions.',
            ]);
        }

        DB::transaction(function () use ($assessment, $questions, $count): void {
            $assessment->update(['question_count' => $count]);

            $assessment->questions()->each(fn ($q) => $q->options()->delete());
            $assessment->questions()->delete();

            foreach ($questions as $index => $questionData) {
                $question = $assessment->questions()->create([
                    'position' => $index + 1,
                    'prompt' => $questionData['prompt'],
                ]);

                foreach ($questionData['options'] as $optIndex => $optionData) {
                    $question->options()->create([
                        'position' => $optIndex + 1,
                        'label' => $optionData['label'],
                        'is_correct' => ((int) $questionData['correct']) === ($optIndex + 1),
                    ]);
                }
            }
        });

        return $count;
    }
}
