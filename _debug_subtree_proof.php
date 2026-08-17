<?php

use App\Enums\ProductApprovalStatus;
use App\Models\Category;
use App\Models\Product;
use App\Services\User\ProductService;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    $fashion = Category::findOrFail(2);

    echo "parent: #{$fashion->id} " . json_encode($fashion->getRawOriginal('name'), JSON_UNESCAPED_UNICODE) . "\n";
    echo "children before: " . $fashion->children()->count() . "\n\n";

    $child = Category::create([
        'name' => ['en' => 'Jeans TEMP', 'ar' => 'جينزات مؤقتة'],
        'parent_id' => $fashion->id,
        'is_active' => true,
        'is_restaurant' => false,
    ]);

    $grandchild = Category::create([
        'name' => ['en' => 'Slim Jeans TEMP', 'ar' => 'جينز ضيق مؤقت'],
        'parent_id' => $child->id,
        'is_active' => true,
        'is_restaurant' => false,
    ]);

    $make = function (int $categoryId, string $label, ProductApprovalStatus $status) {
        return Product::create([
            'category_id' => $categoryId,
            'vendor_id' => 1,
            'name' => ['en' => $label, 'ar' => $label],
            'description' => ['en' => $label, 'ar' => $label],
            'full_description' => ['en' => $label, 'ar' => $label],
            'price' => 10,
            'quantity' => 5,
            'approval_status' => $status,
            'is_active' => true,
        ]);
    };

    $onParent = $make($fashion->id, 'TEMP direct on parent', ProductApprovalStatus::APPROVED);
    $onChild = $make($child->id, 'TEMP on child', ProductApprovalStatus::APPROVED);
    $onGrandchild = $make($grandchild->id, 'TEMP on grandchild', ProductApprovalStatus::APPROVED);
    $pendingOnParent = $make($fashion->id, 'TEMP pending on parent', ProductApprovalStatus::PENDING);

    echo "children after: " . $fashion->children()->count() . " (parent now HAS children)\n";
    echo "idsInSubtree(2) = " . implode(',', $fashion->idsInSubtree()) . "\n\n";

    $query = app(ProductService::class)->queryBuilder(Product::query(), ['category_id' => 2]);

    echo "SQL: " . $query->toSql() . "\n";
    echo "BINDINGS: " . json_encode($query->getBindings()) . "\n\n";

    $ids = (clone $query)->pluck('id')->all();

    echo "returned ids: " . implode(',', $ids) . "\n\n";

    printf("direct-on-parent  #%d approved  -> %s\n", $onParent->id, in_array($onParent->id, $ids, true) ? 'RETURNED' : 'MISSING');
    printf("on-child          #%d approved  -> %s\n", $onChild->id, in_array($onChild->id, $ids, true) ? 'RETURNED' : 'MISSING');
    printf("on-grandchild     #%d approved  -> %s\n", $onGrandchild->id, in_array($onGrandchild->id, $ids, true) ? 'RETURNED' : 'MISSING');
    printf("direct-on-parent  #%d PENDING   -> %s\n", $pendingOnParent->id, in_array($pendingOnParent->id, $ids, true) ? 'RETURNED' : 'CORRECTLY HIDDEN');
} finally {
    DB::rollBack();
    echo "\nrolled back (no data written)\n";
    echo "products count now: " . Product::withTrashed()->count() . "\n";
}
