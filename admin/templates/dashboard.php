<?php
/**
 * Dashboard page template
 * Main analytics page with overview cards, charts, and data tables
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wpvn-container">
    <?php include WPVN_PLUGIN_DIR . 'admin/templates/header.php'; ?>
    
    <div class="wpvn-dashboard">
        <!-- Overview Cards Section -->
        <div class="wpvn-overview-cards">
            <div class="wpvn-card" id="total-visits-card">
                <div class="wpvn-card-header">
                    <h3>Total Visits</h3>
                </div>
                <div class="wpvn-card-content">
                    <div class="wpvn-metric-value" id="total-visits-value">--</div>
                    <div class="wpvn-metric-label">Last 30 days</div>
                </div>
            </div>
            
            <div class="wpvn-card" id="unique-visitors-card">
                <div class="wpvn-card-header">
                    <h3>Unique Visitors</h3>
                </div>
                <div class="wpvn-card-content">
                    <div class="wpvn-metric-value" id="unique-visitors-value">--</div>
                    <div class="wpvn-metric-label">Last 30 days</div>
                </div>
            </div>
            
            <div class="wpvn-card" id="avg-session-card">
                <div class="wpvn-card-header">
                    <h3>Avg Session Duration</h3>
                </div>
                <div class="wpvn-card-content">
                    <div class="wpvn-metric-value" id="avg-session-value">--</div>
                    <div class="wpvn-metric-label">Minutes</div>
                </div>
            </div>
            
            <div class="wpvn-card" id="current-online-card">
                <div class="wpvn-card-header">
                    <h3>Current Online</h3>
                </div>
                <div class="wpvn-card-content">
                    <div class="wpvn-metric-value" id="current-online-value">--</div>
                    <div class="wpvn-metric-label">Active now</div>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="wpvn-charts-section">
            <div class="wpvn-chart-container wpvn-chart-large">
                <div class="wpvn-card">
                    <div class="wpvn-card-header">
                        <h3>Daily Visits</h3>
                        <div class="wpvn-chart-controls">
                            <select id="visits-period">
                                <option value="7">Last 7 days</option>
                                <option value="30" selected>Last 30 days</option>
                                <option value="90">Last 90 days</option>
                            </select>
                        </div>
                    </div>
                    <div class="wpvn-card-content">
                        <canvas id="daily-visits-chart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="wpvn-chart-container wpvn-chart-small">
                <div class="wpvn-card">
                    <div class="wpvn-card-header">
                        <h3>Device Types</h3>
                    </div>
                    <div class="wpvn-card-content">
                        <canvas id="device-stats-chart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="wpvn-chart-container wpvn-chart-small">
                <div class="wpvn-card">
                    <div class="wpvn-card-header">
                        <h3>Browsers</h3>
                    </div>
                    <div class="wpvn-card-content">
                        <canvas id="browser-stats-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data Tables Section -->
        <div class="wpvn-tables-section">
            <div class="wpvn-table-container">
                <div class="wpvn-card">
                    <div class="wpvn-card-header">
                        <h3>Top Pages</h3>
                    </div>
                    <div class="wpvn-card-content">
                        <div class="wpvn-table-wrapper">
                            <table class="wpvn-table" id="top-pages-table">
                                <thead>
                                    <tr>
                                        <th>Page</th>
                                        <th>Title</th>
                                        <th>Views</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wpvn-table-container">
                <div class="wpvn-card">
                    <div class="wpvn-card-header">
                        <h3>Recent Activity</h3>
                    </div>
                    <div class="wpvn-card-content">
                        <div class="wpvn-activity-list" id="recent-activity-list">
                            <!-- Data loaded via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
