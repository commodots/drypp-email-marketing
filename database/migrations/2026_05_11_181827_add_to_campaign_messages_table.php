<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        // Populate existing records with UUIDs
        DB::table('campaign_messages')->whereNull('message_uuid')->orWhere('message_uuid', '')->update([
            'message_uuid' => DB::raw('UUID()')
        ]);

        // Now add the unique constraint
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->unique('message_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->dropUnique(['message_uuid']);
            $table->dropColumn('message_uuid');
        });
    }
};
