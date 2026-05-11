<?php

namespace App\Services;

use App\Models\SmtpServer;
use App\Services\Mail\MailManager;

class TransactionalEmailService
{
    public function send($to, $subject, $body)
    {
        $smtp = SmtpServer::where('is_transactional', 1)->first();

        if (!$smtp) {
            throw new \Exception('No transactional SMTP configured');
        }

        $provider = MailManager::resolve($smtp);

        return $provider->send([
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'smtp' => $smtp,
        ]);
    }
}