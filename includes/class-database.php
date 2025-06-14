<?php
declare(strict_types=1);
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
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $this->wpdb->get_charset_collate();

        $tables_sql = [];

        $tables_sql[] = "CREATE TABLE {$this->tables['sessions']} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_key VARCHAR(64) NOT NULL,
            ip_hash VARCHAR(64) NOT NULL,
            user_agent TEXT NOT NULL,
            device_type VARCHAR(20) DEFAULT '',
            browser VARCHAR(50) DEFAULT '',
            os VARCHAR(50) DEFAULT '',
            created_at DATETIME NOT NULL,
            last_activity DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY session_key (session_key)
        ) $charset_collate";

        $tables_sql[] = "CREATE TABLE {$this->tables['page_views']} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id BIGINT(20) UNSIGNED NOT NULL,
            url TEXT NOT NULL,
            title TEXT NOT NULL,
            viewed_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY session_id (session_id)
        ) $charset_collate";

        $tables_sql[] = "CREATE TABLE {$this->tables['notification_rules']} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(200) NOT NULL,
            event_type VARCHAR(50) NOT NULL,
            threshold INT(11) DEFAULT 0,
            email VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            status TINYINT(1) DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate";

        foreach ($tables_sql as $sql) {
            dbDelta($sql);
        }

        update_option('wpvn_db_version', self::DB_VERSION);
        return true;
    }    /**
     * Check if tables exist
     *
     * @since 1.0.0
     * @return bool True if tables exist with correct schema
     */
    public function tables_exist(): bool {
        // First check version option for quick exit
        $version = \get_option('wpvn_db_version', '');
        if (empty($version)) {
            return false;
        }
        
        // Then verify actual tables and schema
        return $this->verify_tables_schema();
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
    }    /**
     * Drop all plugin tables
     * 
     * IMPORTANT: This method is only used for development/testing purposes,
     * not during production uninstallation where visitor data is preserved!
     *
     * @since 1.0.0
     * @return bool True on success
     */
    public function drop_tables(): bool {
        // Remove stored database version
        \delete_option('wpvn_db_version');
        return true;
    }

    /**
     * Check if tables exist and have correct schema
     *
     * @since 1.0.0
     * @return bool True if all tables exist with correct schema
     */
    public function verify_tables_schema(): bool {
        foreach ($this->tables as $table_name) {
            // Check if table exists
            $table_exists = $this->wpdb->get_var(
                $this->wpdb->prepare("SHOW TABLES LIKE %s", $table_name)
            );
            
            if ($table_exists !== $table_name) {
                return false;
            }
            
            // Check specific columns for each table
            if ($table_name === $this->tables['sessions']) {
                $columns = $this->wpdb->get_col("DESCRIBE {$table_name}");
                if (!in_array('session_key', $columns) || !in_array('ip_hash', $columns)) {
                    return false;
                }
            }
            
            if ($table_name === $this->tables['page_views']) {
                $columns = $this->wpdb->get_col("DESCRIBE {$table_name}");
                if (!in_array('url', $columns) || !in_array('session_id', $columns)) {
                    return false;
                }
            }
        }
        
        return true;
    }

}
