<?php

namespace App\Models;

use App\Enums\ActionPlanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoringActionPlan extends Model
{
    protected $fillable = [
        'mentoring_relationship_id',
        'created_by',
        'title',
        'objectives',
        'due_at',
        'status',
        'progress_percent',
        'progress_notes',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'status' => ActionPlanStatus::class,
            'progress_percent' => 'integer',
        ];
    }

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(MentoringRelationship::class, 'mentoring_relationship_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
