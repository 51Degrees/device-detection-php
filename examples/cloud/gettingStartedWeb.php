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
 * @example cloud/gettingStartedWeb.php
 *
 * @include{doc} example-getting-started-web.txt
 * 
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php/blob/master/examples/cloud/gettingStartedWeb.php). 
 * 
 * @include{doc} example-require-resourcekey.txt
 * 
 * Required Composer Dependencies:
 * - [51degrees/fiftyone.devicedetection](https://packagist.org/packages/51degrees/fiftyone.devicedetection)
 * 
 * ## Overview
 * 
 * The `DeviceDetectionPipelineBuilder` class is used to create a Pipeline instance from the configuration
 * that is supplied.
 * The fiftyone\pipeline\core\Utils module contains helpers which deal with
 * automatically populating evidence from a web request.
 * ```{php}
 * $flowdata->evidence->setFromWebRequest()
 * ```
 *
 * The module can also handling setting response headers (e.g. Accept-CH for User-Agent 
 * Client Hints) and serving requests for client-side JavaScript and JSON resources.
 * ```{php}
 * Utils::setResponseHeader($flowdata);
 * ```
 * 
 * The results of detection can be accessed by through the flowdata object once
 * processed. This can then be used to interrogate the data.
 * ```{php}
 * $flowdata->process();
 * $device = $flowdata->device;
 * $hardwareVendor = $device->hardwarevendor;
 * ```
 * 
 * Results can also be accessed in client-side code by using the `fod` object. See the 
 * [JavaScriptBuilderElement](https://51degrees.com/pipeline-php/classfiftyone_1_1pipeline_1_1core_1_1_javascript_builder_element.html)
 * for details on available settings such as changing the `fod` name.
 * ```{js}
 * window.onload = function () {
 *     fod.complete(function(data) {
 *         var hardwareName = data.device.hardwarename;
 *         alert(hardwareName.join(", "));
 *     }
 * }
 * ```
 *
 * ## View
 * @include static/page.php
 *
 * ## Class
 */

require_once(__DIR__ . '/../../vendor/autoload.php');

use fiftyone\pipeline\core\Logger;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\ExampleUtils;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\GettingStartedWeb;

function main()
{
    // Configure a logger to output to the console
    $logger = new Logger("info");

    // Get the resource key from the environment variable
    $resourceKey = ExampleUtils::getResourceKeyFromEnv();

    // Otherwise, get the resource key from the query parameter
    if (empty($resourceKey)) {
        $resourceKey = ExampleUtils::getResourceKeyFromQueryParameter();
    }
    
    if (empty($resourceKey)) {
        $message = "No resource key specified in CLI args or environment variable '" .
            ExampleUtils::RESOURCE_KEY_ENV_VAR . "'." . PHP_EOL .
            "The 51Degrees cloud service is accessed using a 'ResourceKey'. " .
            "For more detail see " .
            "http://51degrees.com/documentation/4.3/_info__resource_keys.html. " .
            "A resource key with the properties required by this " .
            "example can be created for free at " .
            "https://configure.51degrees.com/g3gMZdPY. " .
            "Once complete, populate the environment variable " .
            "mentioned at the start of this message with the key.";

        ExampleUtils::logErrorAndExit($logger, $message);
    }

    (new GettingStartedWeb())->run($resourceKey, $logger, function($message) { echo $message; });
}

main();