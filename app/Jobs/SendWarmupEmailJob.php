<?php

namespace App\Jobs;

use App\Services\Mail\MailManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWarmupEmailJob implements ShouldQueue
{
    use Queueable;
    

    /**
     * Create a new job instance.
     */
  public function __construct(public $smtp, public $contact)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $provider = MailManager::resolve($this->smtp);

        $subject = $this->randomSubject();
        $body = $this->randomBody();

        $provider->send([
            'to' => $this->contact->email,
            'subject' => $subject,
            'body' => $body,
            'smtp' => $this->smtp,
        ]);
    }
    
    private function randomSubject()
    {
        return collect([
            "Quick question",
            "Hey, are you available?",
            "Following up",
            "Just checking in",
        ])->random();
    }
    private function randomBody()
    {
        return collect([
            "Hey, just testing something. Ignore this 🙂",
            "Quick check to see if this landed properly.",
            "Trying out a new setup, please ignore.",
        ])->random();
    }
}
