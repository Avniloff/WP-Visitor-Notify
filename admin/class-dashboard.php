<?php
declare(strict_types=1);

namespace WPVN\Admin;

use WPVN\Analytics;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard page controller.
 *
 * @since 1.0.0
 */
class Dashboard {
    private Analytics $analytics;

    public function __construct(Analytics $analytics) {
        $this->analytics = $analytics;
    }    public function render(): void {
        // Get all analytics data for dashboard
        $daily_data = $this->analytics->get_daily_visits(7);
        $top_pages = $this->analytics->get_top_pages(10);
        $device_stats = $this->analytics->get_device_stats(7);
        $browser_stats = $this->analytics->get_browser_stats(7);
        $os_stats = $this->analytics->get_os_stats(7);
        $hourly_stats = $this->analytics->get_hourly_stats(7);
        $recent_visitors = $this->analytics->get_recent_visitors(10);
        
        // Template will be included here when created
        // For now, return empty - no HTML output
    }
}
