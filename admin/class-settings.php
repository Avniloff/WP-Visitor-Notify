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

        \add_settings_field(
            'enable_new_visitor_notifications',
            \__('New Visitor Notifications', 'wp-visitor-notify'),
            [$this, 'enable_new_visitor_notifications_callback'],
            'wpvn_settings',
            'wpvn_notifications_section'
        );

        \add_settings_field(
            'enable_threshold_notifications',
            \__('Daily Visitor Threshold', 'wp-visitor-notify'),
            [$this, 'enable_threshold_notifications_callback'],
            'wpvn_settings',
            'wpvn_notifications_section'
        );

        \add_settings_field(
            'visitor_threshold_count',
            \__('Threshold Count', 'wp-visitor-notify'),
            [$this, 'visitor_threshold_count_callback'],
            'wpvn_settings',
            'wpvn_notifications_section'
        );

        \add_settings_field(
            'enable_new_device_notifications',
            \__('New Device Notifications', 'wp-visitor-notify'),
            [$this, 'enable_new_device_notifications_callback'],
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
        
        // Notification settings
        $validated['enable_new_visitor_notifications'] = !empty($input['enable_new_visitor_notifications']) ? 1 : 0;
        $validated['enable_threshold_notifications'] = !empty($input['enable_threshold_notifications']) ? 1 : 0;
        $validated['visitor_threshold_count'] = (int) ($input['visitor_threshold_count'] ?? 100);
        $validated['enable_new_device_notifications'] = !empty($input['enable_new_device_notifications']) ? 1 : 0;
        
        $validated['database_cleanup_days'] = (int) ($input['database_cleanup_days'] ?? 90);
          // Ensure cleanup days is reasonable (min 7, max 3650)
        if ($validated['database_cleanup_days'] < 7) {
            $validated['database_cleanup_days'] = 7;
        } elseif ($validated['database_cleanup_days'] > 3650) {
            $validated['database_cleanup_days'] = 3650;
        }
        
        // Ensure threshold count is reasonable (min 1, max 10000)
        if ($validated['visitor_threshold_count'] < 1) {
            $validated['visitor_threshold_count'] = 1;
        } elseif ($validated['visitor_threshold_count'] > 10000) {
            $validated['visitor_threshold_count'] = 10000;
        }
        
        // Sync notification settings with database rules
        $this->sync_notification_rules($validated);
        
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
    }    public function notification_email_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }

    public function enable_new_visitor_notifications_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }

    public function enable_threshold_notifications_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }

    public function visitor_threshold_count_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }

    public function enable_new_device_notifications_callback(): void {
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
     */    public function get_default_settings(): array {
        return [
            'tracking_enabled' => 1,
            'hash_ip' => 1,
            'exclude_admins' => 1,
            'exclude_bots' => 1,
            'notification_email' => \get_option('admin_email', ''),
            'enable_new_visitor_notifications' => 0,
            'enable_threshold_notifications' => 0,
            'visitor_threshold_count' => 100,
            'enable_new_device_notifications' => 0,
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
        }    }

    /**
     * Sync notification settings with database rules
     *
     * @param array $settings Current settings
     * @return void
     */
    public function sync_notification_rules(array $settings): void {
        global $wpdb;
        
        $rules_table = $wpdb->prefix . 'wpvn_notification_rules';
        
        // Update new visitor notifications
        $wpdb->update(
            $rules_table,
            ['status' => $settings['enable_new_visitor_notifications'] ? 1 : 0],
            ['event_type' => 'new_visitor'],
            ['%d'],
            ['%s']
        );
        
        // Update threshold notifications
        $wpdb->update(
            $rules_table,
            [
                'status' => $settings['enable_threshold_notifications'] ? 1 : 0,
                'threshold' => (int) $settings['visitor_threshold_count']
            ],
            ['event_type' => 'visitor_threshold'],
            ['%d', '%d'],
            ['%s']
        );
        
        // Update new device notifications
        $wpdb->update(
            $rules_table,
            ['status' => $settings['enable_new_device_notifications'] ? 1 : 0],
            ['event_type' => 'new_device'],
            ['%d'],
            ['%s']
        );
          // Update email for all rules
        if (!empty($settings['notification_email'])) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$rules_table} SET email = %s",
                    sanitize_email($settings['notification_email'])
                )
            );
        }
    }    public function render(): void {        // Get current settings
        $settings = get_option('wpvn_settings', []);
        
        // Set template variables
        $template_vars = [
            'settings' => $settings,
            'settings_url' => admin_url('admin.php?page=wp-visitor-notify-settings'),
            'nonce_field' => wp_nonce_field('wpvn_settings_nonce', 'wpvn_settings_nonce', true, false)
        ];
        
        // Include header
        include WPVN_PLUGIN_PATH . 'admin/templates/header.php';
        
        // Include settings template
        extract($template_vars);
        include WPVN_PLUGIN_PATH . 'admin/templates/settings.php';
    }
}
