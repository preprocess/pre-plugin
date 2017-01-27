<?php

namespace Pre;

define("GLOBAL_KEY", "PRE_MACRO_PATHS");

/**
 * Creates the list of macros, if it is undefined.
 */
function initMacroPaths() {
    if (!isset($GLOBALS[GLOBAL_KEY])) {
        $GLOBALS[GLOBAL_KEY] = [];
    }
}

/**
 * Adds a path to the list of macro files.
 *
 * @param string $path
 */
function addMacroPath($path) {
    initMacroPaths();

    array_push($GLOBALS[GLOBAL_KEY], $path);
}

/**
 * Removes a path to the list of macro files.
 *
 * @param string $path
 */
function removeMacroPath($path) {
    initMacroPaths();

    $GLOBALS[GLOBAL_KEY] = array_filter($GLOBALS[GLOBAL_KEY], function($next) use ($path) {
        return $next !== $path;
    });
}

/**
 * Gets all amcro file paths.
 *
 * @return array
 */
function getMacroPaths() {
    initMacroPaths();

    return $GLOBALS[GLOBAL_KEY];
}
