<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\SmtpServer;
use App\Models\Contact;
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
            // Retrieve the actual Contact record to get their custom data
            $contact = Contact::where('email', $message->email)
                              ->where('user_id', $campaign->user_id)
                              ->first();

            // Fetch the raw subject and body
            $rawSubject = $campaign->emailContent->subject;
            $rawBody = $campaign->emailContent->body;

            // Parse the variables
            $parsedSubject = $this->replaceVariables($rawSubject, $contact);
            $parsedBody = $this->replaceVariables($rawBody, $contact);

            try {
                // Send as HTML or Text depending on the campaign settings
                if ($campaign->emailContent->format === 'html') {
                    Mail::html($parsedBody, function($mail) use ($message, $parsedSubject) {
                        $mail->to($message->email)
                             ->subject($parsedSubject);
                    });
                } else {
                    Mail::raw($parsedBody, function($mail) use ($message, $parsedSubject) {
                        $mail->to($message->email)
                             ->subject($parsedSubject);
                    });
                }
                
                $message->update(['status' => 'sent']);
                $smtp->increment('sent_today');
                $campaign->increment('sent');
                $campaign->user->subscription->increment('emails_used');
                
            } catch (\Exception $e) {
                $message->update(['status' => 'failed']);
            }
        }
    }

    /**
     * Replaces {{ variables }} with actual contact data.
     */
    private function replaceVariables($text, $contact)
    {
        // If there's no contact found (fallback), just strip the variables out so it doesn't look broken
        if (!$contact) {
            return preg_replace('/\{\{\s*[^}]+\s*\}\}/', '', $text);
        }

        // Use Regex to find everything inside {{ }}
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function($matches) use ($contact) {
            $key = trim($matches[1]); // e.g., 'name', 'email', or 'meta.address'

            if ($key === 'name') return $contact->name ?? '';
            if ($key === 'email') return $contact->email ?? '';

            // Handle nested JSON meta columns (e.g., meta.child_name)
            if (str_starts_with($key, 'meta.')) {
                $metaKey = substr($key, 5); // remove 'meta.'
                $meta = $contact->meta ?? [];
                return $meta[$metaKey] ?? ''; // Return the value, or an empty string if it doesn't exist
            }

            // If a user types {{ something_random }} that isn't a variable, we just remove it
            return ''; 
        }, $text);
    }
}