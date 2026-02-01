<?php

namespace App\Helpers;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;

class SendFCMNotification
{

    public function __construct(private array  $fcmTokens, private string $title, private string $body, private array $data = []) {}

    public function sendNotification()
    {
        $credentialsFilePath = storage_path('app/cred.json');

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $accessToken = $client->getAccessToken()['access_token'];

        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json',
        ];

        $successCount = 0;
        $failureCount = 0;
        $responses    = [];

        foreach ($this->fcmTokens as $token) {
            $data = [
                "message" => [
                    "token"        => $token,
                    "notification" => [
                        "title" => $this->title,
                        "body"  => $this->body,
                    ],
                    "data" => $this->data,
                ],
            ];

            $payload = json_encode($data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/mawaeed-b493a/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            $response = curl_exec($ch);
            $err      = curl_error($ch);
            curl_close($ch);

            if ($err) {
                $failureCount++;
                $responses[] = [
                    'token' => $token,
                    'error' => $err,
                ];
            } else {
                $responseData = json_decode($response, true);
                if (isset($responseData['error'])) {
                    $failureCount++;
                    $responses[] = [
                        'token'    => $token,
                        'response' => $responseData,
                    ];
                } else {
                    $successCount++;
                    $responses[] = [
                        'token'    => $token,
                        'response' => $responseData,
                    ];
                }
            }
        }


        // return [
        //     'message'   => 'Notifications have been sent',
        //     'success'   => $successCount,
        //     'failure'   => $failureCount,
        //     'responses' => $responses,
        // ];
    }
}
