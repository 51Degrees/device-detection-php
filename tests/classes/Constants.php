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

namespace fiftyone\pipeline\devicedetection\tests\classes;

class Constants
{
    public const RESOURCE_ENV_VAR = 'resource_key';
    public const PLATFORM_ENV_VAR = 'AcceptChPlatformKey';
    public const HARDWARE_ENV_VAR = 'AcceptChHardwareKey';
    public const BROWSER_ENV_VAR = 'AcceptChBrowserKey';
    public const NO_ACCEPTCH_ENV_VAR = 'AcceptChNoneKey';

    public const CHROME_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
            . 'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36';
    public const EDGE_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) '
                    . 'Chrome/95.0.4638.69 Safari/537.36 Edg/95.0.1020.44';
    public const FIREFOX_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64;'
            . ' rv:94.0) Gecko/20100101 Firefox/94.0';
    public const SAFARI_UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_1 '
            . 'like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 '
            . 'Mobile/15E148 Safari/604.1';
    public const CURL_UA = 'curl/7.80.0';

    // Lists of key headers that are expected to be requested in Accept-CH if the browser
    // supports UACH.
    public const SUPER_ACCEPT_CH = ['Sec-CH-UA', 'Sec-CH-UA-Model', 'Sec-CH-UA-Platform'];
    public const PLATFORM_ACCEPT_CH = ['SEC-CH-UA-Platform'];
    public const HARDWARE_ACCEPT_CH = ['SEC-CH-UA-Model'];
    public const BROWSER_ACCEPT_CH = ['SEC-CH-UA'];
    public const EMPTY_ACCEPT_CH = [];

    public const URL = 'http://localhost:3000/';
    public const UA_HEADER = 'User-Agent: ';
}
