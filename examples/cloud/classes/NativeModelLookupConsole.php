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
 * @example cloud/nativeModelLookupConsole.php
 *
 * This example shows how to use the 51Degrees Cloud service to lookup the details of a device
 * based on a given 'native model name'. Native model name is a string of characters that are
 * returned from a query to the device's OS.
 * There are different mechanisms to get native model names for
 * [Android devices](https://developer.android.com/reference/android/os/Build#MODEL) and
 * [iOS devices](https://gist.github.com/soapyigu/c99e1f45553070726f14c1bb0a54053b#file-machinename-swift)
 *
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php/blob/master/examples/cloud/nativeModelLookupConsole.php).
 *
 * @include{doc} example-require-resourcekey.txt
 *
 * Required Composer Dependencies:
 * - 51degrees/fiftyone.devicedetection
 */

namespace fiftyone\pipeline\devicedetection\examples\cloud\classes;

use fiftyone\pipeline\cloudrequestengine\CloudRequestEngine;
use fiftyone\pipeline\cloudrequestengine\Constants;
use fiftyone\pipeline\core\PipelineBuilder;
use fiftyone\pipeline\devicedetection\HardwareProfileCloud;

class NativeModelLookupConsole
{
    // Example values to use when looking up device details from native model names.
    private $nativeModel1 = 'SC-03L';
    private $nativeModel2 = 'iPhone11,8';

    public function run($resourceKey, $logger, callable $output, $cloudEndPoint = '')
    {
        $output('This example shows the details of devices ' .
            "associated with a given 'native model name'.");
        $output('The native model name can be retrieved by ' .
            'code running on the device (For example, a mobile app).');
        $output('For Android devices, see ' .
            'https://developer.android.com/reference/android/os/Build#MODEL');
        $output('For iOS devices, see ' .
            'https://gist.github.com/soapyigu/c99e1f45553070726f14c1bb0a54053b#file-machinename-swift');
        $output('----------------------------------------');

        // This example creates the pipeline and engines in code. For a demonstration
        // of how to do this using a configuration file instead, see the TacLookup example.
        // For more information about builders in general see the documentation at
        // http://51degrees.com/documentation/_concepts__configuration__builders__index.html
        $cloudRequestEngineSettings = ['resourceKey' => $resourceKey];

        // If a cloud endpoint has been provided then set the
        // cloud pipeline endpoint.
        if (!empty($cloudEndPoint)) {
            $cloudRequestEngineSettings['cloudEndpoint'] = $cloudEndPoint;
        }

        // Create the cloud request engine.
        $cloudEngine = new CloudRequestEngine($cloudRequestEngineSettings);
        // Create the hardware profile engine to process the response from the
        // request engine.
        $propertyKeyedEngine = new HardwareProfileCloud();
        // Create the pipeline using the engines.
        $pipeline = (new PipelineBuilder())
            ->addLogger($logger)
            ->add($cloudEngine)
            ->add($propertyKeyedEngine)
            ->build();

        // Pass a native model into the pipeline and list the matching devices.
        $this->analyseModel($this->nativeModel1, $pipeline, $output);
        // Repeat for an alternative native model name.
        $this->analyseModel($this->nativeModel2, $pipeline, $output);
    }

    private function analyseModel($nativemodel, $pipeline, callable $output)
    {
        // Create the FlowData instance.
        $data = $pipeline->createFlowData();
        // Add the native model key as evidence.
        $data->evidence->set(Constants::EVIDENCE_QUERY_NATIVE_MODEL_KEY, $nativemodel);
        // Process the supplied evidence.
        $data->process();
        // Get result data from the flow data.
        $result = $data->hardware;
        $output('Which devices are associated with the ' .
            "native model name '" . $nativemodel . "'?");
        // The 'hardware.profiles' object contains one or more devices.
        // This is the same interface used for standard device detection, so we have
        // access to all the same properties.
        foreach ($result->profiles as $profile) {
            $vendor = ExampleUtils::getHumanReadable($profile, 'hardwarevendor');
            $name = ExampleUtils::getHumanReadable($profile, 'hardwarename');
            $model = ExampleUtils::getHumanReadable($profile, 'hardwaremodel');
            $output("\t" . $vendor . ' ' . $name . ' (' . $model . ')');
        }
    }
}
