<?php

$composer = __DIR__ . "/../hidden";

if (!file_exists("{$composer}/vendor")) {
    exec("cd {$composer} && composer install > /dev/null 2> /dev/null");
}

$npm = __DIR__ . "/..";

if (!file_exists("{$npm}/node_modules")) {
    exec("cd {$npm} && npm install 1> /dev/null 2> /dev/null");
}
