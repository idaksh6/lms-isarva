<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;

class AnswerPolicy
{
    public function create(User $user, Question $question): bool
    {
        return true;
    }

    public function delete(User $user, Answer $answer): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $answer->user_id;
    }

    public function accept(User $user, Answer $answer): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $answer->question->user_id;
    }
}
