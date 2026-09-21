<?php

namespace Tests\Unit;

use App\Support\BooleanQueryNormalizer;
use PHPUnit\Framework\TestCase;

class BooleanQueryNormalizerTest extends TestCase
{
    public function test_normalizes_on_sale_and_in_stock_only_string_booleans(): void
    {
        $normalized = BooleanQueryNormalizer::normalize([
            'on_sale' => 'true',
            'in_stock_only' => 'false',
            'is_free_delivery' => 'true',
            'category_id' => '12',
        ]);

        $this->assertSame(1, $normalized['on_sale']);
        $this->assertSame(0, $normalized['in_stock_only']);
        $this->assertSame(1, $normalized['is_free_delivery']);
        $this->assertSame('12', $normalized['category_id']);
    }

    public function test_recognizes_non_is_boolean_keys(): void
    {
        $this->assertTrue(BooleanQueryNormalizer::isBooleanKey('on_sale'));
        $this->assertTrue(BooleanQueryNormalizer::isBooleanKey('in_stock_only'));
        $this->assertTrue(BooleanQueryNormalizer::isBooleanKey('is_instant_delivery'));
        $this->assertFalse(BooleanQueryNormalizer::isBooleanKey('category_id'));
    }
}
