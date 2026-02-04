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
    public function up()
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            $this->dropForeignKeyIfExists('campaign_messages', 'campaign_id', $table);

            $table->foreign('campaign_id')
                  ->references('id')->on('campaigns')
                  ->onDelete('cascade');
        });
    }

    protected function dropForeignKeyIfExists($table, $column, $blueprint) {
        
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $conn = Schema::getConnection();
        $dbName = $conn->getDatabaseName();
        
       
        $exists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ? 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$dbName, $table, $column]);

        if (!empty($exists)) {
            // Drop the specific constraint we found
            $blueprint->dropForeign($exists[0]->CONSTRAINT_NAME);
        }
    }

    public function down()
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
        //
    });
    }
};