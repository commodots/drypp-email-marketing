<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\SmtpServer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;

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
        $smtp = SmtpServer::where('active', 1)->first();
        if (!$smtp) return;

        config([
            'mail.mailers.smtp.host' => $smtp->host,
            'mail.mailers.smtp.port' => $smtp->port,
            'mail.mailers.smtp.username' => $smtp->username,
            'mail.mailers.smtp.password' => $smtp->password,
            'mail.mailers.smtp.encryption' => $smtp->encryption,
        ]);

        // Force refresh the mailer instance
        Mail::purge('smtp');

        $body = str_replace('{{ name }}', $this->contact->name ?? 'there', $this->step->body);

        Mail::html($body, function ($mail) {
            $mail->to($this->contact->email)
                ->subject($this->step->subject);
        });
    }
}