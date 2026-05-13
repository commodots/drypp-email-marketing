<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Mail;

class SmtpProvider implements MailProvider
{
    public function send($data)
    {
        config([
            'mail.mailers.smtp.host' => $data['smtp']->host,
            'mail.mailers.smtp.port' => $data['smtp']->port,
            'mail.mailers.smtp.username' => $data['smtp']->username,
            'mail.mailers.smtp.password' => $data['smtp']->password,
            'mail.mailers.smtp.encryption' => $data['smtp']->encryption ?? 'tls',
        ]);

       
        Mail::mailer('smtp')->send([], [], function ($message) use ($data) {
            $message->to($data['to'])
                    ->subject($data['subject'])
                    ->html($data['body']);
                    
            // These headers allow your IMAP checker to track which SMTP sent the mail and the specific message
            $message->getHeaders()->addTextHeader('X-App-Smtp-ID', (string) $data['smtp']->id);
            if (isset($data['message_uuid'])) {
                $message->getHeaders()->addTextHeader('X-App-Message-UUID', $data['message_uuid']);
            }
        });
    }
}