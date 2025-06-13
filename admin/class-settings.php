<?php
declare(strict_types=1);

namespace WPVN\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings page controller.
 * Uses the WordPress Settings API.
 *
 * @since 1.0.0
 */
class Settings {
    public function register(): void {
        \register_setting('wpvn_settings_group', 'wpvn_settings');
    }

    public function render(): void {
        include WPVN_PLUGIN_DIR . 'admin/templates/settings.php';
    }
}
