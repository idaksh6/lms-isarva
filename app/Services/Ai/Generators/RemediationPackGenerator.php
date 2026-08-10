<?php

namespace App\Services\Ai\Generators;

class RemediationPackGenerator extends AbstractGenerator
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
FEATURE:remediation_pack
You are an LMS teaching copilot for lecturers. Risk rules are already computed — do not invent new risk scores.
Return JSON only with keys:
- why (string)
- agenda (array of {type, title, notes}) where type is one of support|mentoring|extra_class|strategy|improvement
- study_brief (string)
- quiz (array of exactly 5 objects: {prompt, options:[{label}], correct} with 4 options and correct as 1-based index)
- feedback_starter (string|null)
Be practical, concise, and course-specific.
PROMPT,
            ],
            [
                'role' => 'user',
                'content' => 'Build a remediation pack from this LMS context JSON: '.json_encode($context),
            ],
        ];

        $result = $this->askJson($messages);
        $data = $result['data'];

        $data['agenda'] = array_values(array_slice(
            array_filter($data['agenda'] ?? [], fn ($row) => is_array($row) && filled($row['title'] ?? null)),
            0,
            5
        ));

        $data['quiz'] = $this->normalizeQuiz($data['quiz'] ?? []);

        return [
            'data' => $data,
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
        ];
    }

    /**
     * @param  mixed  $quiz
     * @return list<array{prompt: string, options: list<array{label: string}>, correct: int}>
     */
    private function normalizeQuiz(mixed $quiz): array
    {
        if (! is_array($quiz)) {
            return [];
        }

        $out = [];
        foreach (array_slice($quiz, 0, 5) as $item) {
            if (! is_array($item) || blank($item['prompt'] ?? null)) {
                continue;
            }
            $options = collect($item['options'] ?? [])
                ->map(fn ($opt) => ['label' => is_array($opt) ? (string) ($opt['label'] ?? '') : (string) $opt])
                ->filter(fn ($opt) => $opt['label'] !== '')
                ->take(4)
                ->values()
                ->all();

            if (count($options) < 2) {
                continue;
            }

            $correct = (int) ($item['correct'] ?? 1);
            $correct = max(1, min(count($options), $correct));

            $out[] = [
                'prompt' => (string) $item['prompt'],
                'options' => $options,
                'correct' => $correct,
            ];
        }

        return $out;
    }
}
