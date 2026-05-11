<?php

namespace App\Services\Mail;

use SendGrid\Mail\Mail;

class SendGridProvider implements MailProvider
{
    public function send($data)
    {
        $email = new Mail();
        $email->setFrom($data['sender_email'] ?? config('mail.from.address'));
        $email->setSubject($data['subject']);
        $email->addTo($data['to']);
        $email->addContent("text/html", $data['body']);
        $email->addCustomArg('uuid', $data['message']->message_uuid ?? null);
        $email->addCustomArg('message_uuid', $data['message']->message_uuid ?? null);

        $sendgrid = new \SendGrid($data['smtp']->api_key);
        $response = $sendgrid->send($email);

        if (isset($data['message'])) {
            $data['message']->update([
                'provider_message_id' => $response->headers()['X-Message-Id'] ?? null
            ]);
        }
    }
}