<?php

namespace App\Services\Tenants;

use Exception;
use GuzzleHttp\Client;
use Log;

class ResendService
{

    public static function send($email, $subject, $content, $cc = '', $attachments = null) 
    {
        sleep(rand(1, 3)); // Wait randomly between 1 and 3 seconds

        $client = new Client();
        $apiKey = config("services.sender.key");

        if (!$attachments) {
      
            $response = $client->post('https://api.resend.com/emails', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'from'    => config('mail.from.name') . ' <' . config('mail.from.address') . '>',
                    'to'      => [$email],
                    'subject' => $subject,
                    'html'    => $content,
                    'cc'      => [$cc]
                ],
            ]);

        } else {
      
            $attachment = [
                'path' => $attachments['path'],
                'filename' => $attachments['filename'],
            ];
            $response = $client->post('https://api.resend.com/emails', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'from'    => config('mail.from.address'),
                    'to'      => [$email],
                    'subject' => $subject,
                    'html'    => $content,
                    'cc'      => [$cc],
                    'attachments' => [$attachment],
                ],
            ]);
        }
      
        $data = json_decode($response->getBody(), true);

        Log::info('Resend error: ' . json_encode($data));
    }
}