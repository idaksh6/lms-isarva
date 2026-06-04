<?php

namespace App\Support;

use App\Models\Course;

class CourseCoverImage
{
    /** @var list<string> */
    public const KEYS = [
        'machine-learning',
        'data-engineering',
        'data-analytics',
        'programming-lab',
        'campus-learning',
    ];

    public static function keyFor(Course|string $courseOrCode, ?string $title = null): string
    {
        if ($courseOrCode instanceof Course) {
            $code = $courseOrCode->code;
            $title = $courseOrCode->title;
        } else {
            $code = $courseOrCode;
        }

        $haystack = strtoupper($code).' '.strtolower((string) $title);

        if (preg_match('/\b(502|engineering|pipeline|etl)\b/i', $haystack)) {
            return 'data-engineering';
        }

        if (preg_match('/\b(501|machine|learning|regression|cluster|neural)\b/i', $haystack)) {
            return 'machine-learning';
        }

        if (preg_match('/\b(analytic|evaluation|statistic|visualization|chart)\b/i', $haystack)) {
            return 'data-analytics';
        }

        if (preg_match('/\b(lab|python|programming|notebook|code)\b/i', $haystack)) {
            return 'programming-lab';
        }

        return self::KEYS[crc32($code) % count(self::KEYS)];
    }

    public static function key(Course|string $courseOrCode, ?string $title = null): string
    {
        return $courseOrCode instanceof Course
            ? self::keyFor($courseOrCode)
            : self::keyFor($courseOrCode, $title);
    }

    public static function url(Course|string $courseOrCode, ?string $title = null): string
    {
        return asset('images/course-covers/'.self::key($courseOrCode, $title).'.jpg');
    }

    public static function urlRetina(Course|string $courseOrCode, ?string $title = null): string
    {
        return asset('images/course-covers/'.self::key($courseOrCode, $title).'@2x.jpg');
    }

    public static function srcset(Course|string $courseOrCode, ?string $title = null): string
    {
        $standard = self::url($courseOrCode, $title);
        $retina = self::urlRetina($courseOrCode, $title);

        return "{$standard} 1200w, {$retina} 1536w";
    }

    public static function alt(Course $course): string
    {
        return match (self::keyFor($course)) {
            'machine-learning' => 'Machine learning and data modelling workspace',
            'data-engineering' => 'Data engineering and cloud pipelines',
            'data-analytics' => 'Analytics dashboards and charts',
            'programming-lab' => 'Programming lab with laptops',
            default => 'University data science learning environment',
        };
    }
}
