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
    }

    public function register_settings(): void {
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
        
        \add_settings_field(
            'tracking_frequency',
            \__('Recording Frequency', 'wp-visitor-notify'),
            [$this, 'tracking_frequency_callback'],
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
        
        \add_settings_field(
            'anonymization_level',
            \__('Anonymization Level', 'wp-visitor-notify'),
            [$this, 'anonymization_level_callback'],
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
            'exclude_ips',
            \__('Exclude IP Addresses', 'wp-visitor-notify'),
            [$this, 'exclude_ips_callback'],
            'wpvn_settings',
            'wpvn_exceptions_section'
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
    }

    public function validate_settings(array $input): array {
        $validated = [];
        
        $validated['tracking_enabled'] = !empty($input['tracking_enabled']) ? 1 : 0;
        $validated['tracking_frequency'] = sanitize_text_field($input['tracking_frequency'] ?? 'all');
        $validated['hash_ip'] = !empty($input['hash_ip']) ? 1 : 0;
        $validated['anonymization_level'] = sanitize_text_field($input['anonymization_level'] ?? 'medium');
        $validated['exclude_ips'] = sanitize_textarea_field($input['exclude_ips'] ?? '');
        $validated['exclude_admins'] = !empty($input['exclude_admins']) ? 1 : 0;
        $validated['exclude_bots'] = !empty($input['exclude_bots']) ? 1 : 0;
        
        return $validated;
    }
      // Section callbacks
    public function tracking_section_callback(): void {
        // Section description will be in template
    }
    
    public function privacy_section_callback(): void {
        // Section description will be in template
    }
    
    public function exceptions_section_callback(): void {
        // Section description will be in template
    }    // Field callbacks
    public function tracking_enabled_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }
      public function tracking_frequency_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }
      public function hash_ip_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }
      public function anonymization_level_callback(): void {
        $options = \get_option('wpvn_settings', []);
        // Will be handled in template
    }
      public function exclude_ips_callback(): void {
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
    }    public function render(): void {
        // Template will be included here when created
        // For now, return empty - no HTML output
    }
}
