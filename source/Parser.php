<?php

namespace Pre\Plugin;

use Exception;
use Yay\Engine;

define(
    "COMMENT",
    trim("
// This file is generated and changes you make will be lost.
// Change %s instead.
")
);

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
            array_filter(
                $this->macros,
                function ($key) {
                    return $this->macros[$key];
                },
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    public function getDiscoveredMacros()
    {
        $base = base();

        if (file_exists("{$base}/pre.macros")) {
            $macros = json_decode(file_get_contents("{$base}/pre.macros"), true);

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
            $compilers = json_decode(file_get_contents("{$base}/pre.compilers"), true);

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
        return file_exists($to) && filemtime($from) < filemtime($to);
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

                $code = str_replace("<?php", "<?php\n\n{$comment}", $code);
            }

            file_put_contents($to, $code);
        }
    }

    public function parse($code)
    {
        $code = $this->getCodeWithMacros($code);
        $code = $this->getCodeWithCompilers($code);

        $engine = new Engine();

        return $engine->expand($code, $engine->currentFileName(), Engine::GC_ENGINE_DISABLED);
    }

    private function getCodeWithCompilers($code)
    {
        $compilers = array_merge($this->getCompilers(), $this->getDiscoveredCompilers());

        foreach ($compilers as $compiler) {
            if (is_callable($compiler)) {
                $code = $compiler($code);
            }
        }

        return $code;
    }

    private function getCodeWithMacros($code)
    {
        $macros = array_merge($this->getMacros(), $this->getDiscoveredMacros());

        foreach ($macros as $macro) {
            if (file_exists($macro)) {
                $code = str_replace("<?php", file_get_contents($macro), $code);
            }
        }

        return $code;
    }

    public function format($code)
    {
        $path = __DIR__;
        $code = $this->addOpeningTag(trim($code));
        $encoded = base64_encode($code);

        $command = "node -e '
            const atob = require(\"atob\")
            const prettier = require(\"prettier\")

            prettier.resolveConfig(\"{$path}\").then(options => {
                try {
                    const formatted = prettier.format(atob(\"{$encoded}\").trim(), options)
                    console.log(formatted)
                } catch (e) {}
            })
        '";

        exec($command, $output);

        if (!$output) {
            return $code;
        }

        $output = join("\n", $output);
        return $this->addOpeningTag($output) . "\n";
    }

    private function addOpeningTag($code)
    {
        return "<?php" . preg_replace("/^\<\?php/", "", trim($code));
    }
}
