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

namespace fiftyone\pipeline\devicedetection;

use fiftyone\pipeline\cloudrequestengine\CloudEngine;
use fiftyone\pipeline\core\AspectPropertyValue;
use fiftyone\pipeline\core\FlowData;
use fiftyone\pipeline\engines\AspectDataDictionary;

class HardwareProfileCloud extends CloudEngine
{
    /**
     * The suffix the cloud service adds to a property name to carry the
     * reason a property has no value, for example
     * 'hardwarevendornullreason'.
     */
    public const NULL_REASON_SUFFIX = 'nullreason';

    public string $dataKey = 'hardware';

    public function processInternal(FlowData $flowData): void
    {
        $cloudData = $flowData->get('cloud')->get('cloud');

        $cloudData = json_decode($cloudData, true);

        $hardware = $cloudData['hardware'] ?? null;

        if (!is_array($hardware)) {
            $hardware = [];
        }

        // Properties the resource key is not entitled to are returned by
        // the cloud service at the aspect level rather than inside each
        // profile, with a companion '<name>nullreason' saying why there is
        // no value. Collect those so the reason can travel with every
        // profile instead of being thrown away.
        $aspectValues = self::getAspectValues($hardware);

        $devices = [];

        foreach ($hardware['profiles'] ?? [] as $profile) {
            $device = [];

            foreach ($profile as $propertyKey => $propertyValue) {
                $device[$propertyKey] = new AspectPropertyValue(null, $propertyValue);
            }

            // Add the properties the service could not supply, carrying the
            // reason it gave, so a caller reading a profile gets an
            // explanation rather than nothing at all.
            foreach ($aspectValues as $propertyKey => $aspectValue) {
                if (!array_key_exists($propertyKey, $device)) {
                    $device[$propertyKey] = $aspectValue;
                }
            }

            $devices[] = $device;
        }

        // The aspect level values are exposed alongside the profiles so a
        // caller with no matching profiles can still read the reason.
        $contents = array_merge($aspectValues, ['profiles' => $devices]);

        $data = new AspectDataDictionary($this, $contents);

        $flowData->setElementData($data);
    }

    /**
     * Build the aspect level property values from the 'hardware' section of
     * the cloud response, pairing each null value with the reason the
     * service gave for it.
     *
     * @param array<string, mixed> $hardware
     * @return array<string, AspectPropertyValue>
     */
    private static function getAspectValues(array $hardware): array
    {
        $values = [];
        $suffixLength = strlen(self::NULL_REASON_SUFFIX);

        foreach ($hardware as $key => $value) {
            if ($key === 'profiles') {
                continue;
            }

            // The reason properties are paired with the property they
            // explain, so they are not values in their own right.
            if (substr($key, -$suffixLength) === self::NULL_REASON_SUFFIX) {
                continue;
            }

            if ($value === null) {
                $reason = $hardware[$key . self::NULL_REASON_SUFFIX] ?? null;
                $values[$key] = new AspectPropertyValue(
                    is_string($reason) ? $reason : null
                );
            } else {
                $values[$key] = new AspectPropertyValue(null, $value);
            }
        }

        return $values;
    }
}
