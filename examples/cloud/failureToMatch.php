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

/**
 * @example cloud/failureToMatch.php
 *
 * This example shows how the hasValue function can help make sure
 * that meaningful values * are returned when checking properties
 * returned from the device detection engine.
 *
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php/blob/master/examples/cloud/failureToMatch.php).
 *
 * To run this example, you will need to create a **resource key**.
 * The resource key is used as short-hand to store the particular set of
 * properties you are interested in as well as any associated license keys
 * that entitle you to increased request limits and/or paid-for properties.
 * You can create a resource key using the 51Degrees [Configurator](https://configure.51degrees.com).
 *
 * Expected output:
 *
 * ```
 * Does User-Agent 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114' represent a mobile device?:
 * Yes
 * 
 * Does User-Agent 'xyfga' represent a mobile device?:
 * We don't know for sure. The reason given is:
 * No matching entries could be found for the supplied evidence.
 * This is because the supplied User-Agent cannot be mapped to any known device.
 * ```
*/

namespace Examples\Cloud;

// First we include the deviceDetectionPipelineBuilder

use fiftyone\pipeline\devicedetection\DeviceDetectionPipelineBuilder;

// We then create a pipeline with the builder. Create your own resource key for free at https://configure.51degrees.com.

// Check if there is a resource key in the environment variable and use
// it if there is one. You will need to switch this for your own resource key.

if (isset($_ENV["RESOURCEKEY"])) {
    $resourceKey = $_ENV["RESOURCEKEY"];
} else {
    $resourceKey = "!!YOUR_RESOURCE_KEY!!";
}

if ($resourceKey === "!!YOUR_RESOURCE_KEY!!") {
    echo "You need to create a resource key at " .
        "https://configure.51degrees.com and paste it into the code, " .
        "replacing !!YOUR_RESOURCE_KEY!!.";
    echo "\n<br/>";
    echo "Make sure to include the ismobile property " .
        "as it is used by this example.\n<br />";
    return;
}

$builder = new DeviceDetectionPipelineBuilder(array(
    "resourceKey" => $resourceKey,
    "restrictedProperties" => array() // All properties by default
));

// Next we build the pipeline. We could additionally add extra engines and/or
// flowElements here before building.

// To stop having to construct the pipeline
// and re-make cloud API requests used during construction on every page load,
// we recommend caching the serialized pipeline to a database or disk.
// Below we are using PHP's serialize and writing to a file if it doesn't exist

$serializedPipelineFile = __DIR__ . "failure_to_match_pipeline.pipeline";
if(!file_exists($serializedPipelineFile)){
    $pipeline = $builder->build();
    file_put_contents($serializedPipelineFile, serialize($pipeline));
} else {
    $pipeline = unserialize(file_get_contents($serializedPipelineFile));
}

// Here we create a function that checks if a supplied User-Agent is a
// mobile device

function failuretomatch_checkifmobile($pipeline, $userAgent = "")
{

    // We create the flowData object that is used to add evidence to and read data from
    $flowData = $pipeline->createFlowData();

    // Add the User-Agent as evidence

    $flowData->evidence->set("header.user-agent", $userAgent);

    // Now we process the flowData
    $result = $flowData->process();

    // First we check if the property we're looking for has a meaningful result

    print("Does User-Agent '<b>" . $userAgent . "</b>' represent a mobile device?:");
    print("</br>\n");

    if ($result->device->ismobile->hasValue) {
        if ($result->device->ismobile->value) {
            print("Yes");
        } else {
            print("No");
        }
    } else {
        print("We don't know for sure. The reason given is:");
        print("</br>\n");
        // If it doesn't have a meaningful result, we echo out the reason why
        // it wasn't meaningful
        print($result->device->ismobile->noValueMessage);
        print("</br>\n");
        print("This is because the supplied User-Agent cannot be " .
            "mapped to any known device.");
    }

    print("</br>\n");
    print("</br>\n");
}


// Some example User-Agents to test

$iPhoneUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_2 like Mac OS X) AppleWebKit/604.4.7 (KHTML, like Gecko) Mobile/15C114';
failuretomatch_checkifmobile($pipeline, $iPhoneUA);

$badUserAgent = 'xyfga';
failuretomatch_checkifmobile($pipeline, $badUserAgent);
