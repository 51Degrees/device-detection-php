<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2019 51 Degrees Mobile Experts Limited, 5 Charlotte Close,
 * Caversham, Reading, Berkshire, United Kingdom RG4 7BY.
 *
 * This Original Work is licensed under the European Union Public Licence (EUPL)
 * v.1.2 and is subject to its terms as set out below.
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


require(__DIR__ . "/../vendor/autoload.php");

// Fake remote address for web integration

$_SERVER["REMOTE_ADDR"] = "0.0.0.0";

use PHPUnit\Framework\TestCase;
use fiftyone\pipeline\devicedetection\DeviceDetectionPipelineBuilder;

class ExampleTests extends TestCase
{
    protected $CSVDataFile = __DIR__ . "/51Degrees.csv";

    protected $iPhoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114';

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

    public function testPropertyValueBad()
    {
        $builder1 = new DeviceDetectionPipelineBuilder(array(
            "resourceKey" => $this->getResourceKey()
        ));

        $badUA = 'w5higsnrg';

        $pipeline1 = $builder1->build();

        $flowData1 = $pipeline1->createFlowData();

        $flowData1->evidence->set("header.user-agent", $badUA);

        $result = $flowData1->process();
        
        $this->assertFalse($result->device->ismobile->hasValue);
        $this->assertEquals(
            $result->device->ismobile->noValueMessage,
            "No matching profiles could be found for the supplied evidence. " . 
            "A 'best guess' can be returned by configuring more lenient matching rules. " .
            "See https://51degrees.com/documentation/_device_detection__features__false_positive_control.html"
        );
    }

    public function testPropertyValueGood()
    {
        $builder = new DeviceDetectionPipelineBuilder(array(
            "resourceKey" => $this->getResourceKey()
        ));

        

        $pipeline = $builder->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set("header.user-agent", $this->iPhoneUA);

        $result = $flowData->process();
        
        $this->assertTrue($result->device->ismobile->value);
    }

    public function testGetProperties()
    {
        $builder = new DeviceDetectionPipelineBuilder(array(
            "resourceKey" => $this->getResourceKey()
        ));

        $pipeline = $builder->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set("header.user-agent", $this->iPhoneUA);

        $properties = $pipeline->getElement("device")->getProperties();

        $this->assertEquals($properties["ismobile"]["name"], "IsMobile");
        $this->assertEquals($properties["ismobile"]["type"], "Boolean");
        $this->assertEquals($properties["ismobile"]["category"], "Device");
    }

    public function testAvailableProperties()
    {
        $rows = 0;
        $expectedProperties = [];
		
		//TODO remove setheader properties from this list once UACH datafile is released.
        $excludedProperties = ["setheaderbrowseraccept-ch", "setheaderplatformaccept-ch", "setheaderhardwareaccept-ch"];
        if (($handle = fopen($this->CSVDataFile, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 5000, ",")) !== FALSE && $rows == 0) 
            {
                $expectedProperties = $row;                
                $rows++;
            }
        }

        $builder = new DeviceDetectionPipelineBuilder(array(
            "resourceKey" => $this->getResourceKey()
        ));

        $pipeline = $builder->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set("header.user-agent", $this->iPhoneUA);

        $result = $flowData->process();

        $properties = $pipeline->getElement("device")->getProperties();

        foreach ($expectedProperties as &$property)
        {
			
			$key = strtolower($property);
				
			if (!in_array($key, $excludedProperties)){
				
				$apv = $result->device->getInternal($key);

				$this->assertNotNull($apv, $key);

				if ($apv->hasValue) {

                $this->assertNotNull($apv->value, $key);

				} else {

					$this->assertNotNull($apv->noValueMessage, $key);

				}
			}
        }
    }

    public function testValueTypes()
    {
        $builder = new DeviceDetectionPipelineBuilder(array(
            "resourceKey" => $this->getResourceKey()
        ));        

        $pipeline = $builder->build();

        $flowData = $pipeline->createFlowData();

        $flowData->evidence->set("header.user-agent", $this->iPhoneUA);

        $result = $flowData->process();

        $properties = $pipeline->getElement("device")->getProperties();
		
		//TODO remove setheader properties from this list once UACH datafile is released.
        $excludedProperties = ["setheaderbrowseraccept-ch", "setheaderplatformaccept-ch", "setheaderhardwareaccept-ch"];

        foreach ($properties as &$property)
        {				
			$key = strtolower($property["name"]);
				
			if (!in_array($key, $excludedProperties)){
				
				$apv = $result->device->getInternal($key);

				$expectedType = $property["type"];
				
				$this->assertNotNull($apv, $key);

				$value = $apv->value;

				switch ($expectedType) {
					case "Boolean":
						if (method_exists($this, 'assertInternalType')) {
							$this->assertInternalType("boolean", $value, $key);
						} else {
							$this->assertIsBool($value, $key);
						}
						break;
					case 'String':
						if (method_exists($this, 'assertInternalType')) {
							$this->assertInternalType("string", $value, $key);
						} else {
							$this->assertIsString($value, $key);
						}
						break;
					case 'JavaScript':
						if (method_exists($this, 'assertInternalType')) {
							$this->assertInternalType("string", $value, $key);
						} else {
							$this->assertIsString($value, $key);
						}
						break;
					case 'Int32':
						if (method_exists($this, 'assertInternalType')) {
							$this->assertInternalType("integer", $value, $key);
						} else {
							$this->assertIsInt($value, $key);
						}
						break;
					case 'Double':
						if (method_exists($this, 'assertInternalType')) {
							$this->assertInternalType("double", $value, $key);
						} else {
							$this->assertIsFloat($value, $key);
						}
						break;
					case 'Array':
						if (method_exists($this, 'assertInternalType')) {
							$this->assertInternalType("array", $value, $key);
						} else {
							$this->assertIsArray($value, $key);
						}
						break;
					default:
						$this->fail("expected type for " . $key . " was " . $expectedType);
						break;
				}
			}
	    }
    }

    public function testFailureToMatch()
    {
        include __DIR__ . "/../examples/cloud/failureToMatch.php";

        $this->assertTrue(true);
    }

    public function testGettingStarted()
    {
        include __DIR__ . "/../examples/cloud/gettingstarted.php";
        
        $this->assertTrue(true);
    }
    
    public function testMetaData()
    {
        include __DIR__ . "/../examples/cloud/metadata.php";
        
        $this->assertTrue(true);
    }
    
    public function testWebIntegration()
    {
        include __DIR__ . "/../examples/cloud/webIntegration.php";
        
        $this->assertTrue(true);
    }

    public function testUserAgentClientHints()
    {
        include __DIR__ . "/../examples/cloud/userAgentClientHints.php";
        
        $this->assertTrue(true);
    }
}
