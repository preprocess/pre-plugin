<?php

namespace Pre\Plugin;

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
    function process($from, $to = null, $format = true, $comment = true) {
        $instance = instance();
        return $instance->process($from, $to, $format, $comment);
    }
}

if (!function_exists("\\Pre\\Plugin\\pathFor")) {
    function pathFor($pre) {
        $instance = instance();
        return $instance->pathFor($pre);
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
