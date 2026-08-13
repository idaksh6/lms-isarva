<?php

namespace App\Models;

use App\Enums\MentoringSessionMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoringSession extends Model
{
    protected $fillable = [
        'mentoring_relationship_id',
        'created_by',
        'conducted_at',
        'duration_minutes',
        'mode',
        'topic',
        'remarks',
        'student_progress_notes',
    ];

    protected function casts(): array
    {
        return [
            'conducted_at' => 'datetime',
            'mode' => MentoringSessionMode::class,
            'duration_minutes' => 'integer',
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
