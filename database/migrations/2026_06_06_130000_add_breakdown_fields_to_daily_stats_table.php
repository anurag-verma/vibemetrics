<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_stats', function (Blueprint $table) {
            $table->json('top_browsers')->nullable()->after('devices');
            $table->json('top_os')->nullable()->after('top_browsers');
            $table->json('utm_sources')->nullable()->after('top_campaigns');
            $table->json('utm_mediums')->nullable()->after('utm_sources');
        });
    }

    public function down(): void
    {
        Schema::table('daily_stats', function (Blueprint $table) {
            $table->dropColumn(['top_browsers', 'top_os', 'utm_sources', 'utm_mediums']);
        });
    }
};
