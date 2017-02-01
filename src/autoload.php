<?php

namespace Pre;

spl_autoload_register(function ($class) {
    if (empty(getenv("PRE_BASE_DIR"))) {
        putenv("PRE_BASE_DIR=" . __DIR__ . "/../../..");
    }

    $base = getenv("PRE_BASE_DIR");

    if (!file_exists("{$base}/vendor/composer/autoload_psr4.php")) {
        return;
    }

    $definitions = require "{$base}/vendor/composer/autoload_psr4.php";

    foreach ($definitions as $prefix => $paths) {
        $prefixLength = strlen($prefix);

        if (strncmp($prefix, $class, $prefixLength) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $prefixLength);

        foreach ($paths as $path) {
            $php = $path . "/" . str_replace("\\", "/", $relativeClass) . ".php";
            $pre = $path . "/" . str_replace("\\", "/", $relativeClass) . ".pre";

            if (!file_exists($pre)) {
                continue;
            }

            process($pre, $php, $format = true, $comment = true);

            require_once $php;
        }
    }
}, false, true);
