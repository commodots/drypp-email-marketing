<?php

namespace App\Console\Commands;

use App\Models\SmtpServer;
use Illuminate\Console\Command;

class IncreaseWarmup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:increase-warmup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Increase warmup limits daily';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SmtpServer::where('warmup_day', '<', 30)->each(function ($smtp) {

        $smtp->increment('warmup_day');
        $smtp->increment('warmup_limit', 10);
    });
    }
}
