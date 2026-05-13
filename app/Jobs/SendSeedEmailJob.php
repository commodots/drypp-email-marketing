<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use App\Services\Mail\MailManager;

class SendSeedEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
     public function __construct(public $smtp, public $seed)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $provider = MailManager::resolve($this->smtp);

        $seedMessageUuid = (string) Str::uuid(); // Generate a unique UUID for this seed email

        $subject = "Test " . Str::random(5);

        $provider->send([
            'to' => $this->seed->email,
            'subject' => $subject,
            'body' => "Seed test email",
            'message_uuid' => $seedMessageUuid, // Pass the generated UUID
            'smtp' => $this->smtp,
        ]);
    
    }
}
