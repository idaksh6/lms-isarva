<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function delete(User $actor, User $target): bool
    {
        if (! $actor->isAdmin()) {
            return false;
        }

        return $actor->id !== $target->id;
    }

    public function deactivate(User $actor, User $target): bool
    {
        if (! $actor->isAdmin()) {
            return false;
        }

        return $actor->id !== $target->id;
    }
}
