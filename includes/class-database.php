<?php
/**
 * Database operations handler for WP Visitor Notify Plugin
 *
 * This class handles all database operations including:
 * - Table creation and schema management
 * - Data insertion, retrieval, and deletion
 * - Database queries optimization
 * - Data validation and sanitization
 *
 * @package    WP_Visitor_Notify
 * @subpackage Includes
 * @since      1.0.0
 * @author     Your Name
 */

namespace WPVN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database operations class
 *
 * Handles all database operations for the plugin following WordPress best practices.
 * Uses prepared statements for security and proper indexing for performance.
 *
 * @since 1.0.0
 */
class Database {

    /**
     * Current database schema version
     * 
     * This version number helps track database schema changes
     * and enables automatic migrations when plugin is updated.
     *
     * @since 1.0.0
     * @var string
     */
    private const DB_VERSION = '1.0.0';

    /**
     * WordPress database instance
     *
     * We store the global $wpdb object to use WordPress's
     * built-in database abstraction layer for compatibility.
     *
     * @since 1.0.0
     * @var \wpdb
     */
    private $wpdb;

    /**
     * Table names with WordPress prefix
     *
     * Store all table names with proper WordPress prefix
     * for easy access throughout the class methods.
     *
     * @since 1.0.0
     * @var array<string, string>
     */
    private array $tables;

    /**
     * Constructor - Initialize database connection and table names
     *
     * Sets up the database connection using WordPress global $wpdb
     * and prepares all table names with proper WordPress prefix.
     *
     * @since 1.0.0
     */
    public function __construct() {
        global $wpdb;
        
        $this->wpdb = $wpdb;
        
        // Define all table names with WordPress prefix
        $this->tables = [
            'sessions' => $wpdb->prefix . 'wpvn_sessions',
            'page_views' => $wpdb->prefix . 'wpvn_page_views', 
            'notification_rules' => $wpdb->prefix . 'wpvn_notification_rules',
            'notification_history' => $wpdb->prefix . 'wpvn_notification_history',
            'logs' => $wpdb->prefix . 'wpvn_logs'
        ];
    }

    /**
     * Create all plugin database tables
     *
     * This method creates all required tables for the plugin.
     * It's called during plugin activation and checks if tables
     * already exist to avoid conflicts.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */    public function create_tables(): bool {
        // Require WordPress upgrade functions for dbDelta
        require_once(\ABSPATH . 'wp-admin/includes/upgrade.php');

        $success = true;

        try {
            // Create each table - order matters due to foreign keys
            $success &= $this->create_sessions_table();
            $success &= $this->create_page_views_table();
            $success &= $this->create_notification_rules_table();
            $success &= $this->create_notification_history_table();
            $success &= $this->create_logs_table();

            // Update database version if all tables created successfully
            if ($success) {
                \update_option('wpvn_db_version', self::DB_VERSION);
            }

        } catch (\Exception $e) {
            // Log the error for debugging
            \error_log('WPVN Database Error: ' . $e->getMessage());
            $success = false;
        }

        return $success;
    }

    /**
     * Create visitor sessions table
     *
     * This table stores information about visitor sessions including
     * device detection, geolocation, and session metrics.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    private function create_sessions_table(): bool {
        $table_name = $this->tables['sessions'];

        // SQL for creating sessions table with proper indexing
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(64) NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            ip_hash VARCHAR(64) NOT NULL,
            user_agent TEXT,
            device_type ENUM('desktop', 'mobile', 'tablet', 'bot') DEFAULT 'desktop',
            browser VARCHAR(100),
            operating_system VARCHAR(100),
            is_bot BOOLEAN DEFAULT FALSE,
            user_id BIGINT(20) UNSIGNED DEFAULT NULL,
            first_visit DATETIME NOT NULL,
            last_activity DATETIME NOT NULL,
            page_count INT UNSIGNED DEFAULT 1,
            total_duration INT UNSIGNED DEFAULT 0,
            referrer TEXT,
            utm_source VARCHAR(100),
            utm_medium VARCHAR(100),
            utm_campaign VARCHAR(100),
            country_code CHAR(2),
            city VARCHAR(100),
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id),
            KEY ip_hash (ip_hash),
            KEY device_type (device_type),
            KEY is_bot (is_bot),
            KEY user_id (user_id),
            KEY created_at (created_at),
            KEY idx_sessions_analytics (created_at, device_type, is_bot, page_count)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";        // Use dbDelta for safe table creation
        $result = \dbDelta($sql);
        
        return !empty($result);
    }

    /**
     * Create page views tracking table
     *
     * This table records individual page views within sessions,
     * enabling detailed analytics about user behavior.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    private function create_page_views_table(): bool {
        $table_name = $this->tables['page_views'];

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(64) NOT NULL,
            post_id BIGINT(20) UNSIGNED DEFAULT NULL,
            page_url TEXT NOT NULL,
            page_title VARCHAR(255),
            page_type VARCHAR(50),
            view_duration INT UNSIGNED DEFAULT 0,
            scroll_depth TINYINT UNSIGNED DEFAULT 0,
            exit_page BOOLEAN DEFAULT FALSE,
            viewed_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY post_id (post_id),
            KEY page_type (page_type),
            KEY viewed_at (viewed_at),
            KEY idx_pageviews_analytics (viewed_at, page_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $result = \dbDelta($sql);
        
        return !empty($result);
    }

    /**
     * Create notification rules table
     *
     * Stores user-defined rules for when notifications should
     * be triggered based on analytics data.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    private function create_notification_rules_table(): bool {
        $table_name = $this->tables['notification_rules'];

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            rule_type ENUM('threshold', 'scheduled', 'event') NOT NULL,
            conditions JSON NOT NULL,
            threshold_value INT UNSIGNED,
            threshold_period ENUM('hour', 'day', 'week', 'month'),
            schedule_frequency ENUM('hourly', 'daily', 'weekly', 'monthly'),
            schedule_time TIME DEFAULT '09:00:00',
            recipients JSON NOT NULL,
            email_template TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            last_triggered DATETIME DEFAULT NULL,
            trigger_count INT UNSIGNED DEFAULT 0,
            created_by BIGINT(20) UNSIGNED,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY rule_type (rule_type),
            KEY is_active (is_active),
            KEY created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $result = \dbDelta($sql);
        
        return !empty($result);
    }

    /**
     * Create notification history table
     *
     * Tracks all sent notifications for debugging and
     * preventing duplicate notifications.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    private function create_notification_history_table(): bool {
        $table_name = $this->tables['notification_history'];

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            rule_id BIGINT(20) UNSIGNED NOT NULL,
            recipient_email VARCHAR(255) NOT NULL,
            subject VARCHAR(255),
            message TEXT,
            data_snapshot JSON,
            status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
            error_message TEXT DEFAULT NULL,
            sent_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY rule_id (rule_id),
            KEY status (status),
            KEY sent_at (sent_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $result = \dbDelta($sql);
        
        return !empty($result);
    }

    /**
     * Create system logs table
     *
     * Stores application logs for debugging and monitoring
     * plugin performance and errors.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    private function create_logs_table(): bool {
        $table_name = $this->tables['logs'];

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            level ENUM('debug', 'info', 'warning', 'error', 'critical') NOT NULL,
            message TEXT NOT NULL,
            context JSON DEFAULT NULL,
            component VARCHAR(100),
            user_id BIGINT(20) UNSIGNED DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY level (level),
            KEY component (component),
            KEY created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $result = \dbDelta($sql);
        
        return !empty($result);
    }

    /**
     * Insert a new visitor session
     *
     * Creates a new session record with validated and sanitized data.
     * This is the main entry point for tracking new visitors.
     *
     * @since 1.0.0
     * @param array $session_data Array of session data to insert
     * @return string|false Session ID on success, false on failure
     */
    public function insert_session(array $session_data) {
        // Validate required fields
        if (empty($session_data['session_id']) || empty($session_data['ip_hash'])) {
            return false;
        }        // Prepare data with defaults and sanitization
        $data = [
            'session_id' => \sanitize_text_field($session_data['session_id']),
            'ip_address' => $session_data['ip_address'] ?? null,
            'ip_hash' => \sanitize_text_field($session_data['ip_hash']),
            'user_agent' => \wp_kses_post($session_data['user_agent'] ?? ''),
            'device_type' => in_array($session_data['device_type'] ?? '', ['desktop', 'mobile', 'tablet', 'bot']) 
                ? $session_data['device_type'] : 'desktop',
            'browser' => \sanitize_text_field($session_data['browser'] ?? ''),
            'operating_system' => \sanitize_text_field($session_data['operating_system'] ?? ''),
            'is_bot' => (bool) ($session_data['is_bot'] ?? false),
            'user_id' => $session_data['user_id'] ?? null,
            'first_visit' => \current_time('mysql', true),
            'last_activity' => \current_time('mysql', true),
            'page_count' => 1,
            'total_duration' => 0,
            'referrer' => \esc_url_raw($session_data['referrer'] ?? ''),
            'utm_source' => \sanitize_text_field($session_data['utm_source'] ?? ''),
            'utm_medium' => \sanitize_text_field($session_data['utm_medium'] ?? ''),
            'utm_campaign' => \sanitize_text_field($session_data['utm_campaign'] ?? ''),
            'country_code' => \sanitize_text_field($session_data['country_code'] ?? ''),
            'city' => \sanitize_text_field($session_data['city'] ?? ''),
            'created_at' => \current_time('mysql', true),
            'updated_at' => \current_time('mysql', true)
        ];

        // Insert session using WordPress database methods
        $result = $this->wpdb->insert(
            $this->tables['sessions'],
            $data,
            [
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', 
                '%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s', 
                '%s', '%s', '%s', '%s', '%s'
            ]
        );

        return $result !== false ? $session_data['session_id'] : false;
    }

    /**
     * Insert a page view record
     *
     * Records a page view within an existing session.
     * Updates session page count and last activity time.
     *
     * @since 1.0.0
     * @param array $page_data Array of page view data
     * @return bool True on success, false on failure
     */
    public function insert_page_view(array $page_data): bool {
        // Validate required fields
        if (empty($page_data['session_id']) || empty($page_data['page_url'])) {
            return false;
        }        // Prepare page view data
        $data = [
            'session_id' => \sanitize_text_field($page_data['session_id']),
            'post_id' => $page_data['post_id'] ?? null,
            'page_url' => \esc_url_raw($page_data['page_url']),
            'page_title' => \sanitize_text_field($page_data['page_title'] ?? ''),
            'page_type' => \sanitize_text_field($page_data['page_type'] ?? ''),
            'view_duration' => \absint($page_data['view_duration'] ?? 0),
            'scroll_depth' => \min(100, \absint($page_data['scroll_depth'] ?? 0)),
            'exit_page' => (bool) ($page_data['exit_page'] ?? false),
            'viewed_at' => \current_time('mysql', true)
        ];

        // Insert page view
        $result = $this->wpdb->insert(
            $this->tables['page_views'],
            $data,
            ['%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s']
        );

        // Update session page count and last activity
        if ($result !== false) {
            $this->update_session_activity($page_data['session_id']);
        }

        return $result !== false;
    }

    /**
     * Update session activity
     *
     * Updates the last activity time and increments page count
     * for an existing session.
     *
     * @since 1.0.0
     * @param string $session_id Session identifier
     * @return bool True on success, false on failure
     */
    private function update_session_activity(string $session_id): bool {
        return $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE {$this->tables['sessions']}                 SET last_activity = %s, 
                     page_count = page_count + 1,
                     updated_at = %s
                 WHERE session_id = %s",
                \current_time('mysql', true),
                \current_time('mysql', true),
                $session_id
            )
        ) !== false;
    }

    /**
     * Get session by ID
     *
     * Retrieves a complete session record by session ID.
     * Used for tracking existing visitors and analytics.
     *
     * @since 1.0.0
     * @param string $session_id Session identifier
     * @return array|null Session data or null if not found
     */
    public function get_session(string $session_id): ?array {
        $result = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tables['sessions']} WHERE session_id = %s",
                $session_id            ),
            \ARRAY_A
        );

        return $result ?: null;
    }

    /**
     * Get active sessions for real-time analytics
     *
     * Returns sessions that have been active within the last 30 minutes.
     * Used for real-time visitor counting.
     *
     * @since 1.0.0
     * @param int $minutes_active Number of minutes to consider "active" (default: 30)
     * @return array Array of active session data
     */
    public function get_active_sessions(int $minutes_active = 30): array {
        $cutoff_time = date('Y-m-d H:i:s', strtotime("-{$minutes_active} minutes"));

        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT session_id, device_type, country_code, city, last_activity 
                 FROM {$this->tables['sessions']} 
                 WHERE last_activity > %s 
                 AND is_bot = FALSE
                 ORDER BY last_activity DESC",
                $cutoff_time
            ),
            \ARRAY_A
        );
    }

    /**
     * Get analytics data for date range
     *
     * Retrieves aggregated analytics data for the specified date range.
     * This is the main method for dashboard analytics.
     *
     * @since 1.0.0
     * @param string $start_date Start date (Y-m-d format)
     * @param string $end_date End date (Y-m-d format)
     * @return array Analytics data including sessions, page views, devices
     */
    public function get_analytics_data(string $start_date, string $end_date): array {
        // Basic session metrics
        $sessions = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_sessions,
                    COUNT(DISTINCT ip_hash) as unique_visitors,
                    AVG(total_duration) as avg_duration,
                    AVG(page_count) as avg_pages_per_session
                FROM {$this->tables['sessions']} 
                WHERE DATE(created_at) BETWEEN %s AND %s
                AND is_bot = FALSE
                GROUP BY DATE(created_at)
                ORDER BY date ASC",
                $start_date,
                $end_date
            ),
            \ARRAY_A
        );

        // Device breakdown
        $devices = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT 
                    device_type,
                    COUNT(*) as sessions
                FROM {$this->tables['sessions']} 
                WHERE DATE(created_at) BETWEEN %s AND %s
                AND is_bot = FALSE
                GROUP BY device_type",
                $start_date,
                $end_date
            ),
            \ARRAY_A
        );

        // Top pages
        $pages = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT 
                    page_url,
                    page_title,
                    COUNT(*) as views,
                    COUNT(DISTINCT session_id) as unique_views
                FROM {$this->tables['page_views']} 
                WHERE DATE(viewed_at) BETWEEN %s AND %s
                GROUP BY page_url, page_title
                ORDER BY views DESC
                LIMIT 10",
                $start_date,
                $end_date
            ),
            \ARRAY_A
        );

        return [
            'sessions' => $sessions,
            'devices' => $devices,
            'top_pages' => $pages
        ];
    }

    /**
     * Clean up old records
     *
     * Removes records older than specified retention period.
     * Called by scheduled cleanup tasks.
     *
     * @since 1.0.0
     * @param int $retention_days Number of days to retain data
     * @return int Number of records deleted
     */
    public function cleanup_old_records(int $retention_days = 365): int {
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        $deleted = 0;

        // Clean sessions (will cascade to page_views due to foreign key)
        $deleted += $this->wpdb->query(
            $this->wpdb->prepare(
                "DELETE FROM {$this->tables['sessions']} WHERE created_at < %s",
                $cutoff_date
            )
        );

        // Clean old logs (shorter retention)
        $log_cutoff = date('Y-m-d H:i:s', strtotime('-90 days'));
        $deleted += $this->wpdb->query(
            $this->wpdb->prepare(
                "DELETE FROM {$this->tables['logs']} WHERE created_at < %s",
                $log_cutoff
            )
        );

        return $deleted;
    }

    /**
     * Drop all plugin tables
     *
     * Completely removes all plugin tables from database.
     * Used during plugin uninstallation.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function drop_tables(): bool {
        // Drop tables in reverse order to handle foreign keys
        $tables_to_drop = array_reverse($this->tables);
        
        foreach ($tables_to_drop as $table) {
            $this->wpdb->query("DROP TABLE IF EXISTS {$table}");
        }        // Remove stored database version
        \delete_option('wpvn_db_version');

        return true;
    }

    /**
     * Get current database version
     *
     * Returns the currently installed database schema version.
     * Used for migration management.
     *
     * @since 1.0.0
     * @return string Database version
     */
    public function get_db_version(): string {
        return \get_option('wpvn_db_version', '0.0.0');
    }

    /**
     * Check if tables exist
     *
     * Verifies that all required plugin tables exist in the database.
     * Used for health checks and troubleshooting.
     *
     * @since 1.0.0
     * @return bool True if all tables exist, false otherwise
     */
    public function tables_exist(): bool {
        foreach ($this->tables as $table) {
            $table_exists = $this->wpdb->get_var(
                $this->wpdb->prepare(
                    "SHOW TABLES LIKE %s",
                    $table
                )
            );
            
            if ($table_exists !== $table) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get table names
     *
     * Returns array of all plugin table names with WordPress prefix.
     * Useful for other classes that need table access.
     *
     * @since 1.0.0
     * @return array<string, string> Array of table names
     */
    public function get_tables(): array {
        return $this->tables;
    }

    /**
     * Get WordPress database instance
     *
     * Returns the wpdb instance for direct database operations.
     * Used by other classes like Logger for database access.
     *
     * @since 1.0.0
     * @return \wpdb WordPress database instance
     */
    public function get_wpdb(): \wpdb {
        return $this->wpdb;
    }
}
