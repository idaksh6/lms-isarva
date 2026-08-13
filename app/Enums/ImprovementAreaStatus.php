<?php

namespace App\Enums;

enum ImprovementAreaStatus: string
{
    case Open = 'open';
    case Improving = 'improving';
    case Achieved = 'achieved';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Improving => 'Improving',
            self::Achieved => 'Achieved',
        };
    }
}
