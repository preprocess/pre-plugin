<?php

namespace Pre;

spl_autoload_register(function($class) {
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

            $relative = ltrim(str_replace($base, "", $pre), DIRECTORY_SEPARATOR);
            $macros = "{$base}/macros.pre";

            if (file_exists($pre)) {
                if (file_exists($php)) {
                    unlink($php);
                }

                $preContent = file_get_contents($pre);

                foreach (getMacroPaths() as $macroPath) {
                    $preContent = str_replace(
                        "<?php",
                        file_get_contents($macroPath),
                        $preContent
                    );
                }

                file_put_contents(
                    "{$pre}.interim",
                    $preContent
                );

                exec("{$base}/vendor/bin/yay {$pre}.interim >> {$php}");

                $comment = "# This file is generated, changes you make will be lost.
# Make your changes in {$relative} instead.";

                file_put_contents(
                    $php,
                    str_replace(
                        "<?php",
                        "<?php\n\n{$comment}",
                        file_get_contents($php)
                    )
                );

                exec("{$base}/vendor/bin/php-cs-fixer --quiet --using-cache=no fix {$php}");

                unlink("{$pre}.interim");

                require_once $php;
            }
        }
    }
}, false, true);
