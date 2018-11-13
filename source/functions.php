<?php

namespace Pre\Plugin;

if (!function_exists("\\Pre\\Plugin\\find")) {
    function find($file, $iterations = 10, $prefix = __DIR__) {
        $folder = "../";

        if ($prefix) {
            $folder = "{$prefix}/{$folder}";
        }

        for ($i = 0; $i < $iterations; $i++) {
            $try = "{$folder}{$file}";

            if (file_exists($try)) {
                return realpath($try);
            }

            $folder .= "../";
        }
    }
}

if (!function_exists("\\Pre\\Plugin\\base")) {
    function base() {
        $vendor = find("vendor");
        return realpath("{$vendor}/../");
    }
}

if (!function_exists("\\Pre\\Plugin\\instance")) {
    function instance() {
        static $instance = null;

        if ($instance === null) {
            $instance = new Parser();
        }

        return $instance;
    }
}

if (!function_exists("\\Pre\\Plugin\\process")) {
    function process($from) {
        $instance = instance();
        return $instance->process($from);
    }
}

if (!function_exists("\\Pre\\Plugin\\compile")) {
    function compile($from, $to, $format = true, $comment = true) {
        $instance = instance();
        return $instance->compile($from, $to, $format, $comment);
    }
}

if (!function_exists("\\Pre\\Plugin\\parse")) {
    function parse($code) {
        $instance = instance();
        return $instance->parse($code);
    }
}

if (!function_exists("\\Pre\\Plugin\\format")) {
    function format($code) {
        $instance = instance();
        return $instance->format($code);
    }
}

if (!function_exists("\\Pre\\Plugin\\addMacro")) {
    function addMacro($macro) {
        $instance = instance();
        return $instance->addMacro($macro);
    }
}

if (!function_exists("\\Pre\\Plugin\\removeMacro")) {
    function removeMacro($macro) {
        $instance = instance();
        return $instance->removeMacro($macro);
    }
}

if (!function_exists("\\Pre\\Plugin\\addCompiler")) {
    function addCompiler($compiler, $callable) {
        $instance = instance();
        return $instance->addCompiler($compiler, $callable);
    }
}

if (!function_exists("\\Pre\\Plugin\\removeCompiler")) {
    function removeCompiler($compiler) {
        $instance = instance();
        return $instance->removeCompiler($compiler);
    }
}
