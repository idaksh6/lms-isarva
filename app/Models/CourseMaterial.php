<?php

namespace App\Models;

use App\Enums\MaterialCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'course_id',
    'uploaded_by',
    'category',
    'title',
    'description',
    'file_path',
    'file_name',
    'mime',
    'external_url',
    'sort_order',
])]
class CourseMaterial extends Model
{
    protected function casts(): array
    {
        return [
            'category' => MaterialCategory::class,
            'sort_order' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function hasFile(): bool
    {
        return $this->file_path !== null && Storage::disk('public')->exists($this->file_path);
    }

    public function deleteFile(): void
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
    }
}
