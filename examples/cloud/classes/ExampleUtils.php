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

class ExampleUtils
{
    // The default environment variable used to get the resource key
    // to use when running examples.
    public const RESOURCE_KEY_ENV_VAR = 'resource_key';

    public const ENDPOINT_ENV_VAR = 'cloud_endpoint';

    public static function getResourceKeyFromEnv()
    {
        return self::getEnvVariable(self::RESOURCE_KEY_ENV_VAR);
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
        return $_GET[self::RESOURCE_KEY_ENV_VAR] ?? null;
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

    public static function getHumanReadable($device, $name)
    {
        try {
            if (is_a($device, 'fiftyone\\pipeline\\engines\\AspectDataDictionary')) {
                $value = $device->{$name};
            } else {
                $value = $device[$name];
            }
            if ($value->hasValue) {
                if (is_array($value->value)) {
                    return implode(', ', $value->value);
                }

                return $value->value;
            }

            return 'Unknown (' . $value->noValueMessage . ')';
        } catch (\Exception $e) {
            return 'Property not found using the current resource key.';
        }
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

    private static function getEnvVariable($name)
    {
        $env = getenv();

        return $env[$name] ?? null;
    }
}
