<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'price')) {
                $table->double('price', 12, 2)->nullable()->after('barcode');
            }

            if (!Schema::hasColumn('product_variants', 'quantity')) {
                $table->integer('quantity')->nullable()->after('price');
            }
        });

        $this->backfillPrices();
        $this->backfillQuantities();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            foreach (['quantity', 'price'] as $column) {
                if (Schema::hasColumn('product_variants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Backfill: أقل سعر موجود بين المحلات، وإذا ما في → سعر المنتج.
     */
    private function backfillPrices(): void
    {
        $hasShopPrice = Schema::hasTable('shop_product_variants')
            && Schema::hasColumn('shop_product_variants', 'price');

        DB::table('product_variants')
            ->select('id', 'product_id')
            ->whereNull('price')
            ->orderBy('id')
            ->chunk(500, function ($variants) use ($hasShopPrice) {
                foreach ($variants as $variant) {
                    $price = null;

                    if ($hasShopPrice) {
                        $price = DB::table('shop_product_variants')
                            ->where('product_variant_id', $variant->id)
                            ->whereNull('deleted_at')
                            ->where('price', '>', 0)
                            ->min('price');
                    }

                    $price ??= DB::table('products')
                        ->where('id', $variant->product_id)
                        ->value('price');

                    if ($price === null) {
                        continue;
                    }

                    DB::table('product_variants')
                        ->where('id', $variant->id)
                        ->update(['price' => $price]);
                }
            });
    }

    /**
     * Backfill: مجموع كميات المحلات، وإذا ما في → كمية المنتج.
     */
    private function backfillQuantities(): void
    {
        $hasShopQuantity = Schema::hasTable('shop_product_variants')
            && Schema::hasColumn('shop_product_variants', 'quantity');

        DB::table('product_variants')
            ->select('id', 'product_id')
            ->whereNull('quantity')
            ->orderBy('id')
            ->chunk(500, function ($variants) use ($hasShopQuantity) {
                foreach ($variants as $variant) {
                    $quantity = null;

                    if ($hasShopQuantity) {
                        $hasShopRows = DB::table('shop_product_variants')
                            ->where('product_variant_id', $variant->id)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($hasShopRows) {
                            $quantity = (int) DB::table('shop_product_variants')
                                ->where('product_variant_id', $variant->id)
                                ->whereNull('deleted_at')
                                ->sum('quantity');
                        }
                    }

                    $quantity ??= DB::table('products')
                        ->where('id', $variant->product_id)
                        ->value('quantity');

                    if ($quantity === null) {
                        continue;
                    }

                    DB::table('product_variants')
                        ->where('id', $variant->id)
                        ->update(['quantity' => (int) $quantity]);
                }
            });
    }
};
