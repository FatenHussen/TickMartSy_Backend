<?php

namespace App\Http\Requests\Admin\Product;

trait DropsEmptyProductRelationRows
{
    /**
     * Dashboard posts empty extra/category rows; drop them before validation/save.
     */
    protected function dropEmptyProductRelationRows(): void
    {
        if ($this->exists('category_details') && is_array($this->input('category_details'))) {
            $this->merge([
                'category_details' => collect($this->input('category_details'))
                    ->filter(fn ($row) => is_array($row) && filled($row['category_detail_id'] ?? null))
                    ->values()
                    ->all(),
            ]);
        }

        if ($this->exists('extra_details') && is_array($this->input('extra_details'))) {
            $this->merge([
                'extra_details' => collect($this->input('extra_details'))
                    ->filter(fn ($row) => is_array($row) && filled($row['product_extra_detail_id'] ?? null))
                    ->values()
                    ->all(),
            ]);
        }
    }
}
