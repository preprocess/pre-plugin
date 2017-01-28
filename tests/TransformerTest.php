<?php

namespace Pre\Tests;

use PHPUnit\Framework\TestCase;

use function Pre\addMacroPath;

class TransformerTest extends TestCase
{
    public function testTransformerWithCustomMacro()
    {
        addMacroPath(__DIR__ . "/macros.pre");

        $fixture = new Fixture();

        $expected = "hello chris";
        $actual = $fixture->bar("chris");

        $this->assertEquals($expected, $actual);
    }
}
