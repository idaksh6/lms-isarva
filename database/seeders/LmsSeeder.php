<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Answer;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Question;
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

        $student1 = $students->first();
        $student2 = $students->get(1);
        $student3 = $students->get(2);

        $q1 = Question::query()->updateOrCreate(
            ['title' => 'What file format should we use for the Linear Regression Lab submission?'],
            [
                'user_id' => $student1->id,
                'course_id' => $course->id,
                'body' => "Hi,\n\nFor the Linear Regression Lab assignment, should we submit a .ipynb notebook only, or is a PDF export also required?\n\nThanks!",
                'is_resolved' => true,
                'created_at' => now()->subDays(5)->setTime(10, 15),
                'updated_at' => now()->subDays(4)->setTime(9, 30),
            ]
        );

        Answer::query()->updateOrCreate(
            ['question_id' => $q1->id, 'user_id' => $lecturer->id, 'body' => "Please submit the Jupyter notebook (.ipynb) as your primary file. A brief PDF summary is optional but appreciated if you include extra visualizations.\n\n— Dr. Sharma"],
            [
                'is_accepted' => true,
                'created_at' => now()->subDays(4)->setTime(14, 20),
                'updated_at' => now()->subDays(4)->setTime(14, 20),
            ]
        );

        Answer::query()->updateOrCreate(
            ['question_id' => $q1->id, 'user_id' => $student2->id, 'body' => 'I submitted only the .ipynb last term and it was accepted. The autograder checks the notebook cells.'],
            [
                'is_accepted' => false,
                'created_at' => now()->subDays(4)->setTime(16, 45),
                'updated_at' => now()->subDays(4)->setTime(16, 45),
            ]
        );

        $q2 = Question::query()->updateOrCreate(
            ['title' => 'How do I reset my portal password?'],
            [
                'user_id' => $student3->id,
                'course_id' => null,
                'body' => "I forgot my LMS password and the reset email hasn't arrived yet. Is there an administrator I should contact?",
                'is_resolved' => true,
                'created_at' => now()->subDays(3)->setTime(8, 40),
                'updated_at' => now()->subDays(2)->setTime(11, 10),
            ]
        );

        Answer::query()->updateOrCreate(
            ['question_id' => $q2->id, 'user_id' => $admin->id, 'body' => "Use the \"Forgot password\" link on the login page — emails can take a few minutes. If nothing arrives after 15 minutes, contact IT support with your registered email address.\n\n— LMS Administrator"],
            [
                'is_accepted' => true,
                'created_at' => now()->subDays(2)->setTime(11, 10),
                'updated_at' => now()->subDays(2)->setTime(11, 10),
            ]
        );

        $q3 = Question::query()->updateOrCreate(
            ['title' => 'Recommended Python libraries for the ML course?'],
            [
                'user_id' => $student2->id,
                'course_id' => $course->id,
                'body' => "Besides scikit-learn and pandas, are we allowed to use XGBoost or LightGBM for the model comparison section?",
                'is_resolved' => false,
                'created_at' => now()->subDays(2)->setTime(13, 5),
                'updated_at' => now()->subDays(2)->setTime(13, 5),
            ]
        );

        Answer::query()->updateOrCreate(
            ['question_id' => $q3->id, 'user_id' => $student1->id, 'body' => 'We used XGBoost in the tutorial last week — the lecturer mentioned it is fine as long as you document why you chose it.'],
            [
                'is_accepted' => false,
                'created_at' => now()->subDays(1)->setTime(18, 22),
                'updated_at' => now()->subDays(1)->setTime(18, 22),
            ]
        );

        Question::query()->updateOrCreate(
            ['title' => 'When does enrollment close for Data Engineering & Pipelines?'],
            [
                'user_id' => $student3->id,
                'course_id' => $course2->id,
                'body' => "I'm interested in joining DS502 next intake. Is there a deadline to request enrollment through the portal?",
                'is_resolved' => false,
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ]
        );

        $this->command?->info('Demo accounts (password: password)');
        $this->command?->info('  Admin:    admin@lms.test');
        $this->command?->info('  Lecturer: lecturer@lms.test');
        $this->command?->info('  Student:  student1@lms.test … student10@lms.test');
    }
}
