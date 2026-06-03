<?php

return [
    'items' => [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Courses', 'route' => 'courses.index', 'icon' => 'book', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users', 'roles' => ['admin']],
        ['label' => 'Assignments', 'icon' => 'clipboard', 'roles' => ['admin', 'lecturer', 'student'], 'coming_soon' => true],
        ['label' => 'Submissions', 'icon' => 'inbox', 'roles' => ['admin', 'lecturer', 'student'], 'coming_soon' => true],
        ['label' => 'Gradebook', 'icon' => 'chart', 'roles' => ['admin', 'lecturer'], 'coming_soon' => true],
        ['label' => 'Reports', 'icon' => 'chart-bar', 'roles' => ['admin', 'lecturer'], 'coming_soon' => true],
        ['label' => 'Announcements', 'icon' => 'megaphone', 'roles' => ['admin', 'lecturer', 'student'], 'coming_soon' => true],
        ['label' => 'Calendar', 'icon' => 'calendar', 'roles' => ['admin', 'lecturer', 'student'], 'coming_soon' => true],
        ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'user', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Settings', 'icon' => 'cog', 'roles' => ['admin', 'lecturer', 'student'], 'coming_soon' => true],
        ['label' => 'Help', 'icon' => 'help', 'roles' => ['admin', 'lecturer', 'student'], 'coming_soon' => true],
    ],
];
