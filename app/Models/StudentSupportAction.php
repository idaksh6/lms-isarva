<?php

namespace App\Models;

use App\Enums\SupportActionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_support_case_id',
    'created_by',
    'type',
    'title',
    'notes',
    'conducted_at',
])]
class StudentSupportAction extends Model
{
    protected function casts(): array
    {
        return [
            'type' => SupportActionType::class,
            'conducted_at' => 'datetime',
        ];
    }

    public function supportCase(): BelongsTo
    {
        return $this->belongsTo(StudentSupportCase::class, 'student_support_case_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
