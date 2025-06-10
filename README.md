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
- **PSR-3 Compliant Logging** - Professional logging system
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
- **Logger** (`class-logger.php`) - PSR-3 compliant logging system

### Database Schema

The plugin creates 5 optimized tables:

1. **`wp_wpvn_sessions`** - Visitor session tracking (21 fields)
2. **`wp_wpvn_page_views`** - Page view analytics (10 fields)
3. **`wp_wpvn_notification_rules`** - Alert configuration (15 fields)
4. **`wp_wpvn_notification_history`** - Notification tracking (10 fields)
5. **`wp_wpvn_logs`** - System logging (9 fields)

### Custom Autoloader

Features a PSR-4 compatible autoloader without Composer dependency for maximum compatibility.

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

Run the included test suite:

```bash
php test/simple-test.php
php test/step-test.php
php test/final-test.php
```

## 📚 Documentation

Comprehensive documentation is available in the `/docs` folder:

- [Technical Documentation](docs/technical-documentation.md)
- [Architecture Quick Reference](docs/architecture-quick-reference.md)
- [Method Reference Guide](docs/method-reference-guide.md)
- [Usage Instructions](docs/usage-instructions.md)

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

### v1.0.0 (Current)
- Initial release
- Core plugin architecture
- Database schema implementation
- Custom autoloader
- PSR-3 logging system
- Docker development environment

---

**Made with ❤️ for the WordPress community**

