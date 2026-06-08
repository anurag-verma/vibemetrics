<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('platform_settings')->where('key', 'require_domain_verification')->delete();
    }

    public function down(): void
    {
        DB::table('platform_settings')->updateOrInsert(
            ['key' => 'require_domain_verification'],
            ['value' => 'true', 'updated_at' => now()]
        );
    }
};
