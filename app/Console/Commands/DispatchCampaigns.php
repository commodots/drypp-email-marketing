<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CampaignMessage;
use App\Models\Contact;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class DispatchCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending campaign emails using assigned SMTP servers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Campaign Worker Started... (Press Ctrl+C to stop)");

        while (true) {
            // 1. Fetch the next pending message
            $message = CampaignMessage::with(['campaign.smtp', 'campaign.emailContent'])
                ->where('status', 'pending')
                ->whereHas('campaign', function ($q) {
                    // Only process campaigns that are actively 'sending' and have a server assigned
                    $q->where('status', 'sending')->whereNotNull('smtp_id');
                })
                ->first();

            if (!$message) {
                $this->comment("Queue empty. Waiting...");
                sleep(2);
                continue;
            }

            $server = $message->campaign->smtp;
            $campaign = $message->campaign;

            //  DEBUG: Show which server we are using
            $this->info("[{$server->name}] Preparing email to: {$message->email}");

            // 2. Force Clear Old Config & Set New Config
            // This ensures we don't accidentally use the previous loop's server
            Mail::purge('dynamic_smtp');

            Config::set('mail.mailers.dynamic_smtp', [
                'transport' => 'smtp',
                'host'       => $server->host,
                'port'       => $server->port,
                'username'   => $server->username,
                'password'   => $server->password,
                'encryption' => $server->encryption,
                'timeout'    => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
            ]);


            try {
                // Find the contact, scoped securely to the campaign owner
                $contact = Contact::where('email', $message->email)
                                  ->where('user_id', $campaign->user_id)
                                  ->first();

                // Build replacements matching EXACTLY what the frontend JS inserts
                $replacements = [
                    '{{ name }}'  => $contact->name ?? 'Friend',
                    '{{ email }}' => $message->email,
                ];

                if ($contact && !empty($contact->meta) && is_array($contact->meta)) {
                    foreach ($contact->meta as $key => $value) {
                        $replacements["{{ meta.$key }}"] = $value;
                    }
                }

                // Parse the body and subject
                $body = str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $campaign->emailContent->body
                );

                $parsedSubject = str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $campaign->emailContent->subject
                );

                $isHtml = $campaign->emailContent->format === 'html';

                $this->info("Sending personalized email to {$message->email}...");

                //Send the mail
                Mail::mailer('dynamic_smtp')->send(
                    [],
                    [],
                    // MUST pass $parsedSubject into the closure with 'use'
                    function ($msg) use ($message, $campaign, $parsedSubject, $body, $isHtml) {

                        $senderEmail = $campaign->user->email;
                        $senderName  = $campaign->user->name;

                        $msg->to($message->email)
                            ->subject($parsedSubject) 
                            ->from($senderEmail, $senderName);

                        if ($isHtml) {
                            $msg->html($body);
                        } else {
                            $msg->text($body);
                        }
                    }
                );

                // Update status and progress
                $message->update(['status' => 'sent']);
                $campaign->refresh();
                $campaign->increment('sent');

                $remaining = CampaignMessage::where('campaign_id', $campaign->id)
                    ->where('status', 'pending')
                    ->count();

                if ($remaining === 0) {
                    $campaign->update(['status' => 'completed']);
                    $this->info("Campaign '{$campaign->name}' finished!");
                }
            } catch (\Exception $e) {
                $this->error("Failed: " . $e->getMessage());
                $message->update([
                    'status' => 'failed',
                ]);
            }

            usleep(100000);
        }
    }
}
