<?php

if (empty(getenv("PRE_BASE_DIR"))) {
    putenv("PRE_BASE_DIR=" . realpath(__DIR__ . "/../../../../"));
}

if (!file_exists(__DIR__ . "/../hidden/vendor")) {
    exec("cd " . __DIR__ . "/../hidden && composer install");
}
