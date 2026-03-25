<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Check for the column FIRST, outside the Blueprint
        if (!Schema::hasColumn('contacts', 'name')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->string('name')->nullable()->after('email');
            });
        }

        // 2. Now handle the unique index in a separate block
        Schema::table('contacts', function (Blueprint $table) {
            // We use a try-catch or check to ensure we don't crash if the index exists locally
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('contacts');
            
            if (!array_key_exists('contacts_user_id_email_unique', $indexes)) {
                $table->unique(['user_id', 'email']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'email']);
            if (Schema::hasColumn('contacts', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};