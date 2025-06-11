<?php
/**
 * Simple Logging System for WP Visitor Notify Plugin
 *
 * Simplified logger that writes directly to PHP error_log for Docker visibility.
 * No database dependency - pure error_log output.
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
 * Simple Logger class
 *
 * PSR-3 compatible logger that writes directly to error_log.
 * Perfect for development and Docker environments.
 *
 * @since 1.0.0
 */
class Logger {

    /**
     * Log levels (simplified for practical use)
     *
     * @since 1.0.0
     * @var array<string>
     */
    private const LOG_LEVELS = [
        'debug',     // Detailed debug information
        'info',      // Interesting events
        'error'      // Runtime errors that do not require immediate action
    ];

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
        // Get log level from plugin settings (default to 'info')        $options = \get_option('wp-visitor-notify_options', []);
        $this->log_level = $options['log_level'] ?? 'info';
    }

    /**
     * Log a message with specified level
     *
     * Main logging method that accepts any severity level.
     * Writes directly to PHP error_log for Docker visibility.
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param string $level Log level (debug, info, error)
     * @param array $context Additional context data
     * @param string|null $component Component name that generated the log
     * @return bool Always returns true
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

        // Format message for error_log
        $formatted_message = $this->format_message($message, $level, $context, $component);
          // Write to error_log (visible in Docker logs)
        \error_log($formatted_message);
        
        return true;
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
     * Format message for error_log
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param string $level Log level
     * @param array $context Additional context
     * @param string|null $component Component name
     * @return string Formatted message
     */
    private function format_message(string $message, string $level, array $context, ?string $component): string {
        $timestamp = \current_time('Y-m-d H:i:s');
        $level_upper = \strtoupper($level);
        $component_part = $component ? " [{$component}]" : '';
        
        $formatted = "[{$timestamp}] WPVN.{$level_upper}{$component_part}: {$message}";
        
        // Add context if provided
        if (!empty($context)) {
            $context_json = \wp_json_encode($context);            $formatted .= " | Context: {$context_json}";
        }
        
        return $formatted;
    }
}
