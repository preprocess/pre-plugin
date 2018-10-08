<?php

namespace Pre\Plugin;

use Exception;

define("COMMENT", trim("
# This file is generated, changes you make will be lost.
# Make your changes in %s instead.
"));

class Parser
{
    private $macro = [];

    private $compilers = [];

    private $functions = [];

    public function addMacro($macro)
    {
        $this->macros[$macro] = true;
    }

    public function removeMacro($macro)
    {
        $this->macros[$macro] = false;
    }

    public function getMacros()
    {
        return array_keys(
            array_filter($this->macros, function ($key) {
                return $this->macros[$key];
            }, ARRAY_FILTER_USE_KEY)
        );
    }

    public function getDiscoveredMacros()
    {
        $base = base();

        if (file_exists("{$base}/pre.macros")) {
            $macros = json_decode(
                file_get_contents("{$base}/pre.macros"),
                true
            );

            return array_map(function ($macro) {
                return base64_decode($macro);
            }, $macros);
        }

        return [];
    }

    public function addCompiler($compiler, $callable)
    {
        $this->compilers[$compiler] = $callable;
    }

    public function removeCompiler($compiler)
    {
        unset($this->compilers[$compiler]);
    }

    public function getCompilers()
    {
        return array_values($this->compilers);
    }

    public function getDiscoveredCompilers()
    {
        $base = base();

        if (file_exists("{$base}/pre.compilers")) {
            $compilers = json_decode(
                file_get_contents("{$base}/pre.compilers"),
                true
            );

            return array_map(function ($compiler) {
                return base64_decode($compiler);
            }, $compilers);
        }

        return [];
    }

    public function process($from)
    {
        $to = preg_replace("/\.[a-zA-Z]+$/", ".php", $from);

        if (!$this->isProcessed($from, $to)) {
            $this->compile($from, $to);
        }

        return require $to;
    }

    private function isProcessed($from, $to)
    {
        return file_exists($to) && (filemtime($from) < filemtime($to));
    }

    public function compile($from, $to, $format = true, $comment = true)
    {
        if (file_exists($from)) {
            $code = file_get_contents($from);
            $code = $this->parse($code);

            if ($format) {
                $code = $this->format($code);
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

    public function parse($code)
    {
        $code = $this->getCodeWithMacros($code);
        $code = $this->getCodeWithCompilers($code);
        $code = base64_encode($code);

        return defer("
            \$code = base64_decode('{$code}');
            \$engine = new \Yay\Engine;

            gc_disable();
            \$parsed = \$engine->expand(\$code);
            gc_enable();

            return \$parsed;
        ");
    }

    private function getCodeWithCompilers($code)
    {
        $compilers = array_merge(
            $this->getCompilers(),
            $this->getDiscoveredCompilers()
        );
        
        foreach ($compilers as $compiler) {
            if (is_callable($compiler)) {
                $code = $compiler($code);
            }
        }

        return $code;
    }

    private function getCodeWithMacros($code)
    {
        $macros = array_merge(
            $this->getMacros(),
            $this->getDiscoveredMacros()
        );
        
        foreach ($macros as $macro) {
            if (file_exists($macro)) {
                $code = str_replace(
                    "<?php",
                    file_get_contents($macro),
                    $code
                );
            }
        }

        return $code;
    }

    public function format($code)
    {
        $file = tempnam(sys_get_temp_dir(), "pre");
        file_put_contents($file, $code);

        $path = realpath(__DIR__ . "/../node_modules/prettier/bin-prettier.js");
        exec("{$path} --write --parser=php {$file} 1> /dev/null 2> /dev/null", $output);

        $code = file_get_contents($file);
        unlink($file);

        return $code;
    }

    public function addFunction($name, $function)
    {
        $this->functions[$name] = $function;
    }

    public function getFunction($name)
    {
        if (isset($this->functions[$name])) {
            return $this->functions[$name];
        }

        throw new Exception($name . " has not been added");
    }
}
