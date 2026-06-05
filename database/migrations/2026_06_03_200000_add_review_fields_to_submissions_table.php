<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->decimal('score', 5, 2)->nullable()->after('status');
            $table->string('letter_grade', 2)->nullable()->after('score');
            $table->text('feedback')->nullable()->after('letter_grade');
            $table->timestamp('reviewed_at')->nullable()->after('feedback');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['score', 'letter_grade', 'feedback', 'reviewed_at']);
        });
    }
};
