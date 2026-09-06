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

namespace fiftyone\pipeline\devicedetection\examples\cloud\classes;

use fiftyone\pipeline\core\AspectPropertyValue;

class ExampleUtils
{
    // The environment variable used to get the resource key to use when
    // running examples. This follows the 51Degrees convention that every
    // resource key variable starts with '_51DEGREES_RESOURCE_KEY'.
    public const RESOURCE_KEY_ENV_VAR = '_51DEGREES_RESOURCE_KEY';

    // The environment variable this repository used before the convention
    // above was adopted. It is still read, so an existing setup keeps
    // working, but new setups should use RESOURCE_KEY_ENV_VAR.
    public const LEGACY_RESOURCE_KEY_ENV_VAR = 'resource_key';

    // The query string parameter the web examples accept a resource key on.
    // This is part of the URL rather than the environment, so it keeps its
    // own name.
    public const RESOURCE_KEY_QUERY_PARAM = 'resource_key';

    public const ENDPOINT_ENV_VAR = 'cloud_endpoint';

    public static function getResourceKeyFromEnv()
    {
        $key = self::getEnvVariable(self::RESOURCE_KEY_ENV_VAR);

        if (empty($key)) {
            $key = self::getEnvVariable(self::LEGACY_RESOURCE_KEY_ENV_VAR);
        }

        return $key;
    }

    public static function getCloudEndpoint()
    {
        return self::getEnvVariable(self::ENDPOINT_ENV_VAR);
    }

    public static function getResourceKeyFromCliArgs($argv)
    {
        if (is_array($argv) && count($argv) > 0) {
            return $argv[0];
        }

        return null;
    }

    public static function getResourceKeyFromQueryParameter()
    {
        return $_GET[self::RESOURCE_KEY_QUERY_PARAM] ?? null;
    }

    public static function getResourceKeyFromConfig($config)
    {
        $key = null;

        foreach ($config['PipelineOptions']['Elements'] as $element) {
            if (
                $element['BuilderName'] === 'fiftyone\\pipeline\\cloudrequestengine\\CloudRequestEngine' &&
                !empty($element['BuildParameters']['resourceKey']) &&
                strpos($element['BuildParameters']['resourceKey'], '!!') !== 0
            ) {
                $key = $element['BuildParameters']['resourceKey'];
                break;
            }
        }

        return $key;
    }

    public static function setResourceKeyInConfig(&$config, $key)
    {
        foreach ($config['PipelineOptions']['Elements'] as &$element) {
            if ($element['BuilderName'] === 'fiftyone\\pipeline\\cloudrequestengine\\CloudRequestEngine') {
                $element['BuildParameters']['resourceKey'] = $key;
            }
        }
    }

    public static function output($message)
    {
        if (php_sapi_name() == 'cli') {
            echo $message . "\n";
        } else {
            echo "<pre>{$message}\n</pre>";
        }
    }

    /**
     * Format a property value for display.
     *
     * A property can be unavailable for three reasons, and each one reads
     * differently so the person running the example can tell them apart.
     * The property may have a value, it may have no value with the cloud
     * service giving a reason (most often that the resource key is not
     * entitled to it), or it may not be in the results at all.
     *
     * @param mixed $device An AspectDataDictionary or an array of values
     * @param string $name The property name
     * @return string
     */
    public static function getHumanReadable($device, $name)
    {
        $value = self::getPropertyValue($device, $name);

        if ($value === null) {
            return "Unknown (the property '" . $name . "' is not in the " .
                'results, so the current resource key does not include it)';
        }

        if ($value->hasValue) {
            if (is_array($value->value)) {
                return implode(', ', $value->value);
            }

            return $value->value;
        }

        $reason = $value->noValueMessage;

        if (empty($reason)) {
            $reason = "the cloud service returned no value for '" . $name .
                "' and gave no reason";
        }

        return 'Unknown (' . $reason . ')';
    }

    public static function containsAcceptCh()
    {
        foreach (headers_list() as $header) {
            $parts = explode(': ', $header);
            if (strtolower($parts[0]) === 'accept-ch') {
                return true;
            }
        }

        return false;
    }

    public static function logErrorAndExit($logger, $message)
    {
        $logger->log('error', $message);

        echo $message . PHP_EOL;

        exit(1);
    }

    /**
     * Read the list of hardware profiles from the 'hardware' element data.
     *
     * An empty list is returned when the resource key has no access to the
     * hardware aspect at all, because reading a property that is not there
     * raises an exception rather than returning an empty list.
     *
     * @param mixed $hardware The 'hardware' element data
     * @return array<int, mixed>
     */
    public static function getProfiles($hardware)
    {
        try {
            $profiles = $hardware->profiles;
        } catch (\Exception $e) {
            return [];
        }

        return is_array($profiles) ? $profiles : [];
    }

    /**
     * The line printed when a lookup returned no device profiles at all.
     *
     * @return string
     */
    public static function getNoProfilesMessage()
    {
        return "\tNo device profiles were returned. The current resource " .
            'key does not include the hardware properties this example ' .
            'needs. See https://51degrees.com/pricing?utm_source=code&utm_medium=example&utm_campaign=device-detection-php&utm_content=examples-cloud-classes-exampleutils.php&utm_term=no-profiles';
    }

    /**
     * Read a single AspectPropertyValue out of an element data instance or
     * an array of values, returning null when it is not present rather than
     * raising a warning.
     *
     * @param mixed $device An AspectDataDictionary or an array of values
     * @param string $name The property name
     * @return null|AspectPropertyValue
     */
    private static function getPropertyValue($device, $name)
    {
        $value = null;

        if (is_array($device)) {
            $value = $device[$name] ?? null;
        } elseif (is_object($device)) {
            try {
                $value = $device->{$name};
            } catch (\Exception $e) {
                return null;
            }
        }

        return $value instanceof AspectPropertyValue ? $value : null;
    }

    private static function getEnvVariable($name)
    {
        $env = getenv();

        return $env[$name] ?? null;
    }
}
