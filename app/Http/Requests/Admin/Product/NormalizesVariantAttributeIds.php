<?php

namespace App\Http\Requests\Admin\Product;

trait NormalizesVariantAttributeIds
{
    /**
     * Dashboard may send attributes[].id instead of attributes_values_ids.
     * Keep the same value IDs so every variant stays selectable on the site.
     */
    protected function normalizeVariantAttributeIds(): void
    {
        $variants = $this->all()['variants'] ?? $this->input('variants');
        if (!is_array($variants)) {
            return;
        }

        foreach ($variants as $index => $variant) {
            if (!is_array($variant)) {
                continue;
            }

            $ids = $this->extractAttributeValueIds($variant);
            if ($ids !== null) {
                $variants[$index]['attributes_values_ids'] = $ids;
            }
        }

        $this->merge(['variants' => $variants]);
    }

    /**
     * @param  array<string, mixed>  $variant
     * @return list<int>|null
     */
    private function extractAttributeValueIds(array $variant): ?array
    {
        if (array_key_exists('attributes_values_ids', $variant)) {
            return $this->idsFromMixed($variant['attributes_values_ids']);
        }

        if (!array_key_exists('attributes', $variant)) {
            return null;
        }

        $ids = $this->idsFromMixed($variant['attributes']);

        return $ids === [] ? null : $ids;
    }

    /**
     * @return list<int>
     */
    private function idsFromMixed(mixed $value): array
    {
        return collect(is_array($value) ? $value : [$value])
            ->flatMap(function ($item) {
                if (is_array($item)) {
                    return [$item['id'] ?? $item['attribute_value_id'] ?? null];
                }

                return [$item];
            })
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
