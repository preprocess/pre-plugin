<?php

namespace Pre\Plugin\Testing;

use Exception;
use PHPUnit\Framework\Assert;

use function Pre\Plugin\compile;

class Spec
{
    const BROKEN = "BROKEN";
    const FAILED = "FAILED";
    const PASSED = "PASSED";
    const DIFF_COMMAND = "diff --strip-trailing-cr --label '%s' --label '%s' --unified '%s' '%s'";

    protected $status;
    protected $description;
    protected $givenCode;
    protected $expectedCode;
    protected $outputCode;
    protected $pathToSpec;
    protected $pathToExpected;
    protected $pathToDifference;
    protected $pathToOutput;

    public function __construct(string $pathToSpec)
    {
        $this->pathToSpec = $pathToSpec;
        $this->pathToExpected = preg_replace("/spec$/", "expected", $pathToSpec);
        $this->pathToDifference = preg_replace("/spec$/", "diff", $pathToSpec);
        $this->pathToOutput = preg_replace("/spec$/", "output", $pathToSpec);
    }

    public function run()
    {
        if (!is_readable($this->pathToSpec)) {
            $this->status = static::BROKEN;
            return;
        }

        $raw = file_get_contents($this->pathToSpec);
        $parts = preg_split("/^--(DESCRIPTION|GIVEN|EXPECT)--$/m", $raw);
        $sections = array_values(array_filter(array_map("trim", $parts)));

        if (count($sections) !== 3) {
            $this->status = static::BROKEN;
            return;
        }

        list($this->description, $this->givenCode, $this->expectedCode) = $sections;

        try {
            $this->processSpec();
        } catch (Exception $e) {
            $this->outputCode = $e->getMessage();
        }

        try {
            $actual = trim(substr($this->outputCode, 5));
            Assert::assertStringMatchesFormat($this->expectedCode, $actual);

            $this->status = static::PASSED;
        } catch (Exception $e) {
            $this->status = static::FAILED;

            throw $e;
        }
    }

    private function processSpec()
    {
        $pre = preg_replace("/spec$/", "pre", $this->pathToSpec);
        $php = preg_replace("/spec$/", "php", $this->pathToSpec);

        file_put_contents($pre, "<" . "?php\n\n" . $this->givenCode);

        compile($pre, $php, $format = true, $comment = false);

        $this->outputCode = file_get_contents($php);

        unlink($pre);
        unlink($php);
    }

    public function status(): string
    {
        return $this->status;
    }

    public function dump()
    {
        file_put_contents($this->pathToExpected, $this->expectedCode . PHP_EOL);
        file_put_contents($this->pathToOutput, $this->outputCode . PHP_EOL);
        file_put_contents($this->pathToDifference, $this->diff());
    }

    public function diff(): string
    {
        $diff = "";

        if ($this->status === static::FAILED) {
            $command = sprintf(static::DIFF_COMMAND, "expected", "actual", $this->pathToExpected, $this->pathToOutput);

            exec($command, $output);
            $diff = implode(PHP_EOL, $output);
        }

        return $diff;
    }

    public function clean()
    {
        if (file_exists($this->pathToExpected)) {
            unlink($this->pathToExpected);
        }

        if (file_exists($this->pathToOutput)) {
            unlink($this->pathToOutput);
        }

        if (file_exists($this->pathToDifference)) {
            unlink($this->pathToDifference);
        }
    }

    public function __toString(): string
    {
        $relative = str_replace(__DIR__ . "/", "", $this->pathToSpec);

        return "[{$this->status}] {$this->description} [{$relative}]";
    }
}
