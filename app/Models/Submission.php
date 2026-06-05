<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'assignment_id',
    'user_id',
    'notes',
    'file_path',
    'file_name',
    'status',
    'score',
    'letter_grade',
    'feedback',
    'submitted_at',
    'reviewed_at',
    'reviewed_by',
])]
class Submission extends Model
{
    protected function casts(): array
    {
        return [
            'status' => SubmissionStatus::class,
            'score' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isGraded(): bool
    {
        return $this->score !== null;
    }

    public function canResubmit(): bool
    {
        return $this->status === SubmissionStatus::NeedsRevision;
    }
}
