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

/**
 * @example cloud/userAgentClientHints-Web.php
 *
 * @include{doc} example-web-integration-client-hints.txt
 *
 * This example shows how a simple device detection pipeline can be built
 * that checks if a provided User-Agent is a mobile device
 *
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php/blob/master/examples/cloud/userAgentClientHints-Web.php).
 *
 * To run this example, you will need to create a **resource key**.
 * The resource key is used as short-hand to store the particular set of
 * properties you are interested in as well as any associated license keys
 * that entitle you to increased request limits and/or paid-for properties.
 * You can create a resource key using the 51Degrees [Configurator](https://configure.51degrees.com?utm_source=code&utm_medium=example&utm_campaign=device-detection-php&utm_content=examples-cloud-useragentclienthints-web.php&utm_term=header).
 * Make sure to include required User Agent Client Hints Set Header properties which are in the following format, to get full * client-hints functionality.
 * SetHeader[Component name][Response header name]
 
 * Expected output:
 * ```
 * User Agent Client Hints Example
 * Select the Use User Agent Client Hints button below, to use User Agent Client Hint headers in evidence for device 
 * detections.
 * ...
 * Hardware Vendor: Unknown
 * Hardware Name: Array
 * Device Type: Desktop
 * ...
 * ```
 *
 */

// First we include the deviceDetectionPipelineBuilder

require(__DIR__ . "/../../vendor/autoload.php");

use fiftyone\pipeline\core\Logger;
use fiftyone\pipeline\core\Utils;
use fiftyone\pipeline\devicedetection\DeviceDetectionPipelineBuilder;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\ExampleUtils;

// We then create a pipeline with the builder. Create your own resource key for free at https://configure.51degrees.com?utm_source=code&utm_medium=example&utm_campaign=device-detection-php&utm_content=examples-cloud-useragentclienthints-web.php&utm_term=top.

// Check if there is a resource key in the environment variable or query parameter
// and use it if there is one. You will need to switch this for your own resource key.

$resourceKey = ExampleUtils::getResourceKeyFromEnv();

if (empty($resourceKey)) {
    $resourceKey = ExampleUtils::getResourceKeyFromQueryParameter();
}

if (empty($resourceKey)) {
    $message = 'No resource key specified in the environment variable or query parameter ';
    $message .= "'" . ExampleUtils::RESOURCE_KEY_ENV_VAR . "'." . PHP_EOL;
    $message .= 'Create a resource key with the properties required by this example';
    $message .= 'at https://configure.51degrees.com?utm_source=code&utm_medium=example&utm_campaign=device-detection-php&utm_content=examples-cloud-useragentclienthints-web.php&utm_term=resource-key-required' . '<br/>';
    $message .= 'Once complete, populate the environment variable or query parameter ';
    $message .= 'mentioned at the start of this message with the key.' . '<br/>';

    ExampleUtils::logErrorAndExit(new Logger('info'), $message);
}

$builder = new DeviceDetectionPipelineBuilder(array(
    "resourceKey" => $resourceKey,
    // Set to true so that if the underlying cloud service fails during request
    // processing the pipeline degrades gracefully instead of returning a 500.
    // Use false while developing to surface mistakes loudly.
    "suppressProcessExceptions" => true
));
$pipeline = $builder->build();


// We create the flowData object that is used to add evidence to and read
// data from 
$flowData = $pipeline->createFlowData();

// We set headers, cookies and more information from the web request
$flowData->evidence->setFromWebRequest();

// Now we process the flowData
$flowData->process();

$device = $flowData->device;

// Some browsers require that extra HTTP headers are explicitly
// requested. So set whatever headers are required by the browser in
// order to return the evidence needed by the pipeline.
// More info on this can be found at
// https://51degrees.com/blog/user-agent-client-hints?utm_source=code&utm_medium=example&utm_campaign=device-detection-php&utm_content=examples-cloud-useragentclienthints-web.php&utm_term=top
Utils::setResponseHeader($flowData);

// Generate the HTML
?>
<head>
    <title>User Agent Client Hints Example</title>
    <style>
        <?php
        // The shared pattern-library stylesheet is vendored under static/ as
        // examples-main.min.css. This standalone example script serves the whole
        // response itself, so there is no separate static route to link to. We
        // inline the stylesheet here instead so the page is styled standalone.
        require(__DIR__ . "/static/examples-main.min.css");
        ?>
    </style>
</head>

<div class="c-eg-page">
    <h2 class="c-eg-page__title">User Agent Client Hints example</h2>

    <p class="c-eg-page__lead">
        By default, the user-agent, sec-ch-ua and sec-ch-ua-mobile HTTP headers are sent.
        This means that on the first request, the server can determine the browser from
        sec-ch-ua while other details must be derived from the user-agent.
    </p>
    <p class="c-eg-page__lead">
        If the server determines that the browser supports client hints, then it may request
        additional client hints headers by setting the Accept-CH header in the response.
        Select the <strong>Make second request</strong> button below to send another request
        to the server. This time, any additional client hints headers that have been requested
        will be included.
    </p>

    <div class="c-eg-button-row">
        <button type="button" class="b-btn" onclick="redirect()">Make second request</button>
    </div>

    <script>

        // This script runs when the button is clicked and the device detection request is
        // sent again to the server with all additional client hints that were requested in the
        // previous response by the server.
        // The following sequence is followed.
        // 1. The user sends the first request to the web server for detection.
        // 2. The web server returns the properties in the response based on the headers sent in
        //    the request. Along with the properties, it also sends a new Accept-CH header in the
        //    response indicating the additional evidence it needs. It builds the new response
        //    header using SetHeader[Component name]Accept-CH properties, where Component Name is
        //    the name of the component for which properties are required.
        // 3. When the "Make second request" button is clicked, the device detection request is
        //    sent again to the server with all additional client hints that were requested in the
        //    previous response by the server.
        // 4. The web server returns the properties based on the new User Agent Client Hint
        //    headers being used as evidence.

        function redirect() {
            sessionStorage.reloadAfterPageLoad = true;
            window.location.reload(true);
        }

        window.onload = function () {
            if (sessionStorage.reloadAfterPageLoad) {
                document.getElementById('description').innerHTML = '<p class="c-eg-page__lead">The information shown below is determined using <strong>User Agent Client Hints</strong> that were sent in the request to obtain additional evidence. If no additional information appears then it may indicate an external problem such as <strong>User Agent Client Hints</strong> being disabled in your browser.</p>';
                sessionStorage.reloadAfterPageLoad = false;
            } else {
                document.getElementById('description').innerHTML = '<p class="c-eg-page__lead">The following values are determined by server-side device detection on the first request.</p>';
            }
        }

    </script>

    <div id="evidence">
        <h3 class="c-eg-page__heading">Evidence values used</h3>
        <p class="c-eg-legend">
            Evidence was
            <span class="c-eg-legend__swatch c-eg-legend__swatch--used">used</span>
            /
            <span class="c-eg-legend__swatch c-eg-legend__swatch--present">present</span>
            for detection
        </p>
        <table class="c-eg-table">
            <thead class="c-eg-table__head">
                <tr class="c-eg-table__row">
                    <th class="c-eg-table__cell">Key</th>
                    <th class="c-eg-table__cell">Value</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $evidences = $pipeline->getElement("device")->filterEvidence($flowData);
                foreach ($evidences as $key => $value) {
                    if (strpos($key, strtolower("header.sec-ch")) !== false
                        || strpos($key, strtolower("header.user-agent")) !== false) {
                        echo "<tr class='c-eg-table__row c-eg-table__row--used'>";
                        echo "<td class='c-eg-table__cell c-eg-table__cell--key'>" . strVal($key) . "</td>";
                        echo "<td class='c-eg-table__cell'>" . strVal($value) . "</td>";
                        echo "</tr>";
                    }
                }
            ?>
            </tbody>
        </table>
    </div>

    <h3 class="c-eg-page__heading">Detection results</h3>
    <div id="description"></div>
    <div id="content">
        <table class="c-eg-table">
            <thead class="c-eg-table__head">
                <tr class="c-eg-table__row">
                    <th class="c-eg-table__cell">Key</th>
                    <th class="c-eg-table__cell">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Hardware Vendor:</td><td class="c-eg-table__cell"><?php echo $device->hardwarevendor->hasValue ? $device->hardwarevendor->value : $device->hardwarevendor->noValueMessage; ?></td></tr>
                <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Hardware Name:</td><td class="c-eg-table__cell"><?php echo $device->hardwarename->hasValue ? implode(", ", $device->hardwarename->value) : $device->hardwarename->noValueMessage; ?></td></tr>
                <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Device Type:</td><td class="c-eg-table__cell"><?php echo $device->devicetype->hasValue ? $device->devicetype->value : $device->devicetype->noValueMessage; ?></td></tr>
                <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Platform Vendor:</td><td class="c-eg-table__cell"><?php echo $device->platformvendor->hasValue ? $device->platformvendor->value : $device->platformvendor->noValueMessage; ?></td></tr>
                <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Platform Name:</td><td class="c-eg-table__cell"><?php echo $device->platformname->hasValue ? $device->platformname->value : $device->platformname->noValueMessage; ?></td></tr>
                <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Platform Version:</td><td class="c-eg-table__cell"><?php echo $device->platformversion->hasValue ? $device->platformversion->value : $device->platformversion->noValueMessage; ?></td></tr>
                <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Browser Vendor:</td><td class="c-eg-table__cell"><?php echo $device->browservendor->hasValue ? $device->browservendor->value : $device->browservendor->noValueMessage; ?></td></tr>
                <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Browser Name:</td><td class="c-eg-table__cell"><?php echo $device->browsername->hasValue ? $device->browsername->value : $device->browsername->noValueMessage; ?></td></tr>
                <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Browser Version:</td><td class="c-eg-table__cell"><?php echo $device->browserversion->hasValue ? $device->browserversion->value : $device->browserversion->noValueMessage; ?></td></tr>
            </tbody>
        </table>
    </div>

    <?php
        echo '<div class="c-eg-message">';
        echo '  <p class="c-eg-message__text">Want to try on-premise? <a href="https://51degrees.com/contact-us?utm_source=code&utm_medium=example&utm_campaign=device-detection-php&utm_content=examples-cloud-useragentclienthints-web.php&utm_term=try-on-premise">Contact us</a> to discuss requirements.</p>';
        echo '  <a class="b-btn c-eg-message__cta" href="https://51degrees.com/contact-us?utm_source=code&utm_medium=example&utm_campaign=device-detection-php&utm_content=examples-cloud-useragentclienthints-web.php&utm_term=try-on-premise">Contact us</a>';
        echo '</div>';
    ?>
</div>

