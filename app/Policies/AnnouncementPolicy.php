<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user, ?Course $course = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($course === null) {
            return false;
        }

        return $user->isLecturer() && $course->lecturer_id === $user->id;
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $announcement->user_id;
    }
}
