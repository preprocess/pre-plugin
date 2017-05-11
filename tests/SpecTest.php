<?php

namespace Pre\Plugin;

use Pre\Plugin\Testing\Runner;

class MacroTest extends Runner
{
    protected function path(): string
    {
        return __DIR__ . "/specs";
    }
}
