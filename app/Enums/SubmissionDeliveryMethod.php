<?php

namespace App\Enums;

enum SubmissionDeliveryMethod: string
{
    case File = 'file';
    case Link = 'link';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::File => 'File upload',
            self::Link => 'Cloud link (Google Drive, Dropbox, etc.)',
            self::Both => 'File upload or cloud link',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::File => 'File upload',
            self::Link => 'Cloud link',
            self::Both => 'File or link',
        };
    }
}
