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

namespace fiftyone\pipeline\devicedetection\examples\cloud\classes;

/**
 * Reads an environment variable by its name and, when that is not set or
 * is empty, by the deprecated name it replaced.
 */
class EnvironmentVariable
{
    // Written to the PHP error log once for each deprecated name a value
    // was read from. The deprecated name is given first, then the name
    // that replaces it.
    public const MESSAGE_DEPRECATED =
        'A value was read from the environment variable %s, which is ' .
        'deprecated. %s replaces it, so set that to the same value.';

    /** @var array<string, bool> The deprecated names already reported */
    private static $reported = [];

    /**
     * The variable's value, or an empty string when neither name holds
     * one. The deprecated name is read only when the name gives nothing.
     *
     * @param string $name The variable's name
     * @param string $deprecated The name it replaced
     * @param string $placeholder A value that counts as not set, which is
     * how a phpunit.xml file marks a value nobody has filled in
     */
    public static function read(
        string $name,
        string $deprecated,
        string $placeholder = ''
    ): string {
        $value = self::value($name, $placeholder);
        if ($value !== '') {
            return $value;
        }

        $value = self::value($deprecated, $placeholder);
        if ($value !== '' && !isset(self::$reported[$deprecated])) {
            self::$reported[$deprecated] = true;
            error_log(sprintf(self::MESSAGE_DEPRECATED, $deprecated, $name));
        }

        return $value;
    }

    private static function value(string $name, string $placeholder): string
    {
        $value = getenv($name);
        if ($value === false) {
            $value = $_ENV[$name] ?? '';
        }
        $value = (string) $value;

        return $value === $placeholder ? '' : $value;
    }
}
