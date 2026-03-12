// Service Worker للإشعارات
self.addEventListener('push', function(event) {
    console.log('Push notification received:', event);

    let notificationData = {
        title: 'إشعار جديد',
        body: 'لديك إشعار جديد',
        icon: '/images/notification-icon.png',
        badge: '/images/notification-badge.png',
        tag: 'notification',
        requireInteraction: true,
    };

    if (event.data) {
        try {
            const data = event.data.json();
            notificationData = {
                ...notificationData,
                title: data.title || notificationData.title,
                body: data.body || notificationData.body,
                data: data.data || {},
            };
        } catch (e) {
            notificationData.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(notificationData.title, notificationData)
    );
});

// عند الضغط على الإشعار
self.addEventListener('notificationclick', function(event) {
    console.log('Notification clicked:', event);
    event.notification.close();

    const urlToOpen = '/vendor'; // الرابط الافتراضي

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(function(clientList) {
            // تحقق إذا كان الـ tab مفتوح
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            // إذا ما كان مفتوح، افتح tab جديد
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// عند إغلاق الإشعار
self.addEventListener('notificationclose', function(event) {
    console.log('Notification closed:', event);
});
