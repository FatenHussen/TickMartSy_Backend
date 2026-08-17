<?php

namespace App\Services\Admin;

use App\Models\CategoryAttribute;
use App\Models\AttributeValue;
use App\Models\Color;
use App\Http\Resources\Admin\Category\CategoryAttribute\OneResource;
use App\Http\Resources\Admin\Category\CategoryAttribute\AllResource;
use App\Services\Base\CategoryAttributeDeleteImpactService;
use App\Services\BaseService;
use App\Exceptions\DeleteConfirmationRequiredException;
use Illuminate\Support\Facades\DB;

class CategoryAttributeService extends BaseService
{
    protected $model      = CategoryAttribute::class;
    protected $resource   = OneResource::class;
    protected $collection = AllResource::class;
    protected $pagination = true;
    protected $relations = [
        'category',
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
            $query->forCategoryTree((int) $filters['category_id']);
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
                'name' => [
                    'en' => $color->getTranslation('name', 'en', false) ?? $color->getTranslation('name', 'ar', false) ?? $color->hex,
                    'ar' => $color->getTranslation('name', 'ar', false) ?? $color->getTranslation('name', 'en', false) ?? $color->hex,
                ],
                'color_id' => $color->id,
            ]);
        }
    }

    /**
     * معاينة أثر الحذف دون تنفيذه.
     */
    public function deleteImpact($id): array
    {
        $categoryAttribute = CategoryAttribute::findOrFail($id);

        return (new CategoryAttributeDeleteImpactService())->impact($categoryAttribute);
    }

    /**
     * قائمة مفصّلة بكل ما هو مرتبط بالخاصية (تبويب "العناصر المرتبطة").
     */
    public function linkedItems($id, int $page = 1, int $perPage = 10): array
    {
        $categoryAttribute = CategoryAttribute::findOrFail($id);

        return (new CategoryAttributeDeleteImpactService())->linkedItems($categoryAttribute, $page, $perPage);
    }

    /**
     * الحذف مسموح دائماً، لكنه يتطلب تأكيداً صريحاً إذا كانت الخاصية مستخدمة.
     * عند التأكيد تُزال قيم الخاصية من متغيّرات المنتجات بدل حذف المتغيّرات نفسها.
     */
    public function deleteWithConfirmation($id, bool $confirmed = false): array
    {
        $categoryAttribute = CategoryAttribute::findOrFail($id);
        $impactService = new CategoryAttributeDeleteImpactService();

        $impact = $impactService->impact($categoryAttribute);

        if ($impact['requires_confirmation'] && !$confirmed) {
            throw new DeleteConfirmationRequiredException(
                $impact,
                'custom.category_attribute_delete_impact.requires_confirmation'
            );
        }

        $valueIds = $categoryAttribute->values()->pluck('id')->all();

        DB::transaction(function () use ($categoryAttribute, $impactService, $valueIds) {
            $impactService->detachValuesFromVariants($valueIds);

            // attribute_values تُحذف تلقائياً عبر cascade على مستوى قاعدة البيانات
            $categoryAttribute->delete();
        });

        return $impact;
    }

    public function delete($id): bool
    {
        $this->deleteWithConfirmation($id, request()->boolean('confirm'));

        return true;
    }
}
