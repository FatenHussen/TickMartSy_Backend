{{-- @extends('filament::layouts.app')

@section('content')
    <div>
        @livewire('filament.pages.dashboard')
    </div>
@endsection

@push('scripts')
    <script src="https://www.gstatic.com/firebasejs/10.7.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.0/firebase-messaging.js"></script>

    <script>
        const firebaseConfig = {
            apiKey: "AIzaSyCaWSRgKaqd0P__owf8MtZLhdInskytXKo",
            authDomain: "tikmool-app-3241.firebaseapp.com",
            projectId: "tikmool-app-3241",
            storageBucket: "tikmool-app-3241.firebasestorage.app",
            messagingSenderId: "786190897596",
            appId: "1:786190897596:web:5a3eaba811e0f45141dbb9",
            measurementId: "G-BFM25BQN9N"
        };

        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        document.addEventListener('DOMContentLoaded', function() {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(registration => {
                        if ('Notification' in window && Notification.permission === 'default') {
                            Notification.requestPermission().then(permission => {
                                if (permission === 'granted') {
                                    saveFcmToken();
                                }
                            });
                        } else if (Notification.permission === 'granted') {
                            saveFcmToken();
                        }
                    });
            }
        });

        function saveFcmToken() {
            messaging.getToken({
                    vapidKey: "BCMDGVeweEPiu0hfisL2YH2Nzwlmr6P6sEsxfKibl20AmWdgVg2DfmzjWGPm8g-tI2sLMbPls1bMjsItYvF3R88"
                })
                .then(token => {
                    if (token) {
                        fetch('/api/vendor/fcm-token', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                                    '',
                            },
                            credentials: 'include',
                            body: JSON.stringify({
                                token: token
                            })
                        });
                    }
                });
        }

        messaging.onMessage((payload) => {
            if (Notification.permission === 'granted') {
                new Notification(payload.notification.title, {
                    body: payload.notification.body,
                });
            }
        });
    </script>
@endpush --}}
<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.0/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.7.0/firebase-messaging.js";

const firebaseConfig = {
    apiKey: "AIzaSyCaWSRgKaqd0P__owf8MtZLhdInskytXKo",
    authDomain: "tikmool-app-3241.firebaseapp.com",
    projectId: "tikmool-app-3241",
    storageBucket: "tikmool-app-3241.firebasestorage.app",
    messagingSenderId: "786190897596",
    appId: "1:786190897596:web:5a3eaba811e0f45141dbb9"
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

async function initFCM() {

    const permission = await Notification.requestPermission();

    if (permission !== "granted") {
        return;
    }

    const token = await getToken(messaging, {
        vapidKey: "BCMDGVeweEPiu0hfisL2YH2Nzwlmr6P6sEsxfKibl20AmWdgVg2DfmzjWGPm8g-tI2sLMbPls1bMjsItYvF3R88"
    });

    console.log("FCM TOKEN:", token);

    await fetch("/api/vendor/fcm-token", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            token: token
        })
    });

}

initFCM();

onMessage(messaging, (payload) => {
    new Notification(payload.notification.title, {
        body: payload.notification.body
    });
});

</script>