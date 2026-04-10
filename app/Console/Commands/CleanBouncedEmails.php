<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CampaignMessage;
use App\Models\Contact;

class CleanBouncedEmails extends Command
{
    protected $signature = 'emails:clean-bounced';
    protected $description = 'Updates contact health based on failed campaign messages';

    public function handle()
    {
        $bouncedEmails = CampaignMessage::where('status', 'failed')
            ->pluck('email')
            ->unique();

        CampaignMessage::whereIn('email', $bouncedEmails)
            ->update(['status' => 'bounced']);

        Contact::whereIn('email', $bouncedEmails)
            ->update(['status' => 'bounced']);

        $this->info(count($bouncedEmails) . ' emails marked as bounced.');
    }
}