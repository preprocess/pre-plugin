<?php

namespace Pre\Plugin;

use Composed;

spl_autoload_register(
    function ($class) {
        static $found;

        if (!$found) {
            $found = [];
        }

        if (isset($found[$class])) {
            require_once $found[$class];
            return;
        }

        $base = Composed\BASE_DIR;

        if (!file_exists("{$base}/vendor/composer/autoload_psr4.php")) {
            return;
        }

        require_once __DIR__ . "/bootstrap.php";

        $definitions = require "{$base}/vendor/composer/autoload_psr4.php";

        foreach ($definitions as $prefix => $paths) {
            $prefixLength = strlen($prefix);

            if (strncmp($prefix, $class, $prefixLength) !== 0) {
                continue;
            }

            $relative = substr($class, $prefixLength);

            foreach ($paths as $path) {
                $pre = $path . "/" . str_replace("\\", "/", $relative) . ".pre";

                if (!file_exists($pre)) {
                    continue;
                }

                if (!file_exists("{$base}/pre.lock")) {
                    process($pre);
                }

                $outputPath = pathFor($pre);
                $found[$class] = $outputPath;

                require_once $outputPath;

                return;
            }
        }
    },
    false,
    true
);
