<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class AttributeMapResource extends JsonResource
{
    public function toArray($request)
    {
        $map = [];

        /** @var Collection $variants */
        $variants = $this->resource instanceof Collection
            ? $this->resource
            : collect([$this->resource]);

        foreach ($variants as $variant) {
            foreach ($variant->attributesValues as $attr) {

                $name = $attr?->categoryAttribute?->name;
                if (!$name) {
                    continue;
                }

                if (!isset($map[$name])) {
                    $map[$name] = [
                        'attribute' => $name,
                        'type'      => $attr->categoryAttribute->type,
                        'values'    => [],
                    ];
                }

                if (!in_array($attr->name, $map[$name]['values'], true)) {
                    $map[$name]['values'][] = $attr->name;
                }
            }
        }

        return array_values($map);
    }
}
