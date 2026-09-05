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

declare(strict_types=1);

namespace fiftyone\pipeline\devicedetection\tests;

use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\ExampleUtils;
use fiftyone\pipeline\devicedetection\HardwareProfileCloud;
use fiftyone\pipeline\devicedetection\tests\classes\FakeCloudRequestEngine;
use PHPUnit\Framework\TestCase;

/**
 * These tests run against fixed cloud responses, so they need no network
 * connection and no resource key. They cover the case that used to make the
 * TAC and native model examples fill the console with warnings, which is a
 * resource key that returns the hardware profiles but is not entitled to
 * the hardware properties themselves.
 */
class HardwareProfileCloudTest extends TestCase
{
    // A response of the shape the cloud service returns for a TAC lookup
    // when the resource key has the hardware aspect but is not entitled to
    // the hardware properties. The values are null at the aspect level and
    // each one is paired with a reason. The profiles carry only the
    // properties the key is entitled to.
    private const NOT_ENTITLED_RESPONSE = <<<'JSON'
        {
            "hardware": {
                "profiles": [
                    {
                        "devicetype": "SmartPhone",
                        "ismobile": true
                    }
                ],
                "hardwarevendor": null,
                "hardwarevendornullreason": "HardwareVendor is a paid feature. You need a licence key to retrieve data.",
                "hardwarename": null,
                "hardwarenamenullreason": "HardwareName is a paid feature. You need a licence key to retrieve data.",
                "hardwaremodel": null,
                "hardwaremodelnullreason": "HardwareModel is a paid feature. You need a licence key to retrieve data."
            }
        }
        JSON;

    // A response from a fully entitled resource key.
    private const ENTITLED_RESPONSE = <<<'JSON'
        {
            "hardware": {
                "profiles": [
                    {
                        "hardwarevendor": "Apple",
                        "hardwarename": ["iPhone 6"],
                        "hardwaremodel": "A1586"
                    }
                ]
            }
        }
        JSON;

    // A response where the hardware aspect is absent altogether, which is
    // what a resource key without the hardware aspect returns.
    private const NO_HARDWARE_RESPONSE = '{"device": {"ismobile": true}}';

    public function testNullReasonIsCarriedIntoEachProfile()
    {
        $hardware = $this->process(self::NOT_ENTITLED_RESPONSE);

        $this->assertCount(1, $hardware->profiles);

        $profile = $hardware->profiles[0];

        foreach (['hardwarevendor', 'hardwarename', 'hardwaremodel'] as $name) {
            $this->assertArrayHasKey(
                $name,
                $profile,
                "'{$name}' should be present on the profile so the reason " .
                'it has no value can be reported'
            );
            $this->assertFalse($profile[$name]->hasValue);
            $this->assertStringContainsString(
                'paid feature',
                $profile[$name]->noValueMessage
            );
        }
    }

    public function testNullReasonIsReportedByTheExampleHelper()
    {
        $hardware = $this->process(self::NOT_ENTITLED_RESPONSE);
        $profile = $hardware->profiles[0];

        $this->assertSame(
            'Unknown (HardwareVendor is a paid feature. You need a licence ' .
            'key to retrieve data.)',
            ExampleUtils::getHumanReadable($profile, 'hardwarevendor')
        );
    }

    public function testEntitledValuesAreReportedAsValues()
    {
        $hardware = $this->process(self::ENTITLED_RESPONSE);
        $profile = $hardware->profiles[0];

        $this->assertSame(
            'Apple',
            ExampleUtils::getHumanReadable($profile, 'hardwarevendor')
        );
        // A list value is joined for display.
        $this->assertSame(
            'iPhone 6',
            ExampleUtils::getHumanReadable($profile, 'hardwarename')
        );
        $this->assertSame(
            'A1586',
            ExampleUtils::getHumanReadable($profile, 'hardwaremodel')
        );
    }

    public function testAbsentHardwareAspectGivesNoProfilesRatherThanAnError()
    {
        $hardware = $this->process(self::NO_HARDWARE_RESPONSE);

        $this->assertCount(0, ExampleUtils::getProfiles($hardware));
    }

    public function testMissingPropertyIsNamedInTheMessage()
    {
        $hardware = $this->process(self::ENTITLED_RESPONSE);
        $profile = $hardware->profiles[0];

        $message = ExampleUtils::getHumanReadable($profile, 'screenpixelswidth');

        $this->assertStringContainsString('screenpixelswidth', $message);
        $this->assertStringStartsWith('Unknown (', $message);
    }

    /**
     * Run a fixed cloud response through the hardware profile engine and
     * return the resulting element data.
     *
     * @return \fiftyone\pipeline\engines\AspectDataDictionary
     */
    private function process(string $response)
    {
        $pipeline = (new PipelineBuilder())
            ->add(new FakeCloudRequestEngine($response))
            ->add(new HardwareProfileCloud())
            ->build();

        $flowData = $pipeline->createFlowData();
        $flowData->process();

        $this->assertSame(
            [],
            $flowData->errors,
            'Processing a fixed cloud response should not raise an error'
        );

        return $flowData->hardware;
    }
}
