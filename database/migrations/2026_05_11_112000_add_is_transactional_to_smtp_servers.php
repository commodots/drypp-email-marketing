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
            if (!Schema::hasColumn('smtp_servers', 'is_transactional')) {
                $table->boolean('is_transactional')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smtp_servers', function (Blueprint $table) {
            if (Schema::hasColumn('smtp_servers', 'is_transactional')) {
                $table->dropColumn('is_transactional');
            }
        });
    }
};
