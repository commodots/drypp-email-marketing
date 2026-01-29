<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\SmtpServer;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User', 
                'email' => 'test@example.com'
            ]);
        }

        // Get an SMTP server for the active campaign
        $smtp = SmtpServer::first() ?? SmtpServer::create([
            'name' => 'Default Mailer',
            'host' => 'smtp.mailtrap.io',
            'daily_limit' => 1000
        ]);

        // ==========================================
        // SCENARIO 1: A "Draft" Campaign (HTML)
        // ==========================================
        $draft = Campaign::create([
            'user_id' => $user->id,
            'name' => 'January Newsletter',
            'type' => 'bulk',
            'status' => 'draft',
            'format' => 'html',
            'total_emails' => 0,
            'sent' => 0
        ]);

        $draft->emailContent()->create([
            'subject' => 'Welcome to our January Update!',
            'body' => '<div style="font-family: sans-serif;"><h1>Hello!</h1><p>This is a <b>bold</b> newsletter test.</p></div>',
            'format' => 'html'
        ]);


        // ==========================================
        // SCENARIO 2: A "Queued" Campaign (Text)
        // ==========================================
        $queued = Campaign::create([
            'user_id' => $user->id,
            'name' => 'Cold Outreach #1',
            'type' => 'cold',
            'status' => 'queued',
            'format' => 'text',
            'total_emails' => 50,
            'sent' => 0
        ]);

        $queued->emailContent()->create([
            'subject' => 'Quick Question',
            'body' => "Hi,\n\nJust wondering if you are interested in our services.\n\nBest,\nMe",
            'format' => 'text'
        ]);

        $messagesQueued = [];
        for ($i = 1; $i <= 50; $i++) {
            $messagesQueued[] = [
                'campaign_id' => $queued->id,
                'email' => "pending-lead{$i}@company.com",
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        CampaignMessage::insert($messagesQueued);

        // ==========================================
        // SCENARIO 3: A "Partially Sent" Campaign (NEW)
        // Progress: 10 / 50 (20%)
        // ==========================================
        $active = Campaign::create([
            'user_id' => $user->id,
            'name' => 'Flash Sale Blast',
            'type' => 'bulk',
            'status' => 'sending',
            'smtp_id' => $smtp->id,
            'format' => 'html',
            'total_emails' => 50,
            'sent' => 10, // Already processed 10
        ]);

        $active->emailContent()->create([
            'subject' => 'Flash Sale: 50% Off!',
            'body' => '<h1>Don\'t miss out!</h1><p>Our sale ends in 2 hours.</p>',
            'format' => 'html'
        ]);

        $messagesActive = [];
        for ($i = 1; $i <= 50; $i++) {
            $messagesActive[] = [
                'campaign_id' => $active->id,
                'email' => "customer{$i}@gmail.com",
                'status' => ($i <= 10) ? 'sent' : 'pending', 
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        CampaignMessage::insert($messagesActive);
    }
}