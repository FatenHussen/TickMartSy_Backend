<?php

namespace App\Services\Admin;

use App\Models\Product;
use App\Models\ProductMedia;
use App\Traits\FileTrait;
use Illuminate\Support\Facades\Log;

class ProductVariantService
{
    use FileTrait
    ;

    public function handle(Product $product, bool $isUpdate = false): void
    {

        Log::info('handle');
        if (!request()->has('variants')) {
            Log::info('handle1');

            return;
        }

        foreach (request()->get('variants') as $index => $variantData) {
            Log::info('handle2');

            if (empty($variantData['id'])) {

                Log::info('handle3');

                continue;
            }

            $variant = $product->variants()
                ->where('id', $variantData['id'])
                ->first();
            Log::info('handle4');

            if (!$variant || !request()->hasFile("variants.$index.images")) {
                continue;
            }

            if ($isUpdate) {
                $variant->media()
                    ->where('collection', ProductMedia::COLLECTION_VARIANT)
                    ->delete();
            }

            foreach (request()->file("variants.$index.images") as $image) {
                $path = $image->store('products/variants', 'public');
                Log::info('handle5');

                $variant->media()->create([
                    'collection' => ProductMedia::COLLECTION_VARIANT,
                    'path'       => $path,
                    'order'      => 0,
                ]);
            }
        }
    }
}
