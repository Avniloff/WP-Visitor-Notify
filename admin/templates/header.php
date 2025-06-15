<?php
/**
 * Common header template for all admin pages
 * Displays page title, plugin version, and detection status
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin data
if (defined('WPVN_PLUGIN_FILE') && file_exists(WPVN_PLUGIN_FILE)) {
    $plugin_data = get_plugin_data(WPVN_PLUGIN_FILE);
    $plugin_version = $plugin_data['Version'] ?? '1.0.0';
} else {
    $plugin_version = defined('WPVN_VERSION') ? WPVN_VERSION : '1.0.0';
}

// Get settings to check if tracking is enabled
$settings = get_option('wpvn_settings', []);
$tracking_enabled = $settings['tracking_enabled'] ?? 1;
$detection_status = $tracking_enabled ? 'Active' : 'Inactive';
$status_class = $tracking_enabled ? 'wpvn-status-active' : 'wpvn-status-inactive';

// Get current page name
$current_page = $_GET['page'] ?? 'wp-visitor-notify';
$page_titles = [
    'wp-visitor-notify' => 'Dashboard',
    'wpvn-settings' => 'Settings',
    'wpvn-logs' => 'Logs'
];
$page_title = $page_titles[$current_page] ?? 'Dashboard';
?>

<div class="wpvn-header">
    <div class="wpvn-header-left">
        <h1 class="wpvn-page-title"><?php echo esc_html($page_title); ?></h1>
    </div>
    
    <div class="wpvn-header-right">
        <div class="wpvn-plugin-info">
            <span class="wpvn-version">v<?php echo esc_html($plugin_version); ?></span>
            <span class="wpvn-status <?php echo esc_attr($status_class); ?>">
                Detection <?php echo esc_html($detection_status); ?>
            </span>
        </div>
    </div>
</div>
