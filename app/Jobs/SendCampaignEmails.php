<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\SmtpServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaignId;

    public function __construct($campaignId) {
        $this->campaignId = $campaignId;
    }

    public function handle() {
        $campaign = Campaign::with('emailContent', 'user.subscription')->find($this->campaignId);
        
        $smtp = $campaign->smtp_id 
            ? SmtpServer::find($campaign->smtp_id)
            : SmtpServer::where('active', 1)
                ->whereColumn('sent_today', '<', 'daily_limit')
                ->orderBy('sent_today', 'asc')
                ->first();

        if (!$smtp || !$campaign->user->hasQuota()) return;

        //Configure Mailer on the fly 
        config(['mail.mailers.smtp.host' => $smtp->host]);

        //Send and Increment (Non-Negotiable Dev Rules)
        // Mail::to(...)->send(...); 
        
        $smtp->increment('sent_today');
        $campaign->increment('sent');
        $campaign->user->subscription->increment('emails_used');
    }
}