<?php

namespace App\Jobs;

use App\Services\Mail\MailManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWarmupReplyJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public $originalMessage)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $provider = MailManager::resolve($this->originalMessage->smtp);

        $provider->send([
            'to' => $this->originalMessage->from_email,
            'subject' => "Re: " . $this->originalMessage->subject,
            'body' => "Got it, thanks!",
            'smtp' => $this->originalMessage->smtp,
        ]);
    }
}
