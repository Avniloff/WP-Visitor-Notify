<?php
declare(strict_types=1);
/**
 * Plugin Name: WordPress Visitor Notify
 * Plugin URI: https://github.com/Avniloff/WP-Visitor-Notify
 * Description: Privacy-first visitor analytics and notification system for WordPress sites with real-time tracking, intelligent alerts, and comprehensive dashboards.
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires PHP: 8.2
 * Author: Avniloff Avraham
 * Author Email: avniloff@gmail.com
 * Author URI: https://github.com/avniloff
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-visitor-notify
 * Domain Path: /languages
 * Network: false
 * 
 * @package WPVN
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
if (!defined('WPVN_VERSION')) {
    define('WPVN_VERSION', '1.0.0');
}
if (!defined('WPVN_PLUGIN_FILE')) {
    define('WPVN_PLUGIN_FILE', __FILE__);
}
if (!defined('WPVN_PLUGIN_DIR')) {
    define('WPVN_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('WPVN_PLUGIN_PATH')) {
    define('WPVN_PLUGIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('WPVN_PLUGIN_URL')) {
    define('WPVN_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('WPVN_PLUGIN_BASENAME')) {
    define('WPVN_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

// Check PHP version - if less than 8.2, show error and stop loading
if (version_compare(PHP_VERSION, '8.2', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>WordPress Visitor Notify:</strong> This plugin requires PHP 8.2 or higher. ';
        echo 'You are running PHP ' . PHP_VERSION . '. Please upgrade PHP to activate this plugin.';
        echo '</p></div>';
    });
    return;
}

// Check WordPress version - if less than 6.2, show error and stop loading
global $wp_version;
if (version_compare($wp_version, '6.2', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>WordPress Visitor Notify:</strong> This plugin requires WordPress 6.2 or higher. ';
        echo 'You are running WordPress ' . get_bloginfo('version') . '. Please upgrade WordPress to activate this plugin.';        echo '</p></div>';
    });
    return;
}

// CUSTOM AUTOLOADER
spl_autoload_register(function (string $class): void {
    // Only handle classes from our namespace
    if (strpos($class, 'WPVN\\') !== 0) {
        return;
    }

    // Remove the root namespace prefix
    $relative = substr($class, strlen('WPVN\\'));

    // Break into parts by namespace separators
    $parts = explode('\\', $relative);

    // Determine base directory (core includes or admin)
    $base_dir = WPVN_PLUGIN_DIR . 'includes/';
    if ($parts[0] === 'Admin') {
        $base_dir = WPVN_PLUGIN_DIR . 'admin/';
        array_shift($parts); // Remove the 'Admin' segment
    }

    // The actual class name is the last segment
    $class_name = array_pop($parts);

    // Convert remaining namespace parts to directory structure
    $sub_path = '';
    if (!empty($parts)) {
        $sub_path = implode('/', $parts) . '/';
    }

    // Build the final file name
    $file = $base_dir . $sub_path . 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize the plugin
// Create an instance of the main class through Singleton pattern and initialize
function wpvn_init(): void {
    // Get the single instance of the plugin
    $plugin = WPVN\Plugin::get_instance();
    
    // Initialize all components
    $plugin->init();
}

// Start the plugin after all WordPress plugins are loaded
add_action('plugins_loaded', 'wpvn_init');

// Activation hook
register_activation_hook(__FILE__, 'wpvn_activate');
function wpvn_activate(): void {
    // Get the plugin instance
    $plugin = WPVN\Plugin::get_instance();
    
    // Execute activation procedures
    $plugin->on_activation();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wpvn_deactivate');
function wpvn_deactivate(): void {
    // Get the plugin instance
    $plugin = WPVN\Plugin::get_instance();
    
    // Execute deactivation procedures
    $plugin->on_deactivation();
}

// Uninstall hook
register_uninstall_hook(__FILE__, ['WPVN\Uninstaller', 'uninstall']);
