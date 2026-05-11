<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('smtp_servers', function (Blueprint $table) {
              $table->boolean('warmup_enabled')->default(true);
    $table->integer('warmup_day')->default(1);
    $table->integer('warmup_daily_limit')->default(20);
    $table->integer('warmup_sent_today')->default(0);
    $table->timestamp('warmup_last_sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smtp_servers', function (Blueprint $table) {
            //
        });
    }
};
