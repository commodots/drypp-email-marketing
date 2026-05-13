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
            $table->foreignId('smtp_server_id')->nullable()->constrained('smtp_servers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->dropForeign(['smtp_server_id']);
        $table->dropColumn('smtp_server_id');
        });
    }
};
