<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MAX_TREE_DEPTH = 6;

    public function up(): void
    {
        $attributes = DB::table('category_attributes')->get();

        foreach ($attributes as $attribute) {
            $rootId = $this->resolveRootId((int) $attribute->category_id);

            if (!$rootId || $rootId === (int) $attribute->category_id) {
                continue;
            }

            DB::table('category_attributes')
                ->where('id', $attribute->id)
                ->update(['category_id' => $rootId]);
        }
    }

    public function down(): void
    {
        // Attribute rows stay on the root category; original child assignment is not restored.
    }

    private function resolveRootId(int $categoryId): ?int
    {
        $current = DB::table('categories')->select('id', 'parent_id')->find($categoryId);

        if (!$current) {
            return null;
        }

        $guard = 0;

        while ($current->parent_id && $guard < self::MAX_TREE_DEPTH) {
            $parent = DB::table('categories')
                ->select('id', 'parent_id')
                ->find($current->parent_id);

            if (!$parent) {
                break;
            }

            $current = $parent;
            $guard++;
        }

        return (int) $current->id;
    }
};
