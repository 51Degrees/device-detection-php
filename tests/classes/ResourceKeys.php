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

declare(strict_types=1);

namespace fiftyone\pipeline\devicedetection\tests\classes;

/**
 * Finds the resource key a test needs in the environment.
 *
 * The aligned name is tried first, then the name this repository used
 * before the convention was adopted, so an existing setup keeps working.
 * When neither is set the message names the variable that was wanted,
 * rather than leaving the reader to guess.
 */
class ResourceKeys
{
    // Placeholder written into phpunit.xml, which stands for 'not set'.
    public const PLACEHOLDER = '!!YOUR_RESOURCE_KEY!!';

    /**
     * Get the value of a resource key variable, or null when it is not set.
     *
     * @param string $name One of the Constants::*_ENV_VAR names
     * @return null|string
     */
    public static function find(string $name): ?string
    {
        foreach (self::namesFor($name) as $candidate) {
            $value = getenv($candidate);

            if ($value === false && isset($_ENV[$candidate])) {
                $value = $_ENV[$candidate];
            }

            if (!empty($value) && $value !== self::PLACEHOLDER) {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * The message shown when a resource key is missing, naming the variable
     * the test was looking for.
     */
    public static function missingMessage(string $name): string
    {
        $names = self::namesFor($name);
        $legacy = count($names) > 1 ? $names[1] : null;

        $message = "No resource key found. Set the environment variable " .
            "'" . $names[0] . "'";

        if ($legacy !== null) {
            $message .= " (the older name '" . $legacy . "' is still read)";
        }

        return $message . '. Create a resource key for free at ' .
            'https://configure.51degrees.com?utm_source=code&utm_medium=example&utm_campaign=device-detection-php&utm_content=tests-classes-resourcekeys.php&utm_term=resource-key-required';
    }

    /**
     * The variable names to try, aligned name first.
     *
     * @return array<int, string>
     */
    private static function namesFor(string $name): array
    {
        $names = [$name];

        if (isset(Constants::LEGACY_ENV_VARS[$name])) {
            $names[] = Constants::LEGACY_ENV_VARS[$name];
        }

        return $names;
    }
}
