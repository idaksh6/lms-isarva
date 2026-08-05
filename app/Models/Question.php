<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'course_id',
    'title',
    'body',
    'is_resolved',
])]
class Question extends Model
{
    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->latest();
    }

    public function rootAnswers(): HasMany
    {
        return $this->hasMany(Answer::class)
            ->whereNull('parent_id')
            ->orderByDesc('is_accepted')
            ->oldest();
    }

    public function acceptedAnswer(): HasOne
    {
        return $this->hasOne(Answer::class)->where('is_accepted', true)->whereNull('parent_id');
    }

    public function isGlobal(): bool
    {
        return $this->course_id === null;
    }
}
