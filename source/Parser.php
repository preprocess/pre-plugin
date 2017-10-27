<?php

namespace Pre\Plugin;

define("COMMENT", trim("
# This file is generated, changes you make will be lost.
# Make your changes in %s instead.
"));

class Parser
{
    private $macro = [];

    private $compilers = [];

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
            array_filter($this->macros, function($key) {
                return $this->macros[$key];
            }, ARRAY_FILTER_USE_KEY)
        );
    }

    public function getDiscoveredMacros()
    {
        $base = getenv("PRE_BASE_DIR");
        
        if (file_exists("{$base}/pre.macros")) {
            $macros = json_decode(
                file_get_contents("{$base}/pre.macros"), true
            );

            return array_map(function($macro) {
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
        $base = getenv("PRE_BASE_DIR");

        if (file_exists("{$base}/pre.compilers")) {
            $compilers = json_decode(
                file_get_contents("{$base}/pre.compilers"), true
            );

            return array_map(function($compiler) {
                return base64_decode($compiler);
            }, $compilers);
        }

        return [];
    }

    function process($from)
    {
        $to = preg_replace("/\.[a-zA-Z]+$/", ".php", $from);
        $this->compile($from, $to);

        return require $to;
    }

    function compile($from, $to, $format = true, $comment = true)
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
                    "<?php", "<?php\n\n{$comment}", $code
                );
            }

            file_put_contents($to, $code);
        }
    }

    public function parse($code)
    {
        $code = $this->getCodeWithCompilers($code);
        $code = $this->getCodeWithMacros($code);
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
            $this->getCompilers(), $this->getDiscoveredCompilers()
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
            $this->getMacros(), $this->getDiscoveredMacros()
        );
        
        foreach ($macros as $macro) {
            if (file_exists($macro)) {
                $code = str_replace(
                    "<?php", file_get_contents($macro), $code
                );
            }
        }

        return $code;
    }

    public function format($code)
    {
        $file = tempnam(sys_get_temp_dir(), "pre");
        file_put_contents($file, $code);

        $path = realpath(__DIR__ . "/../hidden/vendor/bin/php-cs-fixer");
        exec("{$path} fix {$file} --using-cache=no --quiet");

        $code = file_get_contents($file);
        unlink($file);

        return $code;
    }
}