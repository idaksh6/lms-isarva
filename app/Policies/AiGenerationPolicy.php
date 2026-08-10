<?php

namespace App\Policies;

use App\Models\AiGeneration;
use App\Models\User;

class AiGenerationPolicy
{
    public function view(User $user, AiGeneration $generation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $generation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isLecturer() || $user->isStudent();
    }
}
