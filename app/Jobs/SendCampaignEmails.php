<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\SmtpServer;
use App\Models\Contact;
use App\Models\SeedInbox;
use App\Services\Mail\MailManager;
use App\Services\Rotation\InboxRotator;
use App\Jobs\SendSeedEmailJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaignId;

    public function __construct($campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function handle()
    {
        $campaign = Campaign::with('emailContent', 'user.subscription')
            ->whereHas('messages', function ($q) {
                $q->where('status', 'pending');
            })
            ->find($this->campaignId);

        if (!$campaign || !$campaign->user || !$campaign->user->subscription) return;

        // Use InboxRotator to pick the best SMTP server
        $rotator = app(InboxRotator::class);
        $smtp = $rotator->pick($campaign);

        if (!$smtp || !$campaign->user->hasQuota()) {
            return; // No available inbox or quota exceeded
        }

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

            // Inject Tracking with UUID
            $parsedBody = $this->injectTracking($parsedBody, $message, $campaign->emailContent->format);

            try {
                $smtp->increment('sent_last_24h');
                // Use the new provider system
                $provider = MailManager::resolve($smtp);

                $provider->send([
                    'to' => $message->email,
                    'subject' => $parsedSubject,
                    'body' => $parsedBody,
                    'smtp' => $smtp,
                    'message' => $message, // Pass message for provider_message_id storage
                ]);

                $message->update(['status' => 'sent',
                'smtp_server_id' => $smtp->id,]);
                $smtp->increment('sent_today');
                $smtp->increment('sent_this_hour');
                $smtp->update(['last_sent_at' => now()]);
                $campaign->increment('sent');

                // Every ~50th email, send to a seed inbox to check spam placement
                if (rand(1, 50) === 1) {
                    $seed = SeedInbox::inRandomOrder()->first();
                    if ($seed) {
                        dispatch(new SendSeedEmailJob($smtp, $seed));
                    }
                }

                if ($campaign->user->subscription) {
                    $campaign->user->subscription->increment('emails_used');
                }

                // Add random delay for next send (anti-spam)
                $delay = rand(20, 90);
                dispatch(new SendCampaignEmails($this->campaignId))
                    ->delay(now()->addSeconds($delay));

            } catch (\Exception $e) {
                Log::error('Failed to send campaign email', [
                    'campaign_id' => $this->campaignId,
                    'message_id' => $message->id,
                    'email' => $message->email,
                    'smtp_id' => $smtp->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $message->update(['status' => 'failed']);
                $smtp->increment('failure_count');
                $smtp->increment('fails_last_24h');

                if ($smtp->failure_count > 5) {
                    $smtp->update(['is_blocked' => true]);
                }
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
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function ($matches) use ($contact) {
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

    /**
     * Injects tracking pixel and wraps links.
     */
    private function injectTracking($body, $message, $format)
    {
        if ($format !== 'html') {
            return $body;
        }

        // 1. Wrap Links for Click Tracking
        // Replaces href="url" with href="track-url"
        $body = preg_replace_callback('/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/', function ($matches) use ($message) {
            $url = $matches[2];
            $trackingUrl = url("/track/click/{$message->message_uuid}?redirect=" . urlencode($url));
            return str_replace($url, $trackingUrl, $matches[0]);
        }, $body);

        // 2. Append Open Tracking Pixel
        $pixelUrl = url("/track/open/{$message->message_uuid}");
        $body .= '<img src="' . $pixelUrl . '" width="1" height="1" style="display:none !important;" />';

        return $body;
    }
}