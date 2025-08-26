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
 * [JavaScriptBuilderElement](/documentation/4.5/apis/pipeline-php-core/classfiftyone_1_1pipeline_1_1core_1_1_javascript_builder_element.html)
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

namespace fiftyone\pipeline\devicedetection\examples\cloud\classes;

use fiftyone\pipeline\core\Utils;
use fiftyone\pipeline\devicedetection\DeviceDetectionPipelineBuilder;

class GettingStartedWeb
{
    public function run($resourceKey, $logger, callable $output)
    {
        $javascriptBuilderSettings = [
            'endpoint' => '/json',
            'minify' => true,
            // The enableCookies setting is needed if you want to work with results from client-side
            // evidence on the server. For example, precise Apple models or screen dimensions.
            // This will store the results of client-side detection scripts on the client as cookies.
            // On subsequent requests, these cookies will be included in the payload and will be
            // used by the device detection API when it runs.
            'enableCookies' => true
        ];

        $builder = new DeviceDetectionPipelineBuilder([
            'resourceKey' => $resourceKey,
            'javascriptBuilderSettings' => $javascriptBuilderSettings]);

        // To stop having to construct the pipeline
        // and re-make cloud API requests used during construction on every page load,
        // we recommend caching the serialized pipeline to a database or disk.
        // Below we are using PHP's serialize and writing to a file if it doesn't exist
        $serializedPipelineFile = __DIR__ . 'gettingStartedWeb.pipeline';
        if (file_exists($serializedPipelineFile)) {
            $pipeline = unserialize(file_get_contents($serializedPipelineFile));
        } else {
            $pipeline = $builder->build();
            file_put_contents($serializedPipelineFile, serialize($pipeline));
        }

        $this->processRequest($pipeline, $output);
    }

    private function processRequest($pipeline, callable $output)
    {
        // Create the flowdata object.
        $flowdata = $pipeline->createFlowData();

        // Add any information from the request (headers, cookies and additional
        // client side provided information)
        $flowdata->evidence->setFromWebRequest();

        // Process the flowdata
        $flowdata->process();

        // Some browsers require that extra HTTP headers are explicitly
        // requested. So set whatever headers are required by the browser in
        // order to return the evidence needed by the pipeline.
        // More info on this can be found at
        // https://51degrees.com/blog/user-agent-client-hints
        Utils::setResponseHeader($flowdata);

        // First we make a JSON route that will be called from the client side
        // and will return a JSON encoded property database using any additional
        // evidence provided by the client.
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/json') {
            header('Content-Type: application/json');
            $output(json_encode($flowdata->jsonbundler->json));

            return;
        }

        include_once __DIR__ . '/../static/page.php';
    }
}
