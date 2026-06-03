<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo login credentials (local / testing only)
    |--------------------------------------------------------------------------
    |
    | Shown on the sign-in page when enabled. Set LMS_SHOW_DEMO_CREDENTIALS=false
    | before production, or leave APP_ENV=production (auto-hidden).
    |
    */

    'show_demo_credentials' => env('LMS_SHOW_DEMO_CREDENTIALS', env('APP_ENV') === 'local'),

    'demo_password' => 'password',

    'demo_accounts' => [
        ['role' => 'Admin', 'email' => 'admin@lms.test', 'name' => 'LMS Administrator'],
        ['role' => 'Lecturer', 'email' => 'lecturer@lms.test', 'name' => 'Dr. Priya Sharma'],
        ['role' => 'Student', 'email' => 'student1@lms.test', 'name' => 'Student 1', 'student_id' => 'DS2024001'],
        ['role' => 'Student', 'email' => 'student2@lms.test', 'name' => 'Student 2', 'student_id' => 'DS2024002'],
        ['role' => 'Student', 'email' => 'student3@lms.test', 'name' => 'Student 3', 'student_id' => 'DS2024003'],
        ['role' => 'Student', 'email' => 'student4@lms.test', 'name' => 'Student 4', 'student_id' => 'DS2024004'],
        ['role' => 'Student', 'email' => 'student5@lms.test', 'name' => 'Student 5', 'student_id' => 'DS2024005'],
        ['role' => 'Student', 'email' => 'student6@lms.test', 'name' => 'Student 6', 'student_id' => 'DS2024006'],
        ['role' => 'Student', 'email' => 'student7@lms.test', 'name' => 'Student 7', 'student_id' => 'DS2024007'],
        ['role' => 'Student', 'email' => 'student8@lms.test', 'name' => 'Student 8', 'student_id' => 'DS2024008'],
        ['role' => 'Student', 'email' => 'student9@lms.test', 'name' => 'Student 9', 'student_id' => 'DS2024009'],
        ['role' => 'Student', 'email' => 'student10@lms.test', 'name' => 'Student 10', 'student_id' => 'DS2024010'],
    ],

];
