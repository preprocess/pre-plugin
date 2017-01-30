<?php

namespace Pre;

use PHPUnit\Framework\TestCase;

class TransformerTest extends TestCase
{
    public function testTransformerWithCustomMacro()
    {
        addMacroPath(__DIR__ . "/Fixture/macros.pre");

        $fixture = new Fixture\Fixture();

        $expected = "hello chris";
        $actual = $fixture->bar("chris");

        $this->assertEquals($expected, $actual);
    }
}
