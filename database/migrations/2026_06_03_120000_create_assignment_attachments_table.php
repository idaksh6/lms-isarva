<?php

use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('name');
            $table->unsignedInteger('size')->nullable();
            $table->string('mime', 100)->nullable();
            $table->timestamps();
        });

        Assignment::query()
            ->whereNotNull('attachment_path')
            ->each(function (Assignment $assignment) {
                AssignmentAttachment::query()->create([
                    'assignment_id' => $assignment->id,
                    'path' => $assignment->attachment_path,
                    'name' => $assignment->attachment_name ?? basename($assignment->attachment_path),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_attachments');
    }
};
