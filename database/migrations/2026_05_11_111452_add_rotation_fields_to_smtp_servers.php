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
    $table->boolean('is_rotating')->default(true);
    $table->integer('hourly_limit')->default(50);
    $table->integer('sent_this_hour')->default(0);
    $table->timestamp('last_sent_at')->nullable();

    $table->integer('failure_count')->default(0);
    $table->boolean('is_blocked')->default(false);

    $table->integer('priority')->default(1); // higher = preferred
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
