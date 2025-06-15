<?php
/**
 * Settings page template
 * Plugin configuration page with form sections and action buttons
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$settings = get_option('wpvn_settings', []);
$defaults = [
    'tracking_enabled' => 1,
    'hash_ip' => 1,
    'exclude_admins' => 1,
    'exclude_bots' => 1,
    'notification_email' => get_option('admin_email'),
    'database_cleanup_days' => 90
];
$settings = wp_parse_args($settings, $defaults);
?>

<div class="wpvn-container">
    <?php include WPVN_PLUGIN_DIR . 'admin/templates/header.php'; ?>
    
    <div class="wpvn-settings">
        <!-- Action Buttons Bar -->
        <div class="wpvn-action-bar">
            <div class="wpvn-action-buttons">
                <button type="button" class="button button-primary" id="save-settings">
                    Save Settings
                </button>
                <button type="button" class="button" id="reset-defaults">
                    Reset to Defaults
                </button>
                <button type="button" class="button" id="cleanup-database">
                    Cleanup Database
                </button>
                <button type="button" class="button button-secondary" id="reset-all-data">
                    Reset All Data
                </button>
            </div>
        </div>
        
        <!-- Settings Form -->
        <form id="wpvn-settings-form" method="post">
            <?php wp_nonce_field('wpvn_settings_nonce', 'wpvn_settings_nonce'); ?>
            
            <!-- Tracking Section -->
            <div class="wpvn-card wpvn-settings-section">
                <div class="wpvn-card-header">
                    <h3>Tracking Settings</h3>
                </div>
                <div class="wpvn-card-content">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="tracking_enabled">Enable Tracking</label>
                            </th>
                            <td>
                                <input type="checkbox" id="tracking_enabled" name="wpvn_settings[tracking_enabled]" 
                                       value="1" <?php checked($settings['tracking_enabled'], 1); ?>>
                                <p class="description">Enable or disable visitor tracking for your website.</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Privacy Section -->
            <div class="wpvn-card wpvn-settings-section">
                <div class="wpvn-card-header">
                    <h3>Privacy Settings</h3>
                </div>
                <div class="wpvn-card-content">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="hash_ip">Hash IP Addresses</label>
                            </th>
                            <td>
                                <input type="checkbox" id="hash_ip" name="wpvn_settings[hash_ip]" 
                                       value="1" <?php checked($settings['hash_ip'], 1); ?>>
                                <p class="description">Hash IP addresses for better privacy compliance.</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Exceptions Section -->
            <div class="wpvn-card wpvn-settings-section">
                <div class="wpvn-card-header">
                    <h3>Tracking Exceptions</h3>
                </div>
                <div class="wpvn-card-content">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="exclude_admins">Exclude Administrators</label>
                            </th>
                            <td>
                                <input type="checkbox" id="exclude_admins" name="wpvn_settings[exclude_admins]" 
                                       value="1" <?php checked($settings['exclude_admins'], 1); ?>>
                                <p class="description">Don't track visits from logged-in administrators.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="exclude_bots">Exclude Bots</label>
                            </th>
                            <td>
                                <input type="checkbox" id="exclude_bots" name="wpvn_settings[exclude_bots]" 
                                       value="1" <?php checked($settings['exclude_bots'], 1); ?>>
                                <p class="description">Don't track visits from bots and crawlers.</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
              <!-- Notifications Section -->
            <div class="wpvn-card wpvn-settings-section">
                <div class="wpvn-card-header">
                    <h3>Notification Settings</h3>
                </div>
                <div class="wpvn-card-content">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="notification_email">Notification Email</label>
                            </th>
                            <td>
                                <input type="email" id="notification_email" name="wpvn_settings[notification_email]" 
                                       value="<?php echo esc_attr($settings['notification_email']); ?>" class="regular-text">
                                <p class="description">Email address for plugin notifications.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="enable_new_visitor_notifications">New Visitor Notifications</label>
                            </th>
                            <td>
                                <input type="checkbox" id="enable_new_visitor_notifications" 
                                       name="wpvn_settings[enable_new_visitor_notifications]" value="1" 
                                       <?php checked($settings['enable_new_visitor_notifications']); ?>>
                                <label for="enable_new_visitor_notifications">Send email notification for every new visitor</label>
                                <p class="description">⚠️ Warning: This can generate many emails on busy sites!</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="enable_threshold_notifications">Daily Visitor Threshold</label>
                            </th>
                            <td>
                                <input type="checkbox" id="enable_threshold_notifications" 
                                       name="wpvn_settings[enable_threshold_notifications]" value="1" 
                                       <?php checked($settings['enable_threshold_notifications']); ?>>
                                <label for="enable_threshold_notifications">Send notification when daily visitor count reaches threshold</label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="visitor_threshold_count">Threshold Count</label>
                            </th>
                            <td>
                                <input type="number" id="visitor_threshold_count" name="wpvn_settings[visitor_threshold_count]" 
                                       value="<?php echo esc_attr($settings['visitor_threshold_count']); ?>" 
                                       min="1" max="10000" class="small-text">
                                <p class="description">Number of visitors per day to trigger threshold notification.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="enable_new_device_notifications">New Device Notifications</label>
                            </th>
                            <td>
                                <input type="checkbox" id="enable_new_device_notifications" 
                                       name="wpvn_settings[enable_new_device_notifications]" value="1" 
                                       <?php checked($settings['enable_new_device_notifications']); ?>>
                                <label for="enable_new_device_notifications">Send notification when new device type is detected</label>
                                <p class="description">Notifies when a new device type (mobile, tablet, desktop) visits your site.</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Database Section -->
            <div class="wpvn-card wpvn-settings-section">
                <div class="wpvn-card-header">
                    <h3>Database Settings</h3>
                </div>
                <div class="wpvn-card-content">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="database_cleanup_days">Data Retention (Days)</label>
                            </th>
                            <td>
                                <input type="number" id="database_cleanup_days" name="wpvn_settings[database_cleanup_days]" 
                                       value="<?php echo esc_attr($settings['database_cleanup_days']); ?>" 
                                       min="7" max="3650" class="small-text">
                                <p class="description">Number of days to keep visitor data (7-3650 days).</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </form>
        
        <!-- Status Messages -->
        <div id="wpvn-settings-messages" class="wpvn-messages"></div>
    </div>
</div>
