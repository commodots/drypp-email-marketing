<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SmtpServer;

class ResetHealthStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-health-stats';

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
        SmtpServer::query()->update([
        'sent_last_24h' => 0,
        'opens_last_24h' => 0,
        'clicks_last_24h' => 0,
        'replies_last_24h' => 0,
        'bounces_last_24h' => 0,
        'fails_last_24h' => 0,
    ]);
    }
}
