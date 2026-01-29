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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['cold', 'bulk']); // Match Task
            $table->enum('status', ['draft', 'queued', 'sending', 'paused', 'completed'])->default('draft');
            $table->foreignId('smtp_id')->nullable()->constrained('smtp_servers');
            $table->integer('total_emails')->default(0);
            $table->integer('sent')->default(0);
            $table->integer('opens')->default(0);
            $table->integer('clicks')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
