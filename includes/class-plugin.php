<?php
/**
 * Main Plugin Class for WP Visitor Notify
 *
 * This is the core class that initializes the plugin using Singleton pattern.
 * It handles the plugin lifecycle, dependency injection, and component initialization.
 *
 * The Singleton pattern ensures that only one instance of the plugin runs,
 * preventing conflicts and maintaining a centralized state management.
 *
 * @package    WP_Visitor_Notify
 * @subpackage Includes
 * @since      1.0.0
 * @author     Your Name
 */

namespace WPVN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

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
     * Visitor tracking engine
     * TODO: Будет добавлен когда создадим класс Tracker
     *
     * @since 1.0.0
     * @var object|null
     */
    private ?object $tracker = null;

    /**
     * Analytics data processor
     * TODO: Будет добавлен когда создадим класс Analytics
     *
     * @since 1.0.0
     * @var object|null
     */
    private ?object $analytics = null;

    /**
     * Device/browser detection
     * TODO: Будет добавлен когда создадим класс Detector
     *
     * @since 1.0.0
     * @var object|null
     */
    private ?object $detector = null;

    /**
     * Notification engine
     * TODO: Будет добавлен когда создадим класс Notifier
     *
     * @since 1.0.0
     * @var object|null
     */
    private ?object $notifier = null;

    /**
     * Logging system
     *
     * @since 1.0.0
     * @var Logger|null
     */
    private ?Logger $logger = null;

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
            // Пока инициализируем только основные компоненты для обучения
            $this->init_logger();
            $this->init_database();
            
            // TODO: Добавим позже когда создадим эти классы
            // $this->init_detector();
            // $this->init_analytics();
            // $this->init_tracker();
            // $this->init_notifier();

            // Set up WordPress hooks (только базовые пока)
            $this->setup_basic_hooks();

            // Load language files for internationalization
            // TODO: Добавим когда будем готовы к переводам
            // $this->load_textdomain();

            // Mark as initialized
            $this->is_initialized = true;

            // Log successful initialization
            $this->logger->log('Plugin initialized successfully (basic mode)', 'info', [
                'version' => self::VERSION,
                'php_version' => \PHP_VERSION,
                'components_loaded' => ['logger', 'database']
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
            }        }
    }

    /**
     * Initialize device/browser detector
     * TODO: Создадим когда будем готовы
     *
     * @since 1.0.0
     * @return void
     */
    /*
    private function init_detector(): void {
        if (null === $this->detector) {
            $this->detector = new Detector($this->logger);
        }
    }
    */

    /**
     * Initialize analytics processor
     * TODO: Создадим когда будем готовы
     *
     * @since 1.0.0
     * @return void
     */
    /*
    private function init_analytics(): void {
        if (null === $this->analytics) {
            $this->analytics = new Analytics($this->database, $this->logger);
        }
    }
    */

    /**
     * Initialize visitor tracker
     * TODO: Создадим когда будем готовы
     *
     * @since 1.0.0
     * @return void
     */
    /*
    private function init_tracker(): void {
        if (null === $this->tracker) {
            $this->tracker = new Tracker(
                $this->database,
                $this->detector,
                $this->logger
            );
        }
    }
    */

    /**
     * Initialize notification system
     * TODO: Создадим когда будем готовы
     *
     * @since 1.0.0
     * @return void
     */
    /*
    private function init_notifier(): void {
        if (null === $this->notifier) {
            $this->notifier = new Notifier(
                $this->database,
                $this->analytics,
                $this->logger
            );        }
    }
    */

    /**
     * Set up basic WordPress hooks and filters
     *
     * Регистрирует только базовые хуки для тестирования.
     * Полная версия будет добавлена позже.
     *
     * @since 1.0.0
     * @return void
     */
    private function setup_basic_hooks(): void {
        // Пока только базовые хуки для тестирования
        
        // Admin-only hooks
        if (\is_admin()) {
            // Initialize admin interface
            \add_action('admin_menu', [$this, 'setup_admin_menu']);
            \add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
            
            // Settings API hooks
            \add_action('admin_init', [$this, 'register_settings']);
        }

        // NOTE: Lifecycle hooks are registered in main plugin file to avoid duplication
        
        // TODO: Добавим позже когда создадим нужные классы
        /*
        // Frontend tracking hooks (только для не-админ страниц)
        if (!\is_admin()) {
            \add_action('wp_head', [$this->tracker, 'enqueue_tracking_script'], 10);
            \add_action('wp_footer', [$this->tracker, 'render_tracking_code'], 20);
        }

        // AJAX hooks для трекинга данных
        \add_action('wp_ajax_wpvn_track_page', [$this->tracker, 'handle_ajax_tracking']);
        \add_action('wp_ajax_nopriv_wpvn_track_page', [$this->tracker, 'handle_ajax_tracking']);

        // Cron jobs для фоновой обработки
        \add_action('wpvn_daily_cleanup', [$this->database, 'cleanup_old_data']);
        \add_action('wpvn_hourly_aggregation', [$this->analytics, 'update_aggregated_data']);        \add_action('wpvn_notification_check', [$this->notifier, 'check_notification_rules']);
        */
    }

    /**
     * Load plugin text domain for internationalization
     * TODO: Добавим когда будем готовы к переводам
     *
     * @since 1.0.0
     * @return void
     */
    /*
    private function load_textdomain(): void {
        \load_plugin_textdomain(
            self::PLUGIN_SLUG,
            false,
            \dirname(\plugin_basename(WPVN_PLUGIN_FILE)) . '/languages'
        );
    }
    */

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
     * Пока заглушка - добавим позже
     *
     * @since 1.0.0
     * @param string $hook The current admin page hook
     * @return void
     */
    public function enqueue_admin_assets(string $hook): void {
        // TODO: Добавим когда создадим CSS/JS файлы
        // Method ready for asset enqueuing when needed
    }

    /**
     * Register plugin settings
     * Пока заглушка - добавим позже
     *
     * @since 1.0.0
     * @return void
     */
    public function register_settings(): void {
        // TODO: Добавим настройки позже        // Method ready for WordPress Settings API integration
    }

    /**
     * TODO: Методы настроек - добавим позже когда будем готовы
     */
    
    /*
    private function register_tracking_settings(): void {
        // ...код настроек трекинга...
    }

    private function register_privacy_settings(): void {
        // ...код настроек приватности...
    }

    private function register_notification_settings(): void {
        // ...код настроек уведомлений...
    }

    public function render_checkbox_field(array $args): void {
        // ...код чекбокса...
    }

    public function render_email_field(array $args): void {
        // ...код email поля...
    }

    public function validate_settings(array $input): array {
        // ...код валидации...    }
    */

    /**
     * Plugin activation handler
     *
     * Called when the plugin is activated. Упрощенная версия для обучения.
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

            // Create default options (простые базовые настройки)
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
     * Called when the plugin is deactivated. Упрощенная версия для обучения.
     *
     * @since 1.0.0
     * @return void
     */
    public function on_deactivation(): void {
        // Пока просто логируем деактивацию
        if ($this->logger) {
            $this->logger->log('Plugin deactivated', 'info');
        }        
        // TODO: Добавим очистку cron-задач когда они будут
        // TODO: Добавим flush_rewrite_rules() когда будет нужно
    }

    /**
     * Schedule cron events
     * TODO: Добавим когда будем готовы к фоновым задачам
     *
     * @since 1.0.0
     * @return void
     */
    /*
    private function schedule_cron_events(): void {
        // Daily cleanup task
        if (!\wp_next_scheduled('wpvn_daily_cleanup')) {
            \wp_schedule_event(\time(), 'daily', 'wpvn_daily_cleanup');
        }

        // Hourly analytics aggregation
        if (!\wp_next_scheduled('wpvn_hourly_aggregation')) {
            \wp_schedule_event(\time(), 'hourly', 'wpvn_hourly_aggregation');
        }

        // Notification checks every 5 minutes
        if (!\wp_next_scheduled('wpvn_notification_check')) {
            \wp_schedule_event(\time(), 'wpvn_5min', 'wpvn_notification_check');
        }    }
    */

    /**
     * Render dashboard admin page
     * Пока простая заглушка для тестирования
     *
     * @since 1.0.0
     * @return void
     */
    public function render_dashboard_page(): void {
        echo '<div class="wrap">';
        echo '<h1>' . \esc_html__('WP Visitor Notify Dashboard', 'wp-visitor-notify') . '</h1>';
        echo '<p>Добро пожаловать в WP Visitor Notify! Это базовая версия для обучения.</p>';
        
        // Покажем статус базы данных
        if ($this->database && $this->database->tables_exist()) {
            echo '<div class="notice notice-success"><p>✅ База данных инициализирована правильно!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>❌ Проблема с базой данных!</p></div>';
        }
        
        echo '</div>';
    }

    /**
     * Render settings admin page
     * Пока простая заглушка для тестирования
     *
     * @since 1.0.0
     * @return void
     */
    public function render_settings_page(): void {
        echo '<div class="wrap">';
        echo '<h1>' . \esc_html__('Settings', 'wp-visitor-notify') . '</h1>';
        echo '<p>Настройки будут добавлены позже.</p>';
        echo '</div>';
    }

    /**
     * Render notifications admin page
     * Пока простая заглушка для тестирования
     *
     * @since 1.0.0
     * @return void
     */
    public function render_notifications_page(): void {
        echo '<div class="wrap">';
        echo '<h1>' . \esc_html__('Notifications', 'wp-visitor-notify') . '</h1>';
        echo '<p>Уведомления будут добавлены позже.</p>';
        echo '</div>';
    }

    /**
     * Render logs admin page
     * Пока простая заглушка для тестирования
     *
     * @since 1.0.0
     * @return void
     */
    public function render_logs_page(): void {
        echo '<div class="wrap">';
        echo '<h1>' . \esc_html__('Logs', 'wp-visitor-notify') . '</h1>';
        echo '<p>Логи будут добавлены позже.</p>';
        
        // Покажем базовый тест логгера
        if ($this->logger) {
            echo '<h3>Тест логгера:</h3>';
            $this->logger->log('Тестовое сообщение из админки', 'info', ['source' => 'admin_page']);
            echo '<p>✅ Лог записан! (проверьте в error_log или базе данных)</p>';
        }
        
        echo '</div>';
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
            case 'tracker':
                return $this->tracker;
            case 'analytics':
                return $this->analytics;
            case 'detector':
                return $this->detector;
            case 'notifier':
                return $this->notifier;
            case 'logger':
                return $this->logger;
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
