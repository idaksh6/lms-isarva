<?php

namespace App\Enums;

enum SupportActionType: string
{
    case Support = 'support';
    case Mentoring = 'mentoring';
    case ExtraClass = 'extra_class';
    case Strategy = 'strategy';
    case Improvement = 'improvement';

    public function label(): string
    {
        return match ($this) {
            self::Support => 'Support provided',
            self::Mentoring => 'Mentoring conducted',
            self::ExtraClass => 'Extra class',
            self::Strategy => 'Strategy applied',
            self::Improvement => 'Improvement note',
        };
    }
}
