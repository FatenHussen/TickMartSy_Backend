<?php

namespace Tests\Feature;

use App\Enums\ProductApprovalStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\Admin\ProductService as AdminProductService;
use App\Services\User\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategorySubtreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_category_filter_includes_direct_and_descendant_products(): void
    {
        $vendor = $this->createVendor();
        $root = $this->createCategory('Fashion');
        $child = $this->createCategory('Clothing', $root);
        $grandchild = $this->createCategory('Jeans', $child);
        $otherRoot = $this->createCategory('Food');

        $directProduct = $this->createProduct($root, $vendor, 'Direct fashion product');
        $childProduct = $this->createProduct($child, $vendor, 'Clothing product');
        $grandchildProduct = $this->createProduct($grandchild, $vendor, 'Jeans product');
        $otherProduct = $this->createProduct($otherRoot, $vendor, 'Food product');

        $ids = app(ProductService::class)
            ->queryBuilder(Product::query(), ['category_id' => $root->id])
            ->pluck('id');

        $this->assertEqualsCanonicalizing([
            $directProduct->id,
            $childProduct->id,
            $grandchildProduct->id,
        ], $ids->all());
        $this->assertNotContains($otherProduct->id, $ids);
    }

    public function test_child_category_filter_does_not_include_ancestor_products(): void
    {
        $vendor = $this->createVendor();
        $root = $this->createCategory('Fashion');
        $child = $this->createCategory('Jeans', $root);

        $rootProduct = $this->createProduct($root, $vendor, 'Direct fashion product');
        $childProduct = $this->createProduct($child, $vendor, 'Jeans product');

        $ids = app(ProductService::class)
            ->queryBuilder(Product::query(), ['category_id' => $child->id])
            ->pluck('id');

        $this->assertSame([$childProduct->id], $ids->all());
        $this->assertNotContains($rootProduct->id, $ids);
    }

    public function test_admin_parent_category_filter_includes_descendant_products(): void
    {
        $vendor = $this->createVendor();
        $root = $this->createCategory('Fashion');
        $child = $this->createCategory('Jeans', $root);

        $rootProduct = $this->createProduct($root, $vendor, 'Direct fashion product');
        $childProduct = $this->createProduct($child, $vendor, 'Jeans product');

        $ids = app(AdminProductService::class)
            ->queryBuilder(Product::query(), ['category_id' => $root->id])
            ->pluck('id');

        $this->assertEqualsCanonicalizing([$rootProduct->id, $childProduct->id], $ids->all());
    }

    public function test_expand_ids_to_subtrees_includes_each_id_and_descendants(): void
    {
        $root = $this->createCategory('Fashion');
        $child = $this->createCategory('Clothing', $root);
        $grandchild = $this->createCategory('Jeans', $child);
        $other = $this->createCategory('Food');

        $this->assertEqualsCanonicalizing(
            [$root->id, $child->id, $grandchild->id],
            Category::expandIdsToSubtrees([$root->id])
        );
        $this->assertEqualsCanonicalizing(
            [$child->id, $grandchild->id, $other->id],
            Category::expandIdsToSubtrees([$child->id, $other->id])
        );
        $this->assertSame([], Category::expandIdsToSubtrees([]));
    }

    private function createCategory(string $name, ?Category $parent = null): Category
    {
        return Category::create([
            'name' => ['en' => $name, 'ar' => $name],
            'parent_id' => $parent?->id,
            'is_active' => true,
            'is_restaurant' => false,
        ]);
    }

    private function createVendor(): Vendor
    {
        return Vendor::create([
            'name' => ['en' => 'Test Vendor', 'ar' => 'Test Vendor'],
            'owner_name' => 'Test Owner',
            'owner_phone' => '0500000000',
            'contract_date' => now()->toDateString(),
            'contract_number' => 'CNT-' . str()->uuid(),
            'contract_duration_months' => 12,
            'commission_rate' => 5,
            'is_active' => true,
        ]);
    }

    private function createProduct(Category $category, Vendor $vendor, string $name): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'vendor_id' => $vendor->id,
            'name' => ['en' => $name, 'ar' => $name],
            'description' => ['en' => $name, 'ar' => $name],
            'approval_status' => ProductApprovalStatus::APPROVED,
            'is_active' => true,
        ]);
    }
}
