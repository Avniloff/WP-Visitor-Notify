<?php
/**
 * Database operations handler for WP Visitor Notify Plugin
 *
 * Simplified database class for basic table management.
 * Contains schema definitions for future use.
 * * @package    WP_Visitor_Notify
 * @subpackage Includes
 * @since      1.0.0
 * @author     Avniloff Avraham
 */

namespace WPVN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database operations class
 *
 * Handles basic database operations for the plugin.
 * Schema ready for future implementations.
 *
 * @since 1.0.0
 */
class Database {

    /**
     * Current database schema version
     * 
     * @since 1.0.0
     * @var string
     */
    private const DB_VERSION = '1.0.0';

    /**
     * WordPress database instance
     *
     * @since 1.0.0
     * @var \wpdb
     */
    private $wpdb;

    /**
     * Table names with WordPress prefix
     *
     * @since 1.0.0
     * @var array<string, string>
     */
    private array $tables;

    /**
     * Constructor - Initialize database connection and table names
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
     * Basic table creation for development.
     * Full implementation will be added later.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function create_tables(): bool {
        // For now, just mark as successful
        // TODO: Implement when we need actual data storage
        \update_option('wpvn_db_version', self::DB_VERSION);
        return true;
    }

    /**
     * Check if tables exist
     *
     * @since 1.0.0
     * @return bool True if tables exist (simplified for now)
     */
    public function tables_exist(): bool {
        // For now, assume tables exist if we have the version option
        $version = \get_option('wpvn_db_version', '');
        return !empty($version);
    }

    /**
     * Get current database version
     *
     * @since 1.0.0
     * @return string Database version
     */
    public function get_db_version(): string {
        return \get_option('wpvn_db_version', '0.0.0');
    }

    /**
     * Get table names
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
     * @since 1.0.0
     * @return \wpdb WordPress database instance
     */
    public function get_wpdb(): \wpdb {
        return $this->wpdb;
    }

    /**
     * Drop all plugin tables
     *
     * @since 1.0.0
     * @return bool True on success
     */
    public function drop_tables(): bool {
        // Remove stored database version
        \delete_option('wpvn_db_version');
        return true;
    }

    /*
     * =========================================================================
     * FUTURE IMPLEMENTATIONS - Schema definitions ready for development
     * =========================================================================
     */

    /*
    // TODO: Full table creation methods will be implemented here:
    
    private function create_sessions_table(): bool {
        $table_name = $this->tables['sessions'];
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        return !empty($result);
    }
    
    // TODO: Add other table creation methods:
    // - create_page_views_table()
    // - create_notification_rules_table() 
    // - create_notification_history_table()
    // - create_logs_table()
    
    // TODO: Add data operation methods:
    // - insert_session()
    // - insert_page_view()
    // - get_session()
    // - get_analytics_data()
    // - cleanup_old_records()
    */
}
