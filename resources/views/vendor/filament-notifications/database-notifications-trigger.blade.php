@php
    $notifications = $this->getDatabaseNotifications();
    $unreadNotificationsCount = $this->getUnreadDatabaseNotificationsCount();
@endphp

<x-filament::icon-button
    :badge="$unreadNotificationsCount"
    icon="heroicon-o-bell"
    :label="__('filament-notifications::database.modal.heading')"
    x-on:click="$dispatch('open-modal', { id: 'database-notifications' })"
/>

<script>
    // Listen for new notifications
    window.addEventListener('database-notification-sent', event => {
        // Show toast notification
        new FilamentNotification()
            .title(event.detail.title || 'إشعار جديد')
            .body(event.detail.body || '')
            .success()
            .send();
    });
</script>
