<?php

namespace App\Support;

class LmsTheme
{
    /** @var array<string, string> */
    private const SIDEBAR_VAR_MAP = [
        'bg' => 'bg',
        'bgSoft' => 'bg-soft',
        'border' => 'border',
        'text' => 'text',
        'textMuted' => 'text-muted',
        'hover' => 'hover',
        'activeBorder' => 'active-border',
        'activeBg' => 'active-bg',
        'accent' => 'accent',
    ];

    /** @var array<string, string> */
    private const LEGACY_KEYS = [
        'corporate' => 'amber',
    ];

    public static function defaultKey(): string
    {
        return (string) config('lms-themes.default', 'classic');
    }

    /** @return array<string, array{name: string, description?: string, colors: array<string, string>, sidebar: array<string, string>}> */
    public static function all(): array
    {
        return config('lms-themes.themes', []);
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function isValid(?string $key): bool
    {
        if (! is_string($key)) {
            return false;
        }

        if (array_key_exists($key, self::all())) {
            return true;
        }

        return array_key_exists($key, self::LEGACY_KEYS);
    }

    public static function normalizeKey(?string $key): ?string
    {
        if (! is_string($key) || $key === '') {
            return null;
        }

        if (array_key_exists($key, self::all())) {
            return $key;
        }

        return self::LEGACY_KEYS[$key] ?? null;
    }

    /**
     * @return array{key: string, name: string, description: string, colors: array<string, string>, sidebar: array<string, string>}
     */
    public static function resolve(?string $key = null): array
    {
        $normalized = self::normalizeKey($key);
        $resolvedKey = $normalized ?? self::defaultKey();
        $theme = self::all()[$resolvedKey];

        return [
            'key' => $resolvedKey,
            'name' => $theme['name'],
            'description' => $theme['description'] ?? '',
            'colors' => $theme['colors'],
            'sidebar' => $theme['sidebar'],
        ];
    }

    public static function cssVariables(?string $key = null): string
    {
        $theme = self::resolve($key);
        $lines = [];

        foreach ($theme['colors'] as $shade => $rgb) {
            $lines[] = "--brand-{$shade}: {$rgb};";
        }

        foreach ($theme['sidebar'] as $name => $rgb) {
            $lines[] = '--sidebar-'.self::SIDEBAR_VAR_MAP[$name].": {$rgb};";
        }

        return implode("\n        ", $lines);
    }

    public static function previewSwatch(string $key): string
    {
        $theme = self::resolve($key);

        return sprintf(
            'linear-gradient(90deg, rgb(%s) 0%%, rgb(%s) 42%%, rgb(%s) 58%%, rgb(%s) 100%%)',
            $theme['sidebar']['bg'],
            $theme['sidebar']['bgSoft'],
            $theme['colors']['600'],
            $theme['colors']['700'],
        );
    }

    /** @return array<string, array{name: string, colors: array<string, string>, sidebar: array<string, string>}> */
    public static function clientPayload(): array
    {
        return collect(self::all())
            ->map(fn (array $theme) => [
                'name' => $theme['name'],
                'colors' => $theme['colors'],
                'sidebar' => $theme['sidebar'],
            ])
            ->all();
    }

    /** @param array<string, string> $sidebar */
    public static function sidebarCssVariableLines(array $sidebar): array
    {
        $lines = [];

        foreach ($sidebar as $name => $rgb) {
            $lines[] = ['--sidebar-'.self::SIDEBAR_VAR_MAP[$name], $rgb];
        }

        return $lines;
    }
}
