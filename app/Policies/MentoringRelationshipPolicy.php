<?php

namespace App\Policies;

use App\Models\MentoringRelationship;
use App\Models\User;

class MentoringRelationshipPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isLecturer() || $user->isStudent();
    }

    public function view(User $user, MentoringRelationship $relationship): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isLecturer()) {
            return $relationship->mentor_id === $user->id
                || ($relationship->course_id && $relationship->course?->lecturer_id === $user->id);
        }

        return $user->isStudent() && $relationship->mentee_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isLecturer();
    }

    public function update(User $user, MentoringRelationship $relationship): bool
    {
        return $this->manage($user, $relationship);
    }

    public function delete(User $user, MentoringRelationship $relationship): bool
    {
        return $user->isAdmin() || $relationship->mentor_id === $user->id;
    }

    public function manageRecords(User $user, MentoringRelationship $relationship): bool
    {
        return $this->manage($user, $relationship);
    }

    private function manage(User $user, MentoringRelationship $relationship): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isLecturer() && $relationship->mentor_id === $user->id;
    }
}
