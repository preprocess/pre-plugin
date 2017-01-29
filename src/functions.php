<?php

namespace Pre;

use function yay_parse;

define("GLOBAL_KEY", "PRE_MACRO_PATHS");

/**
 * Creates the list of macros, if it is undefined.
 */
function initMacroPaths()
{
    if (!isset($GLOBALS[GLOBAL_KEY])) {
        $GLOBALS[GLOBAL_KEY] = [];
    }
}

/**
 * Adds a path to the list of macro files.
 *
 * @param string $path
 */
function addMacroPath($path)
{
    initMacroPaths();
    array_push($GLOBALS[GLOBAL_KEY], $path);
}

/**
 * Removes a path to the list of macro files.
 *
 * @param string $path
 */
function removeMacroPath($path)
{
    initMacroPaths();

    $GLOBALS[GLOBAL_KEY] = array_filter(
        $GLOBALS[GLOBAL_KEY],
        function ($next) use ($path) {
            return $next !== $path;
        }
    );
}

/**
 * Gets all macro file paths.
 *
 * @return array
 */
function getMacroPaths()
{
    initMacroPaths();
    return $GLOBALS[GLOBAL_KEY];
}

/**
 * Compiles a Pre file to a PHP file.
 *
 * @param string $base
 * @param string $from
 * @param string $to
 */
function process($base, $from, $to)
{
    if (file_exists($from)) {
        if (file_exists($to)) {
            unlink($to);
        }

        $interim = file_get_contents($from);

        foreach (getMacroPaths() as $macroPath) {
            $interim = str_replace(
                "<?php",
                file_get_contents($macroPath),
                $interim
            );
        }

        $compiled = yay_parse($interim);

        $comment = "# This file is generated, changes you make will be lost.
# Make your changes in {$from} instead.";

        file_put_contents(
            $to,
            str_replace(
                "<?php",
                "<?php\n\n{$comment}",
                $compiled
            )
        );

        exec("{$base}/vendor/bin/php-cs-fixer --quiet --using-cache=no fix {$to}");

        require_once $to;
    }
}
