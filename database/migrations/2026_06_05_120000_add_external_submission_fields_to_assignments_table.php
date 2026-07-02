<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('delivery_method')->default('file')->after('instructions');
            $table->string('drop_folder_url', 2048)->nullable()->after('delivery_method');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['delivery_method', 'drop_folder_url']);
        });
    }
};
