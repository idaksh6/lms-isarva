<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function delete(User $actor, User $target): bool
    {
        if (! $actor->isAdmin() && ! $actor->isLecturer()) {
            return false;
        }

        return $actor->id !== $target->id;
    }

    public function deactivate(User $actor, User $target): bool
    {
        if (! $actor->isAdmin() && ! $actor->isLecturer()) {
            return false;
        }

        return $actor->id !== $target->id;
    }
}
