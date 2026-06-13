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
?>
<head>
    <title>Web Integration Example</title>
    <?php use fiftyone\pipeline\devicedetection\examples\cloud\classes\ExampleUtils; ?>
    <style>
        <?php
        // The shared pattern-library stylesheet is vendored next to this file as
        // examples-main.min.css. The built-in PHP server runs the example script as
        // the router for every request, so there is no separate static route to link
        // to. We inline the stylesheet here instead so the page is styled standalone.
        require(__DIR__ . "/examples-main.min.css");
        ?>
    </style>
</head>

<div class="c-eg-page">
    <h2 class="c-eg-page__title">Web integration example</h2>

    <p class="c-eg-page__lead">
        This example demonstrates the use of the Pipeline API to perform device detection within a
        simple PHP web project. In particular, it highlights:
    </p>
    <ol>
        <li>
            Automatic handling of the 'Accept-CH' header, which is used to request User-Agent
            Client Hints from the browser
        </li>
        <li>
            Client-side evidence collection in order to identify Apple device models and properties
            such as screen size.
        </li>
    </ol>
    <h3 class="c-eg-page__heading">Client Hints</h3>
    <p class="c-eg-page__lead">
        When the first request is made, browsers that support client hints will typically send a subset
        of client hints values along with the User-Agent header.
        If device detection determines that the browser does support client hints then it will request
        that additional client hints headers are sent with future requests by sending the Accept-CH
        header with the response.
    </p>
    <p class="c-eg-page__lead">
        Note that if you have visited this page previously, the value of Accept-CH will have been
        cached so all requested client hints headers will be sent on the first request. Using features
        such as 'private browsing' or 'incognito mode' will allow you to see the true first request
        experience as the previous Accept-CH value will not be used.
    </p>

    <noscript>
        <div class="c-eg-alert">
            WARNING: JavaScript is disabled in your browser. This means that the callback discussed
            further down this page will not fire and UACH headers will not be sent.
        </div>
    </noscript>

    <div id="content">
        <div id="response-headers">
            <h3 class="c-eg-page__heading">Response headers</h3>
            <p class="c-eg-page__lead">The following response headers were set:</p>
            <table class="c-eg-table">
                <thead class="c-eg-table__head">
                    <tr class="c-eg-table__row">
                        <th class="c-eg-table__cell">Key</th>
                        <th class="c-eg-table__cell">Value</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $rowAlt = false;
                    foreach (headers_list() as $header)
                    {
                        $parts = explode(": ", $header);
                        $rowClass = $rowAlt ? "c-eg-table__row c-eg-table__row--alt" : "c-eg-table__row";
                        $rowAlt = !$rowAlt;
                        $output("<tr class='".$rowClass."'>");
                        $output("<td class='c-eg-table__cell c-eg-table__cell--key'>".$parts[0]."</td>");
                        $output("<td class='c-eg-table__cell'>".$parts[1]."</td>");
                        $output("</tr>");
                    }
                ?>
                </tbody>
            </table>
        </div>

        <?php if (ExampleUtils::containsAcceptCh() == false) { ?>
            <div class="c-eg-alert">
                WARNING: There is no Accept-CH header in the response. This may indicate that your
                browser does not support User-Agent Client Hints. This is not necessarily a problem,
                but if you are wanting to try out detection using User-Agent Client Hints, then make
                sure that your browser
                <a href="https://developer.mozilla.org/en-US/docs/Web/API/User-Agent_Client_Hints_API#browser_compatibility">supports them</a>.
            </div>
        <?php } ?>

        <div id="evidence">
            <h3 class="c-eg-page__heading">Evidence used</h3>
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
                    foreach ($flowdata->evidence->getAll() as $key => $value)
                    {
                        if ($flowdata->pipeline->getElement("cloud")->getEvidenceKeyFilter()->filterEvidenceKey($key))
                        {
                            $output("<tr class='c-eg-table__row c-eg-table__row--used'>");
                        }
                        else
                        {
                            $output("<tr class='c-eg-table__row c-eg-table__row--present'>");
                        }
                        $output("<td class='c-eg-table__cell c-eg-table__cell--key'>".$key."</td>");
                        $output("<td class='c-eg-table__cell'>".$value."</td>");
                        $output("</tr>");
                    }
                ?>
                </tbody>
            </table>
        </div>

        <h3 class="c-eg-page__heading">Device Data</h3>
        <p class="c-eg-page__lead">
            The following values are determined by server-side device detection
            on the first request:
        </p>
        <table class="c-eg-table">
            <thead class="c-eg-table__head">
                <tr class="c-eg-table__row">
                    <th class="c-eg-table__cell">Key</th>
                    <th class="c-eg-table__cell">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Hardware Vendor:</td><td class="c-eg-table__cell"><?php $output(ExampleUtils::getHumanReadable($flowdata->device, "hardwarevendor")); ?></td></tr>
                <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Hardware Name:</td><td class="c-eg-table__cell"><?php $output(ExampleUtils::getHumanReadable($flowdata->device, "hardwarename")); ?></td></tr>
                <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Device Type:</td><td class="c-eg-table__cell"><?php $output(ExampleUtils::getHumanReadable($flowdata->device, "devicetype")); ?></td></tr>
                <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Platform Vendor:</td><td class="c-eg-table__cell"><?php $output(ExampleUtils::getHumanReadable($flowdata->device, "platformvendor")); ?></td></tr>
                <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Platform Name:</td><td class="c-eg-table__cell"><?php $output(ExampleUtils::getHumanReadable($flowdata->device, "platformname")); ?></td></tr>
                <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Platform Version:</td><td class="c-eg-table__cell"><?php $output(ExampleUtils::getHumanReadable($flowdata->device, "platformversion")); ?></td></tr>
                <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Browser Vendor:</td><td class="c-eg-table__cell"><?php $output(ExampleUtils::getHumanReadable($flowdata->device, "browservendor")); ?></td></tr>
                <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Browser Name:</td><td class="c-eg-table__cell"><?php $output(ExampleUtils::getHumanReadable($flowdata->device, "browsername")); ?></td></tr>
                <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Browser Version:</td><td class="c-eg-table__cell"><?php $output(ExampleUtils::getHumanReadable($flowdata->device, "browserversion")); ?></td></tr>
                <tr class="c-eg-table__row"><td class="c-eg-table__cell c-eg-table__cell--key">Screen width (pixels):</td><td class="c-eg-table__cell"><?php $output(ExampleUtils::getHumanReadable($flowdata->device, "screenpixelswidth")); ?></td></tr>
                <tr class="c-eg-table__row c-eg-table__row--alt"><td class="c-eg-table__cell c-eg-table__cell--key">Screen height (pixels):</td><td class="c-eg-table__cell"><?php $output(ExampleUtils::getHumanReadable($flowdata->device, "screenpixelsheight")); ?></td></tr>
            </tbody>
        </table>

        <h3 class="c-eg-page__heading">Client-side Evidence and Apple Models</h3>
        <p class="c-eg-page__lead">
            The information shown below is determined after a callback is made to the server with
            additional evidence that is gathered by JavaScript running on the client-side.
            The callback will also include any additional client hints headers that have been requested.
        </p>
        <p class="c-eg-page__lead">
            When an Apple device is used, the results from
            the first request above will show all Apple models because the server cannot tell the
            exact model of the device. In contrast, the results from the callback below will show
            a smaller set of possible models.
            This can be tested to some extent using most emulators, such as those in the
            'developer tools' menu in Google Chrome. However, these are not the identical to real
            devices so this can cause some unusual results. Using real devices will generally be more
            successful.
        </p>
        <p class="c-eg-page__lead">
            If you want to work with Apple Model or other client-side information, such as screen
            width/height on the server, then you will need to ensure that the 'enableCookies' setting
            is set to 'true' as in the pipeline construction for this example.
            This will cause the additional client-side evidence to be saved as cookies on the client.
            When a future page is requested, these cookies will be included with the request and the
            device detection API will include them when working out the details of the device.
            Refreshing this page can be used to show this in action. Any values that are unique to the
            client-side values below will appear in the evidence values used and server-side results
            after the refresh.
        </p>
    </div>
</div>

<!--
    This script is constructed by the fiftyone\pipeline\core package.
    It adds a JavaScript include for 51Degrees.core.js.
    The 51Degrees pipeline will dynamically generate JavaScript, which includes a
    JSON representation of the contents of flow data.
    i.e. The results from device detection.

    In addition, this JavaScript will look for properties that have a flag set indicating that
    they contain executable script snippets.
    These snippets will be executed and the values they obtain will be sent back to the server
    in order for it to perform the detection process again with the new information.
    This callback will also include any User-Agent Client Hints headers that have been requested
    with the 'Accept-CH' header. (assuming the browser is willing to send them)

    When the server responds, the JSON representation of the results will be updated with the
    new values and the 'complete' event will fire.
    The shared examples.js helper subscribes to this complete event and appends the
    refined client-side results into #content.
-->
<script>
    <?php
        $output($flowdata->javascriptbuilder->javascript);
    ?>
</script>

<script>
    <?php
        // The shared pattern-library helper is vendored next to this file as
        // examples.min.js. As with the stylesheet, there is no static route to
        // reference under the built-in PHP server, so we inline it here.
        $output(file_get_contents(__DIR__ . "/examples.min.js"));
    ?>
</script>

<script>
    window.onload = function () {
        fodExamples.bindDeviceCallback({ targetId: "content" });
    };
</script>
