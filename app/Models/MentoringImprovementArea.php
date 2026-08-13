<?php

namespace App\Models;

use App\Enums\ImprovementAreaPriority;
use App\Enums\ImprovementAreaStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoringImprovementArea extends Model
{
    protected $fillable = [
        'mentoring_relationship_id',
        'created_by',
        'title',
        'description',
        'priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'priority' => ImprovementAreaPriority::class,
            'status' => ImprovementAreaStatus::class,
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
