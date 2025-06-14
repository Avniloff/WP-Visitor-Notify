<?php
declare(strict_types=1);
/**
 * Main Plugin Class for WP Visitor Notify
 *
 * This is the core class that initializes the plugin using Singleton pattern.
 * It handles the plugin lifecycle, dependency injection, and component initialization.
 *
 * The Singleton pattern ensures that only one instance of the plugin runs,
 * preventing conflicts and maintaining a centralized state management.
 * * @package    WP_Visitor_Notify
 * @subpackage Includes
 * @since      1.0.0
 * @author     Avniloff Avraham
 */

namespace WPVN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

use WPVN\Admin\Admin as AdminInterface;

/**
 * Main Plugin class
 *
 * Implements Singleton pattern for single plugin instance.
 * Handles plugin initialization, component management, and lifecycle events.
 *
 * @since 1.0.0
 */
class Plugin {

    /**
     * Plugin version
     * 
     * This version is used for cache busting, database migrations,
     * and ensuring compatibility across plugin updates.
     *
     * @since 1.0.0
     * @var string
     */
    public const VERSION = '1.0.0';

    /**
     * Plugin slug for WordPress hooks and options
     *
     * Used as prefix for WordPress hooks, option names,
     * and other WordPress integration points.
     *
     * @since 1.0.0
     * @var string
     */
    public const PLUGIN_SLUG = 'wp-visitor-notify';

    /**
     * Single instance of the plugin (Singleton pattern)
     *
     * @since 1.0.0
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;

    /**
     * Database operations handler
     *
     * @since 1.0.0
     * @var Database|null
     */
    private ?Database $database = null;


    /**
     * Logging system
     *
     * @since 1.0.0
     * @var Logger|null
     */
    private ?Logger $logger = null;

    /**
     * Device and browser detector
     *
     * @since 1.0.0
     * @var Detector|null
     */
    private ?Detector $detector = null;

    /**
     * Visitor tracker
     *
     * @since 1.0.0
     * @var Tracker|null
     */
    private ?Tracker $tracker = null;

    /**
     * Analytics engine
     *
     * @since 1.0.0
     * @var Analytics|null
     */
    private ?Analytics $analytics = null;

    /**
     * Admin interface controller
     *
     * @since 1.0.0
     * @var AdminInterface|null
     */
    private ?AdminInterface $admin = null;

    /**
     * Plugin initialization flag
     *
     * @since 1.0.0
     * @var bool
     */
    private bool $is_initialized = false;

    /**
     * Private constructor to prevent direct instantiation (Singleton pattern)
     *
     * This ensures that the plugin can only be instantiated through
     * the get_instance() method, maintaining single instance control.
     *
     * @since 1.0.0
     */
    private function __construct() {
        // Private constructor prevents direct instantiation
        // All initialization happens in the init() method
    }

    /**
     * Get the single instance of the plugin (Singleton pattern)
     *
     * This is the only way to get the plugin instance. If no instance
     * exists, it creates one. Otherwise, it returns the existing instance.
     *
     * @since 1.0.0
     * @return Plugin The single plugin instance
     */
    public static function get_instance(): Plugin {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize the plugin
     *
     * This method sets up all plugin components and WordPress hooks.
     * It should be called only once, typically from the main plugin file.
     *
     * @since 1.0.0
     * @return void
     */
    public function init(): void {
        // Prevent multiple initialization
        if ($this->is_initialized) {
            return;
        }

        try {
            // Initialize core components in dependency order
            $this->init_logger();
            $this->init_database();
            $this->init_detector();
            $this->init_tracker();
            $this->init_analytics();
            $this->init_admin();

            // Set up WordPress hooks
            $this->setup_basic_hooks();
            $this->setup_frontend_hooks();

            // Load language files
            $this->load_textdomain();

            // Mark as initialized
            $this->is_initialized = true;

            // Log successful initialization
            $this->logger->log('Plugin initialized successfully', 'info', [
                'version' => self::VERSION,
                'php_version' => \PHP_VERSION,
                'components_loaded' => ['logger', 'database', 'detector', 'tracker', 'analytics', 'admin']
            ]);

        } catch (\Exception $e) {
            // Log initialization error
            \error_log('WPVN Plugin initialization failed: ' . $e->getMessage());
            
            // Show admin notice for critical errors
            \add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>WP Visitor Notify:</strong> Plugin initialization failed - ' . \esc_html($e->getMessage());
                echo '</p></div>';
            });
        }
    }

    /**
     * Initialize logging system
     *
     * The logger is initialized first as other components may need
     * to log messages during their initialization.
     *
     * @since 1.0.0
     * @return void
     */
    private function init_logger(): void {
        if (null === $this->logger) {            $this->logger = new Logger();
        }
    }

    /**
     * Initialize database handler
     *
     * Sets up database tables and ensures proper schema version.
     * This is critical for all data operations.
     *
     * @since 1.0.0
     * @return void
     * @throws \Exception If database initialization fails
     */
    private function init_database(): void {
        if (null === $this->database) {
            $this->database = new Database();
            
            // Logger is now independent - no database dependency needed
            
            // Ensure tables exist on first load
            if (!$this->database->tables_exist()) {
                $success = $this->database->create_tables();
                if (!$success) {
                    throw new \Exception('Failed to create database tables');
                }
            }
        }
    }

    /**
     * Initialize device detector
     */
    private function init_detector(): void {
        if (null === $this->detector) {
            $this->detector = new Detector();
        }
    }

    /**
     * Initialize tracker component
     */
    private function init_tracker(): void {
        if (null === $this->tracker && $this->database && $this->detector && $this->logger) {
            $this->tracker = new Tracker($this->database, $this->detector, $this->logger);
        }
    }

    /**
     * Initialize analytics engine
     */
    private function init_analytics(): void {
        if (null === $this->analytics && $this->database) {
            $this->analytics = new Analytics($this->database);
        }
    }

    /**
     * Initialize admin interface
     */
    private function init_admin(): void {
        if (is_admin() && null === $this->admin && $this->analytics) {
            $this->admin = new AdminInterface($this, $this->analytics);
            $this->admin->init();
        }
    }

    /* Placeholder methods for future components were removed */

    /**
     * Set up basic WordPress hooks and filters
     *
     * Registers only the basic hooks for testing.
     * The full version will be added later.
     *
     * @since 1.0.0
     * @return void
     */
    private function setup_basic_hooks(): void {
        // Only basic hooks for testing for now
          // Admin-only hooks
        if (\is_admin()) {
            // When the Admin class is instantiated it already hooks into these
            // actions via Admin::init(). Register them here only when the
            // Admin component is absent to avoid duplicate callbacks.
            if (null === $this->admin) {
                \add_action('admin_menu', [$this, 'setup_admin_menu']);
                \add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
                \add_action('admin_init', [$this, 'register_settings']);
            }
        }

        // NOTE: Lifecycle hooks are registered in main plugin file to avoid duplication

        // Additional frontend and cron hooks will be added in future versions
    }

    /**
     * Set up front-end hooks for tracking.
     */
    private function setup_frontend_hooks(): void {
        if ($this->tracker) {
            add_action('wp_head', [$this->tracker, 'track_visit']);
        }
    }

    /**
     * Load plugin text domain for internationalization
     *
     * @since 1.0.0
     * @return void
     */
    private function load_textdomain(): void {
        load_plugin_textdomain(self::PLUGIN_SLUG, false, dirname(WPVN_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Set up admin menu pages
     *
     * Creates the admin menu structure for the plugin.
     * This includes the main dashboard and sub-pages.
     *
     * @since 1.0.0
     * @return void
     */
    public function setup_admin_menu(): void {
        if ($this->admin) {
            $this->admin->register_menu();
            return;
        }
        // Check user capabilities
        if (!\current_user_can('manage_options')) {
            return;
        }

        // Main menu page
        \add_menu_page(
            \__('Visitor Analytics', self::PLUGIN_SLUG),     // Page title
            \__('Visitor Analytics', self::PLUGIN_SLUG),     // Menu title
            'manage_options',                                  // Capability
            self::PLUGIN_SLUG,                                // Menu slug
            [$this, 'render_dashboard_page'],                 // Callback
            'dashicons-visibility',                           // Icon
            30                                                // Position
        );

        // Submenu pages
        \add_submenu_page(
            self::PLUGIN_SLUG,
            \__('Settings', self::PLUGIN_SLUG),
            \__('Settings', self::PLUGIN_SLUG),
            'manage_options',
            self::PLUGIN_SLUG . '-settings',
            [$this, 'render_settings_page']
        );

        \add_submenu_page(
            self::PLUGIN_SLUG,
            \__('Notifications', self::PLUGIN_SLUG),
            \__('Notifications', self::PLUGIN_SLUG),
            'manage_options',
            self::PLUGIN_SLUG . '-notifications',
            [$this, 'render_notifications_page']
        );

        \add_submenu_page(
            self::PLUGIN_SLUG,
            \__('Logs', self::PLUGIN_SLUG),
            \__('Logs', self::PLUGIN_SLUG),
            'manage_options',
            self::PLUGIN_SLUG . '-logs',            [$this, 'render_logs_page']
        );
    }

    /**
     * Enqueue admin assets (CSS and JavaScript)
     *
     * @since 1.0.0
     * @param string $hook The current admin page hook
     * @return void
     */
    public function enqueue_admin_assets(string $hook): void {
        if ($this->admin) {
            $this->admin->enqueue_assets($hook);
            return;
        }
    }

    /**
     * Register plugin settings
     *
     * @since 1.0.0
     * @return void
     */
    public function register_settings(): void {
        if ($this->admin) {
            $this->admin->register_settings();
            return;
        }
    }

    /* Placeholder settings helpers removed */

    /**
     * Plugin activation handler
     *
     * Called when the plugin is activated. Simplified version for learning.
     *
     * @since 1.0.0
     * @return void
     */
    public function on_activation(): void {
        try {
            // Ensure database is initialized
            if (null === $this->database) {
                $this->init_database();
            }

            // Create default options (simple basic settings)
            $default_options = [
                'plugin_version' => self::VERSION,
                'activation_time' => \current_time('mysql', true)
            ];
            \add_option(self::PLUGIN_SLUG . '_options', $default_options);

            // Log successful activation (use error_log during activation as logger may not be initialized)
            \error_log('[' . \date('Y-m-d H:i:s') . '] WPVN.INFO: Plugin activated successfully | Context: {"version":"' . self::VERSION . '","options_created":true}');
        } catch (\Exception $e) {
            \error_log('WPVN Plugin activation failed: ' . $e->getMessage());
            \wp_die('Plugin activation failed: ' . $e->getMessage());
        }
    }

    /**
     * Plugin deactivation handler
     *
     * Called when the plugin is deactivated. Simplified version for learning.
     *
     * @since 1.0.0
     * @return void
     */
    public function on_deactivation(): void {
        // For now we simply log the deactivation
        if ($this->logger) {
            $this->logger->log('Plugin deactivated', 'info');
        }        
        // Cron cleanup will be added when scheduled tasks are implemented
    }

    /* Placeholder cron scheduling method removed */

    /**
     * Render dashboard admin page
     * Simple placeholder for testing
     *
     * @since 1.0.0
     * @return void
     */
    public function render_dashboard_page(): void {
        if ($this->admin) {
            $this->admin->render_dashboard();
            return;
        }
        echo '<div class="wrap"><h1>' . esc_html__('WP Visitor Notify Dashboard', 'wp-visitor-notify') . '</h1></div>';
    }

    /**
     * Render settings admin page
     * Simple placeholder for testing
     *
     * @since 1.0.0
     * @return void
     */
    public function render_settings_page(): void {
        if ($this->admin) {
            $this->admin->render_settings();
            return;
        }
        echo '<div class="wrap"><h1>' . esc_html__('Settings', 'wp-visitor-notify') . '</h1></div>';
    }

    /**
     * Render notifications admin page
     * Simple placeholder for testing
     *
     * @since 1.0.0
     * @return void
     */
    public function render_notifications_page(): void {
        if ($this->admin) {
            $this->admin->render_notifications();
            return;
        }
        echo '<div class="wrap"><h1>' . esc_html__('Notifications', 'wp-visitor-notify') . '</h1></div>';
    }

    /**
     * Render logs admin page
     * Simple placeholder for testing
     *
     * @since 1.0.0
     * @return void
     */
    public function render_logs_page(): void {
        if ($this->admin) {
            $this->admin->render_logs();
            return;
        }
        echo '<div class="wrap"><h1>' . esc_html__('Logs', 'wp-visitor-notify') . '</h1></div>';
    }

    /**
     * Get component instance by name
     *
     * Provides access to initialized components for other classes.
     * This is our simple dependency injection mechanism.
     *
     * @since 1.0.0
     * @param string $component Component name
     * @return object|null Component instance or null if not found
     */
    public function get_component(string $component): ?object {
        switch ($component) {
            case 'database':
                return $this->database;
            case 'logger':
                return $this->logger;
            case 'tracker':
                return $this->tracker;
            case 'analytics':
                return $this->analytics;
            case 'detector':
                return $this->detector;
            case 'admin':
                return $this->admin;
            default:
                return null;
        }
    }

    /**
     * Get plugin version
     *
     * @since 1.0.0
     * @return string Plugin version
     */
    public function get_version(): string {
        return self::VERSION;
    }

    /**
     * Check if plugin is initialized
     *
     * @since 1.0.0
     * @return bool True if initialized, false otherwise
     */
    public function is_initialized(): bool {
        return $this->is_initialized;
    }

    /**
     * Prevent cloning of the instance (Singleton pattern)
     *
     * @since 1.0.0
     * @return void
     */
    private function __clone() {        // Prevent cloning
    }

    /**
     * Prevent unserialization of the instance (Singleton pattern)
     *
     * @since 1.0.0
     * @return void
     */
    public function __wakeup() {
        throw new \Exception('Cannot unserialize singleton');
    }
}
