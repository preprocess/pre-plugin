<?php

namespace Pre\Plugin;

spl_autoload_register(function ($class) {
    $base = base();

    if (file_exists("{$base}/pre.lock")) {
        return;
    }

    if (!file_exists("{$base}/vendor/composer/autoload_psr4.php")) {
        return;
    }

    $definitions = require "{$base}/vendor/composer/autoload_psr4.php";

    foreach ($definitions as $prefix => $paths) {
        $prefixLength = strlen($prefix);

        if (strncmp($prefix, $class, $prefixLength) !== 0) {
            continue;
        }

        $relative = substr($class, $prefixLength);

        foreach ($paths as $path) {
            $php = $path . "/" . str_replace("\\", "/", $relative) . ".php";
            $pre = $path . "/" . str_replace("\\", "/", $relative) . ".pre";

            if (!file_exists($pre)) {
                continue;
            }

            process($pre, $php, $format = true, $comment = true);
        }
    }
}, false, true);
