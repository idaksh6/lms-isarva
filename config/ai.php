<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Teaching Copilot
    |--------------------------------------------------------------------------
    |
    | driver: "fake" returns deterministic fixtures (tests / no API key).
    |         "openai" calls an OpenAI-compatible chat completions API.
    |
    */
    'enabled' => (bool) env('AI_ENABLED', true),

    'driver' => env('AI_DRIVER', env('AI_API_KEY') ? 'openai' : 'fake'),

    'api_key' => env('AI_API_KEY'),

    'base_url' => rtrim(env('AI_BASE_URL', 'https://api.openai.com/v1'), '/'),

    'model' => env('AI_MODEL', 'gpt-4o-mini'),

    'timeout' => (int) env('AI_TIMEOUT', 60),

    'max_tokens' => (int) env('AI_MAX_TOKENS', 2500),

    'temperature' => (float) env('AI_TEMPERATURE', 0.4),

    /*
    | Run generation in the HTTP request instead of the queue.
    | Recommended when queue workers are not always running locally.
    */
    'sync' => (bool) env('AI_SYNC', true),

    'rate_limit_per_hour' => (int) env('AI_RATE_LIMIT_PER_HOUR', 30),

    'features' => [
        'remediation_pack' => (bool) env('AI_FEATURE_REMEDIATION_PACK', true),
        'quiz_from_materials' => (bool) env('AI_FEATURE_QUIZ_FROM_MATERIALS', true),
        'feedback_draft' => (bool) env('AI_FEATURE_FEEDBACK_DRAFT', true),
        'material_summary' => (bool) env('AI_FEATURE_MATERIAL_SUMMARY', true),
        'student_doubt' => (bool) env('AI_FEATURE_STUDENT_DOUBT', true),
    ],
];
