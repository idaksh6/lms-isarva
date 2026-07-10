<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->isStudent() && ! $assignment->is_published) {
            return false;
        }

        return app(CoursePolicy::class)->view($user, $assignment->course);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isLecturer();
    }

    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isLecturer() && $assignment->course->lecturer_id === $user->id;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }
}
