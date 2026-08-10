<?php

namespace App\Enums;

enum AiGenerationStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';
    case Failed = 'failed';
    case Accepted = 'accepted';
    case Discarded = 'discarded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Ready => 'Ready',
            self::Failed => 'Failed',
            self::Accepted => 'Accepted',
            self::Discarded => 'Discarded',
        };
    }
}
