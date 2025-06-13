<?php
declare(strict_types=1);

namespace WPVN\Admin;

use WPVN\Analytics;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard page controller.
 *
 * @since 1.0.0
 */
class Dashboard {
    private Analytics $analytics;

    public function __construct(Analytics $analytics) {
        $this->analytics = $analytics;
    }

    public function render(): void {
        $data = $this->analytics->get_daily_visits(7);
        include WPVN_PLUGIN_DIR . 'admin/templates/dashboard.php';
    }
}
