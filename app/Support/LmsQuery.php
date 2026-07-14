<?php

namespace App\Support;

use App\Models\Assignment;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class LmsQuery
{
    public static function assignmentsFor(User $user): Builder
    {
        return match (true) {
            $user->isAdmin() => Assignment::query()
                ->with(['course.lecturer'])
                ->latest(),
            $user->isLecturer() => Assignment::query()
                ->whereHas('course', fn ($q) => $q->where('lecturer_id', $user->id))
                ->with(['course'])
                ->latest(),
            default => Assignment::query()
                ->where('is_published', true)
                ->whereHas('course', fn ($q) => $q
                    ->where('is_active', true)
                    ->whereHas('students', fn ($s) => $s->where('users.id', $user->id)))
                ->with(['course'])
                ->latest(),
        };
    }

    public static function submissionsFor(User $user): Builder
    {
        return match (true) {
            $user->isAdmin() => Submission::query()
                ->with(['assignment.course', 'student'])
                ->latest('submitted_at'),
            $user->isLecturer() => Submission::query()
                ->whereHas('assignment.course', fn ($q) => $q->where('lecturer_id', $user->id))
                ->with(['assignment.course', 'student'])
                ->latest('submitted_at'),
            default => Submission::query()
                ->where('user_id', $user->id)
                ->with(['assignment.course'])
                ->latest('submitted_at'),
        };
    }

    public static function classSessionsFor(User $user): Builder
    {
        return match (true) {
            $user->isAdmin() => ClassSession::query()
                ->with(['course.lecturer'])
                ->orderBy('starts_at'),
            $user->isLecturer() => ClassSession::query()
                ->whereHas('course', fn ($q) => $q->where('lecturer_id', $user->id))
                ->with(['course'])
                ->orderBy('starts_at'),
            default => ClassSession::query()
                ->whereHas('course', fn ($q) => $q
                    ->where('is_active', true)
                    ->whereHas('students', fn ($s) => $s->where('users.id', $user->id)))
                ->with(['course'])
                ->orderBy('starts_at'),
        };
    }

    public static function coursesFor(User $user): Builder
    {
        return match (true) {
            $user->isAdmin() => Course::query()->with('lecturer'),
            $user->isLecturer() => Course::query()->where('lecturer_id', $user->id),
            default => Course::query()
                ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
                ->with('lecturer'),
        };
    }
}
