<?php

namespace Pre\Plugin\Testing;

use Exception;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

abstract class Runner extends TestCase
{
    public function test()
    {
        $specs = $this->specs();

        foreach ($specs as $spec) {
            try {
                $spec->run();
                $spec->clean();
            } catch (Exception $e) {
                $spec->dump();
                throw $e;
            }

            $this->assertNotEquals(Spec::BROKEN, $spec->status());
        }
    }

    /**
     * @return Spec[]
     */
    private function specs()
    {
        $directories = new RecursiveDirectoryIterator($this->path());

        $files = new RegexIterator(
            new RecursiveIteratorIterator($directories),
            "/spec$/",
            RegexIterator::MATCH
        );

        $specs = [];

        foreach ($files as $file) {
            $specs[] = new Spec($file->getRealPath());
        }

        return $specs;
    }

    abstract protected function path(): string;
}
