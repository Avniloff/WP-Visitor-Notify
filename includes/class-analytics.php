<?php
declare(strict_types=1);

namespace WPVN;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Provides aggregated visitor statistics.
 *
 * @since 1.0.0
 */
class Analytics {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Get daily visit counts for the last N days.
     *
     * @param int $days Number of days.
     * @return array<int, array<string, string>>
     */
    public function get_daily_visits(int $days = 7): array {
        $table = $this->db->get_tables()['page_views'];
        $sql = $this->db->get_wpdb()->prepare(
            "SELECT DATE(viewed_at) as date, COUNT(DISTINCT session_id) as visits FROM {$table} WHERE viewed_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY) GROUP BY DATE(viewed_at) ORDER BY DATE(viewed_at) ASC",
            $days
        );
        return $this->db->get_wpdb()->get_results($sql, ARRAY_A) ?: [];
    }

    /**
     * Get weekly visit counts for the last N weeks.
     *
     * @param int $weeks Number of weeks.
     * @return array<int, array<string, string>>
     */
    public function get_weekly_visits(int $weeks = 4): array {
        $table = $this->db->get_tables()['page_views'];
        $sql = $this->db->get_wpdb()->prepare(
            "SELECT YEARWEEK(viewed_at,1) as week, COUNT(DISTINCT session_id) as visits FROM {$table} WHERE viewed_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d WEEK) GROUP BY YEARWEEK(viewed_at,1) ORDER BY YEARWEEK(viewed_at,1) ASC",
            $weeks
        );
        return $this->db->get_wpdb()->get_results($sql, ARRAY_A) ?: [];
    }

    /**
     * Get monthly visit counts for the last N months.
     *
     * @param int $months Number of months.
     * @return array<int, array<string, string>>
     */
    public function get_monthly_visits(int $months = 6): array {
        $table = $this->db->get_tables()['page_views'];
        $sql = $this->db->get_wpdb()->prepare(
            "SELECT DATE_FORMAT(viewed_at, '%Y-%m') as month, COUNT(DISTINCT session_id) as visits FROM {$table} WHERE viewed_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d MONTH) GROUP BY DATE_FORMAT(viewed_at, '%Y-%m') ORDER BY DATE_FORMAT(viewed_at, '%Y-%m') ASC",
            $months
        );
        return $this->db->get_wpdb()->get_results($sql, ARRAY_A) ?: [];
    }

    /**
     * Get top pages by view count.
     *
     * @param int $limit Number of pages to return.
     * @return array<int, array<string, string>>
     */
    public function get_top_pages(int $limit = 10): array {
        $table = $this->db->get_tables()['page_views'];
        $sql = $this->db->get_wpdb()->prepare(
            "SELECT url, title, COUNT(*) as views FROM {$table} GROUP BY url, title ORDER BY views DESC LIMIT %d",
            $limit
        );
        return $this->db->get_wpdb()->get_results($sql, ARRAY_A) ?: [];
    }

    /**
     * Get device type statistics
     *
     * @param int $days Number of days to analyze
     * @return array<string, int> Device types and their visit counts
     */
    public function get_device_stats(int $days = 7): array {
        $sessions_table = $this->db->get_tables()['sessions'];
        $sql = $this->db->get_wpdb()->prepare(
            "SELECT device_type, COUNT(DISTINCT id) as count 
             FROM {$sessions_table} 
             WHERE created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY) 
             GROUP BY device_type 
             ORDER BY count DESC",
            $days
        );
        return $this->db->get_wpdb()->get_results($sql, ARRAY_A) ?: [];
    }

    /**
     * Get browser statistics
     *
     * @param int $days Number of days to analyze
     * @return array<string, int> Browsers and their visit counts
     */
    public function get_browser_stats(int $days = 7): array {
        $sessions_table = $this->db->get_tables()['sessions'];
        $sql = $this->db->get_wpdb()->prepare(
            "SELECT browser, COUNT(DISTINCT id) as count 
             FROM {$sessions_table} 
             WHERE created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY) 
             GROUP BY browser 
             ORDER BY count DESC",
            $days
        );
        return $this->db->get_wpdb()->get_results($sql, ARRAY_A) ?: [];
    }

    /**
     * Get operating system statistics
     *
     * @param int $days Number of days to analyze
     * @return array<string, int> Operating systems and their visit counts
     */
    public function get_os_stats(int $days = 7): array {
        $sessions_table = $this->db->get_tables()['sessions'];
        $sql = $this->db->get_wpdb()->prepare(
            "SELECT os, COUNT(DISTINCT id) as count 
             FROM {$sessions_table} 
             WHERE created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY) 
             GROUP BY os 
             ORDER BY count DESC",
            $days
        );
        return $this->db->get_wpdb()->get_results($sql, ARRAY_A) ?: [];
    }

    /**
     * Get hourly visit distribution
     *
     * @param int $days Number of days to analyze
     * @return array<int, int> Hours (0-23) and their visit counts
     */
    public function get_hourly_stats(int $days = 7): array {
        $views_table = $this->db->get_tables()['page_views'];
        $sql = $this->db->get_wpdb()->prepare(
            "SELECT HOUR(viewed_at) as hour, COUNT(*) as count 
             FROM {$views_table} 
             WHERE viewed_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY) 
             GROUP BY HOUR(viewed_at) 
             ORDER BY hour",
            $days
        );
        return $this->db->get_wpdb()->get_results($sql, ARRAY_A) ?: [];
    }

    /**
     * Get recent visitors
     *
     * @param int $limit Number of recent visitors to return
     * @return array Recent visitor sessions with their details
     */
    public function get_recent_visitors(int $limit = 10): array {
        $sessions_table = $this->db->get_tables()['sessions'];
        $views_table = $this->db->get_tables()['page_views'];
        
        $sql = $this->db->get_wpdb()->prepare(
            "SELECT s.*, v.url as last_url, v.title as last_page_title
             FROM {$sessions_table} s
             LEFT JOIN {$views_table} v ON v.session_id = s.id
             WHERE v.id IN (
                SELECT MAX(id) 
                FROM {$views_table} 
                GROUP BY session_id
             )
             ORDER BY s.last_activity DESC
             LIMIT %d",
            $limit
        );
        return $this->db->get_wpdb()->get_results($sql, ARRAY_A) ?: [];
    }
}
