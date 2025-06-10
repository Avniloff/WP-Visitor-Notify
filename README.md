# WP Visitor Notify

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-6.2%2B-blue)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-purple)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-green)](https://www.gnu.org/licenses/gpl-2.0.html)

Privacy-first visitor analytics and notification system for WordPress sites with real-time tracking, intelligent alerts, and comprehensive dashboards.

## 🚀 Features

- **Real-time Visitor Tracking** - Monitor site visitors in real-time
- **Intelligent Notifications** - Configurable alerts for visitor activity
- **Privacy-First Design** - Compliant with GDPR and privacy regulations
- **Comprehensive Analytics** - Detailed visitor insights and reports
- **Custom Database Schema** - Optimized 5-table structure for performance
- **PSR-3 Compliant Logging** - Optimized logging system (Docker-friendly)
- **Modern PHP 8.2+** - Built with latest PHP features and strict typing

## 📋 Requirements

- **WordPress:** 6.2 or higher
- **PHP:** 8.2 or higher
- **MySQL:** 5.7 or higher

## 🛠 Installation

### Manual Installation

1. Download the plugin files
2. Upload the `wp-visitor-notify` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure settings in the WordPress admin panel

### Development Installation

```bash
git clone https://github.com/avniloff/WP-Visitor-Notify.git
cd WP-Visitor-Notify
```

## 🏗 Architecture

The plugin is built with a modular architecture using modern PHP practices:

### Core Classes

- **Plugin** (`class-plugin.php`) - Main plugin controller with singleton pattern
- **Database** (`class-database.php`) - Database operations and schema management  
- **Logger** (`class-logger.php`) - Optimized PSR-3 logging system (6 methods, Docker-friendly)

### Database Schema

The plugin creates 5 optimized tables:

1. **`wp_wpvn_sessions`** - Visitor session tracking (21 fields)
2. **`wp_wpvn_page_views`** - Page view analytics (10 fields)
3. **`wp_wpvn_notification_rules`** - Alert configuration (15 fields)
4. **`wp_wpvn_notification_history`** - Notification tracking (10 fields)
5. **`wp_wpvn_logs`** - System logging (9 fields)

### Custom Autoloader

Features a PSR-4 compatible autoloader without Composer dependency for maximum compatibility.

### Recent Optimizations (June 2025)

The Logger system has been recently optimized for better performance and Docker compatibility:

- **Simplified log levels**: Reduced from 5 to 3 levels (`debug`, `info`, `error`)
- **Docker-friendly output**: Direct `error_log()` integration for container visibility
- **Method reduction**: Optimized from 8 to 6 methods (-25% code reduction)
- **Comprehensive testing**: 51 automated tests validating all functionality
- **Performance metrics**: Memory usage tracking and execution time analysis

## 🔧 Development

### Docker Environment

The project includes a Docker setup for local development:

```bash
docker-compose up -d
```

This creates:
- WordPress container (PHP 8.2)
- MySQL 5.7 database
- phpMyAdmin interface

### Testing

Run the comprehensive test suite:

```bash
# Run all tests
php test/test-runner.php

# Individual test files
php test/test-all-components.php    # 51 tests covering all components
php test/test-performance.php       # Performance and quality analysis
php test/test-logger-real.php       # Real Logger implementation test
php test/test-logger-simplified.php # Optimized Logger test
```

#### Test Results
- **51 tests total** with 100% success rate
- **Performance analysis** with memory usage tracking
- **Code quality metrics** and architecture validation
- **Logger optimization** validated and documented

## 📚 Documentation

Comprehensive documentation is available in the `/docs` folder:

- [Project Overview](docs/01-overview.md) - Complete project overview
- [Quick Start Guide](docs/02-quick-start.md) - Getting started tutorial
- [API Reference](docs/03-api-reference.md) - Complete API documentation
- [Technical Specifications](docs/04-technical-specs.md) - Detailed technical specs
- [Implementation Guide](docs/05-implementation-guide.md) - Development guide

Additional development documentation:
- [Logger Analysis](test/logger-analysis.md) - Logger method analysis and optimization
- [Optimization Report](test/logger-optimization-report.md) - Performance optimization results

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 🔮 Roadmap

Future classes to be implemented:

- **Tracker** (`class-tracker.php`) - Enhanced visitor tracking
- **Analytics** (`class-analytics.php`) - Advanced data analysis
- **Detector** (`class-detector.php`) - Device/browser detection
- **Notifier** (`class-notifier.php`) - Email notification system

## 📞 Support

- [GitHub Issues](https://github.com/avniloff/WP-Visitor-Notify/issues)
- [Documentation](docs/)

## 🏷 Version History

### v1.0.0 (Current - June 2025)
- Initial release
- Core plugin architecture with Singleton pattern
- Optimized database schema (5 tables, simplified implementation)
- Custom PSR-4 autoloader (no Composer dependency)
- **Optimized PSR-3 logging system** (6 methods, 3 log levels)
- **Comprehensive test suite** (51 tests, 100% success rate)
- **Performance analysis tools** and quality metrics
- Docker development environment with logging visibility
- Complete documentation suite (6 files)

---

**Made with ❤️ for the WordPress community**