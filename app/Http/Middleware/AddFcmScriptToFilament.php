<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddFcmScriptToFilament
{
    
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // أضف الـ script فقط للـ Filament pages
        if ($response->headers->get('content-type') && str_contains($response->headers->get('content-type'), 'text/html')) {
            $content = $response->getContent();

            // تحقق إذا كانت Filament page
            if (str_contains($content, 'filament') || str_contains($request->path(), 'vendor')) {
                // أضف meta tag للـ CSRF token
                $csrfToken = csrf_token();
                $metaTag = "<meta name=\"csrf-token\" content=\"{$csrfToken}\">";
                $content = str_replace('</head>', $metaTag . '</head>', $content);

                // أضف الـ FCM script
                $fcmScript = $this->getFcmScript();
                $content = str_replace('</body>', $fcmScript . '</body>', $content);

                $response->setContent($content);
            }
        }

        return $response;
    }

    private function getFcmScript(): string
    {
        return <<<'SCRIPT'
<script>
    console.log('🚀 FCM Script loaded from middleware');

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

        // احصل على CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
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
                saveFcmToken();
            }, 2000);
        });
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
SCRIPT;
    }
}
