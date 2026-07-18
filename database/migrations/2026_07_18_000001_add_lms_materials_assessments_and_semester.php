<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('semester', 32)->nullable()->after('description');
        });

        Schema::create('course_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('category', 32);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime')->nullable();
            $table->string('external_url', 2048)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'category']);
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('question_count')->default(15);
            $table->unsignedSmallInteger('marks_per_question')->default(2);
            $table->timestamp('due_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['course_id', 'due_at']);
        });

        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->text('prompt');
            $table->timestamps();

            $table->unique(['assessment_id', 'position']);
        });

        Schema::create('assessment_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->text('label');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->unique(['assessment_question_id', 'position']);
        });

        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('score')->nullable();
            $table->unsignedSmallInteger('max_score')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'user_id']);
        });

        Schema::create('assessment_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_option_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_correct')->nullable();
            $table->timestamps();

            $table->unique(['assessment_attempt_id', 'assessment_question_id'], 'attempt_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_attempt_answers');
        Schema::dropIfExists('assessment_attempts');
        Schema::dropIfExists('assessment_options');
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('course_materials');

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('semester');
        });
    }
};
