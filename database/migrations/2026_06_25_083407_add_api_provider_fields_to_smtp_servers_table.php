<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smtp_servers', function (Blueprint $table) {

            if (!Schema::hasColumn('smtp_servers', 'type')) {
                $table->text('type')->nullable()->after('encryption');
            }

if (!Schema::hasColumn('smtp_servers', 'api_key')) {
                $table->text('api_key')->nullable()->after('type');
            }

            if (!Schema::hasColumn('smtp_servers', 'region')) {
                $table->string('region')->nullable()->after('api_key');
            }

            if (!Schema::hasColumn('smtp_servers', 'access_key')) {
                $table->string('access_key')->nullable()->after('region');
            }

            if (!Schema::hasColumn('smtp_servers', 'secret_key')) {
                $table->text('secret_key')->nullable()->after('access_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('smtp_servers', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'api_key',
                'region',
                'access_key',
                'secret_key'
            ]);
        });
    }
};