<?php

namespace App\Enums;

enum AssessmentType: string
{
    case Manual = 'manual';
    case GoogleForm = 'google_form';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::GoogleForm => 'Google Form',
        };
    }
}
