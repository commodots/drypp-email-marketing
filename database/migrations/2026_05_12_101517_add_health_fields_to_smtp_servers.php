<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smtp_servers', function (Blueprint $table) {
            $table->float('health_score')->default(100);
            $table->integer('sent_last_24h')->default(0);
            $table->integer('opens_last_24h')->default(0);
            $table->integer('clicks_last_24h')->default(0);
            $table->integer('replies_last_24h')->default(0);
            $table->integer('bounces_last_24h')->default(0);
            $table->integer('fails_last_24h')->default(0);
            $table->integer('inbox_hits')->default(0);
            $table->integer('spam_hits')->default(0);
            $table->float('placement_score')->default(100);
            $table->timestamp('last_health_check_at')->nullable();
            
            $table->index(['active', 'is_blocked', 'health_score']);
        });
    }

    public function down(): void
    {
        Schema::table('smtp_servers', function (Blueprint $table) {
            $table->dropIndex(['active', 'is_blocked', 'health_score']);
            $table->dropColumn([
                'health_score', 'sent_last_24h', 'opens_last_24h', 'clicks_last_24h',
                'replies_last_24h', 'bounces_last_24h', 'fails_last_24h',
                'last_health_check_at', 'inbox_hits', 'spam_hits', 'placement_score'
            ]);
        });
    }
};