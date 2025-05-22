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

declare(strict_types=1);

namespace fiftyone\pipeline\devicedetection;

use fiftyone\pipeline\cloudrequestengine\CloudRequestEngine;
use fiftyone\pipeline\core\PipelineBuilder;

/**
 * Extension of pipelineBuilder class that allows for the quick generation of a device detection pipeline.
 */
class DeviceDetectionPipelineBuilder extends PipelineBuilder
{
    /**
     * @var array<string>
     */
    public array $restrictedProperties;

    /**
     * @var \fiftyone\pipeline\engines\DataKeyedCache
     */
    public $cache;
    public string $resourceKey;
    public string $licenseKey;

    /**
     * settings.resourceKey
     * settings.cloudEndPoint custom endpoint for the cloud service
     * settings.restrictedProperties (list of properties to restrict the results to)
     * settings.cloudRequestOrigin value to use for the
     * Origin header when sending requests to cloud.
     *
     * @param array{
     *     resourceKey: string,
     *     cloudEndPoint?: string,
     *     cloudRequestOrigin?: string,
     *     restrictedProperties?: array<string>,
     *     javascriptBuilderSettings?: array<string, mixed>
     * } $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);

        // Translate the cloud options with different names
        if (array_key_exists('cloudEndPoint', $settings)) {
            $settings['baseURL'] = $settings['cloudEndPoint'];
        }

        // Add CloudRequestEngine
        $cloud = new CloudRequestEngine($settings);

        $flowElements = [];

        $flowElements[] = $cloud;

        $deviceDetection = new DeviceDetectionCloud();

        if (isset($settings['restrictedProperties'])) {
            $deviceDetection->setRestrictedProperties($settings['restrictedProperties']);
        }

        $flowElements[] = $deviceDetection;

        // Add any extra flowElements
        $flowElements = array_merge($flowElements, $this->flowElements);

        $this->flowElements = $flowElements;
    }
}
