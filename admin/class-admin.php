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
    private Settings $settings;
    private Dashboard $dashboard;
    private Logs $logs;    public function __construct(Plugin $plugin, Analytics $analytics) {
        $this->settings = new Settings();
        $this->dashboard = new Dashboard($analytics);
        $this->logs = new Logs($plugin->get_logger());
    }

    public function init(): void {
        \add_action('admin_menu', [$this, 'register_menu']);
        \add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Initialize settings
        $this->settings->init();
    }    public function register_menu(): void {
        \add_menu_page(
            __('Dashboard', 'wp-visitor-notify'),
            __('Visitor Notify', 'wp-visitor-notify'),
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
        );        \add_submenu_page(
            Plugin::PLUGIN_SLUG,
            __('Logs', 'wp-visitor-notify'),
            __('Logs', 'wp-visitor-notify'),
            'manage_options',
            Plugin::PLUGIN_SLUG . '-logs',
            [$this, 'render_logs']
        );
    }    public function enqueue_assets(string $hook): void {
        \wp_enqueue_style('wpvn-admin', WPVN_PLUGIN_URL . 'admin/assets/css/admin.css', [], WPVN_VERSION);
        \wp_enqueue_script('wpvn-admin', WPVN_PLUGIN_URL . 'admin/assets/js/admin.js', ['jquery'], WPVN_VERSION, true);
    }public function render_dashboard(): void {
        $this->dashboard->render();
    }public function render_settings(): void {
        $this->settings->render();
    }

    public function render_notifications(): void {
        echo '<div class="wrap"><h1>' . esc_html__('Notifications', 'wp-visitor-notify') . '</h1></div>';
    }    public function render_logs(): void {
        // Handle export action
        if (isset($_GET['action']) && $_GET['action'] === 'export') {
            $this->logs->export_logs();
            return;
        }
        
        $this->logs->render();
    }
}
