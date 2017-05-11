<?php

if (empty(getenv("PRE_BASE_DIR"))) {
    putenv("PRE_BASE_DIR=" . realpath(__DIR__ . "/../../../../"));
}
