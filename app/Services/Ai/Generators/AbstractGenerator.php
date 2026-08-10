<?php

namespace App\Services\Ai\Generators;

use App\Services\Ai\AiClient;
use JsonException;
use RuntimeException;

abstract class AbstractGenerator
{
    public function __construct(protected AiClient $client) {}

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{data: array<string, mixed>, prompt_tokens: int|null, completion_tokens: int|null}
     */
    protected function askJson(array $messages): array
    {
        $result = $this->client->chat($messages);

        try {
            $data = json_decode($result['content'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('AI returned invalid JSON: '.$e->getMessage(), 0, $e);
        }

        if (! is_array($data)) {
            throw new RuntimeException('AI returned a non-object JSON payload.');
        }

        return [
            'data' => $data,
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
        ];
    }
}
