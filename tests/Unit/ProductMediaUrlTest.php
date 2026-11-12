<?php

namespace Tests\Unit;

use App\Models\ProductMedia;
use Tests\TestCase;

class ProductMediaUrlTest extends TestCase
{
    public function test_media_url_supports_absolute_and_storage_paths(): void
    {
        foreach ([
            'https://cdn.example.com/product.jpg' => 'https://cdn.example.com/product.jpg',
            'http://cdn.example.com/product.jpg' => 'http://cdn.example.com/product.jpg',
            '/storage/products/front.jpg' => asset('storage/products/front.jpg'),
            'products/front.jpg' => asset('storage/products/front.jpg'),
        ] as $path => $expected) {
            $this->assertSame($expected, (new ProductMedia(['path' => $path]))->url);
        }
    }

    public function test_blank_or_inactive_media_is_not_usable(): void
    {
        foreach (['', '   '] as $path) {
            $media = new ProductMedia(['path' => $path]);
            $this->assertNull($media->url);
            $this->assertFalse($media->isUsable());
        }

        $this->assertFalse((new ProductMedia(['path' => 'products/front.jpg', 'is_active' => false]))->isUsable());
        $this->assertTrue((new ProductMedia(['path' => 'products/front.jpg']))->isUsable());
    }
}
