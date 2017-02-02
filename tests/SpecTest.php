<?php

namespace Pre;

use Pre\Testing\Runner;

class MacroTest extends Runner
{
    protected function path(): string
    {
        return __DIR__ . "/specs";
    }
}
