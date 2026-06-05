<?php

namespace App\Support;

class ModuleHeroImage
{
    /** @var array<string, array{file: string, alt: string}> */
    private const MODULES = [
        'assignments' => ['file' => 'assignments-hub.jpg', 'alt' => 'Assignments workspace'],
        'submissions' => ['file' => 'submissions-inbox.jpg', 'alt' => 'Student submissions inbox'],
        'gradebook' => ['file' => 'gradebook.jpg', 'alt' => 'Gradebook overview'],
        'reports' => ['file' => 'reports.jpg', 'alt' => 'Learning analytics reports'],
        'announcements' => ['file' => 'announcements.jpg', 'alt' => 'Course announcements'],
        'calendar' => ['file' => 'calendar.jpg', 'alt' => 'Assignment calendar'],
        'settings' => ['file' => 'settings.jpg', 'alt' => 'Account settings'],
        'help' => ['file' => 'help.jpg', 'alt' => 'Help and support'],
        'courses' => ['file' => 'assignments-hub.jpg', 'alt' => 'Courses learning workspace'],
        'users' => ['file' => 'settings.jpg', 'alt' => 'User accounts and roles'],
    ];

    public static function url(string $module): string
    {
        $meta = self::MODULES[$module] ?? self::MODULES['assignments'];

        return asset('images/module-heroes/'.$meta['file']);
    }

    public static function alt(string $module): string
    {
        $meta = self::MODULES[$module] ?? self::MODULES['assignments'];

        return $meta['alt'];
    }
}
