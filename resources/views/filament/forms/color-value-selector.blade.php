@php
    $options = $getViewData()['options'] ?? [];
    $type = $getViewData()['type'] ?? 'text';
    $statePath = $getStatePath();
@endphp

<div x-data="{ state: @entangle($statePath) }">
    @if($type === 'color')
        <div style="margin-top: 0.5rem;">
            <label style="display: inline-flex; align-items: center; gap: 0.75rem; font-size: 0.875rem; font-weight: 500; color: #111827;">
                <span>
                    القيمة
                    <sup style="color: #dc2626; font-weight: 500;">*</sup>
                </span>
            </label>

            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 0.5rem; padding: 0.75rem; background-color: #f9fafb; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                @forelse($options as $valueId => $colorHex)
                    <button
                        type="button"
                        @click="state = {{ $valueId }}"
                        style="position: relative; width: 48px; height: 48px; border-radius: 50%; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex-shrink: 0; background-color: {{ $colorHex }}; border: 2px solid #d1d5db; cursor: pointer;"
                        :style="state == {{ $valueId }} ? 'border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2); transform: scale(1.1);' : ''"
                        onmouseover="this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'"
                        onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'"
                        title="{{ $colorHex }}"
                    >
                        <span
                            x-show="state == {{ $valueId }}"
                            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center;"
                        >
                            <svg style="width: 24px; height: 24px; color: white; filter: drop-shadow(0 1px 2px rgba(0,0,0,0.5));" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </span>
                    </button>
                @empty
                    <p style="font-size: 0.875rem; color: #6b7280;">لا توجد ألوان متاحة</p>
                @endforelse
            </div>
        </div>
    @else
        <div style="margin-top: 0.5rem;">
            <label style="display: inline-flex; align-items: center; gap: 0.75rem; font-size: 0.875rem; font-weight: 500; color: #111827;">
                <span>
                    القيمة
                    <sup style="color: #dc2626; font-weight: 500;">*</sup>
                </span>
            </label>

            <select
                x-model="state"
                style="display: block; width: 100%; border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; margin-top: 0.25rem;"
            >
                <option value="">اختر قيمة</option>
                @foreach($options as $valueId => $label)
                    <option value="{{ $valueId }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif
</div>
