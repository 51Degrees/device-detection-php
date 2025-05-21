<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2023 51 Degrees Mobile Experts Limited, Davidson House,
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
 * @example cloud/metadataConsole.php
 * The cloud service exposes meta data that can provide additional information about the various
 * properties that might be returned.
 * This example shows how to access this data and display the values available.
 *
 * A list of the properties will be displayed, along with some additional information about each
 * property. Note that this is the list of properties used by the supplied resource key, rather
 * than all properties that can be returned by the cloud service.
 *
 * In addition, the evidence keys that are accepted by the service are listed. These are the
 * keys that, when added to the evidence collection in flow data, could have some impact on the
 * result that is returned.
 *
 * Bear in mind that this is a list of ALL evidence keys accepted by all products offered by the
 * cloud. If you are only using a single product (for example - device detection) then not all
 * of these keys will be relevant.
 *
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php/blob/main/examples/cloud/classes/MetadataConsole.php).
 *
 * @include{doc} example-require-resourcekey.txt
 *
 * Required Composer Dependencies:
 * - 51degrees/fiftyone.devicedetection
 */

namespace fiftyone\pipeline\devicedetection\examples\cloud\classes;

use fiftyone\pipeline\core\BasicListEvidenceKeyFilter;
use fiftyone\pipeline\devicedetection\DeviceDetectionPipelineBuilder;

class MetadataConsole
{
    /**
     * In this example, we use the DeviceDetectionPipelineBuilder
     * and configure it in code. For more information about
     * pipelines in general see the documentation at
     * https://51degrees.com/documentation/4.4/_concepts__configuration__builders__index.html.
     *
     * @param string $resourceKey
     * @param \fiftyone\pipeline\core\Logger $logger
     */
    public function run($resourceKey, $logger, callable $output)
    {
        $pipeline = (new DeviceDetectionPipelineBuilder(['resourceKey' => $resourceKey]))
            ->addLogger($logger)
            ->build();
        
        $pipeline->createFlowData()->process();

        $this->outputProperties($pipeline->getElement('device'), $output);
        // We use the CloudRequestEngine to get evidence key details, rather than the
        // DeviceDetectionCloudEngine.
        // This is because the DeviceDetectionCloudEngine doesn't actually make use
        // of any evidence values. It simply processes the JSON that is returned
        // by the call to the cloud service that is made by the CloudRequestEngine.
        // The CloudRequestEngine is actually taking the evidence values and passing
        // them to the cloud, so that's the engine we want the keys from.
        $this->outputEvidenceKeyDetails($pipeline->getElement('cloud'), $output);
    }

    private function outputProperties($engine, callable $output)
    {
        foreach ($engine->getProperties() as $property) {
            // Output some details about the property.
            $output('Property - ' . $property['name'] . ' ' .
                '[Category: ' . $property['category'] . '] (' . $property['type'] . ')');
        }
    }

    private function outputEvidenceKeyDetails($engine, callable $output)
    {
        $output('');
        if ($engine->getEvidenceKeyFilter() instanceof BasicListEvidenceKeyFilter) {
            // If the evidence key filter extends BasicListEvidenceKeyFilter then we can
            // display a list of accepted keys.
            $filter = $engine->getEvidenceKeyFilter();
            $output('Accepted evidence keys:');
            foreach ($filter->getList() as $key) {
                $output("\t{$key}");
            }
        } else {
            $output('The evidence key filter has type ' .
                $engine->getEvidenceKeyFilter() . '. As this does not extend ' .
                'BasicListEvidenceKeyFilter, a list of accepted values cannot be ' .
                'displayed. As an alternative, you can pass evidence keys to ' .
                'filter->filterEvidenceKey(string) to see if a particular key will be included ' .
                'or not.');
            $output('For example, header.user-agent is ' .
                ($engine->getEvidenceKeyFilter() . $engine->filterEvidenceKey('header.user-agent') ? '' : 'not ') .
                'accepted.');
        }
    }
}
