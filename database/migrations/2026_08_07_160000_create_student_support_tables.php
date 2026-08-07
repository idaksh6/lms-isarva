<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_support_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('open');
            $table->json('reasons')->nullable();
            $table->json('baseline_metrics')->nullable();
            $table->json('latest_metrics')->nullable();
            $table->timestamp('identified_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'user_id', 'status']);
        });

        Schema::create('student_support_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_support_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamp('conducted_at')->nullable();
            $table->timestamps();

            $table->index(['student_support_case_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_support_actions');
        Schema::dropIfExists('student_support_cases');
    }
};
