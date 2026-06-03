<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LmsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@lms.test'],
            [
                'name' => 'LMS Administrator',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );

        $lecturer = User::query()->updateOrCreate(
            ['email' => 'lecturer@lms.test'],
            [
                'name' => 'Dr. Priya Sharma',
                'password' => Hash::make('password'),
                'role' => UserRole::Lecturer,
                'email_verified_at' => now(),
            ]
        );

        $students = collect();
        for ($i = 1; $i <= 10; $i++) {
            $studentId = 'DS2024'.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            // Match by student_id so re-seeding works after email changes (e.g. @christlms.test → @lms.test)
            $students->push(User::query()->updateOrCreate(
                ['student_id' => $studentId],
                [
                    'name' => "Student {$i}",
                    'email' => "student{$i}@lms.test",
                    'password' => Hash::make('password'),
                    'role' => UserRole::Student,
                    'email_verified_at' => now(),
                ]
            ));
        }

        $course = Course::query()->updateOrCreate(
            ['code' => 'DS501'],
            [
                'title' => 'Machine Learning Foundations',
                'description' => 'Core ML concepts, supervised learning, and model evaluation for the Data Science programme.',
                'lecturer_id' => $lecturer->id,
                'is_active' => true,
            ]
        );

        $course->students()->syncWithoutDetaching($students->pluck('id'));

        $course2 = Course::query()->updateOrCreate(
            ['code' => 'DS502'],
            [
                'title' => 'Data Engineering & Pipelines',
                'description' => 'ETL, data warehousing, and pipeline design with modern tools.',
                'lecturer_id' => $lecturer->id,
                'is_active' => true,
            ]
        );

        $course2->students()->syncWithoutDetaching($students->take(5)->pluck('id'));

        Assignment::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Linear Regression Lab'],
            [
                'created_by' => $lecturer->id,
                'instructions' => "Implement linear regression from scratch on the provided dataset.\n\nSubmit your Jupyter notebook (.ipynb) with clear markdown explanations and visualizations.",
                'due_at' => now()->addDays(7),
                'is_published' => true,
            ]
        );

        Assignment::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Model Evaluation Report'],
            [
                'created_by' => $lecturer->id,
                'instructions' => 'Write a 2-page PDF comparing at least three classification metrics on the class dataset.',
                'due_at' => now()->addDays(14),
                'is_published' => true,
            ]
        );

        $this->command?->info('Demo accounts (password: password)');
        $this->command?->info('  Admin:    admin@lms.test');
        $this->command?->info('  Lecturer: lecturer@lms.test');
        $this->command?->info('  Student:  student1@lms.test … student10@lms.test');
    }
}
