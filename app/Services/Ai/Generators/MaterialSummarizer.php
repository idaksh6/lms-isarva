<?php

namespace App\Services\Ai\Generators;

class MaterialSummarizer extends AbstractGenerator
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
FEATURE:material_summary
Summarise course material for a lecturer.
Return JSON only: {"summary":["bullet1","bullet2","bullet3"]}.
Use 3-5 short bullets grounded in the provided title/description/excerpt.
PROMPT,
            ],
            [
                'role' => 'user',
                'content' => 'Material context: '.json_encode($context),
            ],
        ];

        $result = $this->askJson($messages);
        $summary = collect($result['data']['summary'] ?? [])
            ->map(fn ($line) => (string) $line)
            ->filter()
            ->take(6)
            ->values()
            ->all();

        return [
            'data' => ['summary' => $summary],
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
        ];
    }
}
