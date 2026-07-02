<?php

namespace App\Models;

use App\Enums\SubmissionDeliveryMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'course_id',
    'created_by',
    'title',
    'instructions',
    'delivery_method',
    'drop_folder_url',
    'due_at',
    'attachment_path',
    'attachment_name',
    'is_published',
])]
class Assignment extends Model
{
    protected function casts(): array
    {
        return [
            'delivery_method' => SubmissionDeliveryMethod::class,
            'due_at' => 'datetime',
            'is_published' => 'boolean',
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

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentAttachment::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_at !== null && $this->due_at->isPast();
    }

    public function acceptsFileUpload(): bool
    {
        $method = $this->delivery_method ?? SubmissionDeliveryMethod::File;

        return in_array($method, [SubmissionDeliveryMethod::File, SubmissionDeliveryMethod::Both], true);
    }

    public function acceptsExternalLink(): bool
    {
        $method = $this->delivery_method ?? SubmissionDeliveryMethod::File;

        return in_array($method, [SubmissionDeliveryMethod::Link, SubmissionDeliveryMethod::Both], true);
    }
}
