<?php
declare(strict_types=1);

namespace WPVN\Admin;

use WPVN\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Logs page controller.
 * Displays system logs with filtering and search capabilities.
 *
 * @since 1.0.0
 */
class Logs {
    private Logger $logger;

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }    /**
     * Render logs page
     */
    public function render(): void {
        $logs = $this->get_logs();
        $log_levels = ['debug', 'info', 'error'];
        
        // Handle clear logs action
        if (isset($_POST['clear_logs']) && wp_verify_nonce($_POST['_wpnonce'], 'wpvn_clear_logs')) {
            $this->clear_logs();
            wp_redirect(add_query_arg('cleared', '1', wp_get_referer()));
            exit;
        }
        
        // Template will be included here when created
        // For now, return empty - no HTML output
    }/**
     * Get logs from error_log file
     *
     * @return array Array of log entries
     */
    private function get_logs(): array {
        $logs = [];
        
        // Use logger's method to get preferred log file
        $log_file = $this->logger->get_log_file();
        
        if (!$log_file) {
            return $logs;
        }
        
        // Try to read file safely
        $lines = @file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines || !is_array($lines)) {
            return $logs;
        }
        
        // Get last 500 lines and filter for WPVN logs
        $lines = array_slice($lines, -500);
        
        foreach (array_reverse($lines) as $line) {
            if (strpos($line, 'WPVN.') !== false) {
                $parsed = $this->parse_log_line($line);
                if ($parsed) {
                    $logs[] = $parsed;
                }
            }
        }
        
        return $logs;
    }

    /**
     * Parse a single log line
     *
     * @param string $line Log line
     * @return array|null Parsed log entry or null
     */
    private function parse_log_line(string $line): ?array {
        // Expected format: [timestamp] WPVN.LEVEL[component]: message | Context: {json}
        $pattern = '/\[([^\]]+)\] WPVN\.([A-Z]+)\[([^\]]*)\]: ([^|]+)(?:\| Context: (.+))?/';
        
        if (preg_match($pattern, $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'level' => strtolower($matches[2]),
                'component' => $matches[3] ?: 'general',
                'message' => trim($matches[4]),
                'context' => isset($matches[5]) ? $matches[5] : '',
                'raw' => $line
            ];
        }
        
        return null;
    }    /**
     * Clear logs (removes WPVN entries from error_log)
     */
    private function clear_logs(): void {
        // Use logger's method to get preferred log file
        $log_file = $this->logger->get_log_file();
        
        if (!$log_file || !is_writable($log_file)) {
            return;
        }
        
        $lines = @file($log_file, FILE_IGNORE_NEW_LINES);
        if (!$lines || !is_array($lines)) {
            return;
        }
        
        // Filter out WPVN logs
        $filtered_lines = array_filter($lines, function($line) {
            return strpos($line, 'WPVN.') === false;
        });
        
        // Write back to file
        file_put_contents($log_file, implode(PHP_EOL, $filtered_lines) . PHP_EOL);
        
        $this->logger->info('Logs cleared by user', ['user_id' => get_current_user_id()], 'admin');
    }

    /**
     * Export logs to CSV
     */
    public function export_logs(): void {
        $logs = $this->get_logs();
        
        if (empty($logs)) {
            wp_die(__('No logs to export', 'wp-visitor-notify'));
        }
        
        $filename = 'wpvn-logs-' . date('Y-m-d-H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, ['Timestamp', 'Level', 'Component', 'Message', 'Context']);
        
        // CSV data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['timestamp'],
                $log['level'],
                $log['component'],
                $log['message'],
                $log['context']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
