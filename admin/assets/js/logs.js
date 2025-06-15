/**
 * Logs functionality for WP Visitor Notify
 * Handles DataTables, filtering, modals, and log management
 */

(function($) {
    'use strict';
    
    // Logs object
    window.WPVN_Logs = {
        table: null,
        modal: null,
        
        init: function() {
            this.initDataTable();
            this.initModal();
            this.bindEvents();
            this.initFilters();
        },
        
        // Initialize DataTables
        initDataTable: function() {
            if (!$.fn.DataTable) {
                console.warn('DataTables library not loaded');
                return;
            }
            
            this.table = $('#wpvn-logs-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: WPVN.ajaxUrl,
                    type: 'POST',
                    data: function(d) {
                        // Add custom filters
                        d.action = 'wpvn_get_logs';
                        d.nonce = WPVN.nonce;
                        d.date_from = $('#wpvn-filter-date-from').val();
                        d.date_to = $('#wpvn-filter-date-to').val();
                        d.level = $('#wpvn-filter-level').val();
                        d.ip_address = $('#wpvn-filter-ip').val();
                        d.country = $('#wpvn-filter-country').val();
                        return d;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables AJAX error:', error, thrown);
                    }
                },
                columns: [
                    {
                        data: 'timestamp',
                        title: 'Time',
                        width: '150px',
                        render: function(data) {
                            return new Date(data).toLocaleString();
                        }
                    },
                    {
                        data: 'level',
                        title: 'Level',
                        width: '80px',
                        render: function(data) {
                            return `<span class="wpvn-log-level ${data.toLowerCase()}">${data}</span>`;
                        }
                    },
                    {
                        data: 'ip_address',
                        title: 'IP Address',
                        width: '120px',
                        render: function(data) {
                            return `<span class="wpvn-ip-address">${data}</span>`;
                        }
                    },
                    {
                        data: 'country',
                        title: 'Country',
                        width: '100px',
                        render: function(data, type, row) {
                            if (row.country_flag) {
                                return `<img src="${row.country_flag}" class="country-flag" alt="${data}"> ${data}`;
                            }
                            return data || 'Unknown';
                        }
                    },
                    {
                        data: 'user_agent',
                        title: 'User Agent',
                        render: function(data) {
                            if (data && data.length > 50) {
                                return `<span class="wpvn-user-agent" title="${data}">${data.substring(0, 50)}...</span>`;
                            }
                            return data || 'Unknown';
                        }
                    },
                    {
                        data: 'page_url',
                        title: 'Page',
                        render: function(data) {
                            if (data && data.length > 40) {
                                return `<span title="${data}">${data.substring(0, 40)}...</span>`;
                            }
                            return data || '/';
                        }
                    },
                    {
                        data: null,
                        title: 'Actions',
                        width: '100px',
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="wpvn-table-actions">
                                    <button class="wpvn-btn-small wpvn-btn-view" data-id="${row.id}" title="View Details">
                                        View
                                    </button>
                                    <button class="wpvn-btn-small wpvn-btn-delete" data-id="${row.id}" title="Delete Log">
                                        Delete
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[0, 'desc']], // Sort by timestamp descending
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                responsive: true,
                language: {
                    processing: 'Loading logs...',
                    emptyTable: 'No logs found',
                    zeroRecords: 'No logs match your filters'
                },
                dom: '<"wpvn-table-controls"<"wpvn-table-length"l><"wpvn-table-search"f>>rtip'
            });
        },
        
        // Initialize modal
        initModal: function() {
            this.modal = $('#wpvn-log-modal');
            if (!this.modal.length) {
                // Create modal if it doesn't exist
                this.createModal();
            }
        },
        
        // Create modal HTML
        createModal: function() {
            const modalHtml = `
                <div id="wpvn-log-modal" class="wpvn-modal">
                    <div class="wpvn-modal-content">
                        <div class="wpvn-modal-header">
                            <h3>Log Details</h3>
                            <button class="wpvn-modal-close">&times;</button>
                        </div>
                        <div class="wpvn-modal-body">
                            <div id="wpvn-log-details" class="wpvn-log-details">
                                <!-- Log details will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
            this.modal = $('#wpvn-log-modal');
        },
        
        // Bind event handlers
        bindEvents: function() {
            // Filter form submission
            $('#wpvn-logs-filters').on('submit', (e) => {
                e.preventDefault();
                this.applyFilters();
            });
            
            // Clear filters
            $(document).on('click', '#wpvn-clear-filters', (e) => {
                e.preventDefault();
                this.clearFilters();
            });
            
            // View log details
            $(document).on('click', '.wpvn-btn-view', (e) => {
                e.preventDefault();
                const logId = $(e.target).data('id');
                this.viewLogDetails(logId);
            });
            
            // Delete log
            $(document).on('click', '.wpvn-btn-delete', (e) => {
                e.preventDefault();
                const logId = $(e.target).data('id');
                this.deleteLog(logId);
            });
            
            // Bulk actions
            $(document).on('click', '#wpvn-bulk-delete', (e) => {
                e.preventDefault();
                this.bulkDelete();
            });
            
            // Export logs
            $(document).on('click', '.wpvn-export-logs', (e) => {
                e.preventDefault();
                const format = $(e.target).data('format');
                this.exportLogs(format);
            });
            
            // Modal close
            $(document).on('click', '.wpvn-modal-close, .wpvn-modal', (e) => {
                if (e.target === e.currentTarget) {
                    this.closeModal();
                }
            });
            
            // Keyboard shortcuts
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.modal.hasClass('open')) {
                    this.closeModal();
                }
            });
            
            // Auto-refresh toggle
            $(document).on('change', '#wpvn-auto-refresh', (e) => {
                if (e.target.checked) {
                    this.startAutoRefresh();
                } else {
                    this.stopAutoRefresh();
                }
            });
        },
        
        // Initialize filter controls
        initFilters: function() {
            // Date pickers
            if ($.fn.datepicker) {
                $('#wpvn-filter-date-from, #wpvn-filter-date-to').datepicker({
                    dateFormat: 'yy-mm-dd',
                    maxDate: 0 // No future dates
                });
            }
            
            // Filter changes trigger table reload
            $('#wpvn-logs-filters input, #wpvn-logs-filters select').on('change', () => {
                if (this.table) {
                    this.table.ajax.reload();
                }
            });
        },
        
        // Apply filters
        applyFilters: function() {
            if (this.table) {
                this.table.ajax.reload();
            }
        },
        
        // Clear all filters
        clearFilters: function() {
            $('#wpvn-logs-filters')[0].reset();
            if (this.table) {
                this.table.ajax.reload();
            }
        },
        
        // View detailed log information
        viewLogDetails: function(logId) {
            WPVN.ajax('get_log_details', {
                log_id: logId
            }, (response) => {
                if (response.success) {
                    this.showLogDetails(response.data);
                } else {
                    this.showError('Failed to load log details: ' + response.data);
                }
            }).fail(() => {
                this.showError('Network error occurred while loading log details');
            });
        },
        
        // Display log details in modal
        showLogDetails: function(logData) {
            const detailsHtml = `
                <div class="detail-group">
                    <div class="detail-label">Timestamp:</div>
                    <div class="detail-value">${new Date(logData.timestamp).toLocaleString()}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Level:</div>
                    <div class="detail-value">
                        <span class="wpvn-log-level ${logData.level.toLowerCase()}">${logData.level}</span>
                    </div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">IP Address:</div>
                    <div class="detail-value">${logData.ip_address}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Country:</div>
                    <div class="detail-value">
                        ${logData.country_flag ? `<img src="${logData.country_flag}" class="country-flag" alt="${logData.country}">` : ''}
                        ${logData.country || 'Unknown'}
                    </div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">User Agent:</div>
                    <div class="detail-value">${logData.user_agent || 'Unknown'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Page URL:</div>
                    <div class="detail-value">${logData.page_url || '/'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Referrer:</div>
                    <div class="detail-value">${logData.referrer || 'Direct'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Session ID:</div>
                    <div class="detail-value">${logData.session_id || 'N/A'}</div>
                </div>
                ${logData.additional_data ? `
                <div class="detail-group">
                    <div class="detail-label">Additional Data:</div>
                    <div class="detail-value"><pre>${JSON.stringify(JSON.parse(logData.additional_data), null, 2)}</pre></div>
                </div>
                ` : ''}
            `;
            
            $('#wpvn-log-details').html(detailsHtml);
            this.openModal();
        },
        
        // Delete a log entry
        deleteLog: function(logId) {
            if (!confirm('Are you sure you want to delete this log entry?')) {
                return;
            }
            
            WPVN.ajax('delete_log', {
                log_id: logId
            }, (response) => {
                if (response.success) {
                    this.showSuccess('Log entry deleted successfully');
                    if (this.table) {
                        this.table.ajax.reload();
                    }
                } else {
                    this.showError('Failed to delete log entry: ' + response.data);
                }
            }).fail(() => {
                this.showError('Network error occurred while deleting log entry');
            });
        },
        
        // Bulk delete selected logs
        bulkDelete: function() {
            const selectedIds = [];
            $('.wpvn-log-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) {
                this.showError('Please select logs to delete');
                return;
            }
            
            if (!confirm(`Are you sure you want to delete ${selectedIds.length} log entries?`)) {
                return;
            }
            
            WPVN.ajax('bulk_delete_logs', {
                log_ids: selectedIds
            }, (response) => {
                if (response.success) {
                    this.showSuccess(`${selectedIds.length} log entries deleted successfully`);
                    if (this.table) {
                        this.table.ajax.reload();
                    }
                } else {
                    this.showError('Failed to delete log entries: ' + response.data);
                }
            }).fail(() => {
                this.showError('Network error occurred while deleting log entries');
            });
        },
        
        // Export logs
        exportLogs: function(format) {
            const filters = {
                date_from: $('#wpvn-filter-date-from').val(),
                date_to: $('#wpvn-filter-date-to').val(),
                level: $('#wpvn-filter-level').val(),
                ip_address: $('#wpvn-filter-ip').val(),
                country: $('#wpvn-filter-country').val()
            };
            
            const params = new URLSearchParams({
                action: 'wpvn_export_logs',
                format: format,
                nonce: WPVN.nonce,
                ...filters
            });
            
            window.location.href = `${WPVN.ajaxUrl}?${params.toString()}`;
        },
        
        // Open modal
        openModal: function() {
            this.modal.addClass('open');
            $('body').addClass('wpvn-modal-open');
        },
        
        // Close modal
        closeModal: function() {
            this.modal.removeClass('open');
            $('body').removeClass('wpvn-modal-open');
        },
        
        // Auto-refresh functionality
        startAutoRefresh: function() {
            this.autoRefreshInterval = setInterval(() => {
                if (this.table) {
                    this.table.ajax.reload(null, false); // Don't reset paging
                }
            }, 30000); // Refresh every 30 seconds
        },
        
        stopAutoRefresh: function() {
            if (this.autoRefreshInterval) {
                clearInterval(this.autoRefreshInterval);
                this.autoRefreshInterval = null;
            }
        },
        
        // Show success message
        showSuccess: function(message) {
            this.showMessage(message, 'success');
        },
        
        // Show error message
        showError: function(message) {
            this.showMessage(message, 'error');
        },
        
        // Show message
        showMessage: function(message, type) {
            const messageDiv = $(`<div class="wpvn-${type}">${message}</div>`);
            $('.wpvn-container').prepend(messageDiv);
              setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        },
        
        // Clean up when leaving page
        destroy: function() {
            this.stopAutoRefresh();
            
            if (this.table) {
                this.table.destroy();
            }
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        if ($('#wpvn-logs-table').length) {
            WPVN_Logs.init();
        }
    });
    
    // Clean up on page unload
    $(window).on('beforeunload', function() {
        if (window.WPVN_Logs) {
            WPVN_Logs.destroy();
        }
    });
    
})(jQuery);
