<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Submitted = 'submitted';
    case Late = 'late';
    case NeedsRevision = 'needs_revision';
    case Reviewed = 'reviewed';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Late => 'Late',
            self::NeedsRevision => 'Needs revision',
            self::Reviewed => 'Reviewed',
        };
    }
}
