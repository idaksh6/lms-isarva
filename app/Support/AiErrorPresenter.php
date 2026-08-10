<?php

namespace App\Support;

class AiErrorPresenter
{
    /**
     * @return array{title: string, message: string, action_label: string|null, action_url: string|null, tone: string}
     */
    public static function present(?string $raw): array
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return [
                'title' => 'Generation failed',
                'message' => 'Something went wrong while generating the AI draft. Try again in a moment.',
                'action_label' => null,
                'action_url' => null,
                'tone' => 'error',
            ];
        }

        $lower = strtolower($raw);

        if (str_contains($lower, 'no credits') || str_contains($lower, 'billing') || str_contains($lower, 'quota') || str_contains($lower, 'insufficient')) {
            return [
                'title' => 'API credits exhausted',
                'message' => 'Your OpenAI account has no remaining credits. Add billing credits, or switch the AI driver to Fake in Settings for demos.',
                'action_label' => 'Open OpenAI billing',
                'action_url' => 'https://platform.openai.com/settings/organization/billing/',
                'tone' => 'warning',
            ];
        }

        if (str_contains($lower, 'invalid api key') || str_contains($lower, 'incorrect api key') || str_contains($lower, 'authentication')) {
            return [
                'title' => 'Invalid API key',
                'message' => 'The stored AI API key was rejected. Update it in Settings → AI Teaching Copilot.',
                'action_label' => 'Open AI settings',
                'action_url' => route('settings.index'),
                'tone' => 'error',
            ];
        }

        if (str_contains($lower, 'rate limit') || str_contains($lower, 'too many')) {
            return [
                'title' => 'Rate limit reached',
                'message' => 'The AI provider asked us to slow down. Wait a minute and generate again.',
                'action_label' => null,
                'action_url' => null,
                'tone' => 'warning',
            ];
        }

        // Strip noisy provider prefixes for display
        $message = preg_replace('/^AI provider error:\s*/i', '', $raw) ?: $raw;
        $message = preg_replace('#\s*https?://\S+#', '', $message);
        $message = trim($message) ?: 'The AI provider returned an error.';

        return [
            'title' => 'AI provider error',
            'message' => $message,
            'action_label' => null,
            'action_url' => null,
            'tone' => 'error',
        ];
    }
}
