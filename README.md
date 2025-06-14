# WP Visitor Notify

A lightweight WordPress plugin for tracking visitor statistics and notifications.

## Description

WP Visitor Notify is a clean, modern WordPress plugin that tracks visitor statistics including device types, browsers, operating systems, and page views. Built with clean architecture principles and comprehensive testing.

## Features

- **Device Detection**: Automatic detection of mobile, tablet, and desktop devices
- **Browser & OS Detection**: Identifies visitor browsers and operating systems
- **Visit Tracking**: Tracks page views and unique visitors
- **Analytics Dashboard**: Clean admin interface with statistics
- **Logging System**: Comprehensive logging for debugging and monitoring
- **Clean Architecture**: Modern OOP design with dependency injection
- **Security**: Input sanitization and proper WordPress integration

## Requirements

- WordPress 5.0 or higher
- PHP 8.2 or higher
- MySQL 5.7 or higher

## Installation

1. Download the plugin files
2. Upload to your `/wp-content/plugins/wp-visitor-notify` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure settings in the WordPress admin panel

## Usage

After activation, the plugin automatically starts tracking visitors. Access the dashboard through:

**WordPress Admin → Tools → WP Visitor Notify**

### Admin Features

- **Dashboard**: Overview of visitor statistics
- **Analytics**: Detailed reports on devices, browsers, and page views
- **Settings**: Plugin configuration options
- **Logs**: System logs for debugging

## Architecture

The plugin follows clean architecture principles:

```
wp-visitor-notify/
├── wp-visitor-notify.php     # Main plugin file
├── includes/                 # Core functionality
│   ├── class-plugin.php      # Main plugin class (Singleton)
│   ├── class-detector.php    # Device/browser detection
│   ├── class-tracker.php     # Visit tracking
│   ├── class-analytics.php   # Statistics processing
│   ├── class-database.php    # Database operations
│   ├── class-logger.php      # Logging system
│   └── class-uninstaller.php # Cleanup on uninstall
├── admin/                    # Admin interface
│   ├── class-admin.php       # Main admin controller
│   ├── class-dashboard.php   # Dashboard page
│   ├── class-settings.php    # Settings page
│   └── class-logs.php        # Logs page
└── languages/                # Translation files
    └── wp-visitor-notify.pot
```

## Database Tables

The plugin creates the following tables:

- `wp_wpvn_sessions` - Visitor sessions
- `wp_wpvn_page_views` - Page view tracking
- `wp_wpvn_notification_rules` - Notification configuration
- `wp_wpvn_notification_history` - Notification log
- `wp_wpvn_logs` - System logs

## Development

This plugin is built with modern development practices:

- **PHP 8.2+** with strict typing
- **PSR-4** autoloading
- **Dependency Injection** for clean testable code
- **Singleton Pattern** for plugin lifecycle
- **Comprehensive Testing** (100% test coverage)

### Code Quality

- Follows WordPress Coding Standards
- Clean Architecture principles
- SOLID design principles
- Comprehensive PHPDoc documentation

## Hooks & Filters

### Actions

- `wpvn_visitor_tracked` - Fired when a visitor is tracked
- `wpvn_session_started` - Fired when a new session starts

### Filters

- `wpvn_track_admin` - Control admin page tracking (default: false)
- `wpvn_session_duration` - Modify session duration (default: 30 minutes)
- `wpvn_excluded_ips` - Array of IPs to exclude from tracking

## Configuration

### Basic Settings

```php
// Disable admin tracking
add_filter('wpvn_track_admin', '__return_false');

// Set custom session duration (in seconds)
add_filter('wpvn_session_duration', function() {
    return 3600; // 1 hour
});

// Exclude specific IPs
add_filter('wpvn_excluded_ips', function($ips) {
    $ips[] = '192.168.1.100';
    return $ips;
});
```

## Privacy & GDPR

The plugin is designed with privacy in mind:

- No personal data collection by default
- IP addresses are hashed for anonymization
- Configurable data retention periods
- Easy data export/deletion capabilities

## Performance

- Minimal database queries
- Efficient caching strategies
- Lightweight frontend impact
- Optimized for high-traffic sites

## Support

For support and bug reports, please use the WordPress.org plugin repository or contact the developer.

## Changelog

### 1.0.0 (2025-06-15)
- Initial release
- Device/browser/OS detection
- Visit tracking system
- Admin dashboard
- Analytics reporting
- Comprehensive logging
- Clean architecture implementation

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2025 Avniloff Avraham

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## Author

**Avniloff Avraham**

A clean, modern, and well-tested WordPress plugin built with professional development practices.
