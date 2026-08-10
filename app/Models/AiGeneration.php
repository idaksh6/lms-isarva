<?php

namespace App\Models;

use App\Enums\AiGenerationFeature;
use App\Enums\AiGenerationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'course_id',
    'user_id',
    'feature',
    'status',
    'prompt_hash',
    'input_snapshot',
    'output',
    'error_message',
    'subject_type',
    'subject_id',
    'prompt_tokens',
    'completion_tokens',
    'accepted_at',
])]
class AiGeneration extends Model
{
    protected function casts(): array
    {
        return [
            'feature' => AiGenerationFeature::class,
            'status' => AiGenerationStatus::class,
            'input_snapshot' => 'array',
            'output' => 'array',
            'accepted_at' => 'datetime',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function isReady(): bool
    {
        return $this->status === AiGenerationStatus::Ready;
    }

    public function isPending(): bool
    {
        return $this->status === AiGenerationStatus::Pending;
    }

    public function markAccepted(): void
    {
        $this->update([
            'status' => AiGenerationStatus::Accepted,
            'accepted_at' => now(),
        ]);
    }

    public function markDiscarded(): void
    {
        $this->update([
            'status' => AiGenerationStatus::Discarded,
        ]);
    }
}
