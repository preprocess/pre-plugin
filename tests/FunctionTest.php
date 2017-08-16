<?php

namespace Pre\Plugin;

use PHPUnit\Framework\TestCase;

function compiler($code) {
    return "{$code} COMPILED";
}

class FunctionTest extends TestCase
{
    /**
     * @test
     */
    public function can_register_its_own_macros()
    {
        // this repo registers its own macro file

        $this->assertEquals(1, count(getMacroPaths()));

        $expected = realpath(__DIR__ . "/../src/macros.yay");
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

    /**
     * @test
     */
    public function can_register_custom_macros()
    {
        addMacroPath(__DIR__ . "/Fixture/macros.yay");

        $fixture = new Fixture\TestFixture();

        $expected = "hello chris";
        $actual = $fixture->bar("chris");

        $this->assertEquals($expected, $actual);

        removeMacroPath(__DIR__ . "/Fixture/macros.yay");
    }

    /**
     * @test
     */
    public function can_register_custom_compilers()
    {
        addCompiler("Pre\\Plugin\\compiler");

        $expected = "hello world COMPILED";
        $actual = expand("hello world");

        $this->assertEquals($expected, $actual);

        removeCompiler("Pre\\Plugin\\compiler");
    }
}
