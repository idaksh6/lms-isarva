<?php

namespace App\Policies;

use App\Models\StudentSupportAction;
use App\Models\StudentSupportCase;
use App\Models\User;

class StudentSupportCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isLecturer();
    }

    public function view(User $user, StudentSupportCase $case): bool
    {
        return $this->managesCourse($user, $case);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isLecturer();
    }

    public function update(User $user, StudentSupportCase $case): bool
    {
        return $this->managesCourse($user, $case);
    }

    public function delete(User $user, StudentSupportCase $case): bool
    {
        return $this->managesCourse($user, $case);
    }

    public function addAction(User $user, StudentSupportCase $case): bool
    {
        return $this->managesCourse($user, $case);
    }

    public function deleteAction(User $user, StudentSupportAction $action): bool
    {
        $case = $action->supportCase;

        if (! $case) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isLecturer()
            && $case->course->lecturer_id === $user->id;
    }

    private function managesCourse(User $user, StudentSupportCase $case): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isLecturer() && $case->course->lecturer_id === $user->id;
    }
}
