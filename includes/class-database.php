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

}