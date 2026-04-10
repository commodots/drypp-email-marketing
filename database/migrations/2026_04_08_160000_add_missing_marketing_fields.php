<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing fields for Campaigns, Contacts, and Users
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'sender_email')) {
                $table->string('sender_email')->after('name')->nullable();
            }
        });

        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'status')) {
                $table->enum('status', ['active', 'unsubscribed', 'bounced'])->default('active')->after('email');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'auto_reports')) {
                $table->boolean('auto_reports')->default(false)->after('role');
            }
        });

        // Step 7: Create Automation Tables
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained('contact_groups')->onDelete('set null');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('sequence_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained()->onDelete('cascade');
            $table->integer('delay_days')->default(0);
            $table->string('subject');
            $table->longText('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequence_steps');
        Schema::dropIfExists('sequences');
        Schema::table('users', fn($table) => $table->dropColumn('auto_reports'));
        Schema::table('contacts', fn($table) => $table->dropColumn('status'));
        Schema::table('campaigns', fn($table) => $table->dropColumn('sender_email'));
    }
};
