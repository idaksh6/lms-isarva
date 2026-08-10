<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AiSettings
{
    public const KEYS = [
        'ai.enabled',
        'ai.driver',
        'ai.api_key',
        'ai.base_url',
        'ai.model',
    ];

    /**
     * Overlay DB-managed AI settings onto config('ai.*').
     * Env/.config remain the fallback when a key is unset in the DB.
     */
    public static function applyToConfig(): void
    {
        if (! self::tableReady()) {
            return;
        }

        try {
            $rows = AppSetting::query()
                ->whereIn('key', self::KEYS)
                ->get()
                ->keyBy('key');
        } catch (Throwable) {
            return;
        }

        if ($rows->isEmpty()) {
            return;
        }

        $overlay = [];

        if ($rows->has('ai.enabled')) {
            $overlay['ai.enabled'] = filter_var($rows->get('ai.enabled')->plainValue(), FILTER_VALIDATE_BOOLEAN);
        }

        if ($rows->has('ai.driver')) {
            $driver = $rows->get('ai.driver')->plainValue() ?: 'fake';
            $overlay['ai.driver'] = in_array($driver, ['fake', 'openai'], true) ? $driver : 'fake';
        }

        if ($rows->has('ai.api_key')) {
            $overlay['ai.api_key'] = $rows->get('ai.api_key')->plainValue();
        }

        if ($rows->has('ai.base_url')) {
            $base = rtrim((string) ($rows->get('ai.base_url')->plainValue() ?: config('ai.base_url')), '/');
            $overlay['ai.base_url'] = $base;
        }

        if ($rows->has('ai.model')) {
            $overlay['ai.model'] = $rows->get('ai.model')->plainValue() ?: config('ai.model');
        }

        if ($overlay !== []) {
            // If admin chose openai but cleared the key, fall back to fake at runtime.
            if (($overlay['ai.driver'] ?? config('ai.driver')) === 'openai'
                && blank($overlay['ai.api_key'] ?? config('ai.api_key'))) {
                $overlay['ai.driver'] = 'fake';
            }

            config($overlay);
        }
    }

    /**
     * @return array{
     *     enabled: bool,
     *     driver: string,
     *     api_key_set: bool,
     *     api_key_hint: string|null,
     *     base_url: string,
     *     model: string,
     *     source: string
     * }
     */
    public static function formState(): array
    {
        self::applyToConfig();

        $key = (string) (config('ai.api_key') ?: '');
        $dbHasKey = self::tableReady() && AppSetting::query()->where('key', 'ai.api_key')->whereNotNull('value')->exists();

        return [
            'enabled' => (bool) config('ai.enabled'),
            'driver' => (string) config('ai.driver', 'fake'),
            'api_key_set' => $key !== '',
            'api_key_hint' => $key !== '' ? self::maskKey($key) : null,
            'base_url' => (string) config('ai.base_url'),
            'model' => (string) config('ai.model'),
            'source' => $dbHasKey || AppSetting::query()->whereIn('key', self::KEYS)->exists()
                ? 'database'
                : 'environment',
        ];
    }

    public static function save(array $data): void
    {
        AppSetting::put('ai.enabled', ! empty($data['enabled']) ? '1' : '0');
        AppSetting::put('ai.driver', $data['driver'] ?? 'fake');
        AppSetting::put('ai.base_url', rtrim((string) ($data['base_url'] ?? ''), '/'));
        AppSetting::put('ai.model', (string) ($data['model'] ?? 'gpt-4o-mini'));

        if (! empty($data['clear_api_key'])) {
            AppSetting::forget('ai.api_key');
        } elseif (filled($data['api_key'] ?? null)) {
            AppSetting::put('ai.api_key', (string) $data['api_key'], secret: true);
        }

        self::applyToConfig();
    }

    public static function maskKey(string $key): string
    {
        $key = trim($key);
        if (strlen($key) <= 8) {
            return str_repeat('•', max(4, strlen($key)));
        }

        return str_repeat('•', 8).substr($key, -4);
    }

    private static function tableReady(): bool
    {
        try {
            return Schema::hasTable('app_settings');
        } catch (Throwable) {
            return false;
        }
    }
}
