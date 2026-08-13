<?php

namespace App\Support;

use App\Enums\ActionPlanStatus;
use App\Enums\ImprovementAreaStatus;
use App\Enums\MentoringStatus;
use App\Models\MentoringRelationship;
use App\Models\User;
use Illuminate\Support\Collection;

class MentoringReport
{
    /**
     * @return array{
     *     relationships: Collection<int, MentoringRelationship>,
     *     summary: array<string, int|float|null>,
     *     effectiveness: array<string, int|float|null>
     * }
     */
    public static function build(?User $viewer = null, ?int $courseId = null, ?int $mentorId = null): array
    {
        $query = MentoringRelationship::query()
            ->with([
                'mentor',
                'mentee',
                'course',
                'improvementAreas',
                'sessions',
                'actionPlans',
            ])
            ->latest();

        if ($viewer?->isLecturer()) {
            $query->where('mentor_id', $viewer->id);
        }

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($mentorId && $viewer?->isAdmin()) {
            $query->where('mentor_id', $mentorId);
        }

        $relationships = $query->get();

        $areas = $relationships->flatMap->improvementAreas;
        $sessions = $relationships->flatMap->sessions;
        $plans = $relationships->flatMap->actionPlans;

        $active = $relationships->where('status', MentoringStatus::Active)->count();
        $areasAchieved = $areas->where('status', ImprovementAreaStatus::Achieved)->count();
        $areasTotal = $areas->count();
        $plansCompleted = $plans->where('status', ActionPlanStatus::Completed)->count();
        $plansTotal = $plans->count();
        $avgProgress = $plans->avg('progress_percent');

        return [
            'relationships' => $relationships,
            'summary' => [
                'total' => $relationships->count(),
                'active' => $active,
                'sessions' => $sessions->count(),
                'areas' => $areasTotal,
                'areas_open' => $areas->where('status', ImprovementAreaStatus::Open)->count(),
                'areas_improving' => $areas->where('status', ImprovementAreaStatus::Improving)->count(),
                'areas_achieved' => $areasAchieved,
                'plans' => $plansTotal,
                'plans_completed' => $plansCompleted,
            ],
            'effectiveness' => [
                'area_closure_rate' => $areasTotal > 0 ? round(($areasAchieved / $areasTotal) * 100, 1) : null,
                'plan_completion_rate' => $plansTotal > 0 ? round(($plansCompleted / $plansTotal) * 100, 1) : null,
                'avg_plan_progress' => $avgProgress !== null ? round((float) $avgProgress, 1) : null,
                'avg_sessions_per_mentee' => $relationships->count() > 0
                    ? round($sessions->count() / $relationships->count(), 1)
                    : null,
            ],
        ];
    }
}
