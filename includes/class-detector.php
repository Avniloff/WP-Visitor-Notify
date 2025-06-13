<?php
declare(strict_types=1);

namespace WPVN;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Simple device and browser detector.
 * Provides methods to parse a user agent string and
 * return basic device, browser and operating system information.
 *
 * @since 1.0.0
 */
class Detector {
    /**
     * Detect device type based on user agent.
     *
     * @param string $ua User agent string.
     * @return string Device type.
     */
    public function get_device_type(string $ua): string {
        $ua = strtolower($ua);
        if (stripos($ua, 'mobile') !== false) {
            return 'mobile';
        }
        if (stripos($ua, 'tablet') !== false || stripos($ua, 'ipad') !== false) {
            return 'tablet';
        }
        return 'desktop';
    }

    /**
     * Detect browser name from user agent.
     *
     * @param string $ua User agent string.
     * @return string Browser name.
     */
    public function get_browser(string $ua): string {
        $browsers = ['chrome', 'safari', 'firefox', 'edge', 'opera'];
        $ua = strtolower($ua);
        foreach ($browsers as $browser) {
            if (strpos($ua, $browser) !== false) {
                return $browser;
            }
        }
        return 'unknown';
    }

    /**
     * Detect operating system from user agent.
     *
     * @param string $ua User agent string.
     * @return string Operating system name.
     */
    public function get_os(string $ua): string {
        $ua = strtolower($ua);
        if (strpos($ua, 'windows') !== false) {
            return 'windows';
        }
        if (strpos($ua, 'mac os') !== false || strpos($ua, 'macos') !== false) {
            return 'mac';
        }
        if (strpos($ua, 'linux') !== false) {
            return 'linux';
        }
        if (strpos($ua, 'android') !== false) {
            return 'android';
        }
        if (strpos($ua, 'ios') !== false || strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false) {
            return 'ios';
        }
        return 'unknown';
    }
}
