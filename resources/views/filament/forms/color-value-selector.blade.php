@php
    $options = $getViewData()['options'] ?? [];
    $type = $getViewData()['type'] ?? 'text';
    $statePath = $getStatePath();
@endphp

<div x-data="{ state: @entangle($statePath) }">
    @if($type === 'color' && !empty($options))
        <div class="space-y-2">
            <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    القيمة
                    <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                </span>
            </label>
            
            <div class="flex flex-wrap gap-3">
                @foreach($options as $valueId => $colorHex)
                    <button
                        type="button"
                        @click="state = {{ $valueId }}"
                        :class="state == {{ $valueId }} ? 'ring-2 ring-primary-600 ring-offset-2 dark:ring-offset-gray-900' : 'ring-1 ring-gray-300 dark:ring-gray-600'"
                        class="relative flex flex-col items-center gap-2 p-2 rounded-lg transition-all hover:shadow-md bg-white dark:bg-gray-800"
                        title="{{ $colorHex }}"
                    >
                        <div 
                            class="w-8 h-8 rounded-full border-2 border-gray-200 dark:border-gray-700 shadow-sm"
                            style="background-color: {{ $colorHex }}"
                        ></div>
                        <span class="text-[10px] text-gray-600 dark:text-gray-400 font-mono">
                            {{ $colorHex }}
                        </span>
                        <div 
                            x-show="state == {{ $valueId }}"
                            x-cloak
                            class="absolute -top-1 -right-1 w-4 h-4 bg-primary-600 rounded-full flex items-center justify-center"
                        >
                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @else
        {{-- Regular select for non-color attributes --}}
        <div class="fi-fo-field-wrp">
            <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    القيمة
                    <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                </span>
            </label>
            
            <select 
                x-model="state"
                class="fi-select-input block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:border-primary-600 focus:ring-primary-600 rounded-lg shadow-sm"
            >
                <option value="">اختر قيمة</option>
                @foreach($options as $valueId => $label)
                    <option value="{{ $valueId }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif
</div>
