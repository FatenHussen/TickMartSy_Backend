<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.0/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.7.0/firebase-messaging.js";
console.log('test');
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

    await fetch("/vendor/fcm-token", {
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
<script>
console.log("🔥 Firebase script loaded");

// تسجيل Service Worker
if ('serviceWorker' in navigator) {

    navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then(function (registration) {

            console.log("✅ Service Worker registered:", registration);

        })
        .catch(function (error) {

            console.error("❌ Service Worker registration failed:", error);

        });

}
</script>