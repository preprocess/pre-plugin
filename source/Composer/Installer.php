<?php

namespace Pre\Plugin\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

require_once __DIR__ . "/../environment.php";

class Installer extends LibraryInstaller
{
    private $base;

    public function supports($type)
    {
        return $type === "pre-macro" || $type === "pre-compiler";
    }

    public function getInstallPath(PackageInterface $package)
    {
        $path = parent::getInstallPath($package);

        $this->setBaseFrom($path);

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

    private function setBaseFrom($path)
    {
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $wanted = array_slice($parts, 0, count($parts) - 3);
        $joined = implode(DIRECTORY_SEPARATOR, $wanted);

        $this->base = $joined;

        file_put_contents(__DIR__ . "/../base.php", "<?php return '{$joined}';");
    }

    private function addMacro($macro)
    {
        $base = $this->base;
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
        $base = $this->base;
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
