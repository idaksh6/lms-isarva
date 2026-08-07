<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isLecturer()) {
            return $assessment->course->lecturer_id === $user->id;
        }

        return $assessment->is_published
            && $assessment->course->is_active
            && $assessment->course->students()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isLecturer();
    }

    public function update(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isLecturer() && $assessment->course->lecturer_id === $user->id;
    }

    public function delete(User $user, Assessment $assessment): bool
    {
        return $this->update($user, $assessment);
    }

    public function attempt(User $user, Assessment $assessment): bool
    {
        if ($assessment->isGoogleForm()) {
            return false;
        }

        if (! $user->isStudent() || ! $this->view($user, $assessment)) {
            return false;
        }

        if ($assessment->due_at && now()->isAfter($assessment->due_at)) {
            return false;
        }

        return ! $assessment->attempts()->where('user_id', $user->id)->whereNotNull('submitted_at')->exists();
    }

    public function manageQuestions(User $user, Assessment $assessment): bool
    {
        return $this->update($user, $assessment);
    }

    public function viewResults(User $user, Assessment $assessment): bool
    {
        return $this->update($user, $assessment);
    }
}
