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
            'vapidKey' => env('FIREBASE_VAPID_KEY', ''),
            'apiKey' => "AIzaSyCaWSRgKaqd0P__owf8MtZLhdInskytXKo",
            'authDomain' => "tikmool-app-3241.firebaseapp.com",
            'projectId' => "tikmool-app-3241",
            'storageBucket' => "tikmool-app-3241.firebasestorage.app",
            'messagingSenderId' => "786190897596",
            'appId' => "1:786190897596:web:5a3eaba811e0f45141dbb9",
            'measurementId' => "G-BFM25BQN9N"
        ];
    }

    public static function getProjectId()
    {
        $config = self::getConfig();
        return $config['projectId'] ?? null;
    }
}
