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

/**
 * @example cloud/gettingStartedConsole.php
 *
 * @include{doc} example-getting-started-cloud.txt
 *
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php/blob/master/examples/cloud/gettingStartedConsole.php).
 *
 * @include{doc} example-require-resourcekey.txt
 *
 * Required Composer Dependencies:
 * - [51degrees/fiftyone.devicedetection](https://packagist.org/packages/51degrees/fiftyone.devicedetection)
 */

namespace fiftyone\pipeline\devicedetection\examples\cloud\classes;

use fiftyone\pipeline\core\PipelineBuilder;

class GettingStartedConsole
{
    // This collection contains the various input values that will
    // be passed to the device detection algorithm.
    private $evidenceValues = [
        // A User-Agent from a mobile device.
        [
            'header.user-agent' => 'Mozilla/5.0 (Linux; Android 9; SAMSUNG SM-G960U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/10.1 Chrome/71.0.3578.99 Mobile Safari/537.36'
        ],
        // A User-Agent from a desktop device.
        [
            'header.user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36'
        ],
        // Evidence values from a Windows 11 device using a browser
        // that supports User-Agent Client Hints.
        [
            'header.user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.102 Safari/537.36',
            'header.sec-ch-ua-mobile' => '?0',
            'header.sec-ch-ua' => '" Not A; Brand";v="99", "Chromium";v="98", "Google Chrome";v="98"',
            'header.sec-ch-ua-platform' => '"Windows"',
            'header.sec-ch-ua-platform-version' => '"14.0.0"'
        ]
    ];

    public function run($config, $logger, callable $output)
    {
        // In this example, we use the PipelineBuilder and configure it from a file.
        // For more information about builders in general see the documentation at
        // http://51degrees.com/documentation/_concepts__configuration__builders__index.html
        $pipeline = (new PipelineBuilder())
            ->addLogger($logger)
            ->buildFromConfig($config);

        // carry out some sample detections
        foreach ($this->evidenceValues as &$values) {
            $this->analyseEvidence($values, $pipeline, $output);
        }
    }

    public function outputValue($name, $value, &$message)
    {
        // Individual result values have a wrapper called
        // `AspectPropertyValue`. This functions similarly to
        // a null-able type.
        // If the value has not been set then trying to access the
        // `value` method will throw an exception.
        // `AspectPropertyValue` also includes the `no_value_message`
        // method, which describes why the value has not been set.
        if ($value->hasValue) {
            $message[] = "\t{$name}: {$value->value}";
        } else {
            $message[] = "\t{$name}: {$value->noValueMessage}";
        }
    }

    private function analyseEvidence($evidence, $pipeline, callable $output)
    {
        // FlowData is a data structure that is used to convey
        // information required for detection and the results of the
        // detection through the pipeline.
        // Information required for detection is called "evidence"
        // and usually consists of a number of HTTP Header field
        // values, in this case represented by a dictionary of header
        // name/value entries.
        $data = $pipeline->createFlowData();

        $message = [];

        // List the evidence
        $message[] = 'Input values:';
        foreach ($evidence as $key => $value) {
            $message[] = "\t{$key}: {$value}";
        }

        $output(implode("\n", $message));

        // Add the evidence values to the flow data
        $data->evidence->setArray($evidence);

        // Process the flow data.
        $data->process();

        $message = [];
        $message[] = 'Results:';

        // Now that it's been processed, the flow data will have
        // been populated with the result. In this case, we want
        // information about the device, which we can get by
        // asking for a result matching named "device"
        $device = $data->device;

        // Display the results of the detection, which are called
        // device properties. See the property dictionary at
        // https://51degrees.com/developers/property-dictionary
        // for details of all available properties.
        $this->outputValue('Mobile Device', $device->ismobile, $message);
        $this->outputValue('Platform Name', $device->platformname, $message);
        $this->outputValue('Platform Version', $device->platformversion, $message);
        $this->outputValue('Browser Name', $device->browsername, $message);
        $this->outputValue('Browser Version', $device->browserversion, $message);
        $output(implode("\n", $message));
    }
}
