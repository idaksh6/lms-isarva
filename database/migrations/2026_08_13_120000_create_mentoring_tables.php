<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotent: staging may already have tables from a partial deploy.
        if (! Schema::hasTable('mentoring_relationships')) {
            Schema::create('mentoring_relationships', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('mentee_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
                $table->string('status', 32)->default('active');
                $table->text('goals')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();

                $table->index(['mentor_id', 'status']);
                $table->index(['mentee_id', 'status']);
                $table->index(['course_id', 'status']);
            });
        }

        if (! Schema::hasTable('mentoring_improvement_areas')) {
            Schema::create('mentoring_improvement_areas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mentoring_relationship_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('priority', 16)->default('medium');
                $table->string('status', 32)->default('open');
                $table->timestamps();

                $table->index(['mentoring_relationship_id', 'status']);
            });
        }

        if (! Schema::hasTable('mentoring_sessions')) {
            Schema::create('mentoring_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mentoring_relationship_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('conducted_at');
                $table->unsignedSmallInteger('duration_minutes')->nullable();
                $table->string('mode', 32)->default('in_person');
                $table->string('topic')->nullable();
                $table->text('remarks')->nullable();
                $table->text('student_progress_notes')->nullable();
                $table->timestamps();

                $table->index(['mentoring_relationship_id', 'conducted_at']);
            });
        }

        if (! Schema::hasTable('mentoring_action_plans')) {
            Schema::create('mentoring_action_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mentoring_relationship_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->text('objectives')->nullable();
                $table->timestamp('due_at')->nullable();
                $table->string('status', 32)->default('planned');
                $table->unsignedTinyInteger('progress_percent')->default(0);
                $table->text('progress_notes')->nullable();
                $table->timestamps();

                $table->index(['mentoring_relationship_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoring_action_plans');
        Schema::dropIfExists('mentoring_sessions');
        Schema::dropIfExists('mentoring_improvement_areas');
        Schema::dropIfExists('mentoring_relationships');
    }
};
