<?php

namespace App\Http\Requests\Admin\Section;

use App\Models\Section;

trait NormalizesSectionPayload
{
    protected function mergeNormalizedSectionPayload(): void
    {
        $this->merge($this->normalizeSectionPayload($this->all()));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeSectionPayload(array $data): array
    {
        $contentType = Section::canonicalizeContentType($data['content_type'] ?? null);
        if (!$contentType) {
            return $this->withItemTypes($data, $data['manual_model'] ?? null);
        }

        $type = $data['type'] ?? (empty($data['item_ids']) ? 'api' : 'manual');
        $merged = [
            'type' => $type,
            'content_type' => $contentType,
        ];

        if ($type === 'manual') {
            $manualModel = Section::canonicalizeContentType($data['manual_model'] ?? $contentType);
            $merged['manual_model'] = $manualModel;

            return array_merge($merged, $this->withItemTypes($data, $manualModel));
        }

        $merged['api_method'] = $data['api_method']
            ?? (Section::API_METHOD_BY_CONTENT[$contentType] ?? null);

        if ($contentType === 'restaurant') {
            $filters = $data['filters'] ?? [];
            $filters['is_restaurant'] = true;
            $merged['filters'] = $filters;
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withItemTypes(array $data, ?string $manualModel): array
    {
        $itemIds = $data['item_ids'] ?? null;
        if (!is_array($itemIds) || $itemIds === []) {
            return [];
        }

        $itemType = Section::itemTypeFor($manualModel);
        if (!$itemType) {
            return [];
        }

        foreach ($itemIds as &$item) {
            if (!is_array($item)) {
                continue;
            }
            $item['item_type'] = $itemType;
        }

        return ['item_ids' => $itemIds];
    }
}
