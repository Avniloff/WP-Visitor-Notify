<?php
/**
 * Plugin Uninstaller Class for WP Visitor Notify
 *
 * Handles complete plugin removal including all data, options, and database tables.
 * This class is called when the plugin is completely uninstalled from WordPress.
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
 * Uninstaller class
 *
 * Handles complete cleanup when plugin is uninstalled.
 * Removes all data, options, tables, and scheduled events.
 *
 * @since 1.0.0
 */
class Uninstaller {

    /**
     * Plugin slug for options cleanup
     *
     * @since 1.0.0
     * @var string
     */
    private const PLUGIN_SLUG = 'wp-visitor-notify';

    /**
     * Main uninstall method
     *
     * Called by WordPress when plugin is completely uninstalled.
     * Removes ALL plugin data to ensure clean removal.
     *
     * @since 1.0.0
     * @return void
     */
    public static function uninstall(): void {
        // Verify uninstall request is legitimate
        if (!current_user_can('activate_plugins')) {
            return;
        }

        try {
            // Log uninstall start
            error_log('[' . date('Y-m-d H:i:s') . '] WPVN.INFO: Starting plugin uninstall process');

            // Remove all plugin options
            self::remove_plugin_options();

            // Drop all plugin database tables
            self::drop_database_tables();

            // Clear any scheduled cron events
            self::clear_cron_events();

            // Remove user capabilities if any were added
            self::remove_capabilities();

            // Clean up transients
            self::clear_transients();

            // Log successful uninstall
            error_log('[' . date('Y-m-d H:i:s') . '] WPVN.INFO: Plugin uninstalled successfully - all data removed');

        } catch (\Exception $e) {
            error_log('WPVN Uninstaller Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove all plugin options from wp_options table
     *
     * @since 1.0.0
     * @return void
     */
    private static function remove_plugin_options(): void {
        $options_to_remove = [
            self::PLUGIN_SLUG . '_options',
            self::PLUGIN_SLUG . '_settings',
            'wpvn_db_version',
            'wpvn_activation_time',
            'wpvn_plugin_version',
        ];

        foreach ($options_to_remove as $option) {
            delete_option($option);
        }
    }

    /**
     * Drop all plugin database tables
     *
     * @since 1.0.0
     * @return void
     */
    private static function drop_database_tables(): void {
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'wpvn_sessions',
            $wpdb->prefix . 'wpvn_page_views',
            $wpdb->prefix . 'wpvn_notification_rules',
            $wpdb->prefix . 'wpvn_notification_history',
            $wpdb->prefix . 'wpvn_logs',
        ];

        foreach ($tables as $table) {
            $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %s", $table));
        }
    }

    /**
     * Clear all scheduled cron events
     *
     * @since 1.0.0
     * @return void
     */
    private static function clear_cron_events(): void {
        $cron_hooks = [
            'wpvn_daily_cleanup',
            'wpvn_hourly_aggregation',
            'wpvn_notification_check',
        ];

        foreach ($cron_hooks as $hook) {
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $hook);
            }
        }
    }

    /**
     * Remove any custom capabilities that were added
     *
     * @since 1.0.0
     * @return void
     */
    private static function remove_capabilities(): void {
        // Currently no custom capabilities are added
        // This method is ready for future use
    }

    /**
     * Clear plugin-related transients
     *
     * @since 1.0.0
     * @return void
     */
    private static function clear_transients(): void {
        global $wpdb;

        // Remove transients with our prefix
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_wpvn_%',
                '_transient_timeout_wpvn_%'
            )        );
    }
}
