<?php
/**
 * Logging System for WP Visitor Notify Plugin
 *
 * This class handles all logging operations including:
 * - System events logging
 * - Error tracking and reporting
 * - Debug information collection
 * - Performance monitoring
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
 * Logger class
 *
 * Handles system logging with multiple severity levels.
 * Stores logs in database and provides query methods for admin interface.
 *
 * @since 1.0.0
 */
class Logger {

    /**
     * Log levels (based on PSR-3 standards)
     *
     * @since 1.0.0
     * @var array<string>
     */
    private const LOG_LEVELS = [
        'debug',     // Detailed debug information
        'info',      // Interesting events
        'warning',   // Exceptional occurrences that are not errors
        'error',     // Runtime errors that do not require immediate action
        'critical'   // Critical conditions requiring immediate attention
    ];

    /**
     * Database handler reference
     *
     * Will be set when Database component is available.
     *
     * @since 1.0.0
     * @var Database|null
     */
    private ?Database $database = null;

    /**
     * Current log level threshold
     *
     * Only logs at or above this level will be recorded.
     *
     * @since 1.0.0
     * @var string
     */
    private string $log_level;

    /**
     * Constructor
     *
     * Initializes the logger with configuration from WordPress options.
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Get log level from plugin settings (default to 'info')
        $options = \get_option('wp-visitor-notify_options', []);
        $this->log_level = $options['log_level'] ?? 'info';
    }

    /**
     * Set database handler
     *
     * The database is set after Logger is created to avoid circular dependencies.
     *
     * @since 1.0.0
     * @param Database $database Database handler instance
     * @return void
     */
    public function set_database(Database $database): void {
        $this->database = $database;
    }

    /**
     * Log a message with specified level
     *
     * Main logging method that accepts any severity level.
     * Automatically includes contextual information like timestamp and component.
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param string $level Log level (debug, info, warning, error, critical)
     * @param array $context Additional context data
     * @param string|null $component Component name that generated the log
     * @return bool True on success, false on failure
     */
    public function log(string $message, string $level = 'info', array $context = [], ?string $component = null): bool {
        // Validate log level
        if (!\in_array($level, self::LOG_LEVELS, true)) {
            $level = 'info';
        }

        // Check if this level should be logged
        if (!$this->should_log($level)) {
            return true; // Not an error, just filtered out
        }

        // Prepare log data
        $log_data = [
            'level' => $level,
            'message' => $message,
            'context' => !empty($context) ? \wp_json_encode($context) : null,
            'component' => $component,
            'user_id' => \get_current_user_id() ?: null,
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => \current_time('mysql', true)
        ];

        // Try to store in database if available
        if ($this->database !== null) {
            return $this->store_in_database($log_data);
        }

        // Fallback to WordPress error log
        return $this->store_in_error_log($log_data);
    }

    /**
     * Log debug level message
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param array $context Additional context
     * @param string|null $component Component name
     * @return bool Success status
     */
    public function debug(string $message, array $context = [], ?string $component = null): bool {
        return $this->log($message, 'debug', $context, $component);
    }

    /**
     * Log info level message
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param array $context Additional context
     * @param string|null $component Component name
     * @return bool Success status
     */
    public function info(string $message, array $context = [], ?string $component = null): bool {
        return $this->log($message, 'info', $context, $component);
    }

    /**
     * Log warning level message
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param array $context Additional context
     * @param string|null $component Component name
     * @return bool Success status
     */
    public function warning(string $message, array $context = [], ?string $component = null): bool {
        return $this->log($message, 'warning', $context, $component);
    }

    /**
     * Log error level message
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param array $context Additional context
     * @param string|null $component Component name
     * @return bool Success status
     */
    public function error(string $message, array $context = [], ?string $component = null): bool {
        return $this->log($message, 'error', $context, $component);
    }

    /**
     * Log critical level message
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param array $context Additional context
     * @param string|null $component Component name
     * @return bool Success status
     */
    public function critical(string $message, array $context = [], ?string $component = null): bool {
        return $this->log($message, 'critical', $context, $component);
    }

    /**
     * Get recent log entries
     *
     * Retrieves log entries from database for admin interface.
     *
     * @since 1.0.0
     * @param array $filters Query filters
     * @return array Log entries
     */
    public function get_logs(array $filters = []): array {
        if ($this->database === null) {
            return [];
        }

        // Build query based on filters
        $where_conditions = [];
        $where_values = [];

        // Filter by level
        if (!empty($filters['level']) && \in_array($filters['level'], self::LOG_LEVELS, true)) {
            $where_conditions[] = 'level = %s';
            $where_values[] = $filters['level'];
        }

        // Filter by component
        if (!empty($filters['component'])) {
            $where_conditions[] = 'component = %s';
            $where_values[] = \sanitize_text_field($filters['component']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $where_conditions[] = 'created_at >= %s';
            $where_values[] = \sanitize_text_field($filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $where_conditions[] = 'created_at <= %s';
            $where_values[] = \sanitize_text_field($filters['date_to']);
        }

        // Build WHERE clause
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . \implode(' AND ', $where_conditions);
        }

        // Set limit
        $limit = \intval($filters['limit'] ?? 100);
        $limit = \min($limit, 1000); // Maximum 1000 records

        // Execute query
        $tables = $this->database->get_tables();
        $query = "SELECT * FROM {$tables['logs']} {$where_clause} ORDER BY created_at DESC LIMIT {$limit}";

        if (!empty($where_values)) {
            $query = $this->database->get_wpdb()->prepare($query, $where_values);
        }

        return $this->database->get_wpdb()->get_results($query, \ARRAY_A) ?: [];
    }

    /**
     * Clean up old log entries
     *
     * Removes log entries older than specified retention period.
     *
     * @since 1.0.0
     * @param int $retention_days Number of days to retain logs
     * @return int Number of deleted entries
     */
    public function cleanup_old_logs(int $retention_days = 90): int {
        if ($this->database === null) {
            return 0;
        }

        $tables = $this->database->get_tables();
        $cutoff_date = \date('Y-m-d H:i:s', \strtotime("-{$retention_days} days"));

        $result = $this->database->get_wpdb()->query(
            $this->database->get_wpdb()->prepare(
                "DELETE FROM {$tables['logs']} WHERE created_at < %s",
                $cutoff_date
            )
        );

        return $result ?: 0;
    }

    /**
     * Check if a log level should be recorded
     *
     * @since 1.0.0
     * @param string $level Log level to check
     * @return bool True if should be logged
     */
    private function should_log(string $level): bool {
        $level_priorities = \array_flip(self::LOG_LEVELS);
        $current_priority = $level_priorities[$this->log_level] ?? 1;
        $message_priority = $level_priorities[$level] ?? 1;

        return $message_priority >= $current_priority;
    }

    /**
     * Store log entry in database
     *
     * @since 1.0.0
     * @param array $log_data Log entry data
     * @return bool Success status
     */
    private function store_in_database(array $log_data): bool {
        try {
            $tables = $this->database->get_tables();
            
            $result = $this->database->get_wpdb()->insert(
                $tables['logs'],
                $log_data,
                ['%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
            );

            return $result !== false;

        } catch (\Exception $e) {
            // Fallback to error log if database fails
            $this->store_in_error_log($log_data);
            return false;
        }
    }

    /**
     * Store log entry in WordPress error log (fallback)
     *
     * @since 1.0.0
     * @param array $log_data Log entry data
     * @return bool Always returns true
     */
    private function store_in_error_log(array $log_data): bool {
        $formatted_message = \sprintf(
            '[WPVN] [%s] [%s] %s',
            \strtoupper($log_data['level']),
            $log_data['component'] ?? 'SYSTEM',
            $log_data['message']
        );

        if ($log_data['context']) {
            $formatted_message .= ' | Context: ' . $log_data['context'];
        }

        \error_log($formatted_message);
        return true;
    }

    /**
     * Get client IP address
     *
     * Attempts to get the real client IP, considering proxies and load balancers.
     *
     * @since 1.0.0
     * @return string|null Client IP address
     */
    private function get_client_ip(): ?string {
        $ip_keys = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = \sanitize_text_field($_SERVER[$key]);
                
                // Handle comma-separated IPs (from proxies)
                if (\strpos($ip, ',') !== false) {
                    $ip = \trim(\explode(',', $ip)[0]);
                }

                // Validate IP address
                if (\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
     * Get log level statistics
     *
     * Returns count of log entries by level for admin dashboard.
     *
     * @since 1.0.0
     * @param string $date_from Start date (Y-m-d format)
     * @param string $date_to End date (Y-m-d format)
     * @return array Statistics by log level
     */
    public function get_log_stats(string $date_from = '', string $date_to = ''): array {
        if ($this->database === null) {
            return [];
        }

        $tables = $this->database->get_tables();
        $where_clause = '';
        $where_values = [];

        if ($date_from && $date_to) {
            $where_clause = 'WHERE created_at BETWEEN %s AND %s';
            $where_values = [$date_from . ' 00:00:00', $date_to . ' 23:59:59'];
        }

        $query = "
            SELECT level, COUNT(*) as count 
            FROM {$tables['logs']} 
            {$where_clause} 
            GROUP BY level 
            ORDER BY FIELD(level, 'critical', 'error', 'warning', 'info', 'debug')
        ";

        if (!empty($where_values)) {
            $query = $this->database->get_wpdb()->prepare($query, $where_values);
        }

        $results = $this->database->get_wpdb()->get_results($query, \ARRAY_A) ?: [];
        
        // Initialize all levels with 0 count
        $stats = \array_fill_keys(self::LOG_LEVELS, 0);
        
        // Fill in actual counts
        foreach ($results as $row) {
            $stats[$row['level']] = (int) $row['count'];
        }

        return $stats;
    }
}
