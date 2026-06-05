<?php

namespace App\Support;

class GradeHelper
{
    public static function letterFromScore(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    public static function labelForLetter(?string $letter): string
    {
        return match ($letter) {
            'A' => 'Excellent',
            'B' => 'Good',
            'C' => 'Satisfactory',
            'D' => 'Pass',
            'F' => 'Fail',
            default => 'Not graded',
        };
    }

    public static function colorClass(?string $letter): string
    {
        return match ($letter) {
            'A' => 'lms-grade--a',
            'B' => 'lms-grade--b',
            'C' => 'lms-grade--c',
            'D' => 'lms-grade--d',
            'F' => 'lms-grade--f',
            default => 'lms-grade--none',
        };
    }
}
