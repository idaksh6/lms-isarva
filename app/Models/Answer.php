<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'question_id',
    'parent_id',
    'user_id',
    'body',
    'is_accepted',
])]
class Answer extends Model
{
    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Quoted message this reply references (Google Chat–style quote, not nesting). */
    public function quoted(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->quoted();
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function snippet(int $limit = 120): string
    {
        $text = preg_replace('/\s+/', ' ', trim($this->body)) ?? '';

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $limit - 1)).'…';
    }
}
