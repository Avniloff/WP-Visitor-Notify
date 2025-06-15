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
    private Logs $logs;
    private Analytics $analytics;

    public function __construct(Plugin $plugin, Analytics $analytics) {
        $this->analytics = $analytics;
        $this->settings = new Settings();
        $this->dashboard = new Dashboard($analytics);
        $this->logs = new Logs($plugin->get_logger());
    }

    public function init(): void {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Initialize settings
        $this->settings->init();
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
    }public function register_menu(): void {
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
        // Only load on our plugin pages
        if (strpos($hook, Plugin::PLUGIN_SLUG) === false) {
            return;
        }

        // Enqueue common assets
        wp_enqueue_style('wpvn-common', WPVN_PLUGIN_URL . 'admin/assets/css/common.css', [], WPVN_VERSION);
        wp_enqueue_script('wpvn-common', WPVN_PLUGIN_URL . 'admin/assets/js/common.js', ['jquery'], WPVN_VERSION, true);

        // Localize script for AJAX
        wp_localize_script('wpvn-common', 'wpvn_ajax', [
            'nonce' => wp_create_nonce('wpvn_ajax_nonce'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ]);

        // Enqueue page-specific assets
        if ($hook === 'toplevel_page_' . Plugin::PLUGIN_SLUG) {
            // Dashboard page
            wp_enqueue_style('wpvn-dashboard', WPVN_PLUGIN_URL . 'admin/assets/css/dashboard.css', ['wpvn-common'], WPVN_VERSION);
            wp_enqueue_script('wpvn-dashboard', WPVN_PLUGIN_URL . 'admin/assets/js/dashboard.js', ['wpvn-common'], WPVN_VERSION, true);
            
            // Enqueue Chart.js
            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js', [], '4.4.0', true);
            
        } elseif ($hook === 'visitor-notify_page_' . Plugin::PLUGIN_SLUG . '-settings') {
            // Settings page
            wp_enqueue_style('wpvn-settings', WPVN_PLUGIN_URL . 'admin/assets/css/settings.css', ['wpvn-common'], WPVN_VERSION);
            wp_enqueue_script('wpvn-settings', WPVN_PLUGIN_URL . 'admin/assets/js/settings.js', ['wpvn-common'], WPVN_VERSION, true);
            
        } elseif ($hook === 'visitor-notify_page_' . Plugin::PLUGIN_SLUG . '-logs') {
            // Logs page
            wp_enqueue_style('wpvn-logs', WPVN_PLUGIN_URL . 'admin/assets/css/logs.css', ['wpvn-common'], WPVN_VERSION);
            wp_enqueue_script('wpvn-logs', WPVN_PLUGIN_URL . 'admin/assets/js/logs.js', ['wpvn-common'], WPVN_VERSION, true);
            
            // Enqueue DataTables
            wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css', [], '1.13.6');
            wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', ['jquery'], '1.13.6', true);
        }
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

    /**
     * Register AJAX handlers for frontend functionality
     */
    private function register_ajax_handlers(): void {
        // Dashboard AJAX handlers
        add_action('wp_ajax_wpvn_get_dashboard_data', [$this, 'ajax_get_dashboard_data']);
        add_action('wp_ajax_wpvn_export_dashboard', [$this, 'ajax_export_dashboard']);
        
        // Settings AJAX handlers
        add_action('wp_ajax_wpvn_save_settings', [$this, 'ajax_save_settings']);
        add_action('wp_ajax_wpvn_reset_settings', [$this, 'ajax_reset_settings']);
        add_action('wp_ajax_wpvn_test_database', [$this, 'ajax_test_database']);
        add_action('wp_ajax_wpvn_clear_logs', [$this, 'ajax_clear_logs']);
        
        // Logs AJAX handlers
        add_action('wp_ajax_wpvn_get_logs', [$this, 'ajax_get_logs']);
        add_action('wp_ajax_wpvn_get_log_details', [$this, 'ajax_get_log_details']);
        add_action('wp_ajax_wpvn_delete_log', [$this, 'ajax_delete_log']);
        add_action('wp_ajax_wpvn_bulk_delete_logs', [$this, 'ajax_bulk_delete_logs']);
        add_action('wp_ajax_wpvn_export_logs', [$this, 'ajax_export_logs']);
    }

    /**
     * AJAX handler for getting dashboard data
     */
    public function ajax_get_dashboard_data(): void {
        check_ajax_referer('wpvn_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $time_period = (int) ($_POST['time_period'] ?? 7);
        
        try {
            $data = [
                'metrics' => [
                    'total_visits' => $this->analytics->get_total_visits(),
                    'unique_visitors' => $this->analytics->get_unique_visitors(),
                    'page_views' => $this->analytics->get_total_visits(),
                    'avg_session_duration' => $this->analytics->get_session_duration(),
                    'bounce_rate' => 65.2, // Placeholder
                    'online_visitors' => $this->get_online_visitors()
                ],
                'charts' => [
                    'visits' => [
                        'labels' => array_column($this->analytics->get_daily_visits($time_period), 'date'),
                        'data' => array_column($this->analytics->get_daily_visits($time_period), 'visits')
                    ],
                    'devices' => [
                        'data' => array_values($this->analytics->get_device_stats($time_period))
                    ]
                ],
                'recent_visitors' => $this->analytics->get_recent_visitors(10)
            ];
              wp_send_json_success($data);
        } catch (\Exception $e) {
            wp_send_json_error('Failed to load dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler for saving settings
     */
    public function ajax_save_settings(): void {
        check_ajax_referer('wpvn_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            // Parse settings data
            parse_str($_POST['settings'] ?? '', $settings);
            
            // Validate and save settings
            $updated = update_option('wpvn_settings', $settings);
            
            if ($updated) {
                wp_send_json_success('Settings saved successfully');            } else {
                wp_send_json_error('No changes were made');
            }
        } catch (\Exception $e) {
            wp_send_json_error('Failed to save settings: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler for getting logs (DataTables)
     */
    public function ajax_get_logs(): void {
        check_ajax_referer('wpvn_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            // Get DataTables parameters
            $draw = (int) ($_POST['draw'] ?? 1);
            $start = (int) ($_POST['start'] ?? 0);
            $length = (int) ($_POST['length'] ?? 25);
            
            // Get filters
            $filters = [
                'date_from' => sanitize_text_field($_POST['date_from'] ?? ''),
                'date_to' => sanitize_text_field($_POST['date_to'] ?? ''),
                'level' => sanitize_text_field($_POST['level'] ?? ''),
                'ip_address' => sanitize_text_field($_POST['ip_address'] ?? ''),
                'country' => sanitize_text_field($_POST['country'] ?? '')
            ];
            
            // Get logs from database (this would need to be implemented)
            $logs = $this->get_logs_for_datatable($start, $length, $filters);
            $total_logs = $this->get_total_logs_count($filters);
            
            $response = [
                'draw' => $draw,
                'recordsTotal' => $total_logs,
                'recordsFiltered' => $total_logs,
                'data' => $logs
            ];
              wp_send_json($response);
        } catch (\Exception $e) {
            wp_send_json_error('Failed to load logs: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler for exporting logs
     */
    public function ajax_export_logs(): void {
        check_ajax_referer('wpvn_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $this->logs->export_logs();
    }

    /**
     * Get online visitors count
     */
    private function get_online_visitors(): int {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'visitor_logs';
        $five_minutes_ago = date('Y-m-d H:i:s', time() - 300);
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT ip_address) FROM $table_name WHERE timestamp >= %s",
            $five_minutes_ago
        ));
        
        return (int) $count;
    }

    /**
     * Get logs for DataTable (placeholder implementation)
     */
    private function get_logs_for_datatable(int $start, int $length, array $filters): array {
        // This would need to be implemented to fetch from the actual logs table
        return [];
    }

    /**
     * Get total logs count (placeholder implementation)
     */
    private function get_total_logs_count(array $filters): int {
        // This would need to be implemented to count from the actual logs table
        return 0;
    }
}
