/**
 * Common JavaScript functionality for WP Visitor Notify
 * Shared JS functionality for all admin pages
 */

(function($) {
    'use strict';
    
    // Common WPVN object
    window.WPVN = {
        ajaxUrl: ajaxurl,
        nonce: wpvn_ajax ? wpvn_ajax.nonce : '',
        
        // AJAX helper method
        ajax: function(action, data, callback) {
            return $.post(this.ajaxUrl, {
                action: 'wpvn_' + action,
                nonce: this.nonce,
                ...data
            }, callback);
        },
        
        // Show loading spinner
        showLoading: function(element) {
            if (element) {
                $(element).addClass('wpvn-loading');
            }
        },
        
        // Hide loading spinner
        hideLoading: function(element) {
            if (element) {
                $(element).removeClass('wpvn-loading');
            }
        },
        
        // Show message
        showMessage: function(message, type = 'info', duration = 5000) {
            const messageDiv = $(`<div class="wpvn-message wpvn-${type}">${message}</div>`);
            $('.wpvn-container').prepend(messageDiv);
              if (duration > 0) {
                setTimeout(() => {
                    messageDiv.remove();
                }, duration);
            }
            
            return messageDiv;
        },
        
        // Show success message
        showSuccess: function(message, duration = 5000) {
            return this.showMessage(message, 'success', duration);
        },
        
        // Show error message
        showError: function(message, duration = 5000) {
            return this.showMessage(message, 'error', duration);
        },
        
        // Show warning message
        showWarning: function(message, duration = 5000) {
            return this.showMessage(message, 'warning', duration);
        },
        
        // Format number with separators
        formatNumber: function(num) {
            return new Intl.NumberFormat().format(num);
        },
        
        // Format bytes to human readable
        formatBytes: function(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        },
        
        // Format duration in seconds to human readable
        formatDuration: function(seconds) {
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);
            
            if (days > 0) {
                return `${days}d ${hours % 24}h`;
            } else if (hours > 0) {
                return `${hours}h ${minutes % 60}m`;
            } else if (minutes > 0) {
                return `${minutes}m ${seconds % 60}s`;
            } else {
                return `${seconds}s`;
            }
        },
        
        // Debounce function
        debounce: function(func, wait, immediate) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },
        
        // Throttle function
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },
        
        // Copy text to clipboard
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                return navigator.clipboard.writeText(text).then(() => {
                    this.showSuccess('Copied to clipboard');
                }).catch(() => {
                    this.showError('Failed to copy to clipboard');
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    this.showSuccess('Copied to clipboard');
                } catch (err) {
                    this.showError('Failed to copy to clipboard');
                }
                document.body.removeChild(textArea);
            }
        },
        
        // Confirm dialog
        confirm: function(message, callback) {
            if (window.confirm(message)) {
                if (typeof callback === 'function') {
                    callback();
                }
                return true;
            }
            return false;
        },
        
        // Check if element is in viewport
        isInViewport: function(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
    };
    
    // Initialize common functionality
    $(document).ready(function() {
        // Add copy functionality to IP addresses
        $(document).on('click', '.wpvn-ip-address', function() {
            const ip = $(this).text();
            WPVN.copyToClipboard(ip);
        });
          // Add tooltips functionality
        if ($.fn.tooltip) {
            $('[title]').tooltip();
        }
        
        console.log('WP Visitor Notify: Common JS loaded');
    });
    
})(jQuery);
