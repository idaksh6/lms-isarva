<?php

namespace App\Models;

use App\Enums\SupportCaseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'course_id',
    'user_id',
    'created_by',
    'status',
    'reasons',
    'baseline_metrics',
    'latest_metrics',
    'identified_at',
    'resolved_at',
])]
class StudentSupportCase extends Model
{
    protected function casts(): array
    {
        return [
            'status' => SupportCaseStatus::class,
            'reasons' => 'array',
            'baseline_metrics' => 'array',
            'latest_metrics' => 'array',
            'identified_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(StudentSupportAction::class)->latest('conducted_at')->latest('id');
    }

    public function isActive(): bool
    {
        return ($this->status ?? SupportCaseStatus::Open)->isActive();
    }
}
