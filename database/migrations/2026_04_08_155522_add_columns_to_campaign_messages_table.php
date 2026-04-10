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
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->dropColumn(['sent_at', 'opened_at', 'clicked_at']);
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->change();
        });
    }
};
