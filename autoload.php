<?php

namespace Pre;

if (file_exists(__DIR__ . "/../../autoload.php")) {
    define("BASE_DIR", realpath(__DIR__ . "/../../../"));
}

spl_autoload_register(function($class) {
    $definitions = require BASE_DIR . "/vendor/composer/autoload_psr4.php";

    foreach ($definitions as $prefix => $paths) {
        $prefixLength = strlen($prefix);

        if (strncmp($prefix, $class, $prefixLength) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $prefixLength);

        foreach ($paths as $path) {
            $php = $path . "/" . str_replace("\\", "/", $relativeClass) . ".php";
            $pre = $path . "/" . str_replace("\\", "/", $relativeClass) . ".pre";

            $relative = ltrim(str_replace(BASE_DIR, "", $pre), DIRECTORY_SEPARATOR);
            $macros = BASE_DIR . "/macros.pre";

            if (file_exists($pre)) {
                if (file_exists($php)) {
                    unlink($php);
                }

                foreach (getMacroPaths() as $macroPath) {
                    file_put_contents(
                        "{$pre}.interim",
                        str_replace(
                            "<?php",
                            file_get_contents($macroPath),
                            file_get_contents($pre)
                        )
                    );
                }

                exec(BASE_DIR . "/vendor/bin/yay {$pre}.interim >> {$php}");

                $comment = "
# This file is generated, changes you make will be lost.
# Make your changes in {$relative} instead.
                ";

                file_put_contents(
                    $php,
                    str_replace(
                        "<?php",
                        "<?php\n{$comment}",
                        file_get_contents($php)
                    )
                );

                unlink("{$pre}.interim");

                require_once $php;
            }
        }
    }
}, false, true);
