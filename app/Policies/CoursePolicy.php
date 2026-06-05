<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isLecturer() && $course->lecturer_id === $user->id) {
            return true;
        }

        if ($user->isStudent()) {
            return $user->enrolledCourses()->where('courses.id', $course->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isLecturer();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($user->isLecturer() && $course->lecturer_id === $user->id);
    }

    public function manageEnrollments(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($user->isLecturer() && $course->lecturer_id === $user->id);
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($user->isLecturer() && $course->lecturer_id === $user->id);
    }
}
