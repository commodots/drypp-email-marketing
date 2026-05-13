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
        Schema::create('spam_checks', function (Blueprint $table) {
           $table->id();
    $table->foreignId('smtp_server_id');
    $table->foreignId('seed_inbox_id');
    $table->string('message_uuid')->unique()->nullable();

    $table->string('placement'); // inbox, spam, missing
    $table->timestamp('checked_at');

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spam_checks');
    }
};
