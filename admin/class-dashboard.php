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
        // Get analytics data for dashboard
        $total_visits = $this->analytics->get_total_visits();
        $unique_visitors = $this->analytics->get_unique_visitors();
        $daily_data = $this->analytics->get_daily_visits(7);
        $top_pages = $this->analytics->get_top_pages(10);
        $device_stats = $this->analytics->get_device_stats(7);
        $browser_stats = $this->analytics->get_browser_stats(7);
        $recent_visitors = $this->analytics->get_recent_visitors(10);
        
        // Calculate additional metrics
        $page_views = array_sum(array_column($daily_data, 'visits'));
        $avg_session_duration = $this->analytics->get_session_duration();
        $online_visitors = $this->get_online_visitors();
        $bounce_rate = $this->calculate_bounce_rate();
        
        // Prepare chart data
        $chart_data = [
            'visits' => [
                'labels' => array_column($daily_data, 'date'),
                'data' => array_column($daily_data, 'visits')
            ],
            'devices' => [
                'data' => array_values($device_stats)
            ]
        ];
          // Set template variables
        $template_vars = [
            'total_visits' => $total_visits,
            'unique_visitors' => $unique_visitors,
            'page_views' => $page_views,
            'avg_session_duration' => $this->format_duration((int) $avg_session_duration),
            'bounce_rate' => $bounce_rate,
            'online_visitors' => $online_visitors,
            'chart_data' => $chart_data,
            'recent_visitors' => $recent_visitors,
            'device_stats' => $device_stats,
            'browser_stats' => $browser_stats,
            'top_pages' => $top_pages
        ];
        
        // Include header
        include WPVN_PLUGIN_PATH . 'admin/templates/header.php';
        
        // Include dashboard template
        extract($template_vars);
        include WPVN_PLUGIN_PATH . 'admin/templates/dashboard.php';
    }

    /**
     * Get number of visitors online in the last 5 minutes
     */
    private function get_online_visitors(): int {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'visitor_logs';
        $five_minutes_ago = date('Y-m-d H:i:s', time() - 300);
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT ip_address) FROM $table_name WHERE timestamp >= %s",
            $five_minutes_ago
        ));
        
        return (int) $count;
    }

    /**
     * Calculate bounce rate (simplified calculation)
     */
    private function calculate_bounce_rate(): float {
        // For now, return a placeholder value
        // This would need more complex session tracking
        return 65.2;
    }

    /**
     * Format duration in seconds to human readable format
     */
    private function format_duration(int $seconds): string {
        $minutes = floor($seconds / 60);
        $remaining_seconds = $seconds % 60;
        
        if ($minutes > 0) {
            return sprintf('%d:%02d', $minutes, $remaining_seconds);
        }
        
        return sprintf('0:%02d', $seconds);
    }
}
