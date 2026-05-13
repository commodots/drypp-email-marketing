<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SmtpServer;
use App\Services\Health\InboxHealthService;

class UpdateInboxHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inbox:health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Inbox Health';

    /**
     * Execute the console command.
     */
    public function handle(InboxHealthService $service)
    {

        SmtpServer::all()->each(function ($smtp) use ($service) {

            $score = $service->calculate($smtp);

            $smtp->update([
                'health_score' => $score,
                'last_health_check_at' => now(),
            ]);

            // Dynamic Throttling
            if ($score < 40) {
                $smtp->hourly_limit = 10;
            } elseif ($score > 80) {
                $smtp->hourly_limit = 50;
            }

            if ($smtp->placement_score < 60) {
                $smtp->hourly_limit = min($smtp->hourly_limit, 5); 
            }
            // Auto-Blocking
            if ($score < 20 || $smtp->placement_score < 30) {
                $smtp->is_blocked = true;
            } elseif ($score > 60 && $smtp->placement_score > 50) {
                $smtp->is_blocked = false;
            }

            $smtp->save();
        });

        $this->info('Inbox health scores updated successfully.');
    }
}
