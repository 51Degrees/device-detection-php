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

use fiftyone\pipeline\devicedetection\DeviceDetectionPipelineBuilder;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\ExampleUtils;
use PHPUnit\Framework\TestCase;

class DeviceDetectionTests extends TestCase
{
    protected $CSVDataFile = __DIR__ . '/51Degrees.csv';

    protected $iPhoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114';

    // TODO: Fix the test
    public function __SKIP__testValueTypes()
    {
        $builder = new DeviceDetectionPipelineBuilder([
            'resourceKey' => $this->getResourceKey()
        ]);

        $pipeline = $builder->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set('header.user-agent', $this->iPhoneUA);

        $result = $flowData->process();

        $properties = $pipeline->getElement('device')->getProperties();

        // TODO remove setheader properties from this list once UACH datafile is released.
        $excludedProperties = ['setheaderbrowseraccept-ch', 'setheaderplatformaccept-ch', 'setheaderhardwareaccept-ch'];

        foreach ($properties as &$property) {
            $key = strtolower($property['name']);

            if (!in_array($key, $excludedProperties)) {
                $apv = $result->device->getInternal($key);

                $expectedType = $property['type'];

                $this->assertNotNull($apv, $key);

                $value = $apv->value;

                switch ($expectedType) {
                    case 'Boolean':
                        if (method_exists($this, 'assertInternalType')) {
                            $this->assertInternalType('boolean', $value, $key);
                        } else {
                            $this->assertIsBool($value, $key);
                        }
                        break;
                    case 'JavaScript':
                    case 'String':
                        if (method_exists($this, 'assertInternalType')) {
                            $this->assertInternalType('string', $value, $key);
                        } else {
                            $this->assertIsString($value, $key);
                        }
                        break;
                    case 'Int32':
                        if (method_exists($this, 'assertInternalType')) {
                            $this->assertInternalType('integer', $value, $key);
                        } else {
                            $this->assertIsInt($value, $key);
                        }
                        break;
                    case 'Double':
                        if (method_exists($this, 'assertInternalType')) {
                            $this->assertInternalType('double', $value, $key);
                        } else {
                            $this->assertIsFloat($value, $key);
                        }
                        break;
                    case 'Array':
                        if (method_exists($this, 'assertInternalType')) {
                            $this->assertInternalType('array', $value, $key);
                        } else {
                            $this->assertIsArray($value, $key);
                        }
                        break;
                    default:
                        $this->fail('expected type for ' . $key . ' was ' . $expectedType);
                }
            }
        }
    }

    public function testPropertyValueBad()
    {
        $builder1 = new DeviceDetectionPipelineBuilder([
            'resourceKey' => $this->getResourceKey()
        ]);

        $badUA = '~';

        $pipeline1 = $builder1->build();

        $flowData1 = $pipeline1->createFlowData();

        $flowData1->evidence->set('header.user-agent', $badUA);

        $result = $flowData1->process();

        $this->assertFalse($result->device->ismobile->hasValue);
        $this->assertSame(
            $result->device->ismobile->noValueMessage,
            'No matching profiles could be found for the supplied evidence. ' .
            "A 'best guess' can be returned by configuring more lenient matching rules. " .
            'See https://51degrees.com/documentation/_device_detection__features__false_positive_control.html'
        );
    }

    public function testPropertyValueGood()
    {
        $builder = new DeviceDetectionPipelineBuilder([
            'resourceKey' => $this->getResourceKey()
        ]);

        $pipeline = $builder->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set('header.user-agent', $this->iPhoneUA);

        $result = $flowData->process();

        $this->assertTrue($result->device->ismobile->value);
    }

    public function testGetProperties()
    {
        $builder = new DeviceDetectionPipelineBuilder([
            'resourceKey' => $this->getResourceKey()
        ]);

        $pipeline = $builder->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set('header.user-agent', $this->iPhoneUA);
        $flowData->process();

        $properties = $pipeline->getElement('device')->getProperties();

        $this->assertSame('IsMobile', $properties['ismobile']['name']);
        $this->assertSame('Boolean', $properties['ismobile']['type']);
        $this->assertSame('Device', $properties['ismobile']['category']);
    }

    public function testAvailableProperties()
    {
        $rows = 0;
        $expectedProperties = [];

        // TODO remove setheader properties from this list once UACH datafile is released.
        $excludedProperties = ['setheaderbrowseraccept-ch', 'setheaderplatformaccept-ch', 'setheaderhardwareaccept-ch'];
        if (($handle = fopen($this->CSVDataFile, 'r')) !== false) {
            while (($row = fgetcsv($handle, 5000, ',')) !== false && $rows == 0) {
                $expectedProperties = $row;
                ++$rows;
            }
        } else {
            throw new \Exception("Failed to open {$this->CSVDataFile}");
        }

        $builder = new DeviceDetectionPipelineBuilder([
            'resourceKey' => $this->getResourceKey()
        ]);

        $pipeline = $builder->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set('header.user-agent', $this->iPhoneUA);

        $result = $flowData->process();

        $properties = $pipeline->getElement('device')->getProperties();

        foreach ($expectedProperties as &$property) {
            $key = strtolower($property);

            if (!in_array($key, $excludedProperties)) {
                $apv = $result->device->get($key);

                $this->assertNotNull($apv, $key);

                if ($apv->hasValue) {
                    $this->assertNotNull($apv->value, $key);
                } else {
                    $this->assertNotNull($apv->noValueMessage, $key);
                }
            }
        }
    }

    public static function providerCloudRequestOriginTestData()
    {
        return [
            ['', true],
            ['test.com', true],
            ['51Degrees.com', false]
        ];
    }

    /**
     * Verify that making requests using a resource key that
     * is limited to particular origins will fail or succeed
     * in the expected scenarios.
     * This is an integration test that uses the live cloud service
     * so any problems with that service could affect the result
     * of this test.
     *
     * @dataProvider providerCloudRequestOriginTestData
     * @param mixed $origin
     * @param mixed $expectException
     */
    public function testCloudRequestOrigin($origin, $expectException)
    {
        $exception = false;

        try {
            $builder = new DeviceDetectionPipelineBuilder([
                'resourceKey' => 'AQS5HKcyVj6B8wNG2Ug',
                'cloudRequestOrigin' => $origin
            ]);

            $pipeline = $builder->build();

            $flowData = $pipeline->createFlowData();

            $flowData->evidence->set('header.user-agent', $this->iPhoneUA);

            $result = $flowData->process();
        } catch (\Exception $e) {
            $exception = true;

            $expectedMessage = "This Resource Key is not authorized for use with this domain: '" . $origin . "'.";

            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                'Exception did not contain expected text (' . $e->getMessage() . ')'
            );
        }

        $this->assertSame($expectException, $exception);
    }

    public function testFailureToMatch()
    {
        ob_start(); // hide output
        include __DIR__ . '/../examples/cloud/failureToMatch.php';
        ob_end_clean(); // discard output

        $this->assertTrue(true);
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
