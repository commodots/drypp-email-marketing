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
            $table->string('port')->default('587')->after('host');
            $table->string('username')->nullable()->after('port');
            $table->string('password')->nullable()->after('username');
            $table->string('encryption')->default('tls')->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smtp_servers', function (Blueprint $table) {
            $table->dropColumn(['port', 'username', 'password', 'encryption']);
        });
    }
};
