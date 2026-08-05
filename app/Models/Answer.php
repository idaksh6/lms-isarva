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
    public const MAX_DEPTH = 5;

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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with(['author', 'childrenRecursive']);
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function depth(): int
    {
        $depth = 0;
        $parent = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first();

        while ($parent !== null && $depth < self::MAX_DEPTH + 1) {
            $depth++;
            $parent = $parent->relationLoaded('parent')
                ? $parent->parent
                : $parent->parent()->first();
        }

        return $depth;
    }

    public function canReceiveReply(): bool
    {
        return $this->depth() < self::MAX_DEPTH;
    }

    public function descendantCount(): int
    {
        $count = $this->children->count();

        foreach ($this->children as $child) {
            $count += $child->descendantCount();
        }

        return $count;
    }
}
