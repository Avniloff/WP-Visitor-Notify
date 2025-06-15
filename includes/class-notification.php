<?php
declare(strict_types=1);
/**
 * Notification handler for WP Visitor Notify Plugin
 *
 * Handles email notifications when visitors access the site.
 * Manages notification rules, thresholds, and email sending.
 *
 * @package    WP_Visitor_Notify
 * @subpackage Includes
 * @since      1.0.0
 * @author     Avniloff Avraham
 */

namespace WPVN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Notification system class
 *
 * Handles email notifications based on visitor activity and rules.
 *
 * @since 1.0.0
 */
class Notification {

    /**
     * Database instance
     *
     * @since 1.0.0
     * @var Database
     */
    private Database $db;

    /**
     * Logger instance
     *
     * @since 1.0.0
     * @var Logger
     */
    private Logger $logger;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param Database $db Database instance
     * @param Logger $logger Logger instance
     */
    public function __construct(Database $db, Logger $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Send notification when a visitor is tracked
     *
     * @since 1.0.0
     * @param array $visitor_data Visitor data from tracking
     * @return void
     */
    public function send_visitor_notification(array $visitor_data): void {
        try {
            // Get plugin settings
            $settings = \get_option('wpvn_settings', []);
            
            // Check if notifications are enabled and email is set
            if (empty($settings['notification_email']) || !is_email($settings['notification_email'])) {
                return;
            }

            // Check each notification type from settings
            $this->check_and_send_notifications($visitor_data, $settings);

        } catch (\Exception $e) {
            $this->logger->error('Notification sending failed: ' . $e->getMessage(), [], 'notification');
        }
    }

    /**
     * Check and send notifications based on settings
     *
     * @since 1.0.0
     * @param array $visitor_data Visitor data
     * @param array $settings Plugin settings
     * @return void
     */
    private function check_and_send_notifications(array $visitor_data, array $settings): void {
        $email = $settings['notification_email'];
        
        // Check new visitor notifications
        if (!empty($settings['enable_new_visitor_notifications'])) {
            $this->send_new_visitor_notification($visitor_data, $email);
        }
        
        // Check threshold notifications
        if (!empty($settings['enable_threshold_notifications'])) {
            $threshold = (int) ($settings['visitor_threshold_count'] ?? 100);
            if ($this->check_visitor_threshold($threshold)) {
                $this->send_threshold_notification($visitor_data, $email, $threshold);
            }
        }
        
        // Check new device notifications
        if (!empty($settings['enable_new_device_notifications'])) {
            if (!empty($visitor_data['device_type']) && $this->is_new_device_type($visitor_data['device_type'])) {
                $this->send_new_device_notification($visitor_data, $email);
            }
        }
    }

    /**
     * Get active notification rules
     *
     * @since 1.0.0
     * @return array Array of active notification rules
     */
    public function get_active_notification_rules(): array {
        $table = $this->db->get_tables()['notification_rules'];
        
        $results = $this->db->get_wpdb()->get_results(
            "SELECT * FROM {$table} WHERE status = 1 ORDER BY created_at ASC",
            ARRAY_A
        );

        return $results ?: [];
    }

    /**
     * Check if visitor count exceeds threshold
     *
     * @since 1.0.0
     * @param int $threshold Visitor threshold
     * @return bool True if threshold exceeded
     */
    private function check_visitor_threshold(int $threshold): bool {
        if ($threshold <= 0) {
            return false;
        }

        $sessions_table = $this->db->get_tables()['sessions'];
        $today_start = date('Y-m-d 00:00:00');
        
        $count = $this->db->get_wpdb()->get_var(
            $this->db->get_wpdb()->prepare(
                "SELECT COUNT(*) FROM {$sessions_table} WHERE created_at >= %s",
                $today_start
            )
        );

        return (int) $count >= $threshold;
    }

    /**
     * Check if device type is new (first occurrence)
     *
     * @since 1.0.0
     * @param string $device_type Device type
     * @return bool True if device type is new
     */
    private function is_new_device_type(string $device_type): bool {
        $sessions_table = $this->db->get_tables()['sessions'];
        $one_hour_ago = date('Y-m-d H:i:s', time() - 3600);
        
        $count = $this->db->get_wpdb()->get_var(
            $this->db->get_wpdb()->prepare(
                "SELECT COUNT(*) FROM {$sessions_table} WHERE device_type = %s AND created_at < %s",
                $device_type,
                $one_hour_ago
            )
        );

        return (int) $count === 0;
    }

    /**
     * Send new visitor notification
     *
     * @since 1.0.0
     * @param array $visitor_data Visitor data
     * @param string $email Email address
     * @return void
     */
    private function send_new_visitor_notification(array $visitor_data, string $email): void {
        $subject = $this->build_simple_email_subject('new_visitor', $visitor_data);
        $message = $this->build_simple_email_message('new_visitor', $visitor_data);
        $headers = $this->build_email_headers();

        $sent = \wp_mail($email, $subject, $message, $headers);

        if ($sent) {
            $this->log_simple_notification_sent('new_visitor', $visitor_data, $email);
            $this->logger->info('New visitor notification sent', [
                'email' => $email,
                'device_type' => $visitor_data['device_type'] ?? 'unknown'
            ], 'notification');
        } else {
            $this->logger->error('Failed to send new visitor notification', [
                'email' => $email
            ], 'notification');
        }
    }

    /**
     * Send threshold notification
     *
     * @since 1.0.0
     * @param array $visitor_data Visitor data
     * @param string $email Email address
     * @param int $threshold Threshold value
     * @return void
     */
    private function send_threshold_notification(array $visitor_data, string $email, int $threshold): void {
        $subject = $this->build_simple_email_subject('visitor_threshold', $visitor_data);
        $message = $this->build_simple_email_message('visitor_threshold', $visitor_data, $threshold);
        $headers = $this->build_email_headers();

        $sent = \wp_mail($email, $subject, $message, $headers);

        if ($sent) {
            $this->log_simple_notification_sent('visitor_threshold', $visitor_data, $email);
            $this->logger->info('Threshold notification sent', [
                'email' => $email,
                'threshold' => $threshold
            ], 'notification');
        } else {
            $this->logger->error('Failed to send threshold notification', [
                'email' => $email,
                'threshold' => $threshold
            ], 'notification');
        }
    }

    /**
     * Send new device notification
     *
     * @since 1.0.0
     * @param array $visitor_data Visitor data
     * @param string $email Email address
     * @return void
     */
    private function send_new_device_notification(array $visitor_data, string $email): void {
        $subject = $this->build_simple_email_subject('new_device', $visitor_data);
        $message = $this->build_simple_email_message('new_device', $visitor_data);
        $headers = $this->build_email_headers();

        $sent = \wp_mail($email, $subject, $message, $headers);

        if ($sent) {
            $this->log_simple_notification_sent('new_device', $visitor_data, $email);
            $this->logger->info('New device notification sent', [
                'email' => $email,
                'device_type' => $visitor_data['device_type'] ?? 'unknown'
            ], 'notification');
        } else {
            $this->logger->error('Failed to send new device notification', [
                'email' => $email,
                'device_type' => $visitor_data['device_type'] ?? 'unknown'
            ], 'notification');
        }
    }

    /**
     * Build simple email subject
     *
     * @since 1.0.0
     * @param string $event_type Event type
     * @param array $visitor_data Visitor data
     * @return string Email subject
     */
    private function build_simple_email_subject(string $event_type, array $visitor_data): string {
        $site_name = \get_bloginfo('name');
        
        switch ($event_type) {
            case 'new_visitor':
                return "[{$site_name}] New Visitor Alert";
                
            case 'visitor_threshold':
                return "[{$site_name}] Visitor Threshold Reached";
                
            case 'new_device':
                $device = $visitor_data['device_type'] ?? 'Unknown';
                return "[{$site_name}] New Device Type: {$device}";
                
            default:
                return "[{$site_name}] Visitor Notification";
        }
    }

    /**
     * Build simple email message
     *
     * @since 1.0.0
     * @param string $event_type Event type
     * @param array $visitor_data Visitor data
     * @param int|null $threshold Threshold value for threshold notifications
     * @return string Email message
     */
    private function build_simple_email_message(string $event_type, array $visitor_data, ?int $threshold = null): string {
        $site_name = \get_bloginfo('name');
        $site_url = \home_url();
        $timestamp = date('Y-m-d H:i:s');
        
        $message = "Hello,\n\n";
        
        switch ($event_type) {
            case 'new_visitor':
                $message .= "A new visitor has accessed your website.\n\n";
                break;
                
            case 'visitor_threshold':
                $message .= "Your website has reached the daily visitor threshold of {$threshold} visitors.\n\n";
                break;
                
            case 'new_device':
                $device = $visitor_data['device_type'] ?? 'Unknown';
                $message .= "A new device type ({$device}) has been detected on your website.\n\n";
                break;
        }
        
        $message .= "Visitor Details:\n";
        $message .= "- Time: {$timestamp}\n";
        $message .= "- Site: {$site_name} ({$site_url})\n";
        
        if (!empty($visitor_data['device_type'])) {
            $message .= "- Device: " . ucfirst($visitor_data['device_type']) . "\n";
        }
        
        if (!empty($visitor_data['browser'])) {
            $message .= "- Browser: {$visitor_data['browser']}\n";
        }
        
        if (!empty($visitor_data['os'])) {
            $message .= "- Operating System: {$visitor_data['os']}\n";
        }
        
        if (!empty($visitor_data['url'])) {
            $message .= "- Page: {$visitor_data['url']}\n";
        }
        
        $message .= "\n--\n";
        $message .= "This notification was sent by WP Visitor Notify plugin.\n";
        $message .= "You can manage these notifications in your WordPress admin panel.";
        
        return $message;
    }

    /**
     * Build email headers
     *
     * @since 1.0.0
     * @return array Email headers
     */
    private function build_email_headers(): array {
        $site_name = \get_bloginfo('name');
        $admin_email = \get_option('admin_email');
        
        return [
            'Content-Type: text/plain; charset=UTF-8',
            "From: {$site_name} <{$admin_email}>"
        ];
    }

    /**
     * Log simple notification in history table
     *
     * @since 1.0.0
     * @param string $event_type Event type
     * @param array $visitor_data Visitor data
     * @param string $email Email address
     * @return void
     */
    private function log_simple_notification_sent(string $event_type, array $visitor_data, string $email): void {
        $history_table = $this->db->get_tables()['notification_history'];
        
        $this->db->get_wpdb()->insert(
            $history_table,
            [
                'rule_id' => 0, // No specific rule ID for settings-based notifications
                'email' => $email,
                'event_type' => $event_type,
                'visitor_data' => \wp_json_encode($visitor_data),
                'sent_at' => \current_time('mysql', true),
                'status' => 'sent'
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s']
        );
    }

    /**
     * Create default notification rules
     *
     * @since 1.0.0
     * @return void
     */
    public function create_default_rules(): void {
        $rules_table = $this->db->get_tables()['notification_rules'];
        
        // Check if rules already exist
        $existing = $this->db->get_wpdb()->get_var(
            "SELECT COUNT(*) FROM {$rules_table}"
        );
        
        if ((int) $existing > 0) {
            return; // Rules already exist
        }
        
        $default_rules = [
            [
                'name' => 'New Visitor Alert',
                'event_type' => 'new_visitor',
                'threshold' => 0,
                'email' => \get_option('admin_email', ''),
                'message' => 'A new visitor has accessed your website.',
                'status' => 0, // Disabled by default
                'created_at' => \current_time('mysql', true),
                'updated_at' => \current_time('mysql', true)
            ],
            [
                'name' => 'Daily Visitor Threshold',
                'event_type' => 'visitor_threshold',
                'threshold' => 100,
                'email' => \get_option('admin_email', ''),
                'message' => 'Your website has reached the daily visitor threshold.',
                'status' => 0, // Disabled by default
                'created_at' => \current_time('mysql', true),
                'updated_at' => \current_time('mysql', true)
            ]
        ];
        
        foreach ($default_rules as $rule) {
            $this->db->get_wpdb()->insert(
                $rules_table,
                $rule,
                ['%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s']
            );
        }
        
        $this->logger->info('Default notification rules created', [], 'notification');
    }

    /**
     * Get notification history
     *
     * @since 1.0.0
     * @param int $limit Number of records to retrieve
     * @return array Notification history
     */
    public function get_notification_history(int $limit = 50): array {
        $history_table = $this->db->get_tables()['notification_history'];
        
        $results = $this->db->get_wpdb()->get_results(
            $this->db->get_wpdb()->prepare(
                "SELECT * FROM {$history_table} ORDER BY sent_at DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        );

        return $results ?: [];
    }
}
