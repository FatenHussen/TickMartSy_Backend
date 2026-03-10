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
            $rawName = $attributeValue->name;
            $translations = $attributeValue->getTranslations('name') ?? [];

            if (empty($translations) && is_string($rawName)) {
                $decoded = json_decode($rawName, true);
                if (is_array($decoded)) {
                    $translations = $decoded;
                }
            }

            $displayName = $translations[app()->getLocale()]
                ?? $translations['en']
                ?? $translations['ar']
                ?? $rawName;

            if (is_array($displayName)) {
                $displayName = $displayName['en']
                    ?? $displayName['ar']
                    ?? reset($displayName);
            }

            $displayName = is_string($displayName) ? $displayName : (string) $displayName;

            $isHex = is_string($displayName) && preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $displayName);
            $isBareHex = is_string($displayName) && preg_match('/^([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $displayName);
            $colorValue = $isBareHex ? ('#' . $displayName) : $displayName;
            $showColor = $type === 'color' || $isHex || $isBareHex;
        @endphp

        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $attribute->name }}:
            </span>

            @if($showColor)
                <span class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded">
                    {{ $displayName }}
                </span>
            @elseif($type === 'circle')
                {{-- ??? ????? --}}
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center bg-gray-100 dark:bg-gray-800"
                    >
                        <span class="text-xs font-medium">{{ $displayName }}</span>
                    </div>
                </div>
            @else
                {{-- ??? ???? ?? ???? --}}
                <span class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded">
                    {{ $displayName }}
                </span>
            @endif
        </div>
    @endforeach
</div>
