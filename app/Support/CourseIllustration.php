<?php

namespace App\Support;

class CourseIllustration
{
    /**
     * Course-themed banner art (no text). Stable per course code.
     *
     * books — reading / modules
     * notebook — assignments & notes
     * analytics — data & progress
     * laptop — online learning / labs
     */
    public const VARIANTS = ['books', 'notebook', 'analytics', 'laptop'];

    public static function variantFor(string $courseCode): string
    {
        return self::VARIANTS[crc32($courseCode) % count(self::VARIANTS)];
    }
}
