<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SeedInbox;
use App\Models\SpamCheck;
use Webklex\IMAP\Facades\Client;
use App\Models\SmtpServer;

class CheckSpamPlacement extends Command
{
    protected $signature = 'spam:check';

    public function handle()
    {
        $seeds = SeedInbox::all();

        foreach ($seeds as $seed) {
            try {
                $client = Client::make([
                    'host' => $seed->imap_host,
                    'port' => $seed->imap_port,
                    'encryption' => 'ssl',
                    'validate_cert' => true,
                    'username' => $seed->imap_username,
                    'password' => $seed->imap_password,
                    'protocol' => 'imap'
                ]);

                $client->connect();
                $folders = ['INBOX', '[Gmail]/Spam', 'Spam', 'Junk'];

                foreach ($folders as $folderName) {
                    $folder = $client->getFolder($folderName);
                    if (!$folder) continue;

                    $messages = $folder->query()->since(now()->subMinutes(30))->get();

                    foreach ($messages as $msg) {
                        // Extract application-specific message UUID and SMTP ID from custom headers
                        $appMessageUuid = $msg->getHeaders()->get('x-app-message-uuid')[0] ?? null;
                        $appSmtpId = $msg->getHeaders()->get('x-app-smtp-id')[0] ?? null;

                        if (!$appMessageUuid || !$appSmtpId) {
                            continue; // Skip if custom tracking headers are missing
                        }
                        
                        // Prevent duplicate counting
                        if (SpamCheck::where('message_uuid', $appMessageUuid)->exists()) {
                            continue;
                        }
                        
                        if ($appSmtpId) {
                            $placement = (str_contains(strtolower($folderName), 'spam') || str_contains(strtolower($folderName), 'junk')) ? 'spam' : 'inbox';
                            SpamCheck::create([
                                'smtp_server_id' => $appSmtpId,
                                'seed_inbox_id' => $seed->id,
                                'message_uuid' => $appMessageUuid,
                                'placement' => $placement,
                                'checked_at' => now(),
                            ]);

                            $smtp = SmtpServer::find($appSmtpId);
                            if (!$smtp) continue;

                            if ($placement === 'inbox') {
                                $smtp->increment('inbox_hits');
                            } else {
                                $smtp->increment('spam_hits');
                            }


                            $total = max(1, $smtp->inbox_hits + $smtp->spam_hits);
                            $newPlacementScore = ($smtp->inbox_hits / $total) * 100;

                            $smtp->update([
                                'placement_score' => $newPlacementScore
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Seed check failed for {$seed->email}: " . $e->getMessage());
            }
        }
        $this->info('Spam placement check completed.');
    }
}
