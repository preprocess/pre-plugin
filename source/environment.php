<?php

if (!file_exists(__DIR__ . "/../hidden/vendor")) {
    exec("cd " . __DIR__ . "/../hidden && composer install");
}
