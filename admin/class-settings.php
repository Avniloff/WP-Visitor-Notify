<?php
declare(strict_types=1);

namespace WPVN\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings page controller.
 * Uses the WordPress Settings API.
 *
 * @since 1.0.0
 */
class Settings {
    public function init(): void {
        \add_action('admin_init', [$this, 'register_settings']);
    }    public function register_settings(): void {
        // Register main settings group
        \register_setting('wpvn_settings_group', 'wpvn_settings', [$this, 'validate_settings']);
        
        // Tracking Section
        \add_settings_section(
            'wpvn_tracking_section',
            \__('Tracking Settings', 'wp-visitor-notify'),
            [$this, 'tracking_section_callback'],
            'wpvn_settings'
        );
        
        \add_settings_field(
            'tracking_enabled',
            \__('Enable Tracking', 'wp-visitor-notify'),
            [$this, 'tracking_enabled_callback'],
            'wpvn_settings',
            'wpvn_tracking_section'
        );
        
        // Privacy Section
        \add_settings_section(
            'wpvn_privacy_section',
            \__('Privacy Settings', 'wp-visitor-notify'),
            [$this, 'privacy_section_callback'],
            'wpvn_settings'
        );
        
        \add_settings_field(
            'hash_ip',
            \__('Hash IP Addresses', 'wp-visitor-notify'),
            [$this, 'hash_ip_callback'],
            'wpvn_settings',
            'wpvn_privacy_section'
        );
        
        // Exceptions Section
        \add_settings_section(
            'wpvn_exceptions_section',
            \__('Tracking Exceptions', 'wp-visitor-notify'),
            [$this, 'exceptions_section_callback'],
            'wpvn_settings'
        );
        
        \add_settings_field(
            'exclude_admins',
            \__('Exclude Administrators', 'wp-visitor-notify'),
            [$this, 'exclude_admins_callback'],
            'wpvn_settings',
            'wpvn_exceptions_section'
        );
        
        \add_settings_field(
            'exclude_bots',
            \__('Exclude Bots', 'wp-visitor-notify'),
            [$this, 'exclude_bots_callback'],
            'wpvn_settings',
            'wpvn_exceptions_section'
        );

        // Notifications Section
        \add_settings_section(
            'wpvn_notifications_section',
            \__('Notification Settings', 'wp-visitor-notify'),
            [$this, 'notifications_section_callback'],
            'wpvn_settings'
        );
        
        \add_settings_field(
            'notification_email',
            \__('Notification Email', 'wp-visitor-notify'),
            [$this, 'notification_email_callback'],
            'wpvn_settings',
            'wpvn_notifications_section'
        );

        // Database Section
        \add_settings_section(
            'wpvn_database_section',
            \__('Database Settings', 'wp-visitor-notify'),
            [$this, 'database_section_callback'],
            'wpvn_settings'
        );
        
        \add_settings_field(
            'database_cleanup_days',
            \__('Data Retention (Days)', 'wp-visitor-notify'),
            [$this, 'database_cleanup_days_callback'],
            'wpvn_settings',
            'wpvn_database_section'
        );
    }    public function validate_settings(array $input): array {
        $validated = [];
        
        $validated['tracking_enabled'] = !empty($input['tracking_enabled']) ? 1 : 0;
        $validated['hash_ip'] = !empty($input['hash_ip']) ? 1 : 0;
        $validated['exclude_admins'] = !empty($input['exclude_admins']) ? 1 : 0;
        $validated['exclude_bots'] = !empty($input['exclude_bots']) ? 1 : 0;
        $validated['notification_email'] = sanitize_email($input['notification_email'] ?? '');
        $validated['database_cleanup_days'] = (int) ($input['database_cleanup_days'] ?? 90);
        
        // Ensure cleanup days is reasonable (min 7, max 3650)
        if ($validated['database_cleanup_days'] < 7) {
            $validated['database_cleanup_days'] = 7;
        } elseif ($validated['database_cleanup_days'] > 3650) {
            $validated['database_cleanup_days'] = 3650;
        }
        
        return $validated;
    }    // Section callbacks
    public function tracking_section_callback(): void {
        // Section description will be in template
    }
    
    public function privacy_section_callback(): void {
        // Section description will be in template
    }
    
    public function exceptions_section_callback(): void {
        // Section description will be in template
    }

    public function notifications_section_callback(): void {
        // Section description will be in template
    }

    public function database_section_callback(): void {
        // Section description will be in template
    }

    // Field callbacks
    public function tracking_enabled_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }

    public function hash_ip_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }

    public function exclude_admins_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }

    public function exclude_bots_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }

    public function notification_email_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }

    public function database_cleanup_days_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }

    /**
     * Get default settings values.
     *
     * @return array<string, mixed> Default settings
     */
    public function get_default_settings(): array {
        return [
            'tracking_enabled' => 1,
            'hash_ip' => 1,
            'exclude_admins' => 1,
            'exclude_bots' => 1,
            'notification_email' => \get_option('admin_email', ''),
            'database_cleanup_days' => 90
        ];
    }

    /**
     * Get current settings with defaults.
     *
     * @return array<string, mixed> Current settings
     */
    public function get_settings(): array {
        $defaults = $this->get_default_settings();
        $settings = \get_option('wpvn_settings', []);
        return \wp_parse_args($settings, $defaults);
    }

    /**
     * Clean up old database records based on retention setting.
     *
     * @return int Number of records deleted
     */
    public function cleanup_database(): int {
        $settings = $this->get_settings();
        $days = (int) $settings['database_cleanup_days'];
        
        if ($days <= 0) {
            return 0;
        }

        global $wpdb;
        
        $deleted = 0;
        
        // Clean up old sessions
        $sessions_table = $wpdb->prefix . 'wpvn_sessions';
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$sessions_table} WHERE created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY)",
            $days
        ));
        $deleted += (int) $result;
        
        // Clean up old page views
        $views_table = $wpdb->prefix . 'wpvn_page_views';
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$views_table} WHERE viewed_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY)",
            $days
        ));
        $deleted += (int) $result;
        
        // Clean up old logs
        $logs_table = $wpdb->prefix . 'wpvn_logs';
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$logs_table} WHERE created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY)",
            $days
        ));
        $deleted += (int) $result;
        
        return $deleted;
    }

    /**
     * Reset all plugin data and settings.
     *
     * @return bool True on success
     */
    public function reset_all_data(): bool {
        global $wpdb;
        
        try {
            // Truncate all plugin tables
            $tables = [
                $wpdb->prefix . 'wpvn_sessions',
                $wpdb->prefix . 'wpvn_page_views',
                $wpdb->prefix . 'wpvn_notification_rules',
                $wpdb->prefix . 'wpvn_notification_history',
                $wpdb->prefix . 'wpvn_logs'
            ];
            
            foreach ($tables as $table) {
                $wpdb->query("TRUNCATE TABLE {$table}");
            }
            
            // Reset settings to defaults
            $defaults = $this->get_default_settings();
            \update_option('wpvn_settings', $defaults);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function render(): void {
        // Template will be included here when created
        // For now, return empty - no HTML output
    }
}
