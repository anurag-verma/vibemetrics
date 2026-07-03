<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('visitor_id', 36)->nullable();
            $table->string('url', 2048)->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['site_id', 'created_at']);
            $table->index(['site_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_events');
    }
};
