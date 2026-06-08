<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('sites', 'verification_token') ? 'verification_token' : null,
            Schema::hasColumn('sites', 'is_verified') ? 'is_verified' : null,
            Schema::hasColumn('sites', 'verified_at') ? 'verified_at' : null,
        ]));

        if ($columns === []) {
            return;
        }

        Schema::table('sites', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('verification_token', 64)->nullable()->unique()->after('tracking_id');
            $table->boolean('is_verified')->default(false)->after('verification_token');
            $table->timestamp('verified_at')->nullable()->after('is_verified');
        });
    }
};
