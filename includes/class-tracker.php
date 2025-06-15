<?php
declare(strict_types=1);

namespace WPVN;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Visitor tracker responsible for creating sessions
 * and recording page views.
 *
 * @since 1.0.0
 */
class Tracker {
    private Database $db;
    private Detector $detector;
    private Logger $logger;

    public function __construct(Database $db, Detector $detector, Logger $logger) {
        $this->db = $db;
        $this->detector = $detector;
        $this->logger = $logger;
    }    /**
     * Entry point for tracking a page view.
     * Runs on every front-end page load.
     */
    public function track_visit(): void {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        try {
            // Check if tables exist before tracking
            if (!$this->db->tables_exist()) {
                $this->logger->error('Database tables missing for tracking', [], 'tracker');
                return;
            }
            
            $session_id = $this->get_or_create_session();
            if ($session_id > 0) {
                $this->record_page_view($session_id);
                
                // Send notification if enabled
                $this->send_visitor_notification($session_id);
            }
        } catch (\Exception $e) {
            $this->logger->error('Tracking failed: ' . $e->getMessage(), [], 'tracker');
        }
    }

    private function get_or_create_session(): int {
        $cookie = 'wpvn_session';
        if (!empty($_COOKIE[$cookie])) {
            $session_key = sanitize_text_field($_COOKIE[$cookie]);
            $row = $this->db->get_wpdb()->get_row(
                $this->db->get_wpdb()->prepare(
                    "SELECT id FROM {$this->db->get_tables()['sessions']} WHERE session_key=%s LIMIT 1",
                    $session_key
                )
            );
            if ($row) {
                $this->update_session_activity((int)$row->id);
                return (int)$row->id;
            }
        }

        $session_key = wp_generate_password(32, false, false);
        setcookie($cookie, $session_key, time() + DAY_IN_SECONDS * 30, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

        $ip = $this->get_ip();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $this->db->get_wpdb()->insert(
            $this->db->get_tables()['sessions'],
            [
                'session_key' => $session_key,
                'ip_hash' => wp_hash($ip),
                'user_agent' => $ua,
                'device_type' => $this->detector->get_device_type($ua),
                'browser' => $this->detector->get_browser($ua),
                'os' => $this->detector->get_os($ua),
                'created_at' => current_time('mysql', true),
                'last_activity' => current_time('mysql', true)
            ],
            ['%s','%s','%s','%s','%s','%s','%s','%s']
        );
        $session_id = (int)$this->db->get_wpdb()->insert_id;
        $this->logger->debug('New session created', ['session_id' => $session_id], 'tracker');
        return $session_id;
    }

    private function update_session_activity(int $id): void {
        $this->db->get_wpdb()->update(
            $this->db->get_tables()['sessions'],
            ['last_activity' => current_time('mysql', true)],
            ['id' => $id],
            ['%s'],
            ['%d']
        );
    }

    private function record_page_view(int $session_id): void {
        global $wp;
        $url = home_url(add_query_arg([], $wp->request));
        $title = wp_get_document_title();

        $this->db->get_wpdb()->insert(
            $this->db->get_tables()['page_views'],
            [
                'session_id' => $session_id,
                'url' => $url,
                'title' => $title,
                'viewed_at' => current_time('mysql', true)
            ],
            ['%d','%s','%s','%s']
        );
        $this->logger->debug('Page view recorded', ['session' => $session_id, 'url' => $url], 'tracker');
    }    private function get_ip(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    /**
     * Send visitor notification
     *
     * @param int $session_id Session ID
     * @return void
     */
    private function send_visitor_notification(int $session_id): void {
        try {
            // Get plugin instance to access notification system
            $plugin = Plugin::get_instance();
            $notification = $plugin->get_notification();
            
            if (!$notification) {
                return; // Notification system not available
            }
            
            // Get session data for notification
            $session_data = $this->get_session_data($session_id);
            if (!$session_data) {
                return;
            }
            
            // Prepare visitor data for notification
            $visitor_data = [
                'session_id' => $session_id,
                'device_type' => $session_data['device_type'] ?? '',
                'browser' => $session_data['browser'] ?? '',
                'os' => $session_data['os'] ?? '',
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'timestamp' => current_time('mysql', true)
            ];
            
            // Send notification
            $notification->send_visitor_notification($visitor_data);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to send visitor notification: ' . $e->getMessage(), [], 'tracker');
        }
    }

    /**
     * Get session data by ID
     *
     * @param int $session_id Session ID
     * @return array|null Session data or null if not found
     */
    private function get_session_data(int $session_id): ?array {
        $sessions_table = $this->db->get_tables()['sessions'];
        
        $session = $this->db->get_wpdb()->get_row(
            $this->db->get_wpdb()->prepare(
                "SELECT * FROM {$sessions_table} WHERE id = %d LIMIT 1",
                $session_id
            ),
            ARRAY_A
        );
        
        return $session ?: null;
    }
}
