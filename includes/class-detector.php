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
     * Determine if user agent belongs to a bot/crawler.
     *
     * @param string $ua User agent string.
     * @return bool True if bot, false otherwise.
     */
    public function is_bot(string $ua): bool {
        $ua = strtolower($ua);
        
        // Common bot signatures
        $bot_patterns = [
            'bot',
            'crawler',
            'spider',
            'scraper',
            'googlebot',
            'bingbot',
            'slurp',          // Yahoo bot
            'duckduckbot',
            'baiduspider',
            'yandexbot',
            'facebookexternalhit',
            'twitterbot',
            'linkedinbot',
            'whatsapp',
            'telegrambot',
            'discordbot',
            'applebot',
            'msnbot',
            'ia_archiver',    // Internet Archive
            'wget',
            'curl',
            'python-requests',
            'scrapy',
            'node-fetch',
            'postman',
            'insomnia',
            'pingdom',
            'gtmetrix',
            'pagespeed',
            'lighthouse',
            'headlesschrome',
            'phantomjs',
            'selenium',
            'webdriver'
        ];
        
        foreach ($bot_patterns as $pattern) {
            if (strpos($ua, $pattern) !== false) {
                return true;
            }
        }
        
        // Check for empty or very short user agent (often bots)
        if (empty($ua) || strlen($ua) < 10) {
            return true;
        }
        
        return false;
    }

    /**
     * Detect browser name from user agent.
     *
     * @param string $ua User agent string.
     * @return string Browser name.
     */
    public function get_browser(string $ua): string {
        $ua = strtolower($ua);
        
        // Check specific browsers first (order matters!)
        if (strpos($ua, 'edg/') !== false || strpos($ua, 'edge') !== false) {
            return 'edge';
        }
        if (strpos($ua, 'opr/') !== false || strpos($ua, 'opera') !== false) {
            return 'opera';
        }
        if (strpos($ua, 'chrome') !== false) {
            return 'chrome';
        }
        if (strpos($ua, 'safari') !== false) {
            return 'safari';
        }
        if (strpos($ua, 'firefox') !== false) {
            return 'firefox';
        }
        
        return 'unknown';
    }    /**
     * Detect operating system from user agent.
     *
     * @param string $ua User agent string.
     * @return string Operating system name.
     */
    public function get_os(string $ua): string {
        $ua = strtolower($ua);
        
        // Check mobile OS first (they often contain desktop OS keywords)
        if (strpos($ua, 'android') !== false) {
            return 'android';
        }
        if (strpos($ua, 'ios') !== false || strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false) {
            return 'ios';
        }
        
        // Then check desktop OS
        if (strpos($ua, 'windows') !== false) {
            return 'windows';
        }
        if (strpos($ua, 'mac os') !== false || strpos($ua, 'macos') !== false) {
            return 'mac';
        }
        if (strpos($ua, 'linux') !== false) {
            return 'linux';
        }
        
        return 'unknown';
    }
}
