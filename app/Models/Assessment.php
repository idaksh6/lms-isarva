<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'course_id',
    'created_by',
    'title',
    'instructions',
    'question_count',
    'marks_per_question',
    'due_at',
    'is_published',
])]
class Assessment extends Model
{
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'is_published' => 'boolean',
            'question_count' => 'integer',
            'marks_per_question' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function maxScore(): int
    {
        return $this->question_count * $this->marks_per_question;
    }

    public function isReadyToPublish(): bool
    {
        return $this->questions()->count() === $this->question_count
            && $this->questions()->whereHas('options', fn ($q) => $q->where('is_correct', true))->count() === $this->question_count;
    }
}
