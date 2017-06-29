<?php

namespace Pre\Plugin;

use PhpCsFixer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Yay\Engine;

require_once __DIR__ . "/environment.php";

define("COMMENT", trim("
# This file is generated, changes you make will be lost.
# Make your changes in %s instead.
"));

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
 * @param string $from
 * @param string $to
 * @param bool $format
 * @param bool $comment
 */
function compile($from, $to, $format = true, $comment = true)
{
    if (file_exists($from)) {
        $code = file_get_contents($from);
        $code = expand($code);

        if ($format) {
            $path = tempnam(sys_get_temp_dir(), "pre");
            file_put_contents($path, $code);

            formatFile($path);
            $code = file_get_contents($path);

            unlink($path);
        }

        if ($comment) {
            $comment = sprintf(COMMENT, $from);

            $code = str_replace(
                "<?php",
                "<?php\n\n{$comment}",
                $code
            );
        }

        file_put_contents($to, $code);
    }
}

/**
 * Compiles Pre syntax to PHP syntax.
 *
 * @param string $code
 *
 * @return string
 */
function expand($code, $includeStaticPaths = true)
{
    static $engine;

    if (is_null($engine)) {
        $engine = new Engine;
    }

    static $staticPaths;

    if ($includeStaticPaths) {
        if (!is_array($staticPaths)) {
            $staticPaths = [];
        }

        $base = getenv("PRE_BASE_DIR");

        if (file_exists("{$base}/pre.paths")) {
            $data = json_decode(file_get_contents("{$base}/pre.paths"), true);
            $staticPaths = array_keys($data);
        }
    }

    $paths = array_merge(getMacroPaths(), $staticPaths);

    foreach ($paths as $path) {
        if (file_exists($path)) {
            $code = str_replace(
                "<?php",
                file_get_contents($path),
                $code
            );
        }
    }

    gc_disable();
    $parsed = $engine->expand($code);
    gc_enable();

    return preg_replace('/\n\s+\n/', "\n\n", $parsed);
}

/**
 * Formats PHP syntax to be PSR-2 compliant.
 *
 * @param string $code
 */
function formatCode($code)
{
    $dir = sys_get_temp_dir();
    $name = tempnam($dir);

    file_put_contents($name, $code);
    formatFile($name);
    $formatted = file_get_contents($name);
    unlink($name);
    return $formatted;
}

/**
 * Formats PHP syntax to be PSR-2 compliant.
 *
 * @param string $path
 */
function formatFile($path)
{
    $application = new Application();
    $application->setAutoExit(false);

    if (!is_array($path)) {
        $path = [$path];
    }

    $input = new ArrayInput([
        "command" => "fix",
        "path" => $path,
        "--using-cache" => "no",
        "--quiet",
    ]);

    $output = new BufferedOutput();

    $application->run($input, $output);
}

/**
 * Processes and requires a Pre file.
 *
 * @param string $pre
 *
 * @return mixed
 */
function process($pre)
{
    static $required;

    if (is_null($required)) {
        $required = [];
    }

    if (!isset($required[$pre])) {
        $php = preg_replace("/pre$/", "php", $pre);
        compile($pre, $php);

        $required[$pre] = require_once $php;
    }

    return $required[$pre];
}
