<?php

namespace App\Helpers;

class FirebaseConfigHelper
{
    public static function getConfig()
    {
        $credPath = storage_path('app/cred.json');

        if (!file_exists($credPath)) {
            return null;
        }

        $cred = json_decode(file_get_contents($credPath), true);

        return [
            'projectId' => $cred['project_id'] ?? null,
            'apiKey' => env('FIREBASE_API_KEY', 'AIzaSyDummyKeyForWeb'), // هذا يحتاج يكون من Firebase Console
            'authDomain' => $cred['project_id'] . '.firebaseapp.com',
            'storageBucket' => $cred['project_id'] . '.appspot.com',
            'messagingSenderId' => $cred['client_id'] ?? null,
            'appId' => env('FIREBASE_APP_ID', '1:' . ($cred['client_id'] ?? '') . ':web:dummy'),
            'vapidKey' => env('FIREBASE_VAPID_KEY', ''),
        ];
    }

    public static function getProjectId()
    {
        $config = self::getConfig();
        return $config['projectId'] ?? null;
    }
}
