<x-filament-widgets::widget>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">الاشعارات</h3>
            @if($this->getUnreadCount() > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-sm text-primary-600 hover:text-primary-700"
                >
                    تحديد الكل كمقروء
                </button>
            @endif
        </div>
    </x-slot>

    <div class="space-y-2">
        @forelse($this->getNotifications() as $notification)
            <div class="flex items-start gap-3 p-3 rounded-lg border {{ $notification->read_at ? 'bg-gray-50 border-gray-200' : 'bg-blue-50 border-blue-200' }}">
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-semibold text-sm">{{ $notification->title }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ $notification->body }}</p>
                        </div>
                        @if(!$notification->read_at)
                            <button
                                wire:click="markAsRead('{{ $notification->id }}')"
                                class="text-xs text-primary-600 hover:text-primary-700 whitespace-nowrap ml-2"
                            >
                                تحديد كمقروء
                            </button>
                        @endif
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs text-gray-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                        <span class="text-xs px-2 py-1 rounded-full {{ $this->getNotificationBadgeColor($notification->type) }}">
                            {{ $this->getNotificationTypeLabel($notification->type) }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8">
                <p class="text-gray-500">لا توجد اشعارات</p>
            </div>
        @endforelse
    </div>
</x-filament-widgets::widget>
