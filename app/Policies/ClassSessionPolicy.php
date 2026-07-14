<?php

namespace App\Policies;

use App\Models\ClassSession;
use App\Models\User;

class ClassSessionPolicy
{
    public function view(User $user, ClassSession $session): bool
    {
        return app(CoursePolicy::class)->view($user, $session->course);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isLecturer();
    }

    public function update(User $user, ClassSession $session): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isLecturer() && $session->course->lecturer_id === $user->id;
    }

    public function delete(User $user, ClassSession $session): bool
    {
        return $this->update($user, $session);
    }
}
