<?php

namespace App\Console\Commands;

use App\Models\SmtpServer;
use Illuminate\Console\Command;

class ResetSmtpHourly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-smtp-hourly';

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
        SmtpServer::update(['sent_this_hour' => 0]);
    }
}
