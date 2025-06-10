<?php
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

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
// Определяем глобальные константы для использования по всему плагину
define('WPVN_VERSION', '1.0.0');
define('WPVN_PLUGIN_FILE', __FILE__);
define('WPVN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPVN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPVN_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check PHP version
// Проверяем версию PHP - если меньше 8.2, показываем ошибку и останавливаем загрузку
if (version_compare(PHP_VERSION, '8.2', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>WordPress Visitor Notify:</strong> This plugin requires PHP 8.2 or higher. ';
        echo 'You are running PHP ' . PHP_VERSION . '. Please upgrade PHP to activate this plugin.';
        echo '</p></div>';
    });
    return;
}

// Check WordPress version
// Проверяем версию WordPress - если меньше 6.2, показываем ошибку и останавливаем загрузку
global $wp_version;
if (version_compare($wp_version, '6.2', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>WordPress Visitor Notify:</strong> This plugin requires WordPress 6.2 or higher. ';
        echo 'You are running WordPress ' . get_bloginfo('version') . '. Please upgrade WordPress to activate this plugin.';
        echo '</p></div>';
    });    return;
}

// СОБСТВЕННЫЙ АВТОЗАГРУЗЧИК
// Регистрируем функцию для автоматической загрузки наших классов при их использовании
spl_autoload_register(function ($class) {
    // Проверяем, что класс принадлежит нашему namespace
    if (strpos($class, 'WPVN\\') === 0) {
        // Убираем namespace и заменяем подчеркивания на дефисы
        $class_name = str_replace(['WPVN\\', '_'], ['', '-'], $class);
        
        // Формируем путь к файлу класса
        $file = WPVN_PLUGIN_DIR . 'includes/class-' . strtolower($class_name) . '.php';
        
        // Загружаем файл, если он существует
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Initialize the plugin
// Создаем экземпляр главного класса через Singleton pattern и инициализируем
function wpvn_init(): void {
    // Получаем единственный экземпляр плагина
    $plugin = WPVN\Plugin::get_instance();
    
    // Инициализируем все компоненты
    $plugin->init();
}

// Hook into WordPress
// Запускаем плагин после загрузки всех плагинов WordPress
add_action('plugins_loaded', 'wpvn_init');

// Activation hook
// Срабатывает при нажатии "Активировать" в админке
register_activation_hook(__FILE__, 'wpvn_activate');
function wpvn_activate(): void {
    // Получаем экземпляр плагина
    $plugin = WPVN\Plugin::get_instance();
    
    // Выполняем процедуры активации
    $plugin->on_activation();
}

// Deactivation hook
// Срабатывает при нажатии "Деактивировать" в админке  
register_deactivation_hook(__FILE__, 'wpvn_deactivate');
function wpvn_deactivate(): void {
    // Получаем экземпляр плагина
    $plugin = WPVN\Plugin::get_instance();
    
    // Выполняем процедуры деактивации
    $plugin->on_deactivation();
}

// Uninstall hook
// Срабатывает при полном удалении плагина - удаляем ВСЕ данные
register_uninstall_hook(__FILE__, ['WPVN\Uninstaller', 'uninstall']);