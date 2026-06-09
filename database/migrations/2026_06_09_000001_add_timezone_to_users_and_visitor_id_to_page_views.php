<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone', 64)->default('UTC')->after('email');
        });

        Schema::table('page_views', function (Blueprint $table) {
            $table->string('visitor_id', 36)->nullable()->after('site_id');
            $table->index(['site_id', 'visitor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->dropIndex(['site_id', 'visitor_id', 'created_at']);
            $table->dropColumn('visitor_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
