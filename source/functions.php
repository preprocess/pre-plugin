<?php

namespace Pre\Plugin;

function defer($code)
{
    $base = getenv("PRE_BASE_DIR");

    $hidden = realpath(__DIR__ . "/../hidden/vendor/autoload.php");
    $visible = realpath("{$base}/vendor/autoload.php");

    $defer = "
        require '{$hidden}';
        require '{$visible}';

        \$function = function() {
            {$code};
        };

        print base64_encode(serialize(\$function()));
    ";

    $result = exec(
        "php -r 'eval(base64_decode(\"" . base64_encode($defer) . "\"));'"
    );
    
    return unserialize(base64_decode($result));
}

function instance()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Parser();
    }

    return $instance;
}

function process($from)
{
    $instance = instance();
    return $instance->process($from);
}

function compile($from, $to, $format = true, $comment = true)
{
    $instance = instance();
    return $instance->compile($from, $to, $format, $comment);
}

function parse($code)
{
    $instance = instance();
    return $instance->parse($code);
}

function format($code)
{
    $instance = instance();
    return $instance->format($code);
}

function addMacro($macro)
{
    $instance = instance();
    return $instance->addMacro($macro);
}

function removeMacro($macro)
{
    $instance = instance();
    return $instance->removeMacro($macro);
}

function addCompiler($compiler, $callable)
{
    $instance = instance();
    return $instance->addCompiler($compiler, $callable);
}

function removeCompiler($compiler)
{
    $instance = instance();
    return $instance->removeCompiler($compiler);
}

function addFunction(...$args)
{
    $instance = instance();
    return $instance->addFunction(...$args);
}

function getFunction(...$args)
{
    $instance = instance();
    return $instance->getFunction(...$args);
}
