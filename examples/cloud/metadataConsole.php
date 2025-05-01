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
 * This example is available in full on [GitHub](https://github.com/51Degrees/device-detection-php/blob/main/examples/cloud/metadataConsole.php). 
 * 
 * @include{doc} example-require-resourcekey.txt
 * 
 * Required Composer Dependencies:
 * - 51degrees/fiftyone.devicedetection
 */

require_once(__DIR__ . '/../../vendor/autoload.php');

use fiftyone\pipeline\core\Logger;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\ExampleUtils;
use fiftyone\pipeline\devicedetection\examples\cloud\classes\MetadataConsole;

// Only declare and call the main function if this is being run directly.
// This prevents main from being run where examples are run as part of
// PHPUnit tests.
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
{
    function main($argv)
    {
        // Configure a logger to output to the console
        $logger = new Logger("info");

        // Get the resource key from the environment variable
        $resourceKey = ExampleUtils::getResourceKeyFromEnv();

        // Otherwise, get the resource key from command line args
        if (empty($resourceKey)) {
            $resourceKey = ExampleUtils::getResourceKeyFromCliArgs($argv);
        }

        if (empty($resourceKey)) {
            $message = "No resource key specified in CLI args or environment variable '" .
                ExampleUtils::RESOURCE_KEY_ENV_VAR . "'." . PHP_EOL .
                "The 51Degrees cloud service is accessed using a 'ResourceKey'. " .
                "For more detail see " .
                "http://51degrees.com/documentation/4.3/_info__resource_keys.html. " .
                "A resource key with the properties required by this " .
                "example can be created for free at " .
                "https://configure.51degrees.com/1QWJwHxl. " .
                "Once complete, populate the environment variable " .
                "mentioned at the start of this message with the key.";

            ExampleUtils::logErrorAndExit($logger, $message);
        }

        (new MetaDataConsole())->run($resourceKey, $logger, [ExampleUtils::class, 'output']);
    }

    main(isset($argv) ? array_slice($argv, 1) : null);
}