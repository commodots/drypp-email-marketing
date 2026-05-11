<?php

namespace App\Services\Mail;

use Aws\Ses\SesClient;

class SesProvider implements MailProvider
{
    public function send($data)
    {
        $client = new SesClient([
            'region' => $data['smtp']->region,
            'version' => 'latest',
            'credentials' => [
                'key' => $data['smtp']->access_key,
                'secret' => $data['smtp']->secret_key,
            ],
        ]);

        $response = $client->sendEmail([
            'Destination' => [
                'ToAddresses' => [$data['to']],
            ],
            'Message' => [
                'Body' => [
                    'Html' => ['Data' => $data['body']],
                ],
                'Subject' => ['Data' => $data['subject']],
            ],
            'Source' => $data['sender_email'] ?? config('mail.from.address'),
        ]);

        if (isset($data['message'])) {
            $data['message']->update([
                'provider_message_id' => $response['MessageId'] ?? null
            ]);
        }
    }
}