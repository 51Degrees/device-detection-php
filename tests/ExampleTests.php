<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2025 51 Degrees Mobile Experts Limited, Davidson House,
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
use PHPUnit\Framework\TestCase;

class ExampleTests extends TestCase
{
    public function testGettingStartedConsole()
    {
        $logger = new Logger('info');
        $config = json_decode(file_get_contents(__DIR__ . '/../examples/cloud/gettingStartedConsole.json'), true);
        ExampleUtils::setResourceKeyInConfig($config, $this->getResourceKey());
        $output = [];
        (new GettingStartedConsole())->run($config, $logger, function ($str) use (&$output) { $output[] = $str; });
        $this->assertGreaterThan(0, count($output));
    }

    public function testTacLookupConsole()
    {
        $logger = new Logger('info');
        $config = json_decode(file_get_contents(__DIR__ . '/../examples/cloud/tacLookupConsole.json'), true);
        ExampleUtils::setResourceKeyInConfig($config, $this->getResourceKey());
        $output = [];
        (new TacLookupConsole())->run($config, $logger, function ($str) use (&$output) { $output[] = $str; });
        $this->assertGreaterThan(0, count($output));
    }

    public function testNativeModelLookupConsole()
    {
        $logger = new Logger('info');
        $output = [];
        (new NativeModelLookupConsole())->run($this->getResourceKey(), $logger, function ($str) use (&$output) { $output[] = $str; });
        $this->assertGreaterThan(0, count($output));
    }

    public function testMetadataConsole()
    {
        $logger = new Logger('info');
        $output = [];
        (new MetaDataConsole())->run($this->getResourceKey(), $logger, function ($str) use (&$output) { $output[] = $str; });
        $this->assertGreaterThan(0, count($output));
    }

    private function getResourceKey()
    {
        $resourceKey = $_ENV[ExampleUtils::RESOURCE_KEY_ENV_VAR];

        if ($resourceKey === '!!YOUR_RESOURCE_KEY!!') {
            $this->fail('You need to create a resource key at ' .
            'https://configure.51degrees.com and paste it into the ' .
            'phpunit.xml config file, ' .
            'replacing !!YOUR_RESOURCE_KEY!!.');
        }

        return $resourceKey;
    }
}
