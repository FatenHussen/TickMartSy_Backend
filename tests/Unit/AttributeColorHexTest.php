<?php

namespace Tests\Unit;

use App\Support\AttributeColorHex;
use PHPUnit\Framework\TestCase;

class AttributeColorHexTest extends TestCase
{
    public function test_normalizes_hex_values(): void
    {
        $this->assertSame('#FF0000', AttributeColorHex::normalize('#ff0000'));
        $this->assertSame('#FF0000', AttributeColorHex::normalize('ff0000'));
        $this->assertSame('#AABBCC', AttributeColorHex::normalize('#abc'));
        $this->assertNull(AttributeColorHex::normalize('أسود'));
        $this->assertNull(AttributeColorHex::normalize(null));
    }
}
