<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function create(User $user, Assignment $assignment): bool
    {
        if (! $user->isStudent() || ! $assignment->is_published) {
            return false;
        }

        return $user->enrolledCourses()->where('courses.id', $assignment->course_id)->exists();
    }

    public function view(User $user, Submission $submission): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStudent() && $submission->user_id === $user->id) {
            return true;
        }

        if ($user->isLecturer()) {
            return $submission->assignment->course->lecturer_id === $user->id;
        }

        return false;
    }
}
