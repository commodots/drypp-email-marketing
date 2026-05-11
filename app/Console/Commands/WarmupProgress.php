<?php

namespace App\Console\Commands;

use App\Models\SmtpServer;
use Illuminate\Console\Command;

class WarmupProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:warmup-progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SmtpServer::where('warmup_enabled', 1)->each(function ($smtp) {

        if ($smtp->warmup_day < 30) {

            $smtp->increment('warmup_day');

            $smtp->update([
                'warmup_daily_limit' => min(
                    $smtp->warmup_daily_limit + 10,
                    200
                ),
                'warmup_sent_today' => 0,
            ]);
        }
    });
    }
}
