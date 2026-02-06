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
                // Find the contact to get their real name
                $contact = Contact::where('email', $message->email)->first();

                $replacements = [
                    '@{{ $name }}'    => $contact->name ?? 'Friend',
                    '@{{ $email }}'   => $contact->email,
                    '@{{ $country }}' => $contact->country ?? 'N/A',
                ];

                if (!empty($contact->meta) && is_array($contact->meta)) {
                    foreach ($contact->meta as $key => $value) {
                        $replacements["@{{ \$meta.$key }}"] = $value;
                    }
                }

                $body = str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $campaign->emailContent->body
                );

                $subject = str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $campaign->emailContent->subject
                );

                $isHtml = $campaign->emailContent->format === 'html';


                $this->info("Sending personalized email to {$contact->email}...");

                Mail::mailer('dynamic_smtp')->send(
                    [],
                    [],
                    function ($msg) use ($message, $campaign, $server, $body, $isHtml) {

                        $senderEmail = $campaign->user->email;
                        $senderName  = $campaign->user->name;

                        $msg->to($message->email)
                            ->subject($campaign->emailContent->subject)

                            ->from($senderEmail, $senderName);

                        if ($isHtml) {
                            $msg->html($body);
                        } else {
                            $msg->text($body);
                        }
                    }
                );

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
