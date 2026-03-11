<script>
    console.log('🚀 FCM Script loaded');

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

                    // احفظ FCM Token
                    saveFcmToken();
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
        const token = generateUniqueToken();
        console.log('📤 Attempting to save FCM token:', token);

        // استخدم Livewire dispatch
        if (window.Livewire) {
            console.log('📡 Dispatching to Livewire...');
            Livewire.dispatch('saveFcmToken', { token: token });
        } else {
            console.error('❌ Livewire not available');
        }
    }

    /**
     * توليد token فريد للمتصفح
     */
    function generateUniqueToken() {
        const stored = localStorage.getItem('fcm_token');
        if (stored) {
            console.log('📦 Using stored token from localStorage');
            return stored;
        }

        const token = 'web_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('fcm_token', token);
        console.log('🆕 Generated new token:', token);
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
