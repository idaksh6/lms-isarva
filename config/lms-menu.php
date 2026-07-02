<?php

return [
    'items' => [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Courses', 'route' => 'courses.index', 'icon' => 'book', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users', 'roles' => ['admin']],
        ['label' => 'Assignments', 'route' => 'assignments.index', 'icon' => 'clipboard', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Submissions', 'route' => 'submissions.index', 'icon' => 'inbox', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Gradebook', 'route' => 'gradebook.index', 'icon' => 'chart', 'roles' => ['admin', 'lecturer']],
        ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'chart-bar', 'roles' => ['admin', 'lecturer']],
        ['label' => 'Announcements', 'route' => 'announcements.index', 'icon' => 'megaphone', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Q&A', 'route' => 'questions.index', 'icon' => 'chat', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Calendar', 'route' => 'calendar.index', 'icon' => 'calendar', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'user', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'cog', 'roles' => ['admin', 'lecturer', 'student']],
        ['label' => 'Help', 'route' => 'help.index', 'icon' => 'help', 'roles' => ['admin', 'lecturer', 'student']],
    ],
];
