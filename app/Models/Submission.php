<?php

namespace App\Models;

use App\Enums\SubmissionSource;
use App\Enums\SubmissionStatus;
use App\Support\ExternalSubmissionLink;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'assignment_id',
    'user_id',
    'notes',
    'source',
    'external_url',
    'external_label',
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
            'source' => SubmissionSource::class,
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

    public function isExternalLink(): bool
    {
        return ($this->source ?? SubmissionSource::File) === SubmissionSource::Link
            || filled($this->external_url);
    }

    public function isFileUpload(): bool
    {
        return ! $this->isExternalLink() && filled($this->file_path);
    }

    public function displayName(): string
    {
        if ($this->isExternalLink()) {
            return $this->external_label
                ?? ExternalSubmissionLink::labelFromUrl((string) $this->external_url);
        }

        return (string) $this->file_name;
    }
}
