<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('theme', 'corporate')->update(['theme' => 'amber']);
    }

    public function down(): void
    {
        DB::table('users')->where('theme', 'amber')->update(['theme' => 'corporate']);
    }
};
