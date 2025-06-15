/**
 * Settings functionality for WP Visitor Notify
 * Handles form validation, AJAX saving, and settings management
 */

(function($) {
    'use strict';
    
    // Settings object
    window.WPVN_Settings = {
        form: null,
        originalData: {},
          init: function() {
            this.form = $('#wpvn-settings-form');
            this.bindEvents();
            this.initToggles();
            this.initAdvancedSections();
            this.initNotificationLogic();
            this.storeOriginalData();
            this.initValidation();
        },
        
        // Bind event handlers
        bindEvents: function() {
            // Form submission
            this.form.on('submit', (e) => {
                e.preventDefault();
                this.saveSettings();
            });
            
            // Reset button
            $(document).on('click', '#wpvn-reset-settings', (e) => {
                e.preventDefault();
                this.resetSettings();
            });
            
            // Export settings
            $(document).on('click', '#wpvn-export-settings', (e) => {
                e.preventDefault();
                this.exportSettings();
            });
            
            // Import settings
            $(document).on('change', '#wpvn-import-file', (e) => {
                this.importSettings(e.target.files[0]);
            });
            
            // Test database connection
            $(document).on('click', '#wpvn-test-db', (e) => {
                e.preventDefault();
                this.testDatabaseConnection();
            });
            
            // Clear logs
            $(document).on('click', '#wpvn-clear-logs', (e) => {
                e.preventDefault();
                this.clearLogs();
            });
            
            // Form change detection
            this.form.on('change input', () => {
                this.markAsChanged();
            });
            
            // Prevent accidental navigation
            $(window).on('beforeunload', (e) => {
                if (this.hasUnsavedChanges()) {
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
        },
        
        // Initialize toggle switches
        initToggles: function() {
            $('.wpvn-toggle input[type="checkbox"]').each(function() {
                const checkbox = $(this);
                const isChecked = checkbox.is(':checked');
                  checkbox.on('change', function() {
                    const relatedFields = $(`[data-depends="${checkbox.attr('name')}"]`);
                    if (this.checked) {
                        relatedFields.show();
                    } else {
                        relatedFields.hide();
                    }
                });
                
                // Trigger initial state
                checkbox.trigger('change');
            });
        },
          // Initialize advanced settings sections
        initAdvancedSections: function() {            $('.wpvn-advanced-header').on('click', function() {
                const content = $(this).siblings('.wpvn-advanced-content');
                const icon = $(this).find('.toggle-icon');
                
                content.toggle();
                content.toggleClass('open');
                
                if (icon.length) {
                    icon.text(content.hasClass('open') ? '−' : '+');
                }
            });
        },

        // Initialize notification-specific logic
        initNotificationLogic: function() {
            // Handle threshold count field visibility
            const thresholdCheckbox = $('#enable_threshold_notifications');
            const thresholdCountRow = $('#visitor_threshold_count').closest('tr');
            
            if (thresholdCheckbox.length && thresholdCountRow.length) {
                const toggleThresholdCount = () => {
                    if (thresholdCheckbox.is(':checked')) {
                        thresholdCountRow.show();
                    } else {
                        thresholdCountRow.hide();
                    }
                };
                
                thresholdCheckbox.on('change', toggleThresholdCount);
                toggleThresholdCount(); // Initial state
            }
            
            // Show warning for new visitor notifications
            const newVisitorCheckbox = $('#enable_new_visitor_notifications');
            if (newVisitorCheckbox.length) {
                newVisitorCheckbox.on('change', function() {
                    const warning = $(this).closest('td').find('.wpvn-warning');
                    if (this.checked) {
                        if (!warning.length) {
                            $(this).closest('td').append(
                                '<div class="wpvn-warning" style="color: #dc3232; font-weight: bold; margin-top: 8px;">' +
                                '⚠️ Warning: This will send many emails on busy sites!</div>'
                            );
                        }
                    } else {
                        warning.remove();
                    }
                });
                
                // Trigger initial state
                newVisitorCheckbox.trigger('change');
            }
        },
        
        // Store original form data for change detection
        storeOriginalData: function() {
            this.originalData = this.form.serialize();
        },
        
        // Initialize form validation
        initValidation: function() {
            // Real-time validation
            this.form.find('input, textarea, select').on('blur', (e) => {
                this.validateField($(e.target));
            });
        },
        
        // Save settings via AJAX
        saveSettings: function() {
            if (!this.validateForm()) {
                this.showError('Please fix the validation errors before saving.');
                return;
            }
            
            const submitButton = $('#wpvn-save-settings');
            const originalText = submitButton.text();
            
            submitButton.prop('disabled', true).text('Saving...');
            
            const formData = this.form.serialize();
            
            WPVN.ajax('save_settings', {
                settings: formData
            }, (response) => {
                if (response.success) {
                    this.showSuccess('Settings saved successfully!');
                    this.storeOriginalData(); // Update original data
                    this.removeChangeIndicators();
                } else {
                    this.showError('Failed to save settings: ' + response.data);
                }
            }).fail(() => {
                this.showError('Network error occurred while saving settings');
            }).always(() => {
                submitButton.prop('disabled', false).text(originalText);
            });
        },
        
        // Reset settings to defaults
        resetSettings: function() {
            if (!confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
                return;
            }
            
            WPVN.ajax('reset_settings', {}, (response) => {
                if (response.success) {
                    location.reload(); // Reload to show default values
                } else {
                    this.showError('Failed to reset settings: ' + response.data);
                }
            }).fail(() => {
                this.showError('Network error occurred while resetting settings');
            });
        },
        
        // Export settings as JSON
        exportSettings: function() {
            const formData = {};
            this.form.serializeArray().forEach(item => {
                formData[item.name] = item.value;
            });
            
            const dataStr = JSON.stringify(formData, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = `wpvn-settings-${new Date().toISOString().split('T')[0]}.json`;
            link.click();
            
            URL.revokeObjectURL(url);
        },
        
        // Import settings from JSON file
        importSettings: function(file) {
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const settings = JSON.parse(e.target.result);
                    this.applyImportedSettings(settings);
                } catch (error) {
                    this.showError('Invalid JSON file format');
                }
            };
            reader.readAsText(file);
        },
        
        // Apply imported settings to form
        applyImportedSettings: function(settings) {
            Object.keys(settings).forEach(key => {
                const field = this.form.find(`[name="${key}"]`);
                if (field.length) {
                    if (field.is(':checkbox') || field.is(':radio')) {
                        field.prop('checked', field.val() === settings[key]);
                    } else {
                        field.val(settings[key]);
                    }
                }
            });
            
            this.markAsChanged();
            this.showSuccess('Settings imported successfully. Click Save to apply changes.');
        },
        
        // Test database connection
        testDatabaseConnection: function() {
            const button = $('#wpvn-test-db');
            const originalText = button.text();
            
            button.prop('disabled', true).text('Testing...');
            
            WPVN.ajax('test_database', {}, (response) => {
                if (response.success) {
                    this.showSuccess('Database connection successful!');
                } else {
                    this.showError('Database connection failed: ' + response.data);
                }
            }).fail(() => {
                this.showError('Network error occurred while testing database');
            }).always(() => {
                button.prop('disabled', false).text(originalText);
            });
        },
        
        // Clear all logs
        clearLogs: function() {
            if (!confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
                return;
            }
            
            const button = $('#wpvn-clear-logs');
            const originalText = button.text();
            
            button.prop('disabled', true).text('Clearing...');
            
            WPVN.ajax('clear_logs', {}, (response) => {
                if (response.success) {
                    this.showSuccess('All logs cleared successfully!');
                } else {
                    this.showError('Failed to clear logs: ' + response.data);
                }
            }).fail(() => {
                this.showError('Network error occurred while clearing logs');
            }).always(() => {
                button.prop('disabled', false).text(originalText);
            });
        },
        
        // Form validation
        validateForm: function() {
            let isValid = true;
            
            this.form.find('input[required], textarea[required], select[required]').each((index, field) => {
                if (!this.validateField($(field))) {
                    isValid = false;
                }
            });
            
            return isValid;
        },
        
        // Validate individual field
        validateField: function(field) {
            const value = field.val().trim();
            const fieldName = field.attr('name');
            let isValid = true;
            let message = '';
            
            // Remove existing error
            field.removeClass('error');
            field.siblings('.field-error').remove();
            
            // Required validation
            if (field.is('[required]') && !value) {
                isValid = false;
                message = 'This field is required';
            }
            
            // Email validation
            if (field.is('[type="email"]') && value && !this.isValidEmail(value)) {
                isValid = false;
                message = 'Please enter a valid email address';
            }
            
            // Number validation
            if (field.is('[type="number"]') && value) {
                const min = field.attr('min');
                const max = field.attr('max');
                const num = parseFloat(value);
                
                if (isNaN(num)) {
                    isValid = false;
                    message = 'Please enter a valid number';
                } else if (min && num < parseFloat(min)) {
                    isValid = false;
                    message = `Value must be at least ${min}`;
                } else if (max && num > parseFloat(max)) {
                    isValid = false;
                    message = `Value must be no more than ${max}`;
                }
            }
              // Custom validation rules
            if (fieldName === 'log_retention_days' && value && parseInt(value) < 1) {
                isValid = false;
                message = 'Log retention must be at least 1 day';
            }
            
            // Notification threshold validation
            if (fieldName === 'wpvn_settings[visitor_threshold_count]' && value) {
                const num = parseInt(value);
                if (num < 1) {
                    isValid = false;
                    message = 'Threshold must be at least 1 visitor';
                } else if (num > 10000) {
                    isValid = false;
                    message = 'Threshold cannot exceed 10,000 visitors';
                }
            }
            
            // Notification email validation (required if any notifications are enabled)
            if (fieldName === 'wpvn_settings[notification_email]') {
                const anyNotificationEnabled = 
                    $('#enable_new_visitor_notifications').is(':checked') ||
                    $('#enable_threshold_notifications').is(':checked') ||
                    $('#enable_new_device_notifications').is(':checked');
                    
                if (anyNotificationEnabled && !value) {
                    isValid = false;
                    message = 'Email is required when notifications are enabled';
                }
            }
            
            if (!isValid) {
                field.addClass('error');
                field.after(`<div class="field-error">${message}</div>`);
            }
            
            return isValid;
        },
        
        // Email validation helper
        isValidEmail: function(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        },
        
        // Check if form has unsaved changes
        hasUnsavedChanges: function() {
            return this.form.serialize() !== this.originalData;
        },
        
        // Mark form as changed
        markAsChanged: function() {
            this.form.addClass('changed');
            $('#wpvn-save-settings').addClass('unsaved');
        },
        
        // Remove change indicators
        removeChangeIndicators: function() {
            this.form.removeClass('changed');
            $('#wpvn-save-settings').removeClass('unsaved');
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
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        if ($('#wpvn-settings-form').length) {
            WPVN_Settings.init();
        }
    });
    
})(jQuery);
