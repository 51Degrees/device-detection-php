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

namespace Examples\Cloud;

use Exception;
use fiftyone\pipeline\core\Logger;

class ExampleUtils
{
    // The default environment variable used to get the resource key 
    // to use when running examples.
    const RESOURCE_KEY_ENV_VAR = "resource_key";

    const ENDPOINT_ENV_VAR = "cloud_endpoint";
    
    public static function getResourceKey()
    {
        return ExampleUtils::getEnvVariable(ExampleUtils::RESOURCE_KEY_ENV_VAR);
    }

    public static function getCloudEndpoint()
    {
        return ExampleUtils::getEnvVariable(ExampleUtils::ENDPOINT_ENV_VAR);
    }
    
    private static function getEnvVariable($name)
    {
        $env = getenv();
        if (isset($env[$name]))
        {
            return $env[$name];
        }
        else
        {
            return "";
        }
    }
    
    public static function getResourceKeyFromConfig($config)
    {
        $key = "";
        foreach ($config["PipelineOptions"]["Elements"] as $element)
        {
            if ($element["BuilderName"] === "fiftyone\\pipeline\\cloudrequestengine\\CloudRequestEngine")
            {
                $key = $element["BuildParameters"]["resourceKey"];
            }
        }
        return $key;
    }

    public static function setResourceKeyInConfig(&$config, $key)
    {
        foreach ($config["PipelineOptions"]["Elements"] as &$element)
        {
            if ($element["BuilderName"] === "fiftyone\\pipeline\\cloudrequestengine\\CloudRequestEngine")
            {
                $element["BuildParameters"]["resourceKey"] = $key;
            }
        }
    }

    public static function output($message)
    {
        if (php_sapi_name() == "cli")
        {
            echo $message."\n";
        }
        else
        {
            echo "<pre>$message\n</pre>";
        }
    }

    public static function getHumanReadable($device, $name)
    {
        try
        {
            if (is_a($device, "fiftyone\\pipeline\\engines\\AspectDataDictionary"))
            {
                $value = $device->$name;
            }
            else
            {
                $value = $device[$name];
            }
            if ($value->hasValue)
            {
                if (is_array($value->value))
                {
                    return implode(", ", $value->value);
                }
                else
                {
                    return $value->value;
                }
            }
            else
            {
                return "Unknown (".$value->noValueMessage.")";
            }
        }
        catch (Exception $e)
        {
            return "Property not found using the current resource key.";
        }
    }

    public static function containsAcceptCh()
    {
        foreach (headers_list() as $header)
        {
            $parts = explode(": ", $header);
            if (strtolower($parts[0]) === "accept-ch")
            {
                return true;
            }
        }
        return false;
    }
}
?>