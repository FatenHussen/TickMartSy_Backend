@php
    $variant = $getRecord();
    $attributeValues = \App\Models\AttributeValue::with('categoryAttribute')
        ->whereIn('id', $variant->attributes_values_ids ?? [])
        ->get();
@endphp

<div class="space-y-2">
    @foreach($attributeValues as $attributeValue)
        @php
            $attribute = $attributeValue->categoryAttribute;
            $type = $attribute->type ?? 'square';
            $name = $attributeValue->name;
        @endphp

        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $attribute->name }}:
            </span>

            @if($type === 'color')
                {{-- عرض اللون كمربع ملون --}}
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded border-2 border-gray-300 dark:border-gray-600"
                        style="background-color: {{ $name }}"
                        title="{{ $name }}"
                    ></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $name }}</span>
                </div>
            @elseif($type === 'circle')
                {{-- عرض دائرة --}}
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center bg-gray-100 dark:bg-gray-800"
                    >
                        <span class="text-xs font-medium">{{ $name }}</span>
                    </div>
                </div>
            @else
                {{-- عرض مربع نص عادي --}}
                <span class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded">
                    {{ $name }}
                </span>
            @endif
        </div>
    @endforeach
</div>
