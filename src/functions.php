<?php

namespace Pre\Plugin;

require_once __DIR__ . "/environment.php";

define("COMMENT", trim("
# This file is generated, changes you make will be lost.
# Make your changes in %s instead.
"));

define("GLOBAL_MACRO_PATH_KEY", "PRE_MACRO_PATHS");
define("GLOBAL_COMPILER_KEY", "PRE_COMPILERS");

/**
 * Creates the list of macros, if it is undefined.
 */
function initMacroPaths()
{
    if (!isset($GLOBALS[GLOBAL_MACRO_PATH_KEY])) {
        $GLOBALS[GLOBAL_MACRO_PATH_KEY] = [];
    }
}

/**
 * Creates the list of compilers, if it is undefined.
 */
function initCompilers()
{
    if (!isset($GLOBALS[GLOBAL_COMPILER_KEY])) {
        $GLOBALS[GLOBAL_COMPILER_KEY] = [];
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
    array_push($GLOBALS[GLOBAL_MACRO_PATH_KEY], $path);
}

/**
 * Adds a compiler to the list of compilers.
 *
 * @param string $compiler
 */
function addCompiler($compiler)
{
    initCompilers();
    array_push($GLOBALS[GLOBAL_COMPILER_KEY], $compiler);
}

/**
 * Removes a path to the list of macro files.
 *
 * @param string $path
 */
function removeMacroPath($path)
{
    initMacroPaths();

    $GLOBALS[GLOBAL_MACRO_PATH_KEY] = array_filter(
        $GLOBALS[GLOBAL_MACRO_PATH_KEY],
        function ($next) use ($path) {
            return $next !== $path;
        }
    );
}

/**
 * Removes a path to the list of macro files.
 *
 * @param string $compiler
 */
function removeCompiler($compiler)
{
    initCompilers();

    $GLOBALS[GLOBAL_COMPILER_KEY] = array_filter(
        $GLOBALS[GLOBAL_COMPILER_KEY],
        function ($next) use ($compiler) {
            return $next !== $compiler;
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
    return $GLOBALS[GLOBAL_MACRO_PATH_KEY];
}

/**
 * Gets all compiler functions.
 *
 * @return array
 */
function getCompilers()
{
    initCompilers();
    return $GLOBALS[GLOBAL_COMPILER_KEY];
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
function expand($code, $includeStaticPaths = true, $includeStaticCompilers = true)
{
    $base = getenv("PRE_BASE_DIR");
    $vendor = realpath("{$base}/vendor/autoload.php");

    static $staticCompilers = [];

    if ($includeStaticCompilers) {
        if (file_exists("{$base}/pre.compilers")) {
            $staticCompilers = json_decode(file_get_contents("{$base}/pre.compilers"), true);
        }
    }

    $compilers = array_merge(getCompilers(), $staticCompilers);

    foreach ($compilers as $compiler) {
        $code = $compiler($code);
    }

    static $staticPaths = [];

    if ($includeStaticPaths) {
        if (file_exists("{$base}/pre.paths")) {
            $data = json_decode(file_get_contents("{$base}/pre.paths"), true);
            $data = $data ?? [];

            if ((int) getenv("PRE_ISOLATE_DEPENDENCIES") === 1) {
                $defer = '
                    require "' . $vendor . '";

                    $data = unserialize(base64_decode("' . base64_encode(serialize($data)) . '"));
                    $base62 = new \Tuupola\Base62();

                    print base64_encode(serialize(
                        array_map(function($key) use ($base62) {
                            return $base62->decode($key);
                        }, array_keys($data))
                    ));
                ';

                $result = exec("php -r 'eval(base64_decode(\"" . base64_encode($defer) . "\"));'");
                $staticPaths = unserialize(base64_decode($result));
            } else {
                static $base62;
                
                if (!$base62) {
                    $base62 = new \Tuupola\Base62();
                }
                
                $staticPaths = array_map(function ($key) use ($base62) {
                    return $base62->decode($key);
                }, array_keys($data));
            }
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

    if ((int) getenv("PRE_ISOLATE_DEPENDENCIES") === 1) {
        $defer = '
            require "' . $vendor . '";

            $code = base64_decode("' . base64_encode($code) . '");
            $engine = new \Yay\Engine;

            gc_disable();
            $parsed = $engine->expand($code);
            gc_enable();

            print base64_encode(serialize($parsed));
        ';

        $result = exec("php -r 'eval(base64_decode(\"" . base64_encode($defer) . "\"));'");
        $parsed = unserialize(base64_decode($result));
    } else {
        static $engine;
        
        if (is_null($engine)) {
            $engine = new \Yay\Engine;
        }

        gc_disable();
        $parsed = $engine->expand($code);
        gc_enable();
    }

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
    $name = tempnam($dir, "pre");

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
    $base = getenv("PRE_BASE_DIR");
    $vendor = realpath("{$base}/vendor/autoload.php");

    if ((int) getenv("PRE_ISOLATE_DEPENDENCIES") === 1) {
        $defer = '
            require "' . $vendor . '";

            $path = base64_decode("' . base64_encode($path) . '");

            $application = new PhpCsFixer\Console\Application();
            $application->setAutoExit(false);
        
            if (!is_array($path)) {
                $path = [$path];
            }
        
            $input = new Symfony\Component\Console\Input\ArrayInput([
                "command" => "fix",
                "path" => $path,
                "--using-cache" => "no",
                "--quiet",
            ]);
        
            $output = new Symfony\Component\Console\Output\BufferedOutput();
        
            $application->run($input, $output);
        ';

        exec("php -r 'eval(base64_decode(\"" . base64_encode($defer) . "\"));'");
    } else {
        $application = new \PhpCsFixer\Console\Application();
        $application->setAutoExit(false);
    
        if (!is_array($path)) {
            $path = [$path];
        }
    
        $input = new \Symfony\Component\Console\Input\ArrayInput([
            "command" => "fix",
            "path" => $path,
            "--using-cache" => "no",
            "--quiet",
        ]);
    
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
    
        $application->run($input, $output);
    }
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
