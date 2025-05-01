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
 * @example cloud/tacLookupConsole.php
 *
 * This example shows how to use the 51Degrees Cloud service to lookup the details of a device
 * based on a given 'TAC'. More background information on TACs can be found through various online
 * sources such as <a href="https://en.wikipedia.org/wiki/Type_Allocation_Code">Wikipedia</a>.
 *
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php/blob/master/examples/cloud/tacLookupConsole.php).
 *
 * @include{doc} example-require-resourcekey.txt
 *
 * Required Composer Dependencies:
 * - 51degrees/fiftyone.devicedetection
 */

namespace fiftyone\pipeline\devicedetection\examples\cloud\classes;

use fiftyone\pipeline\cloudrequestengine\Constants;
use fiftyone\pipeline\core\PipelineBuilder;

class TacLookupConsole
{
    // Example values to use when looking up device details from TACs.
    private $tac1 = '35925406';
    private $tac2 = '86386802';

    public function run($config, $logger, callable $output, $cloudEndPoint = '')
    {
        $output('This example shows the details of devices ' .
            "associated with a given 'Type Allocation Code' or 'TAC'.");
        $output('More background information on TACs can be ' .
            'found through various online sources such as Wikipedia: ' .
            'https://en.wikipedia.org/wiki/Type_Allocation_Code');
        $output('----------------------------------------');

        // In this example, we use the PipelineBuilder and configure it from a file.
        // For a demonstration of how to do this in code instead, see the
        // NativeModelLookup example.
        // For more information about builders in general see the documentation at
        // http://51degrees.com/documentation/_concepts__configuration__builders__index.html

        // Create the pipeline using the service provider and the configured options.
        $pipeline = (new PipelineBuilder())
            ->addLogger($logger)
            ->buildFromConfig($config);

        // Pass a TAC into the pipeline and list the matching devices.
        $this->analyseTac($this->tac1, $pipeline, $output);
        // Repeat for an alternative TAC.
        $this->analyseTac($this->tac2, $pipeline, $output);
    }

    private function analyseTac($tac, $pipeline, callable $output)
    {
        // Create the FlowData instance.
        $data = $pipeline->createFlowData();
        // Add the TAC as evidence.
        $data->evidence->set(Constants::EVIDENCE_QUERY_TAC_KEY, $tac);
        // Process the supplied evidence.
        $data->process();
        // Get result data from the flow data.
        $result = $data->hardware;
        $output('Which devices are associated with the ' .
            "TAC '" . $tac . "'?");
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
