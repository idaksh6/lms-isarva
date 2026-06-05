<?php

namespace App\Policies;

use App\Enums\SubmissionStatus;
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

        if (! $user->enrolledCourses()->where('courses.id', $assignment->course_id)->exists()) {
            return false;
        }

        $existing = $assignment->submissions()->where('user_id', $user->id)->first();

        return $existing === null || $existing->status === SubmissionStatus::NeedsRevision;
    }

    public function review(User $user, Submission $submission): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isLecturer()
            && $submission->assignment->course->lecturer_id === $user->id;
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
