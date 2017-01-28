<?php

namespace Pre\Tests;

use PHPUnit\Framework\TestCase;

use function Pre\getMacroPaths;
use function Pre\addMacroPath;
use function Pre\removeMacroPath;

class MacroTest extends TestCase
{
    public function testMacroPaths()
    {
        // this repo registers its own macro file

        $this->assertEquals(1, count(getMacroPaths()));

        $expected = realpath(__DIR__ . "/../src/macros.pre");
        $actual = realpath(getMacroPaths()[0]);

        $this->assertEquals($expected, $actual);

        // what happens when we register another?

        $expected = "foo/bar/baz";
        addMacroPath($expected);

        $this->assertEquals(2, count(getMacroPaths()));

        $actual = getMacroPaths()[1];

        $this->assertEquals($expected, $actual);

        // can we remove it again?

        removeMacroPath("foo/bar/baz");

        $this->assertEquals(1, count(getMacroPaths()));
    }
}
