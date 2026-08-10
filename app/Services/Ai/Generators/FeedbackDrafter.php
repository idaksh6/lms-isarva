<?php

namespace App\Services\Ai\Generators;

class FeedbackDrafter extends AbstractGenerator
{
    /**
     * @param  array<string, mixed>  $context
     * @return array{data: array<string, mixed>, prompt_tokens: int|null, completion_tokens: int|null}
     */
    public function generate(array $context): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => <<<'PROMPT'
FEATURE:feedback_draft
Draft concise lecturer feedback for a student assignment submission.
Return JSON only: {"feedback":"..."}.
Be constructive, specific to the brief, and suitable to paste into an LMS feedback box.
Do not invent grades.
PROMPT,
            ],
            [
                'role' => 'user',
                'content' => 'Submission context: '.json_encode($context),
            ],
        ];

        $result = $this->askJson($messages);

        return [
            'data' => [
                'feedback' => (string) ($result['data']['feedback'] ?? ''),
            ],
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
        ];
    }
}
