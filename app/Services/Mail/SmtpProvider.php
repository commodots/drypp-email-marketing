<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Mail;
use App\Mail\GenericMail;

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

        Mail::mailer('smtp')->to($data['to'])->send(
            new GenericMail($data['subject'], $data['body'])
        );
    }
}