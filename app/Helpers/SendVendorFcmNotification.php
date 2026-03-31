<?php

namespace App\Helpers;

use App\Models\VendorFcmToken;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;

class SendVendorFcmNotification
{
    public function __construct(
        private array $fcmTokens,
        private string $title,
        private string $body,
        private array $data = []
    ) {}

    public function sendNotification()
    {
        $credentialsFilePath = storage_path('app/cred.json');

        // تحقق من وجود الملف
        if (!file_exists($credentialsFilePath)) {
            Log::error('FCM Credentials file not found', ['path' => $credentialsFilePath]);
            return;
        }

        // تحقق من إمكانية القراءة
        if (!is_readable($credentialsFilePath)) {
            Log::error('FCM Credentials file is not readable', ['path' => $credentialsFilePath]);
            return;
        }

        Log::info('FCM Credentials file loaded successfully', ['path' => $credentialsFilePath]);

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
        $responses = [];

        // حول الـ data لـ flat strings (FCM ما بتقبل nested objects)
        $flatData = $this->flattenData($this->data);

        $mediaUrl = $this->resolveMediaUrl();

        foreach ($this->fcmTokens as $token) {
            $notificationPayload = [
                "title" => $this->title,
                "body" => $this->body,
                "icon" => asset('images/notification-icon.png'),
                "badge" => asset('images/notification-badge.png'),
                "tag" => "vendor-notification-" . time(),
                "requireInteraction" => true,
            ];

            if ($mediaUrl) {
                $notificationPayload['image'] = $mediaUrl;
            }

            $data = [
                "message" => [
                    "token" => $token,
                    "data" => $flatData,
                    "webpush" => [
                        "headers" => [
                            "TTL" => "86400"
                        ],
                        "notification" => $notificationPayload,
                    ]
                ],
            ];

            $payload = json_encode($data);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/tikmool-app-3241/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            $response = curl_exec($ch);
            $err = curl_error($ch);
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
                        'token' => $token,
                        'response' => $responseData,
                    ];
                } else {
                    $successCount++;
                    $responses[] = [
                        'token' => $token,
                        'response' => $responseData,
                    ];
                }
            }
        }

        Log::info('Vendor notifications sent', [
            'message' => 'Notifications have been sent',
            'success' => $successCount,
            'failure' => $failureCount,
            'responses' => $responses,
        ]);
    }

    /**
     * حول الـ nested arrays لـ flat strings
     */
    private function flattenData(array $data, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $newKey = $prefix ? "{$prefix}_{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenData($value, $newKey));
            } else {
                $result[$newKey] = (string) $value;
            }
        }

        return $result;
    }

    private function resolveMediaUrl(): ?string
    {
        if (! empty($this->data['media']['url'])) {
            return $this->data['media']['url'];
        }

        foreach (['media_url', 'image', 'gif'] as $key) {
            if (! empty($this->data[$key])) {
                return $this->data[$key];
            }
        }

        return null;
    }
}
