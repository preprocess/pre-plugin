<?php

namespace Pre;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PluginEvents;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\Event;
use Composer\IO\ConsoleIO;
use ReflectionClass;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            "pre-autoload-dump" => [
                "onPreAutoloadDump",
            ],
        ];
    }

    /**
     * Preprocesses all files if the autoloader should be optimized.
     *
     * @param Event $event
     */
    public function onPreAutoloadDump(Event $event)
    {
        $basePath = $this->getBasePath($event);
        $lockPath = "{$basePath}/pre.lock";

        $shouldOptimize = $this->shouldOptimize($event);

        if ($shouldOptimize) {
            touch($lockPath);

            require_once "{$basePath}/vendor/autoload.php";

            $directory = new RecursiveDirectoryIterator($basePath);
            $files = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                if (stripos($file, "{$basePath}/vendor") === 0) {
                    continue;
                }

                if ($file->getExtension() !== "pre") {
                    continue;
                }

                $pre = $file->getPathname();
                $php = preg_replace("/pre$/", "php", $pre);

                process($pre, $php, $format = true, $comment = false);
            }
        } else {
            if (file_exists($lockPath)) {
                unlink($basePath . "/pre.lock");
            }
        }
    }

    /**
     * Finds the base application path.
     *
     * @param Event $event
     *
     * @return string
     */
    private function getBasePath(Event $event)
    {
        $config = $event->getComposer()->getConfig();

        return realpath($config->get("vendor-dir") . "/..");
    }

    /**
     * Checks whether the autoloader should be optimized, based on
     * --optimize / --optimize-autoloader command line options.
     *
     * @param Event $event
     *
     * @return bool
     */
    private function shouldOptimize(Event $event)
    {
        $io = $event->getIO();

        // I will surely burn for this.

        $class = new ReflectionClass(ConsoleIO::class);
        $property = $class->getProperty("input");
        $property->setAccessible(true);

        $input = $property->getValue($io);

        if ($input->hasOption("optimize")) {
            return $input->getOption("optimize");
        }

        if ($input->hasOption("optimize-autoloader")) {
            return $input->getOption("optimize-autoloader");
        }

        return false;
    }
}
