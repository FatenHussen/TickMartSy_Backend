@extends('filament::layouts.app')

@section('content')
    <div>
        @livewire('filament.pages.dashboard')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Dashboard loaded');

            // تسجيل Service Worker
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(registration => {
                        console.log('✅ Service Worker registered successfully');

                        // اطلب إذن الإشعارات
                        if ('Notification' in window) {
                            if (Notification.permission === 'default') {
                                Notification.requestPermission().then(permission => {
                                    console.log('Notification permission:', permission);
                                });
                            } else if (Notification.permission === 'granted') {
                                console.log('✅ Notifications already permitted');
                            }
                        }

                        // احفظ FCM Token
                        saveFcmToken();

                        // استمع للإشعارات في الخلفية
                        if ('serviceWorker' in navigator) {
                            navigator.serviceWorker.addEventListener('message', function(event) {
                                console.log('Message from Service Worker:', event.data);
                            });
                        }
                    })
                    .catch(error => console.error('❌ Service Worker registration failed:', error));
            } else {
                console.warn('⚠️ Service Workers not supported in this browser');
            }
        });

        /**
         * حفظ FCM Token للـ Vendor User
         */
        function saveFcmToken() {
            // توليد token فريد للمتصفح
            const token = generateUniqueToken();

            console.log('📤 Attempting to save FCM token:', token);

            saveFcmTokenViaFetch(token);
        }

        /**
         * حفظ FCM Token عبر Fetch
         */
        function saveFcmTokenViaFetch(token) {
            // احصل على CSRF token من الـ page
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                            document.querySelector('input[name="_token"]')?.value ||
                            document.querySelector('[data-csrf-token]')?.getAttribute('data-csrf-token');

            console.log('🔐 CSRF Token:', csrfToken ? 'Present' : 'Missing');

            fetch('/api/vendor/fcm-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({
                    token: token
                })
            })
            .then(response => {
                console.log('📨 Response status:', response.status);
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(`HTTP ${response.status}: ${JSON.stringify(data)}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('✅ FCM Token saved successfully:', data);
                localStorage.setItem('fcm_token', token);
            })
            .catch(error => {
                console.error('❌ Error saving FCM token:', error);
                // حاول مرة أخرى بعد 2 ثانية
                setTimeout(() => {
                    console.log('🔄 Retrying to save FCM token...');
                    saveFcmTokenViaFetch(token);
                }, 2000);
            });
        }

        /**
         * توليد token فريد للمتصفح
         */
        function generateUniqueToken() {
            const stored = localStorage.getItem('fcm_token');
            if (stored) {
                return stored;
            }

            const token = 'web_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('fcm_token', token);
            return token;
        }

        /**
         * اختبار الإشعارات (للتطوير فقط)
         */
        window.testNotification = function() {
            if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({
                    type: 'SHOW_NOTIFICATION',
                    title: 'إشعار اختبار',
                    options: {
                        body: 'هذا إشعار اختبار من الـ Dashboard',
                        icon: '/images/notification-icon.png',
                        badge: '/images/notification-badge.png',
                        tag: 'test-notification'
                    }
                });
            }
        };
    </script>
@endsection
