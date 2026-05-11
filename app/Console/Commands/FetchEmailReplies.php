<?php

namespace App\Console\Commands;

use App\Models\CampaignMessage;
use App\Models\EmailReply;
use App\Models\SmtpServer;
use Webklex\IMAP\Facades\Client;
use Illuminate\Console\Command;

class FetchEmailReplies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-email-replies';

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
        $smtps = SmtpServer::whereNotNull('imap_host')->get();

    foreach ($smtps as $smtp) {

        $client = Client::make([
            'host' => $smtp->imap_host,
            'port' => $smtp->imap_port,
            'encryption' => 'ssl',
            'validate_cert' => true,
            'username' => $smtp->imap_username,
            'password' => $smtp->imap_password,
        ]);

        $client->connect();

        $folder = $client->getFolder('INBOX');

        $messages = $folder->query()
            ->since(now()->subDays(2))
            ->get();

        foreach ($messages as $msg) {

            $inReplyTo = $msg->getInReplyTo();

            if (!$inReplyTo) continue;

            $campaignMessage = CampaignMessage::where('provider_message_id', $inReplyTo)->first();

            if (!$campaignMessage) continue;

            // Save reply
            EmailReply::create([
                'campaign_message_id' => $campaignMessage->id,
                'from_email' => $msg->getFrom()[0]->mail,
                'body' => $msg->getTextBody(),
            ]);

            $campaignMessage->update([
                'replied' => true,
                'replied_at' => now(),
            ]);
        }
    }
    }
}
