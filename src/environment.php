<?php

if (empty(getenv("PRE_ISOLATE_DEPENDENCIES"))) {
    putenv("PRE_ISOLATE_DEPENDENCIES=1");
}

if (empty(getenv("PRE_BASE_DIR"))) {
    putenv("PRE_BASE_DIR=" . realpath(__DIR__ . "/../../../../"));
}
