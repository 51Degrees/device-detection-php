<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2026 51 Degrees Mobile Experts Limited, Davidson House,
 * Forbury Square, Reading, Berkshire, United Kingdom RG1 3EU.
 *
 * This Original Work is licensed under the European Union Public Licence
 * (EUPL) v.1.2 and is subject to its terms as set out below.
 *
 * If a copy of the EUPL was not distributed with this file, You can obtain
 * one at https://opensource.org/licenses/EUPL-1.2.
 *
 * The 'Compatible Licences' set out in the Appendix to the EUPL (as may be
 * amended by the European Commission) shall be deemed incompatible for
 * the purposes of the Work and the provisions of the compatibility
 * clause in Article 5 of the EUPL shall not apply.
 *
 * If using the Work as, or as part of, a network application, by
 * including the attribution notice(s) required under Article 5 of the EUPL
 * in the end user terms of the application under an appropriate heading,
 * such notice(s) shall fulfill the requirements of that article.
 * ********************************************************************* */

namespace fiftyone\pipeline\devicedetection\tests;

// Fake remote address for web integration

$_SERVER['REMOTE_ADDR'] = '0.0.0.0';
$_SERVER['REQUEST_URI'] = 'http://localhost';

use fiftyone\pipeline\core\Logger;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\ExampleUtils;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\GettingStartedConsole;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\MetadataConsole;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\NativeModelLookupConsole;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\TacLookupConsole;
use fiftyone\pipeline\devicedetection\tests\classes\Constants;
use fiftyone\pipeline\devicedetection\tests\classes\ResourceKeys;
use PHPUnit\Framework\TestCase;

class ExampleTests extends TestCase
{
    // Text that shows an example printed a programming fault rather than a
    // result. An example that reaches any of these is broken, however
    // little of the data the resource key is entitled to.
    private const FAULT_MARKERS = [
        'TypeError',
        'ValueError',
        'Fatal error',
        'Warning:',
        'Undefined',
        'Traceback',
        'Unknown ()'
    ];

    public function testGettingStartedConsole()
    {
        $key = $this->getResourceKey();

        $output = $this->runExample(function ($record) use ($key) {
            $logger = new Logger('info');
            $config = json_decode(file_get_contents(__DIR__ . '/../examples/cloud/gettingStartedConsole.json'), true);
            ExampleUtils::setResourceKeyInConfig($config, $key);
            (new GettingStartedConsole())->run($config, $logger, $record);
        });

        $this->assertStringContainsString('Input values:', $output);
        $this->assertStringContainsString('Mobile Device:', $output);
    }

    public function testTacLookupConsole()
    {
        $key = $this->getResourceKey();

        $output = $this->runExample(function ($record) use ($key) {
            $logger = new Logger('info');
            $config = json_decode(file_get_contents(__DIR__ . '/../examples/cloud/tacLookupConsole.json'), true);
            ExampleUtils::setResourceKeyInConfig($config, $key);
            (new TacLookupConsole())->run($config, $logger, $record);
        });

        $this->assertStringContainsString(
            "Which devices are associated with the TAC '35925406'?",
            $output
        );
        $this->assertDeviceLinesAreMeaningful($output);
    }

    public function testNativeModelLookupConsole()
    {
        $key = $this->getResourceKey();

        $output = $this->runExample(function ($record) use ($key) {
            $logger = new Logger('info');
            (new NativeModelLookupConsole())->run($key, $logger, $record);
        });

        $this->assertStringContainsString(
            "Which devices are associated with the native model name 'SC-03L'?",
            $output
        );
        $this->assertDeviceLinesAreMeaningful($output);
    }

    public function testMetadataConsole()
    {
        $key = $this->getResourceKey();

        $output = $this->runExample(function ($record) use ($key) {
            $logger = new Logger('info');
            (new MetaDataConsole())->run($key, $logger, $record);
        });

        $this->assertNotEmpty($output);
    }

    /**
     * Run an example, collecting everything it writes through its output
     * callback, and fail if any of it reads as a programming fault.
     *
     * @param callable $example Given the output callback to pass on
     * @return string Everything the example wrote, one line per call
     */
    private function runExample(callable $example): string
    {
        $lines = [];

        $example(function ($line) use (&$lines) { $lines[] = $line; });

        $output = implode("\n", $lines);

        $this->assertGreaterThan(
            0,
            count($lines),
            'The example produced no output at all'
        );

        foreach (self::FAULT_MARKERS as $marker) {
            $this->assertStringNotContainsString(
                $marker,
                $output,
                "The example output contains '{$marker}', which means it " .
                "failed rather than reporting a result. Output was:\n" . $output
            );
        }

        return $output;
    }

    /**
     * Every device line the TAC and native model examples print must say
     * something useful. Either it names a device, or it says the property
     * has no value and gives the reason the cloud service supplied.
     */
    private function assertDeviceLinesAreMeaningful(string $output): void
    {
        $lines = array_filter(
            explode("\n", $output),
            static function ($line) { return strpos($line, "\t") === 0; }
        );

        $this->assertGreaterThan(
            0,
            count($lines),
            'The example listed no devices and gave no reason for it'
        );

        foreach ($lines as $line) {
            $this->assertNotSame(
                '',
                trim($line),
                'A device line is empty'
            );
        }
    }

    /**
     * The failure to match example is a script rather than a class, so it
     * is run as its own process.
     *
     * It used to keep the pipeline it built in a file beside the examples
     * and reuse it on the next run, so a run with one resource key answered
     * with the properties of whichever key had run first. The file name was
     * also built without a separator, so it landed outside the folder the
     * example lives in. The example builds its pipeline on every run now,
     * and this checks that it answers and leaves nothing behind.
     */
    public function testFailureToMatchLeavesNoPipelineBehind()
    {
        $key = $this->getResourceKey();
        $examples = __DIR__ . '/../examples';

        foreach ($this->pipelineFilesUnder($examples) as $stale) {
            unlink($stale);
        }

        $output = $this->runScript(
            $examples . '/cloud/failureToMatch.php',
            $key
        );

        $this->assertStringContainsString(
            'represent a mobile device',
            $output
        );

        $this->assertSame(
            [],
            $this->pipelineFilesUnder($examples),
            'The example left a serialized pipeline behind, and the next run '
            . 'would answer from whatever resource key wrote it'
        );
    }

    /**
     * Runs a PHP script with the resource key in its environment and
     * returns everything it wrote.
     */
    private function runScript(string $script, string $key): string
    {
        $environment = getenv();
        $environment[Constants::RESOURCE_ENV_VAR] = $key;

        $process = proc_open(
            [PHP_BINARY, $script],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            null,
            $environment
        );
        $this->assertIsResource($process, 'Could not start ' . $script);

        $output = stream_get_contents($pipes[1])
            . stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return $output;
    }

    /**
     * Every serialized pipeline file in the examples folder, in the folders
     * below it, and beside it, because a name built without a separator
     * lands beside the folder rather than in it.
     *
     * @return string[]
     */
    private function pipelineFilesUnder(string $folder): array
    {
        $found = array_merge(
            (array) glob($folder . '/*.pipeline'),
            (array) glob($folder . '/*/*.pipeline'),
            (array) glob(dirname($folder) . '/*.pipeline')
        );

        return array_values(array_filter($found, 'is_file'));
    }

    private function getResourceKey()
    {
        $resourceKey = ResourceKeys::find(Constants::RESOURCE_ENV_VAR);

        if ($resourceKey === null) {
            $this->fail(ResourceKeys::missingMessage(Constants::RESOURCE_ENV_VAR));
        }

        return $resourceKey;
    }
}
