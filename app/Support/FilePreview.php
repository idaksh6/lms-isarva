<?php

namespace App\Support;

class FilePreview
{
    public static function type(?string $mime, ?string $filename = null): string
    {
        $mime = strtolower((string) $mime);
        $extension = strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));

        if ($mime === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }

        if (str_starts_with($mime, 'image/') || in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'], true)) {
            return 'image';
        }

        if (in_array($mime, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-powerpoint',
        ], true) || in_array($extension, ['doc', 'docx', 'ppt', 'pptx'], true)) {
            return 'office';
        }

        return 'other';
    }

    public static function canPreviewInline(?string $mime, ?string $filename = null): bool
    {
        return in_array(self::type($mime, $filename), ['pdf', 'image', 'office'], true);
    }

    public static function officeEmbedUrl(string $absoluteFileUrl): string
    {
        return 'https://view.officeapps.live.com/op/embed.aspx?src='.urlencode($absoluteFileUrl);
    }
}
