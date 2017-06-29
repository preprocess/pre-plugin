<?php

namespace Pre\Plugin\Integration;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Tuupola\Base62;

class Installer extends LibraryInstaller
{
    /**
     * @param string $type
     */
    public function supports($type)
    {
        return $type === "pre-macro";
    }

    /**
     * @param PackageInterface $package
     *
     * @return string
     */
    public function getInstallPath(PackageInterface $package)
    {
        $path = parent::getInstallPath($package);

        $extra = $package->getExtra();

        if (isset($extra["macros"]) && is_array($extra["macros"])) {
            foreach ($extra["macros"] as $macro) {
                $this->add("{$path}/{$macro}");
            }
        }

        return $path;
    }

    /**
     * @param string $path
     */
    private function add($path)
    {
        if (!file_exists($path)) {
            return;
        }

        $base = $this->base();
        $file = "{$base}/pre.paths";

        $paths = [];

        if (file_exists($file)) {
            $paths = json_decode(file_get_contents($file), true);
        }

        static $base62;

        if (!$base62) {
          $base62 = new Base62();
        }

        $paths[$base62->encode($path)] = true;

        file_put_contents($file, json_encode($paths, JSON_PRETTY_PRINT));
    }

    /**
     * @return string
     */
    private function base()
    {
        require_once __DIR__ . "/../environment.php";
        return getenv("PRE_BASE_DIR");
    }
}
