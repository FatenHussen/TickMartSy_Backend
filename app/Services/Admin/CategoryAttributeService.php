<?php

namespace App\Services\Admin;

use App\Models\CategoryAttribute;
use App\Models\ProductVariant;
use App\Models\AttributeValue;
use App\Models\Color;
use App\Http\Resources\Admin\Category\CategoryAttribute\OneResource;
use App\Http\Resources\Admin\Category\CategoryAttribute\AllResource;
use App\Services\BaseService;
use App\Exceptions\CustomExceptionWithMessage;

class CategoryAttributeService extends BaseService
{
    protected $model      = CategoryAttribute::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;
    protected $pagination = true;
    protected $relations = [
        'values',
        'values.color',
    ];

    protected $syncRelations = [
        'values' => 'values',
    ];

    protected $searchableFields = [
        'id',
        'name',
        'category_id',
        'type'
    ];

    protected $sortableFields = [
        'id',
        'name',
        'type',
        'created_at',
    ];

    /**
     * Create a new category attribute
     * If type is 'color', automatically create attribute values from colors table
     */
    public function create($data)
    {
        // Create the category attribute using parent method
        $object = $this->model::create($data);
        $this->handleSingleImages($object, $data);
        $this->handleRelations($object, $data);
        $this->handleMedia($object, $data);

        // If type is 'color', automatically create attribute values from colors table
        if (isset($data['type']) && $data['type'] === 'color') {
            $this->createColorAttributeValues($object);
        }

        // Refresh to get updated data including the newly created values
        $object->refresh();

        return new $this->resource($object) ?? true;
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
            unset($filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
            unset($filters['date_to']);
        }

        if (!empty($filters['name'])) {
            $search = strtolower(trim((string) $filters['name']));
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ["%{$search}%"]);
            });
            unset($filters['name']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
            unset($filters['category_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
            unset($filters['type']);
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
            unset($filters['is_active']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }

    /**
     * Create attribute values for color type from colors table
     */
    protected function createColorAttributeValues($categoryAttribute)
    {
        $colors = Color::all();

        foreach ($colors as $color) {
            AttributeValue::create([
                'category_attribute_id' => $categoryAttribute->id,
                'name' => ['en' => $color->hex, 'ar' => $color->hex],
                'color_id' => $color->id,
            ]);
        }
    }

    /**
     * Delete a category attribute
     * Prevents deletion if any product variants are using its attribute values
     */
    public function delete($id): bool
    {
        $categoryAttribute = CategoryAttribute::findOrFail($id);

        // Get all attribute value IDs for this category attribute
        $attributeValueIds = $categoryAttribute->values()->pluck('id')->toArray();

        if (!empty($attributeValueIds)) {
            // Check if any product variants are using these attribute values
            $variantsCount = ProductVariant::where(function ($query) use ($attributeValueIds) {
                foreach ($attributeValueIds as $valueId) {
                    $query->orWhereJsonContains('attributes_values_ids', $valueId);
                }
            })->count();

            if ($variantsCount > 0) {
                throw new CustomExceptionWithMessage(
                    __('custom.cannot_delete_category_attribute_in_use', [
                        'count' => $variantsCount
                    ])
                );
            }
        }

        return parent::delete($id);
    }
}
