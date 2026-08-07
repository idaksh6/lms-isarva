<?php

namespace App\Policies;

use App\Models\StudentSupportAction;
use App\Models\User;

class StudentSupportActionPolicy
{
    public function delete(User $user, StudentSupportAction $action): bool
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
}
