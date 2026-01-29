<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SmtpServer;

class SmtpServerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Mailgun (Simulated)
        SmtpServer::updateOrCreate(
            ['host' => 'smtp.mailgun.org'], 
            [
                'name' => 'Mailgun Primary',
                'daily_limit' => 2000,
                'sent_today' => 0,
                'active' => true,
            ]
        );

        // 2. SendGrid (Simulated)
        SmtpServer::updateOrCreate(
            ['host' => 'smtp.sendgrid.net'], 
            [
                'name' => 'SendGrid Backup',
                'daily_limit' => 500,
                'sent_today' => 0,
                'active' => true,
            ]
        );

        // 3. Local/Custom (Simulated)
        SmtpServer::updateOrCreate(
            ['host' => '127.0.0.1'], 
            [
                'name' => 'Local Postfix',
                'daily_limit' => 10000,
                'sent_today' => 150, // Simulate some usage
                'active' => true,
            ]
        );
    }
}