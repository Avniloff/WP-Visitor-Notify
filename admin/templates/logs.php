<?php
/**
 * Logs page template
 * Log viewing page with filters, table, and export functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wpvn-container">
    <?php include WPVN_PLUGIN_DIR . 'admin/templates/header.php'; ?>
    
    <div class="wpvn-logs">
        <!-- Action Buttons and Filters Bar -->
        <div class="wpvn-logs-toolbar">
            <div class="wpvn-logs-filters">
                <select id="log-level-filter">
                    <option value="">All Levels</option>
                    <option value="DEBUG">Debug</option>
                    <option value="INFO">Info</option>
                    <option value="ERROR">Error</option>
                </select>
                
                <select id="log-component-filter">
                    <option value="">All Components</option>
                    <option value="Tracker">Tracker</option>
                    <option value="Analytics">Analytics</option>
                    <option value="Database">Database</option>
                    <option value="Detector">Detector</option>
                </select>
                
                <input type="date" id="log-date-from" placeholder="From date">
                <input type="date" id="log-date-to" placeholder="To date">
                
                <button type="button" class="button" id="apply-filters">Apply Filters</button>
                <button type="button" class="button" id="clear-filters">Clear Filters</button>
            </div>
            
            <div class="wpvn-logs-actions">
                <button type="button" class="button" id="refresh-logs">
                    <span class="dashicons dashicons-update"></span> Refresh
                </button>
                <button type="button" class="button" id="export-logs">
                    <span class="dashicons dashicons-download"></span> Export CSV
                </button>
                <button type="button" class="button button-secondary" id="clear-logs">
                    <span class="dashicons dashicons-trash"></span> Clear Logs
                </button>
                
                <label class="wpvn-auto-refresh">
                    <input type="checkbox" id="auto-refresh-toggle"> Auto-refresh
                </label>
            </div>
        </div>
        
        <!-- Logs Table -->
        <div class="wpvn-card">
            <div class="wpvn-card-header">
                <h3>System Logs</h3>
                <div class="wpvn-logs-info">
                    <span id="logs-count">-- entries</span>
                    <span id="logs-last-updated">Last updated: --</span>
                </div>
            </div>
            <div class="wpvn-card-content">
                <div class="wpvn-table-wrapper">
                    <table class="wpvn-table wpvn-logs-table" id="logs-table">
                        <thead>
                            <tr>
                                <th class="wpvn-col-timestamp">Timestamp</th>
                                <th class="wpvn-col-level">Level</th>
                                <th class="wpvn-col-component">Component</th>
                                <th class="wpvn-col-message">Message</th>
                                <th class="wpvn-col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="wpvn-pagination" id="logs-pagination">
                    <!-- Pagination loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div id="wpvn-log-modal" class="wpvn-modal">
    <div class="wpvn-modal-content">
        <div class="wpvn-modal-header">
            <h3>Log Details</h3>
            <button type="button" class="wpvn-modal-close">&times;</button>
        </div>
        <div class="wpvn-modal-body">
            <div class="wpvn-log-details">
                <div class="wpvn-log-field">
                    <label>Timestamp:</label>
                    <span id="modal-timestamp">--</span>
                </div>
                <div class="wpvn-log-field">
                    <label>Level:</label>
                    <span id="modal-level" class="wpvn-log-level">--</span>
                </div>
                <div class="wpvn-log-field">
                    <label>Component:</label>
                    <span id="modal-component">--</span>
                </div>
                <div class="wpvn-log-field">
                    <label>Message:</label>
                    <div id="modal-message">--</div>
                </div>
                <div class="wpvn-log-field">
                    <label>Context:</label>
                    <pre id="modal-context">--</pre>
                </div>
            </div>
        </div>
        <div class="wpvn-modal-footer">
            <button type="button" class="button" onclick="WPVN.logs.closeModal()">Close</button>
        </div>
    </div>
</div>
