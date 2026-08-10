<?php

namespace App\Services\Ai\Generators;

class StudentDoubtAssistant extends AbstractGenerator
{
    /**
     * @param  array<string, mixed>  $context
     * @return array{data: array<string, mixed>, prompt_tokens: int|null, completion_tokens: int|null}
     */
    public function generate(array $context, string $question): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => <<<'PROMPT'
FEATURE:student_doubt
You help enrolled students using only the provided course materials and Q&A context.
Return JSON only: {"answer":"...","citations":["Material title",...],"refused":false}.
If the question is off-course or cannot be answered from materials, set refused=true and explain briefly in answer.
Always cite material titles when answering.
PROMPT,
            ],
            [
                'role' => 'user',
                'content' => json_encode([
                    'question' => $question,
                    'context' => $context,
                ]),
            ],
        ];

        $result = $this->askJson($messages);

        return [
            'data' => [
                'answer' => (string) ($result['data']['answer'] ?? ''),
                'citations' => array_values(array_filter(array_map(
                    'strval',
                    $result['data']['citations'] ?? []
                ))),
                'refused' => (bool) ($result['data']['refused'] ?? false),
            ],
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
        ];
    }
}
