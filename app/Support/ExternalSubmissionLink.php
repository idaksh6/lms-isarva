<?php

namespace App\Support;

use Illuminate\Support\Str;

class ExternalSubmissionLink
{
    /** @var list<string> */
    private const ALLOWED_HOSTS = [
        'drive.google.com',
        'docs.google.com',
        'dropbox.com',
        'www.dropbox.com',
        'onedrive.live.com',
        '1drv.ms',
        'sharepoint.com',
    ];

    public static function isAllowed(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);

        foreach (self::ALLOWED_HOSTS as $allowed) {
            if ($host === $allowed || Str::endsWith($host, '.'.$allowed)) {
                return true;
            }
        }

        return false;
    }

    public static function providerLabel(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return match (true) {
            str_contains($host, 'google') => 'Google Drive',
            str_contains($host, 'dropbox') => 'Dropbox',
            str_contains($host, 'onedrive'), str_contains($host, '1drv'), str_contains($host, 'sharepoint') => 'OneDrive',
            default => 'Cloud storage',
        };
    }

    public static function labelFromUrl(string $url, ?string $fallback = null): string
    {
        if ($fallback !== null && $fallback !== '') {
            return $fallback;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (is_string($path) && $path !== '' && $path !== '/') {
            $basename = basename($path);
            if ($basename !== '' && $basename !== 'view' && $basename !== 'edit') {
                return urldecode($basename);
            }
        }

        return self::providerLabel($url).' link';
    }
}
