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
        $campaign = Campaign::with('emailContent', 'user.subscription')
            ->whereHas('messages', function($q) {
                $q->where('status', 'pending');
            })
            ->find($this->campaignId);
        
        if (!$campaign) return;
        
        $smtp = $campaign->smtp_id 
            ? SmtpServer::find($campaign->smtp_id)
            : SmtpServer::where('active', 1)
                ->whereColumn('sent_today', '<', 'daily_limit')
                ->orderBy('sent_today', 'asc')
                ->first();

        if (!$smtp || !$campaign->user->hasQuota()) return;

        // Configure Mailer with complete SMTP settings
        config([
            'mail.mailers.smtp.host' => $smtp->host,
            'mail.mailers.smtp.port' => $smtp->port,
            'mail.mailers.smtp.username' => $smtp->username,
            'mail.mailers.smtp.password' => $smtp->password,
            'mail.mailers.smtp.encryption' => $smtp->encryption,
        ]);

        // Get next pending message to send
        $message = $campaign->messages()->where('status', 'pending')->first();
        
        if ($message) {
            try {
                Mail::raw(
                    $campaign->emailContent->body,
                    function($mail) use ($message, $campaign) {
                        $mail->to($message->email)
                             ->subject($campaign->emailContent->subject);
                    }
                );
                
                $message->update(['status' => 'sent']);
                $smtp->increment('sent_today');
                $campaign->increment('sent');
                $campaign->user->subscription->increment('emails_used');
            } catch (\Exception $e) {
                $message->update(['status' => 'failed']);
            }
        }
    }
}