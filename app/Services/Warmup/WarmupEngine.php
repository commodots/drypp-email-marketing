<?php

namespace App\Services\Warmup;

use App\Jobs\SendWarmupEmailJob;
use App\Models\SmtpServer;
use App\Models\WarmupContact;

class WarmupEngine
{
    public function run()
    {
        $smtps = SmtpServer::where('warmup_enabled', 1)->get();

        foreach ($smtps as $smtp) {

            if ($smtp->warmup_sent_today >= $smtp->warmup_daily_limit) {
                continue;
            }

            $contacts = WarmupContact::inRandomOrder()->limit(5)->get();

            foreach ($contacts as $contact) {

                dispatch(new SendWarmupEmailJob($smtp, $contact))
                    ->delay(now()->addSeconds(rand(30, 180)));

                $smtp->increment('warmup_sent_today');
            }
        }
    }
}