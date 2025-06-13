<?php
declare(strict_types=1);

namespace WPVN\Admin;

use WPVN\Analytics;
use WPVN\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main admin controller.
 * Handles menu registration, asset enqueuing and page rendering.
 *
 * @since 1.0.0
 */
class Admin {
    private Plugin $plugin;
    private Analytics $analytics;

    public function __construct(Plugin $plugin, Analytics $analytics) {
        $this->plugin = $plugin;
        $this->analytics = $analytics;
    }

    public function init(): void {
        \add_action('admin_menu', [$this, 'register_menu']);
        \add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        \add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_menu(): void {
        \add_menu_page(
            __('Visitor Analytics', 'wp-visitor-notify'),
            __('Visitor Analytics', 'wp-visitor-notify'),
            'manage_options',
            Plugin::PLUGIN_SLUG,
            [$this, 'render_dashboard'],
            'dashicons-chart-bar',
            30
        );
        \add_submenu_page(
            Plugin::PLUGIN_SLUG,
            __('Settings', 'wp-visitor-notify'),
            __('Settings', 'wp-visitor-notify'),
            'manage_options',
            Plugin::PLUGIN_SLUG . '-settings',
            [$this, 'render_settings']
        );
    }

    public function enqueue_assets(string $hook): void {
        \wp_enqueue_style('wpvn-admin', WPVN_PLUGIN_URL . 'admin/assets/css/admin.css', [], Plugin::VERSION);
        \wp_enqueue_script('wpvn-admin', WPVN_PLUGIN_URL . 'admin/assets/js/admin.js', ['jquery'], Plugin::VERSION, true);
    }

    public function register_settings(): void {
        \register_setting('wpvn_settings_group', 'wpvn_settings');
    }

    public function render_dashboard(): void {
        $data = $this->analytics->get_daily_visits(7);
        include WPVN_PLUGIN_DIR . 'admin/templates/dashboard.php';
    }

    public function render_settings(): void {
        include WPVN_PLUGIN_DIR . 'admin/templates/settings.php';
    }

    public function render_notifications(): void {
        echo '<div class="wrap"><h1>' . esc_html__('Notifications', 'wp-visitor-notify') . '</h1></div>';
    }

    public function render_logs(): void {
        echo '<div class="wrap"><h1>' . esc_html__('Logs', 'wp-visitor-notify') . '</h1></div>';
    }
}
