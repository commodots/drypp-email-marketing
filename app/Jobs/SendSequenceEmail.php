<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\SmtpServer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use App\Services\Mail\MailManager;

class SendSequenceEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $contact;
    public $step;

    public function __construct($contact, $step)
    {
        $this->contact = $contact;
        $this->step = $step;
    }

    public function handle()
    {
        // Pick the best SMTP using the Rotator
        $rotator = app(\App\Services\Rotation\InboxRotator::class);
        $smtp = $rotator->pick(); 

        if (!$smtp) return;

        $provider = MailManager::resolve($smtp);
        $body = str_replace('{{ name }}', $this->contact->name ?? 'there', $this->step->body);

        $provider->send([
            'to' => $this->contact->email,
            'subject' => $this->step->subject,
            'body' => $body,
            'smtp' => $smtp,
        ]);

        //Stats & Seed Checking
        $smtp->increment('sent_last_24h');
        $smtp->increment('sent_today');

        if (rand(1, 50) === 1) {
            $seed = SeedInbox::inRandomOrder()->first();
            if ($seed) {
                dispatch(new SendSeedEmailJob($smtp, $seed));
            }
        }
    }
}