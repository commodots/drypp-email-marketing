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
            'Headers' => [
                [
                    'Name' => 'X-App-Smtp-ID',
                    'Value' => (string) $data['smtp']->id,
                ],
                // Add X-App-Message-UUID if available
                ...(isset($data['message_uuid']) ? [['Name' => 'X-App-Message-UUID', 'Value' => $data['message_uuid']]] : []),
            ],
        ]);

        if (isset($data['message'])) {
            $data['message']->update([
                'provider_message_id' => $response['MessageId'] ?? null
            ]);
        }
    }
}