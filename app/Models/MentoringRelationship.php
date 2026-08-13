<?php

namespace App\Models;

use App\Enums\MentoringStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentoringRelationship extends Model
{
    protected $fillable = [
        'mentor_id',
        'mentee_id',
        'course_id',
        'assigned_by',
        'status',
        'goals',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => MentoringStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function improvementAreas(): HasMany
    {
        return $this->hasMany(MentoringImprovementArea::class)->latest();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(MentoringSession::class)->orderByDesc('conducted_at');
    }

    public function actionPlans(): HasMany
    {
        return $this->hasMany(MentoringActionPlan::class)->latest();
    }

    public function isActive(): bool
    {
        return $this->status === MentoringStatus::Active;
    }
}
