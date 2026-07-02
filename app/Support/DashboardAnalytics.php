<?php

namespace App\Support;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardAnalytics
{
    public static function forUser(User $user, ?Collection $courses = null): array
    {
        if ($user->isAdmin()) {
            return self::build(Submission::query());
        }

        if ($user->isLecturer()) {
            return self::build(
                Submission::query()->whereHas('assignment.course', fn ($q) => $q->where('lecturer_id', $user->id))
            );
        }

        $assignmentIds = ($courses ?? collect())->flatMap(fn ($course) => $course->assignments->pluck('id'));

        return self::buildStudent(
            $user,
            $assignmentIds,
            $assignmentIds->count()
        );
    }

    /**
     * @return array{activity: array<int, array{label: string, value: int}>, status: array<int, array{label: string, value: int, pct: int, tone: string}>}
     */
    private static function build(Builder $submissions): array
    {
        return [
            'activity' => self::weeklyActivity($submissions),
            'status' => self::statusSegments($submissions),
        ];
    }

    /**
     * @return array{activity: array<int, array{label: string, value: int}>, status: array<int, array{label: string, value: int, pct: int, tone: string}>}
     */
    private static function buildStudent(User $user, Collection $assignmentIds, int $publishedCount): array
    {
        $submissions = Submission::query()
            ->where('user_id', $user->id)
            ->when($assignmentIds->isNotEmpty(), fn ($q) => $q->whereIn('assignment_id', $assignmentIds))
            ->when($assignmentIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'));

        $submitted = (clone $submissions)->count();
        $reviewed = (clone $submissions)->where('status', SubmissionStatus::Reviewed)->count();
        $pending = max(0, $publishedCount - $submitted);
        $awaitingReview = max(0, $submitted - $reviewed);
        $total = max(1, $publishedCount);

        return [
            'activity' => self::weeklyActivity($submissions),
            'status' => [
                ['label' => 'Pending', 'value' => $pending, 'pct' => DashboardMetrics::percent($pending, $total), 'tone' => 'slate'],
                ['label' => 'Awaiting review', 'value' => $awaitingReview, 'pct' => DashboardMetrics::percent($awaitingReview, $total), 'tone' => 'brand'],
                ['label' => 'Reviewed', 'value' => $reviewed, 'pct' => DashboardMetrics::percent($reviewed, $total), 'tone' => 'emerald'],
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private static function weeklyActivity(Builder $query): array
    {
        $weeks = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = now()->subWeeks($i)->endOfWeek();

            $weeks[] = [
                'label' => $i === 0 ? 'This wk' : $start->format('M j'),
                'value' => (clone $query)->whereBetween('submitted_at', [$start, $end])->count(),
            ];
        }

        return $weeks;
    }

    /**
     * @return array<int, array{label: string, value: int, pct: int, tone: string}>
     */
    private static function statusSegments(Builder $query): array
    {
        $total = (clone $query)->count();
        $denominator = max(1, $total);

        $items = [
            ['label' => 'Submitted', 'status' => SubmissionStatus::Submitted, 'tone' => 'brand'],
            ['label' => 'Late', 'status' => SubmissionStatus::Late, 'tone' => 'amber'],
            ['label' => 'Needs revision', 'status' => SubmissionStatus::NeedsRevision, 'tone' => 'rose'],
            ['label' => 'Reviewed', 'status' => SubmissionStatus::Reviewed, 'tone' => 'emerald'],
        ];

        return array_map(function (array $item) use ($query, $denominator) {
            $value = (clone $query)->where('status', $item['status'])->count();

            return [
                'label' => $item['label'],
                'value' => $value,
                'pct' => DashboardMetrics::percent($value, $denominator),
                'tone' => $item['tone'],
            ];
        }, $items);
    }
}
