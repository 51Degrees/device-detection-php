<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2022 51 Degrees Mobile Experts Limited, Davidson House,
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

namespace Tests;

// Fake remote address for web integration

$_SERVER["REMOTE_ADDR"] = "0.0.0.0";
$_SERVER["REQUEST_URI"] = "http://localhost";

use Examples\Cloud\ExampleUtils;
use Examples\Cloud\GettingStartedConsole;
use Examples\Cloud\MetaDataConsole;
use Examples\Cloud\NativeModelLookupConsole;
use Examples\Cloud\TacLookupConsole;
use PHPUnit\Framework\TestCase;
use fiftyone\pipeline\core\Logger;

class ExampleTests extends TestCase
{
    private function getResourceKey() {

        $resourceKey = $_ENV["RESOURCEKEY"];

        if ($resourceKey === "!!YOUR_RESOURCE_KEY!!") {
            $this->fail("You need to create a resource key at " .
            "https://configure.51degrees.com and paste it into the " .
            "phpunit.xml config file, " .
            "replacing !!YOUR_RESOURCE_KEY!!.");
        }

        return $resourceKey;
    }

    public function testGettingStartedConsole()
    {
        $logger = new Logger("info");
        $config = json_decode(file_get_contents(__DIR__."/../examples/cloud/gettingStartedConsole.json"), true);
        ExampleUtils::setResourceKeyInConfig($config, $this->getResourceKey());
        $output = array();
        (new GettingStartedConsole())->run($config, $logger, function($str) use (&$output) { $output[] = $str; });
        $this->assertTrue(count($output) > 0);
    }

    public function testTacLookupConsole()
    {
        $logger = new Logger("info");
        $config = json_decode(file_get_contents(__DIR__."/../examples/cloud/tacLookupConsole.json"), true);
        ExampleUtils::setResourceKeyInConfig($config, $this->getResourceKey());
        $output = array();
        (new TacLookupConsole())->run($config, $logger, function($str) use (&$output) { $output[] = $str; });
        $this->assertTrue(count($output) > 0);
    }

    public function testNativeModelLookupConsole()
    {
        $logger = new Logger("info");
        $output = array();
        (new NativeModelLookupConsole())->run($this->getResourceKey(), $logger, function($str) use (&$output) { $output[] = $str; });
        $this->assertTrue(count($output) > 0);
    }

    public function testMetadataConsole()
    {
        $logger = new Logger("info");
        $output = array();
        (new MetaDataConsole())->run($this->getResourceKey(), $logger, function($str) use (&$output) { $output[] = $str; });
        $this->assertTrue(count($output) > 0);
    }
}
