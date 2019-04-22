<?php

namespace Pre\Plugin\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use function Pre\Plugin\base;

require_once __DIR__ . "/../functions.php";

class Installer extends LibraryInstaller
{
    public function supports($type)
    {
        return $type === "pre-macro" || $type === "pre-compiler";
    }

    public function getInstallPath(PackageInterface $package)
    {
        $path = parent::getInstallPath($package);

        $extra = $package->getExtra();

        if (isset($extra["macros"]) && is_array($extra["macros"])) {
            foreach ($extra["macros"] as $macro) {
                $this->addMacro("{$path}/{$macro}");
            }
        }

        if (isset($extra["compilers"]) && is_array($extra["compilers"])) {
            foreach ($extra["compilers"] as $compiler) {
                $this->addCompiler($compiler);
            }
        }

        return $path;
    }

    private function addMacro($macro)
    {
        $base = base();
        $file = "{$base}/pre.macros";

        $macro = base64_encode($macro);

        $macros = [];

        if (file_exists($file)) {
            $macros = json_decode(file_get_contents($file), true);
        }

        if (!in_array($macro, $macros)) {
            array_push($macros, $macro);
        }

        file_put_contents($file, json_encode($macros, JSON_PRETTY_PRINT));
    }

    /**
     * @param string $compiler
     */
    private function addCompiler($compiler)
    {
        $base = base();
        $file = "{$base}/pre.compilers";

        $compiler = base64_encode($compiler);

        $compilers = [];

        if (file_exists($file)) {
            $compilers = json_decode(file_get_contents($file), true);
        }

        if (!in_array($compiler, $compilers)) {
            array_push($compilers, $compiler);
        }

        file_put_contents($file, json_encode($compilers, JSON_PRETTY_PRINT));
    }
}
