<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SmtpServer;

class ResetSmtpCounters extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'smtp:reset';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Resets daily SMTP sending counters to zero';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            SmtpServer::query()->update(['sent_today' => 0]);
            $this->info('Limits reset for the new day.');
        } catch (\Exception $e) {
            $this->error('Failed to reset SMTP counters: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}