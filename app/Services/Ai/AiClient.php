<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiClient
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{content: string, prompt_tokens: int|null, completion_tokens: int|null}
     */
    public function chat(array $messages, ?int $maxTokens = null): array
    {
        $driver = config('ai.driver', 'fake');

        if ($driver === 'fake' || ! config('ai.api_key')) {
            return $this->fakeResponse($messages);
        }

        return $this->openaiResponse($messages, $maxTokens);
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{content: string, prompt_tokens: int|null, completion_tokens: int|null}
     */
    private function openaiResponse(array $messages, ?int $maxTokens): array
    {
        $response = Http::baseUrl((string) config('ai.base_url'))
            ->withToken((string) config('ai.api_key'))
            ->timeout((int) config('ai.timeout', 60))
            ->acceptJson()
            ->post('/chat/completions', [
                'model' => config('ai.model'),
                'messages' => $messages,
                'temperature' => (float) config('ai.temperature', 0.4),
                'max_tokens' => $maxTokens ?? (int) config('ai.max_tokens', 2500),
                'response_format' => ['type' => 'json_object'],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'AI provider error: '.$response->json('error.message', $response->body())
            );
        }

        $content = (string) data_get($response->json(), 'choices.0.message.content', '');

        if ($content === '') {
            throw new RuntimeException('AI provider returned an empty response.');
        }

        return [
            'content' => $content,
            'prompt_tokens' => data_get($response->json(), 'usage.prompt_tokens'),
            'completion_tokens' => data_get($response->json(), 'usage.completion_tokens'),
        ];
    }

    /**
     * Deterministic fixtures keyed by a marker in the system prompt.
     *
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{content: string, prompt_tokens: int|null, completion_tokens: int|null}
     */
    private function fakeResponse(array $messages): array
    {
        $blob = collect($messages)->pluck('content')->implode("\n");

        $payload = match (true) {
            str_contains($blob, 'FEATURE:remediation_pack') => [
                'why' => 'This student is behind on overdue work and scoring below the course average. Focus mentoring on catching up submissions and reinforcing core assignment skills.',
                'agenda' => [
                    [
                        'type' => 'mentoring',
                        'title' => 'Catch-up mentoring session',
                        'notes' => 'Review missing overdue items, agree a 7-day submission plan, and check understanding of the latest assignment brief.',
                    ],
                    [
                        'type' => 'strategy',
                        'title' => 'Weekly check-in strategy',
                        'notes' => 'Schedule a short weekly progress check until missing work is cleared and average rises above 60%.',
                    ],
                    [
                        'type' => 'extra_class',
                        'title' => 'Remedial practice block',
                        'notes' => 'Run a short extra session on the weakest topic inferred from low quiz/assignment scores.',
                    ],
                ],
                'study_brief' => 'Revisit the published course notes for the current module. Practice one small task from each material, then attempt the remediation quiz. Priority: complete overdue assignments first.',
                'quiz' => [
                    [
                        'prompt' => 'What should you do first when you have overdue assignments?',
                        'options' => [
                            ['label' => 'Ignore them and wait for the next topic'],
                            ['label' => 'Prioritise completing the oldest overdue work'],
                            ['label' => 'Only message classmates'],
                            ['label' => 'Unenroll from the course'],
                        ],
                        'correct' => 2,
                    ],
                    [
                        'prompt' => 'A score below 60% on graded work usually signals that you should:',
                        'options' => [
                            ['label' => 'Skip feedback'],
                            ['label' => 'Review materials and request mentoring'],
                            ['label' => 'Stop submitting'],
                            ['label' => 'Change courses immediately'],
                        ],
                        'correct' => 2,
                    ],
                    [
                        'prompt' => 'Participation in the LMS is improved by:',
                        'options' => [
                            ['label' => 'Submitting work and engaging in Q&A'],
                            ['label' => 'Only logging in once a month'],
                            ['label' => 'Deleting drafts'],
                            ['label' => 'Avoiding quizzes'],
                        ],
                        'correct' => 1,
                    ],
                    [
                        'prompt' => 'When a submission needs revision, the best next step is:',
                        'options' => [
                            ['label' => 'Resubmit without reading feedback'],
                            ['label' => 'Apply the lecturer feedback and resubmit'],
                            ['label' => 'Leave it forever'],
                            ['label' => 'Submit a blank file'],
                        ],
                        'correct' => 2,
                    ],
                    [
                        'prompt' => 'Low quiz scores are most useful when you:',
                        'options' => [
                            ['label' => 'Treat them as a signal to revisit notes'],
                            ['label' => 'Hide them from your dashboard'],
                            ['label' => 'Retake without studying'],
                            ['label' => 'Blame the LMS'],
                        ],
                        'correct' => 1,
                    ],
                ],
                'feedback_starter' => 'Please address the missing/late work and revise against the assignment brief. Focus on clarity, completeness, and applying the feedback from your last review before resubmitting.',
            ],
            str_contains($blob, 'FEATURE:quiz_from_materials') => [
                'questions' => [
                    [
                        'prompt' => 'Which statement best reflects the core idea in the selected materials?',
                        'options' => [
                            ['label' => 'Concepts can be skipped if examples look hard'],
                            ['label' => 'Understanding definitions helps apply the workflow correctly'],
                            ['label' => 'Only memorise the last slide'],
                            ['label' => 'Ignore datasets entirely'],
                        ],
                        'correct' => 2,
                    ],
                    [
                        'prompt' => 'When working with course datasets, you should first:',
                        'options' => [
                            ['label' => 'Delete unused columns without checking'],
                            ['label' => 'Inspect schema and sample rows'],
                            ['label' => 'Train a model immediately'],
                            ['label' => 'Export without reviewing'],
                        ],
                        'correct' => 2,
                    ],
                    [
                        'prompt' => 'A good study approach for this module is to:',
                        'options' => [
                            ['label' => 'Read notes, try an example, then self-check'],
                            ['label' => 'Only watch unrelated videos'],
                            ['label' => 'Skip practice tasks'],
                            ['label' => 'Submit empty work early'],
                        ],
                        'correct' => 1,
                    ],
                ],
            ],
            str_contains($blob, 'FEATURE:feedback_draft') => [
                'feedback' => 'Solid effort overall. Strengthen the submission by addressing the brief more directly, showing clearer reasoning steps, and fixing any incomplete sections before the next review. Use the course notes to tighten weak spots called out above.',
            ],
            str_contains($blob, 'FEATURE:material_summary') => [
                'summary' => [
                    'This material introduces key module concepts and expected workflows.',
                    'Focus on definitions, worked examples, and any attached dataset or reference links.',
                    'Use it to prepare for upcoming assignments and quizzes on the same topic.',
                ],
            ],
            str_contains($blob, 'FEATURE:student_doubt') => [
                'answer' => 'Based on your course materials, start from the published notes for this topic, then try the worked example before asking a follow-up. If something is still unclear, quote the exact step that confuses you in Q&A.',
                'citations' => [
                    'Course notes',
                    'Module reference material',
                ],
                'refused' => false,
            ],
            default => [
                'message' => 'AI fake driver response.',
            ],
        };

        return [
            'content' => json_encode($payload, JSON_THROW_ON_ERROR),
            'prompt_tokens' => 120,
            'completion_tokens' => 220,
        ];
    }
}
