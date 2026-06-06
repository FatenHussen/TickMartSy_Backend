<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'price')) {
                    $table->double('price', 12, 2)->nullable()->change();
                }
                if (Schema::hasColumn('products', 'cost_price')) {
                    $table->double('cost_price', 12, 2)->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('shop_product_variants')) {
            Schema::table('shop_product_variants', function (Blueprint $table) {
                if (Schema::hasColumn('shop_product_variants', 'price')) {
                    $table->double('price', 12, 2)->nullable()->change();
                }
                if (Schema::hasColumn('shop_product_variants', 'cost_price')) {
                    $table->double('cost_price', 12, 2)->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('baskets')) {
            Schema::table('baskets', function (Blueprint $table) {
                if (Schema::hasColumn('baskets', 'price')) {
                    $table->double('price', 12, 2)->change();
                }
                if (Schema::hasColumn('baskets', 'discount')) {
                    $table->double('discount', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('baskets', 'delivery_price')) {
                    $table->double('delivery_price', 12, 2)->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('basket_items')) {
            Schema::table('basket_items', function (Blueprint $table) {
                if (Schema::hasColumn('basket_items', 'price')) {
                    $table->double('price', 12, 2)->change();
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'delivery_price')) {
                    $table->double('delivery_price', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('orders', 'original_delivery_price')) {
                    $table->double('original_delivery_price', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('orders', 'subtotal')) {
                    $table->double('subtotal', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('orders', 'basket_discount')) {
                    $table->double('basket_discount', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('orders', 'coupon_discount')) {
                    $table->double('coupon_discount', 12, 2)->nullable()->change();
                }
                if (Schema::hasColumn('orders', 'total')) {
                    $table->double('total', 12, 2)->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (Schema::hasColumn('order_items', 'price')) {
                    $table->double('price', 12, 2)->change();
                }
                if (Schema::hasColumn('order_items', 'unit_price')) {
                    $table->double('unit_price', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('order_items', 'final_price')) {
                    $table->double('final_price', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('order_items', 'subtotal')) {
                    $table->double('subtotal', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('order_items', 'extras_total')) {
                    $table->double('extras_total', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('order_items', 'total')) {
                    $table->double('total', 12, 2)->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('order_item_extras')) {
            Schema::table('order_item_extras', function (Blueprint $table) {
                if (Schema::hasColumn('order_item_extras', 'price')) {
                    $table->double('price', 12, 2)->change();
                }
            });
        }

        if (Schema::hasTable('product_extra_detail_options')) {
            Schema::table('product_extra_detail_options', function (Blueprint $table) {
                if (Schema::hasColumn('product_extra_detail_options', 'price')) {
                    $table->double('price', 12, 2)->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('packages')) {
            Schema::table('packages', function (Blueprint $table) {
                if (Schema::hasColumn('packages', 'price')) {
                    $table->double('price', 12, 2)->change();
                }
            });
        }

        if (Schema::hasTable('vendor_packages')) {
            Schema::table('vendor_packages', function (Blueprint $table) {
                if (Schema::hasColumn('vendor_packages', 'price')) {
                    $table->double('price', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('vendor_packages', 'commission_per_order')) {
                    $table->double('commission_per_order', 12, 2)->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('service_orders')) {
            Schema::table('service_orders', function (Blueprint $table) {
                if (Schema::hasColumn('service_orders', 'price')) {
                    $table->double('price', 12, 2)->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('shop_vendor_services')) {
            Schema::table('shop_vendor_services', function (Blueprint $table) {
                if (Schema::hasColumn('shop_vendor_services', 'price')) {
                    $table->double('price', 12, 2)->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('recipes')) {
            Schema::table('recipes', function (Blueprint $table) {
                if (Schema::hasColumn('recipes', 'delivery_price')) {
                    $table->double('delivery_price', 12, 2)->default(0)->change();
                }
            });
        }
    }

    public function down(): void
    {
        // No down migration to avoid unintended precision loss.
    }
};
