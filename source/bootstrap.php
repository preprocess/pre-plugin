<?php

if (!file_exists(__DIR__ . "/../node_modules")) {
    exec("cd " . __DIR__ . "/.. && npm install > /dev/null 2> /dev/null");
}
