/**
 * Dashboard functionality for WP Visitor Notify
 * Handles charts, analytics display, and real-time updates
 */

(function($) {
    'use strict';
    
    // Dashboard object
    window.WPVN_Dashboard = {
        charts: {},
        refreshInterval: null,
        
        init: function() {
            this.initCharts();
            this.initRefresh();
            this.bindEvents();
            this.loadDashboardData();
        },
        
        // Initialize Chart.js charts
        initCharts: function() {
            const visitsChartCtx = document.getElementById('wpvn-visits-chart');
            if (visitsChartCtx) {
                this.charts.visits = new Chart(visitsChartCtx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Visits',
                            data: [],
                            borderColor: '#2271b1',
                            backgroundColor: 'rgba(34, 113, 177, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            const devicesChartCtx = document.getElementById('wpvn-devices-chart');
            if (devicesChartCtx) {
                this.charts.devices = new Chart(devicesChartCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Desktop', 'Mobile', 'Tablet'],
                        datasets: [{
                            data: [0, 0, 0],
                            backgroundColor: [
                                '#2271b1',
                                '#00a32a',
                                '#dba617'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        },
        
        // Initialize auto-refresh
        initRefresh: function() {
            const refreshButton = $('#wpvn-refresh-dashboard');
            if (refreshButton.length) {
                this.refreshInterval = setInterval(() => {
                    this.loadDashboardData(true);
                }, 30000); // Refresh every 30 seconds
            }
        },
        
        // Bind event handlers
        bindEvents: function() {
            // Manual refresh button
            $(document).on('click', '#wpvn-refresh-dashboard', (e) => {
                e.preventDefault();
                this.loadDashboardData(true);
            });
            
            // Time period selector
            $(document).on('change', '#wpvn-time-period', (e) => {
                this.loadDashboardData();
            });
            
            // Export buttons
            $(document).on('click', '.wpvn-export-btn', (e) => {
                e.preventDefault();
                const format = $(e.target).data('format');
                this.exportData(format);
            });
        },
        
        // Load dashboard data via AJAX
        loadDashboardData: function(showLoader = false) {
            if (showLoader) {
                this.showLoader();
            }
            
            const timePeriod = $('#wpvn-time-period').val() || '7';
            
            WPVN.ajax('get_dashboard_data', {
                time_period: timePeriod
            }, (response) => {
                if (response.success) {
                    this.updateMetrics(response.data.metrics);
                    this.updateCharts(response.data.charts);
                    this.updateRecentVisitors(response.data.recent_visitors);
                    this.updateLastUpdated();
                } else {
                    this.showError('Failed to load dashboard data: ' + response.data);
                }
                
                if (showLoader) {
                    this.hideLoader();
                }
            }).fail(() => {
                this.showError('Network error occurred while loading dashboard data');
                if (showLoader) {
                    this.hideLoader();
                }
            });
        },
        
        // Update metric cards
        updateMetrics: function(metrics) {
            $('#wpvn-total-visits .metric').text(metrics.total_visits || '0');
            $('#wpvn-unique-visitors .metric').text(metrics.unique_visitors || '0');
            $('#wpvn-page-views .metric').text(metrics.page_views || '0');
            $('#wpvn-avg-session .metric').text(metrics.avg_session_duration || '0:00');
            $('#wpvn-bounce-rate .metric').text((metrics.bounce_rate || 0) + '%');
            $('#wpvn-online-visitors .metric').text(metrics.online_visitors || '0');
        },
        
        // Update charts with new data
        updateCharts: function(chartData) {
            // Update visits chart
            if (this.charts.visits && chartData.visits) {
                this.charts.visits.data.labels = chartData.visits.labels;
                this.charts.visits.data.datasets[0].data = chartData.visits.data;
                this.charts.visits.update();
            }
            
            // Update devices chart
            if (this.charts.devices && chartData.devices) {
                this.charts.devices.data.datasets[0].data = chartData.devices.data;
                this.charts.devices.update();
            }
        },
        
        // Update recent visitors table
        updateRecentVisitors: function(visitors) {
            const tbody = $('#wpvn-recent-visitors tbody');
            tbody.empty();
            
            if (visitors && visitors.length > 0) {
                visitors.forEach(visitor => {
                    const row = $(`
                        <tr>
                            <td>
                                ${visitor.country_flag ? `<img src="${visitor.country_flag}" class="country-flag" alt="${visitor.country}">` : ''}
                                ${visitor.country || 'Unknown'}
                            </td>
                            <td>${visitor.ip_address}</td>
                            <td>${visitor.user_agent_short || 'Unknown'}</td>
                            <td>${visitor.page_url}</td>
                            <td>${visitor.visit_time}</td>
                        </tr>
                    `);
                    tbody.append(row);
                });
            } else {
                tbody.append('<tr><td colspan="5" class="text-center">No recent visitors</td></tr>');
            }
        },
        
        // Update last updated timestamp
        updateLastUpdated: function() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            $('#wpvn-last-updated').text(`Last updated: ${timeString}`);
        },
        
        // Export dashboard data
        exportData: function(format) {
            const timePeriod = $('#wpvn-time-period').val() || '7';
            
            window.location.href = `${WPVN.ajaxUrl}?action=wpvn_export_dashboard&format=${format}&time_period=${timePeriod}&nonce=${WPVN.nonce}`;
        },
        
        // Show loading state
        showLoader: function() {
            $('.wpvn-dashboard').addClass('wpvn-loading');
        },
        
        // Hide loading state
        hideLoader: function() {
            $('.wpvn-dashboard').removeClass('wpvn-loading');
        },
        
        // Show error message
        showError: function(message) {
            const errorDiv = $('<div class="wpvn-error">' + message + '</div>');
            $('.wpvn-container').prepend(errorDiv);
              setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        },
        
        // Clean up when leaving page
        destroy: function() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
            
            // Destroy charts
            Object.values(this.charts).forEach(chart => {
                if (chart && typeof chart.destroy === 'function') {
                    chart.destroy();
                }
            });
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        if ($('.wpvn-dashboard').length) {
            WPVN_Dashboard.init();
        }
    });
    
    // Clean up on page unload
    $(window).on('beforeunload', function() {
        if (window.WPVN_Dashboard) {
            WPVN_Dashboard.destroy();
        }
    });
    
})(jQuery);
