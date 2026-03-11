<div style="display: none;">
    <!-- Hidden widget for FCM token management -->
    <script>
        console.log('🚀 FCM Token Widget loaded');

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Dashboard loaded - DOMContentLoaded');

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
                    })
                    .catch(error => console.error('❌ Service Worker registration failed:', error));
            } else {
                console.warn('⚠️ Service Workers not supported in this browser');
            }
        });

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
</div>
