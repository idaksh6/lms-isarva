<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('question_id')
                ->constrained('answers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('question_id')
                ->constrained('answers')
                ->cascadeOnDelete();
        });
    }
};
