<?php

namespace App\Models;

use App\Enums\SessionDeliveryMode;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'course_id',
    'created_by',
    'title',
    'starts_at',
    'ends_at',
    'mode',
    'meeting_link',
    'location',
    'notes',
])]
class ClassSession extends Model
{
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'mode' => SessionDeliveryMode::class,
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

    public function displayTitle(): string
    {
        return $this->title ?: $this->course->code.' class';
    }

    public function timeRangeLabel(): string
    {
        $start = $this->starts_at->format('g:i A');

        if (! $this->ends_at) {
            return $start;
        }

        if ($this->ends_at->isSameDay($this->starts_at)) {
            return $start.' – '.$this->ends_at->format('g:i A');
        }

        return $start.' – '.$this->ends_at->format('M j, g:i A');
    }

    public function dateLabel(): string
    {
        return $this->starts_at->format('M j, Y');
    }

    public function shortDateLabel(): string
    {
        return $this->starts_at->format('M j');
    }
}
