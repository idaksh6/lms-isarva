<?php

namespace App\Services\Ai\Generators;

class McqGenerator extends AbstractGenerator
{
    /**
     * @param  array<string, mixed>  $context
     * @return array{data: array<string, mixed>, prompt_tokens: int|null, completion_tokens: int|null}
     */
    public function generate(array $context, int $count): array
    {
        $count = max(1, min(20, $count));

        $messages = [
            [
                'role' => 'system',
                'content' => <<<PROMPT
FEATURE:quiz_from_materials
Generate multiple-choice questions for an LMS assessment from course materials.
Return JSON only: {"questions":[{"prompt":"...","options":[{"label":"..."}],"correct":1}]}
Create exactly {$count} questions. Each question must have exactly 4 options. correct is 1-based.
PROMPT,
            ],
            [
                'role' => 'user',
                'content' => 'Materials context: '.json_encode($context),
            ],
        ];

        $result = $this->askJson($messages);
        $questions = [];

        foreach (array_slice($result['data']['questions'] ?? [], 0, $count) as $item) {
            if (! is_array($item) || blank($item['prompt'] ?? null)) {
                continue;
            }

            $options = collect($item['options'] ?? [])
                ->map(fn ($opt) => ['label' => is_array($opt) ? (string) ($opt['label'] ?? '') : (string) $opt])
                ->filter(fn ($o) => $o['label'] !== '')
                ->take(4)
                ->values()
                ->all();

            while (count($options) < 4) {
                $options[] = ['label' => 'Option '.((string) (count($options) + 1))];
            }

            $correct = (int) ($item['correct'] ?? 1);
            $questions[] = [
                'prompt' => (string) $item['prompt'],
                'options' => $options,
                'correct' => max(1, min(4, $correct)),
            ];
        }

        return [
            'data' => ['questions' => $questions],
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
        ];
    }
}
