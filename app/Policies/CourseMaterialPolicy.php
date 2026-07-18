<?php

namespace App\Policies;

use App\Models\CourseMaterial;
use App\Models\User;

class CourseMaterialPolicy
{
    public function view(User $user, CourseMaterial $material): bool
    {
        return app(CoursePolicy::class)->view($user, $material->course);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isLecturer();
    }

    public function update(User $user, CourseMaterial $material): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isLecturer() && $material->course->lecturer_id === $user->id;
    }

    public function delete(User $user, CourseMaterial $material): bool
    {
        return $this->update($user, $material);
    }
}
