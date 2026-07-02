<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('source')->default('file')->after('notes');
            $table->string('external_url', 2048)->nullable()->after('source');
            $table->string('external_label')->nullable()->after('external_url');
            $table->string('file_path')->nullable()->change();
            $table->string('file_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['source', 'external_url', 'external_label']);
            $table->string('file_path')->nullable(false)->change();
            $table->string('file_name')->nullable(false)->change();
        });
    }
};
