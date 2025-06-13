<?php
/**
 * WordPress Functions Stubs for IDE
 * 
 * This file provides function definitions for common WordPress functions
 * to help IDE understand WordPress API without errors.
 * 
 * DO NOT INCLUDE IN PRODUCTION!
 */

if (false) { // This ensures the code never executes    // WordPress Core Functions
    function plugin_dir_path(string $file): string { return ''; }
    function plugin_dir_url(string $file): string { return ''; }
    function plugin_basename(string $file): string { return ''; }
    function get_bloginfo(string $show = ''): string { return ''; }
    function add_action(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): bool { return true; }
    function add_menu_page(string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = null, string $icon_url = '', int $position = null): string { return ''; }
    function add_submenu_page(string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = null): string { return ''; }
    function register_activation_hook(string $file, callable $callback): void {}
    function register_deactivation_hook(string $file, callable $callback): void {}
    function register_uninstall_hook(string $file, callable $callback): void {}
    function current_user_can(string $capability): bool { return true; }
    function is_admin(): bool { return true; }
    function wp_die(string $message, string $title = '', array $args = []): void {}
    function wp_next_scheduled(string $hook, array $args = []): int|false { return false; }
    function wp_unschedule_event(int $timestamp, string $hook, array $args = []): bool { return true; }
    function wp_json_encode($data, int $options = 0, int $depth = 512): string|false { return ''; }
    function current_time(string $type, bool $gmt = false): string|int { return ''; }
      // WordPress Options API
    function get_option(string $option, $default = false) { return $default; }
    function update_option(string $option, $value, bool $autoload = null): bool { return true; }
    function add_option(string $option, $value = '', string $deprecated = '', bool $autoload = 'yes'): bool { return true; }
    function delete_option(string $option): bool { return true; }
      // WordPress Escaping Functions
    function esc_html(string $text): string { return $text; }
    function esc_html__(string $text, string $domain = 'default'): string { return $text; }
    function esc_attr(string $text): string { return $text; }
    function esc_url(string $url, array $protocols = null, string $_context = 'display'): string { return $url; }
      // WordPress Internationalization
    function __(string $text, string $domain = 'default'): string { return $text; }
    function _e(string $text, string $domain = 'default'): void {}
    function _x(string $text, string $context, string $domain = 'default'): string { return $text; }
    function _n(string $single, string $plural, int $number, string $domain = 'default'): string { return $number === 1 ? $single : $plural; }
      // WordPress Database
    function dbDelta(string|array $queries): array { return []; }
    
    // WordPress Constants
    if (!defined('ABSPATH')) {
        define('ABSPATH', '/path/to/wordpress/');
    }
    
    // WordPress Global Variables
    global $wpdb;    class wpdb {
        public string $prefix = 'wp_';
        public function prepare(string $query, ...$args): string { return $query; }
        public function query(string $query): int|false { return 1; }
        public function get_results(string $query, string $output = OBJECT): array|null { return []; }
        public function get_var(string $query, int $col = 0, int $row = 0) { return null; }
        public function insert(string $table, array $data, array $format = null): int|false { return 1; }
        public function update(string $table, array $data, array $where, array $format = null, array $where_format = null): int|false { return 1; }
        public function delete(string $table, array $where, array $where_format = null): int|false { return 1; }
    }
}
