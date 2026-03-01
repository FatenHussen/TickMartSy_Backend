@php
    $workingHours = $getState();
    $days = [
        'monday' => 'الإثنين',
        'tuesday' => 'الثلاثاء',
        'wednesday' => 'الأربعاء',
        'thursday' => 'الخميس',
        'friday' => 'الجمعة',
        'saturday' => 'السبت',
        'sunday' => 'الأحد',
    ];
@endphp

@if(empty($workingHours))
    <div class="text-gray-500 text-center py-4">
        🕐 لا توجد أوقات عمل محددة
    </div>
@else
    <div class="space-y-2">
        @foreach($workingHours as $day => $hours)
            @php
                $dayName = $days[$day] ?? $day;
                $closed = $hours['closed'] ?? false;
            @endphp
            
            <div class="flex items-center justify-between p-3 rounded-lg {{ $closed ? 'bg-gray-50' : 'bg-green-50' }}">
                <div class="flex items-center gap-2">
                    @if($closed)
                        <span class="text-red-500">❌</span>
                    @else
                        <span class="text-green-500">✅</span>
                    @endif
                    <span class="font-semibold text-gray-700">{{ $dayName }}</span>
                </div>
                
                <div class="text-gray-600">
                    @if($closed)
                        <span class="text-red-600 font-medium">مغلق</span>
                    @else
                        <span class="font-mono">{{ $hours['open'] ?? '-' }}</span>
                        <span class="mx-2">-</span>
                        <span class="font-mono">{{ $hours['close'] ?? '-' }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
