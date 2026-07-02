<?php

namespace App\Enums;

enum SubmissionSource: string
{
    case File = 'file';
    case Link = 'link';

    public function label(): string
    {
        return match ($this) {
            self::File => 'Uploaded file',
            self::Link => 'Cloud link',
        };
    }
}
