# WordPress Visitor Notify Plugin - Техническая спецификация

**Version**: 1.0  
**Target Audience**: Junior to Mid-level WordPress Developers  
**Last Updated**: June 10, 2025

> **🔄 ОБНОВЛЕНО 10.06.2025**: Исправлены противоречия в структуре файлов!  
> **✅ Реальные файлы**: `class-database.php`, `class-logger.php`, `class-plugin.php`  
> **❌ НЕ ИСПОЛЬЗУЕТСЯ**: `composer.json` (собственный автозагрузчик)  
> **📁 Актуальная структура**: Обновлена согласно реальным файлам проекта

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technology Stack & Requirements](#2-technology-stack--requirements)
3. [Architecture & Design Patterns](#3-architecture--design-patterns)
4. [Database Schema](#4-database-schema)
5. [File Structure & Components](#5-file-structure--components)
6. [Core Features & Functionality](#6-core-features--functionality)
7. [Security Implementation](#7-security-implementation)
8. [Performance & Optimization](#8-performance--optimization)
9. [API Documentation](#9-api-documentation)
10. [Testing Strategy](#10-testing-strategy)
11. [Installation & Migration](#11-installation--migration)
12. [Development Workflow](#12-development-workflow)
13. [Deployment & Maintenance](#13-deployment--maintenance)

---

## 1. Project Overview

### 1.1 Plugin Description

**WordPress Visitor Notify** is a comprehensive, privacy-first analytics plugin designed to provide real-time visitor tracking and intelligent notification systems for WordPress sites.

#### Core Capabilities:
- **Real-time Visitor Tracking**: Page views, unique sessions, visit duration
- **Advanced Device Detection**: Mobile, desktop, tablet, bot identification
- **Smart Notifications**: Configurable email alerts and periodic reports
- **Privacy Compliance**: GDPR-ready with local data storage and IP anonymization
- **Comprehensive Analytics**: Interactive dashboards with detailed metrics
- **Developer-Friendly**: Extensive hooks, filters, and REST API integration

#### Target Use Cases:
- Site administrators monitoring traffic patterns
- Content creators tracking engagement
- E-commerce sites monitoring visitor behavior
- Agencies managing multiple client sites
- Developers needing visitor analytics integration

### 1.2 Plugin Goals

- **Simplicity**: Easy installation and configuration
- **Performance**: Minimal impact on site speed
- **Privacy**: Full GDPR compliance without external services
- **Extensibility**: Rich API for custom integrations
- **Reliability**: Robust error handling and logging

---

## 2. Technology Stack & Requirements

### 2.1 Core Requirements

| Component | Version | Notes |
|-----------|---------|-------|
| **PHP** | 8.2+ | Required for modern language features, typed properties |
| **WordPress** | 6.2+ | Minimum for REST API v2, block editor support |
| **MySQL** | 5.7+ / MariaDB 10.3+ | For JSON column support, window functions |
| **Web Server** | Apache 2.4+ / Nginx 1.18+ | mod_rewrite/URL rewriting required |

### 2.2 Frontend Technologies

- **Chart.js** (v4.x) - Data visualization via CDN
- **Vanilla JavaScript** (ES6+) - No jQuery dependency
- **CSS Grid/Flexbox** - Modern responsive layouts
- **WordPress Admin UI** - Native styling integration

### 2.3 Development Dependencies

**ВНИМАНИЕ:** Проект использует собственный автозагрузчик, composer.json НЕ ТРЕБУЕТСЯ!

```json
// Для будущих нужд (тестирование, линтеры):
{
  "require-dev": {
    "phpunit/phpunit": "^10.0",
    "wp-cli/wp-cli": "^2.8",
    "squizlabs/php_codesniffer": "^3.7",
    "phpcompatibility/php-compatibility": "^9.3"
  }
}
```

### 2.4 WordPress Standards Compliance

- **WPCS** (WordPress Coding Standards)
- **PHPCompatibility** for version checking
- **WordPress Plugin Handbook** guidelines
- **GDPR/Privacy** compliance requirements

---

## 3. Architecture & Design Patterns

### 3.1 Architectural Principles

#### 3.1.1 Singleton Pattern
```php
/**
 * Main plugin instance - ensures single initialization
 */
class WPVN_Plugin {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

#### 3.1.2 Factory Pattern
```php
/**
 * Component factory for dependency injection
 */
class WPVN_Factory {
    public static function create_tracker(): WPVN_Tracker {
        return new WPVN_Tracker(
            self::create_detector(),
            self::create_logger(),
            self::create_db()
        );
    }
}
```

#### 3.1.3 Observer Pattern
```php
/**
 * Hook management system
 */
class WPVN_Hook_Manager {
    private array $observers = [];
    
    public function add_observer(string $hook, callable $callback): void {
        $this->observers[$hook][] = $callback;
    }
}
```

#### 3.1.4 MVC-Lite Pattern
- **Models**: Data access layer (`WPVN_DB`, `WPVN_Analytics`)
- **Views**: Template files (`admin/templates/`)
- **Controllers**: Admin page handlers (`admin/class-*.php`)

### 3.2 Dependency Injection

```php
interface WPVN_Container_Interface {
    public function bind(string $abstract, callable $concrete): void;
    public function resolve(string $abstract): object;
}

class WPVN_Container implements WPVN_Container_Interface {
    private array $bindings = [];
    
    public function bind(string $abstract, callable $concrete): void {
        $this->bindings[$abstract] = $concrete;
    }
    
    public function resolve(string $abstract): object {
        return $this->bindings[$abstract]();
    }
}
```

---

## 4. Database Schema

### 4.1 Primary Tables

#### 4.1.1 Visitor Sessions Table
```sql
CREATE TABLE {prefix}wpvn_sessions (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id VARCHAR(64) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL, -- Supports IPv6
    ip_hash VARCHAR(64) NOT NULL, -- SHA-256 hash for anonymization
    user_agent TEXT,
    device_type ENUM('desktop', 'mobile', 'tablet', 'bot') DEFAULT 'desktop',
    browser VARCHAR(100),
    operating_system VARCHAR(100),
    is_bot BOOLEAN DEFAULT FALSE,
    user_id BIGINT(20) UNSIGNED DEFAULT NULL,
    first_visit DATETIME NOT NULL,
    last_activity DATETIME NOT NULL,
    page_count INT UNSIGNED DEFAULT 1,
    total_duration INT UNSIGNED DEFAULT 0, -- in seconds
    referrer TEXT,
    utm_source VARCHAR(100),
    utm_medium VARCHAR(100),
    utm_campaign VARCHAR(100),
    country_code CHAR(2),
    city VARCHAR(100),
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY session_id (session_id),
    KEY ip_hash (ip_hash),
    KEY device_type (device_type),
    KEY is_bot (is_bot),
    KEY user_id (user_id),
    KEY created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 4.1.2 Page Views Table
```sql
CREATE TABLE {prefix}wpvn_page_views (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id VARCHAR(64) NOT NULL,
    post_id BIGINT(20) UNSIGNED DEFAULT NULL,
    page_url TEXT NOT NULL,
    page_title VARCHAR(255),
    page_type VARCHAR(50), -- 'post', 'page', 'archive', 'home', etc.
    view_duration INT UNSIGNED DEFAULT 0, -- in seconds
    scroll_depth TINYINT UNSIGNED DEFAULT 0, -- percentage 0-100
    exit_page BOOLEAN DEFAULT FALSE,
    viewed_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY session_id (session_id),
    KEY post_id (post_id),
    KEY page_type (page_type),
    KEY viewed_at (viewed_at),
    FOREIGN KEY (session_id) REFERENCES {prefix}wpvn_sessions(session_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 4.1.3 Notification Rules Table
```sql
CREATE TABLE {prefix}wpvn_notification_rules (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    rule_type ENUM('threshold', 'scheduled', 'event') NOT NULL,
    conditions JSON NOT NULL, -- Store complex rule conditions
    threshold_value INT UNSIGNED,
    threshold_period ENUM('hour', 'day', 'week', 'month'),
    schedule_frequency ENUM('hourly', 'daily', 'weekly', 'monthly'),
    schedule_time TIME DEFAULT '09:00:00',
    recipients JSON NOT NULL, -- Array of email addresses
    email_template TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    last_triggered DATETIME DEFAULT NULL,
    trigger_count INT UNSIGNED DEFAULT 0,
    created_by BIGINT(20) UNSIGNED,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY rule_type (rule_type),
    KEY is_active (is_active),
    KEY created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 4.1.4 Notification History Table
```sql
CREATE TABLE {prefix}wpvn_notification_history (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    rule_id BIGINT(20) UNSIGNED NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT,
    data_snapshot JSON, -- Analytics data at time of sending
    status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
    error_message TEXT DEFAULT NULL,
    sent_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY rule_id (rule_id),
    KEY status (status),
    KEY sent_at (sent_at),
    FOREIGN KEY (rule_id) REFERENCES {prefix}wpvn_notification_rules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 4.1.5 System Logs Table
```sql
CREATE TABLE {prefix}wpvn_logs (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    level ENUM('debug', 'info', 'warning', 'error', 'critical') NOT NULL,
    message TEXT NOT NULL,
    context JSON DEFAULT NULL,
    component VARCHAR(100), -- 'tracker', 'notifier', 'analytics', etc.
    user_id BIGINT(20) UNSIGNED DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY level (level),
    KEY component (component),
    KEY created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.2 Database Indexes Strategy

#### 4.2.1 Performance Indexes
```sql
-- Composite indexes for common queries
CREATE INDEX idx_sessions_date_device ON {prefix}wpvn_sessions (created_at, device_type);
CREATE INDEX idx_pageviews_date_type ON {prefix}wpvn_page_views (viewed_at, page_type);
CREATE INDEX idx_logs_level_date ON {prefix}wpvn_logs (level, created_at);

-- Covering indexes for analytics queries
CREATE INDEX idx_sessions_analytics ON {prefix}wpvn_sessions (created_at, device_type, is_bot, page_count);
```

### 4.3 Data Retention Policies

```php
class WPVN_Data_Retention {
    private const DEFAULT_RETENTION_DAYS = 365;
    
    public function get_retention_settings(): array {
        return [
            'sessions' => get_option('wpvn_retention_sessions', self::DEFAULT_RETENTION_DAYS),
            'page_views' => get_option('wpvn_retention_pageviews', self::DEFAULT_RETENTION_DAYS),
            'logs' => get_option('wpvn_retention_logs', 90),
            'notifications' => get_option('wpvn_retention_notifications', 180)
        ];
    }
}
```

---

## 5. File Structure & Components

### 5.1 Simplified Directory Structure

```plaintext
wp-visitor-notify/
├── wp-visitor-notify.php                # Main plugin file (✅ СУЩЕСТВУЕТ)
├── uninstall.php                        # Cleanup on plugin deletion (✅ СУЩЕСТВУЕТ)
├── includes/                            # Core PHP classes
│   ├── class-plugin.php                 # Main plugin class (Singleton) (✅ СУЩЕСТВУЕТ)
│   ├── class-database.php               # Database operations (✅ СУЩЕСТВУЕТ)
│   ├── class-logger.php                 # Logging system (✅ СУЩЕСТВУЕТ)
│   ├── class-tracker.php                # Visitor tracking engine (🔲 ПЛАНИРУЕТСЯ)
│   ├── class-analytics.php              # Analytics data processor (🔲 ПЛАНИРУЕТСЯ)
│   ├── class-detector.php               # Device/browser detection (🔲 ПЛАНИРУЕТСЯ)
│   ├── class-notifier.php               # Notification engine (🔲 ПЛАНИРУЕТСЯ)
│   ├── class-activator.php              # Plugin activation handler (🔲 ПЛАНИРУЕТСЯ)
│   ├── class-deactivator.php            # Plugin deactivation handler (🔲 ПЛАНИРУЕТСЯ)
│   ├── class-cleanup.php                # Data cleanup utilities (🔲 ПЛАНИРУЕТСЯ)
│   └── class-validator.php              # Input validation (🔲 ПЛАНИРУЕТСЯ)
├── admin/                               # WordPress admin integration (✅ СУЩЕСТВУЕТ)
│   ├── class-admin.php                  # Main admin controller (✅ СУЩЕСТВУЕТ)
│   ├── class-dashboard.php              # Dashboard page controller (✅ СУЩЕСТВУЕТ)
│   ├── class-settings.php               # Settings page controller (✅ СУЩЕСТВУЕТ)
│   ├── class-notifications.php          # Notifications page controller (✅ СУЩЕСТВУЕТ)
│   ├── class-logs.php                   # Logs page controller (✅ СУЩЕСТВУЕТ)
│   ├── templates/                       # Admin page templates (✅ СУЩЕСТВУЕТ)
│   │   ├── dashboard.php                # Main dashboard (✅ СУЩЕСТВУЕТ)
│   │   ├── settings.php                 # Settings form (✅ СУЩЕСТВУЕТ)
│   │   ├── notifications.php            # Notification rules UI (✅ СУЩЕСТВУЕТ)
│   │   └── logs.php                     # Log entries viewer (✅ СУЩЕСТВУЕТ)
│   └── assets/                          # CSS and JS for admin (✅ СУЩЕСТВУЕТ)
│       ├── css/admin.css                # Admin styles (✅ СУЩЕСТВУЕТ)
│       └── js/admin.js                  # Admin functionality (✅ СУЩЕСТВУЕТ)
├── public/                              # Frontend assets (🔲 ПЛАНИРУЕТСЯ)
│   ├── js/tracking.js                   # Visitor tracking script (🔲 ПЛАНИРУЕТСЯ)
│   └── css/public.css                   # Frontend styles (🔲 ПЛАНИРУЕТСЯ)
├── languages/                           # Internationalization (✅ СУЩЕСТВУЕТ)
│   └── wp-visitor-notify.pot            # Translation template (🔲 ПЛАНИРУЕТСЯ)
├── tests/                               # Testing suite (🔲 ПЛАНИРУЕТСЯ)
│   ├── unit/                            # Unit tests (🔲 ПЛАНИРУЕТСЯ)
│   └── integration/                     # Integration tests (🔲 ПЛАНИРУЕТСЯ)
├── docs/                                # Documentation (✅ СУЩЕСТВУЕТ)
│   └── README.md                        # Installation and usage (✅ СУЩЕСТВУЕТ)
├── Dockerfile                           # Development container (✅ СУЩЕСТВУЕТ)
├── docker-compose.yml                   # Development environment (✅ СУЩЕСТВУЕТ)
└── .gitignore                          # Git ignore patterns (🔲 ПЛАНИРУЕТСЯ)
```

**Key Design Principles:**
- **Flat Structure**: Avoid over-nesting for easier navigation
- **Clear Separation**: Core logic, admin interface, and frontend assets separated
- **Custom Autoloader**: Uses PSR-4 compatible custom autoloader (NO Composer needed!)
- **Standard WordPress**: Follow WordPress plugin conventions

**ВАЖНО:** В проекте используется собственный автозагрузчик через `spl_autoload_register()` в файле `wp-visitor-notify.php`. Composer НЕ используется и НЕ требуется!
│   ├── bootstrap.php                    # Test bootstrap
│   ├── unit/                            # Unit tests
│   │   ├── test-tracker.php
│   │   ├── test-analytics.php
│   │   └── test-notifier.php
│   ├── integration/                     # Integration tests
│   │   ├── test-database.php
│   │   └── test-api.php
│   ├── e2e/                             # End-to-end tests
│   │   └── cypress/
│   └── fixtures/                        # Test data
├── docs/                                # Documentation
│   ├── installation.md
│   ├── configuration.md
│   ├── api-reference.md
│   └── developer-guide.md
├── docker/                              # Development environment
│   ├── Dockerfile
│   ├── docker-compose.yml
│   └── config/
└── bin/                                 # Build scripts
    ├── build.sh
    └── deploy.sh
```

### 5.2 Component Responsibilities

#### 5.2.1 Core Components

| Component | Responsibility | Status |
|-----------|----------------|---------|
| `WPVN_Plugin` | Main plugin initialization and lifecycle | ✅ СУЩЕСТВУЕТ |
| `WPVN_Container` | Dependency injection and service location | 🔲 ПЛАНИРУЕТСЯ |
| `WPVN_Hook_Manager` | WordPress hook registration and management | 🔲 ПЛАНИРУЕТСЯ |
| `WPVN_Activator` | Plugin activation procedures | 🔲 ПЛАНИРУЕТСЯ |
| `WPVN_Deactivator` | Plugin deactivation cleanup | 🔲 ПЛАНИРУЕТСЯ |

#### 5.2.2 Database Layer

| Component | Responsibility |
|-----------|----------------|
| `WPVN_Database` | Database operations and query builder (✅ СУЩЕСТВУЕТ) |
| `WPVN_Migration` | Database schema versioning (🔲 ПЛАНИРУЕТСЯ) |
| `WPVN_Model_*` | Data models for entities (🔲 ПЛАНИРУЕТСЯ) |

#### 5.2.3 Tracking System

| Component | Responsibility | Status |
|-----------|----------------|---------|
| `WPVN_Tracker` | Main tracking coordination | 🔲 ПЛАНИРУЕТСЯ |
| `WPVN_Session_Manager` | Session lifecycle management | 🔲 ПЛАНИРУЕТСЯ |
| `WPVN_Detector` | Device and browser detection | 🔲 ПЛАНИРУЕТСЯ |
| `WPVN_Geolocation` | IP-based location services | 🔲 ПЛАНИРУЕТСЯ |

---

## 6. Core Features & Functionality

### 6.1 Visitor Tracking System

#### 6.1.1 Session Management
```php
class WPVN_Session_Manager {
    private const SESSION_TIMEOUT = 1800; // 30 minutes
    
    public function start_session(): string {
        $session_id = $this->generate_session_id();
        $this->store_session_data($session_id);
        return $session_id;
    }
    
    private function generate_session_id(): string {
        return wp_hash(
            $_SERVER['REMOTE_ADDR'] . 
            $_SERVER['HTTP_USER_AGENT'] . 
            time()
        );
    }
}
```

#### 6.1.2 Page View Tracking
```php
class WPVN_Page_Tracker {
    public function track_page_view(string $session_id): void {
        $page_data = [
            'session_id' => $session_id,
            'post_id' => get_queried_object_id(),
            'page_url' => $this->get_current_url(),
            'page_title' => wp_get_document_title(),
            'page_type' => $this->determine_page_type(),
            'viewed_at' => current_time('mysql', true)
        ];
        
        $this->db->insert_page_view($page_data);
    }
}
```

### 6.2 Analytics Engine

#### 6.2.1 Metrics Calculation
```php
class WPVN_Analytics {
    public function get_metrics(array $filters = []): array {
        return [
            'total_visits' => $this->calculate_total_visits($filters),
            'unique_visitors' => $this->calculate_unique_visitors($filters),
            'avg_session_duration' => $this->calculate_avg_duration($filters),
            'bounce_rate' => $this->calculate_bounce_rate($filters),
            'top_pages' => $this->get_top_pages($filters),
            'device_breakdown' => $this->get_device_stats($filters),
            'hourly_traffic' => $this->get_hourly_traffic($filters)
        ];
    }
    
    private function calculate_unique_visitors(array $filters): int {
        return $this->db->count_unique_sessions($filters);
    }
}
```

#### 6.2.2 Real-time Data
```php
class WPVN_Realtime {
    public function get_live_visitors(): array {
        $recent_sessions = $this->db->get_active_sessions();
        
        return array_map(function($session) {
            return [
                'location' => $this->format_location($session),
                'page' => $session['current_page'],
                'device' => $session['device_type'],
                'duration' => $this->format_duration($session['duration'])
            ];
        }, $recent_sessions);
    }
}
```

### 6.3 Notification System

#### 6.3.1 Rule Engine
```php
class WPVN_Rule_Processor {
    public function evaluate_rule(array $rule, array $current_data): bool {
        switch ($rule['type']) {
            case 'threshold':
                return $this->check_threshold($rule, $current_data);
            case 'spike':
                return $this->check_traffic_spike($rule, $current_data);
            case 'inactivity':
                return $this->check_inactivity($rule, $current_data);
            default:
                return false;
        }
    }
    
    private function check_threshold(array $rule, array $data): bool {
        $current_value = $data[$rule['metric']] ?? 0;
        return $current_value >= $rule['threshold'];
    }
}
```

#### 6.3.2 Email Templates
```php
class WPVN_Email_Template {
    public function render_template(string $template_name, array $data): string {
        $templates = [
            'threshold_alert' => $this->get_threshold_template(),
            'daily_summary' => $this->get_summary_template(),
            'weekly_report' => $this->get_weekly_template()
        ];
        
        return $this->process_template($templates[$template_name], $data);
    }
}
```

### 6.4 Admin Interface Components

#### 6.4.1 Dashboard Widgets
```php
class WPVN_Dashboard_Widgets {
    public function render_metrics_cards(array $metrics): void {
        foreach ($metrics as $metric => $value) {
            $this->render_metric_card($metric, $value);
        }
    }
    
    private function render_metric_card(string $metric, $value): void {
        include WPVN_PLUGIN_PATH . 'admin/partials/metric-card.php';
    }
}
```

#### 6.4.2 Chart Components
```javascript
class WPVNCharts {
    constructor() {
        this.charts = new Map();
    }
    
    createLineChart(elementId, data, options = {}) {
        const ctx = document.getElementById(elementId).getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                ...options
            }
        });
        
        this.charts.set(elementId, chart);
        return chart;
    }
}
```

---

## 7. Security Implementation

### 7.1 Data Validation & Sanitization

#### 7.1.1 Input Validation
```php
class WPVN_Validator {
    public function validate_session_data(array $data): array {
        return [
            'user_agent' => $this->sanitize_user_agent($data['user_agent']),
            'ip_address' => $this->validate_ip_address($data['ip_address']),
            'referrer' => esc_url_raw($data['referrer']),
            'page_url' => esc_url_raw($data['page_url'])
        ];
    }
    
    private function validate_ip_address(string $ip): ?string {
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }
}
```

#### 7.1.2 SQL Injection Prevention
```php
class WPVN_DB {
    public function get_sessions_by_date($start_date, $end_date): array {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wpvn_sessions 
             WHERE created_at BETWEEN %s AND %s",
            $start_date,
            $end_date
        );
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
}
```

### 7.2 Access Control

#### 7.2.1 Capability Checks
```php
class WPVN_Capabilities {
    public const VIEW_ANALYTICS = 'wpvn_view_analytics';
    public const MANAGE_SETTINGS = 'wpvn_manage_settings';
    public const EXPORT_DATA = 'wpvn_export_data';
    
    public function add_custom_capabilities(): void {
        $admin_role = get_role('administrator');
        $admin_role->add_cap(self::VIEW_ANALYTICS);
        $admin_role->add_cap(self::MANAGE_SETTINGS);
        $admin_role->add_cap(self::EXPORT_DATA);
    }
}
```

#### 7.2.2 Nonce Verification
```php
class WPVN_Security {
    public function verify_ajax_nonce(string $action): bool {
        $nonce = $_POST['nonce'] ?? '';
        return wp_verify_nonce($nonce, "wpvn_{$action}");
    }
    
    public function create_nonce(string $action): string {
        return wp_create_nonce("wpvn_{$action}");
    }
}
```

### 7.3 Privacy Protection

#### 7.3.1 IP Anonymization
```php
class WPVN_Privacy {
    public function anonymize_ip(string $ip): string {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0'; // Anonymize last octet
            return implode('.', $parts);
        }
        
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            // Anonymize last 80 bits (5 groups of 16 bits)
            return implode(':', array_slice($parts, 0, 3)) . '::';
        }
        
        return '';
    }
}
```

#### 7.3.2 GDPR Compliance
```php
class WPVN_GDPR {
    public function export_user_data(string $email): array {
        $user = get_user_by('email', $email);
        if (!$user) {
            return [];
        }
        
        return [
            'sessions' => $this->get_user_sessions($user->ID),
            'page_views' => $this->get_user_page_views($user->ID)
        ];
    }
    
    public function erase_user_data(string $email): bool {
        $user = get_user_by('email', $email);
        if (!$user) {
            return false;
        }
        
        return $this->anonymize_user_records($user->ID);
    }
}
```

---

## 8. Performance & Optimization

### 8.1 Caching Strategy

#### 8.1.1 Object Caching
```php
class WPVN_Cache {
    private const CACHE_GROUP = 'wpvn';
    private const DEFAULT_EXPIRATION = 3600; // 1 hour
    
    public function get_analytics_cache(string $key, array $args = []): ?array {
        $cache_key = $this->build_cache_key($key, $args);
        return wp_cache_get($cache_key, self::CACHE_GROUP);
    }
    
    public function set_analytics_cache(string $key, array $data, array $args = []): bool {
        $cache_key = $this->build_cache_key($key, $args);
        return wp_cache_set(
            $cache_key, 
            $data, 
            self::CACHE_GROUP, 
            self::DEFAULT_EXPIRATION
        );
    }
}
```

#### 8.1.2 Database Query Optimization
```php
class WPVN_Query_Optimizer {
    public function get_optimized_session_stats($date_range): array {
        global $wpdb;
        
        // Use covering index and avoid SELECT *
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as sessions,
                    COUNT(DISTINCT ip_hash) as unique_visitors,
                    AVG(total_duration) as avg_duration
                FROM {$wpdb->prefix}wpvn_sessions 
                WHERE created_at BETWEEN %s AND %s
                  AND is_bot = FALSE
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        return $wpdb->get_results(
            $wpdb->prepare($sql, $date_range['start'], $date_range['end']),
            ARRAY_A
        );
    }
}
```

### 8.2 Background Processing

#### 8.2.1 WP-Cron Jobs
```php
class WPVN_Cron {
    public function schedule_events(): void {
        // Data cleanup - daily
        if (!wp_next_scheduled('wpvn_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'wpvn_daily_cleanup');
        }
        
        // Analytics aggregation - hourly
        if (!wp_next_scheduled('wpvn_hourly_aggregation')) {
            wp_schedule_event(time(), 'hourly', 'wpvn_hourly_aggregation');
        }
        
        // Notification checks - every 5 minutes
        if (!wp_next_scheduled('wpvn_notification_check')) {
            wp_schedule_event(time(), 'wpvn_5min', 'wpvn_notification_check');
        }
    }
}
```

#### 8.2.2 Batch Processing
```php
class WPVN_Batch_Processor {
    private const BATCH_SIZE = 1000;
    
    public function process_cleanup_batch(): void {
        $retention_days = get_option('wpvn_retention_days', 365);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        global $wpdb;
        
        // Process in batches to avoid memory issues
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}wpvn_sessions 
                 WHERE created_at < %s 
                 LIMIT %d",
                $cutoff_date,
                self::BATCH_SIZE
            )
        );
        
        // Reschedule if more records to process
        if ($deleted === self::BATCH_SIZE) {
            wp_schedule_single_event(time() + 30, 'wpvn_cleanup_batch');
        }
    }
}
```

### 8.3 Frontend Performance

#### 8.3.1 Asynchronous Tracking
```javascript
class WPVNTracker {
    constructor() {
        this.queue = [];
        this.isProcessing = false;
    }
    
    track(event, data) {
        this.queue.push({ event, data, timestamp: Date.now() });
        this.processQueue();
    }
    
    async processQueue() {
        if (this.isProcessing || this.queue.length === 0) {
            return;
        }
        
        this.isProcessing = true;
        
        try {
            const batch = this.queue.splice(0, 10); // Process in batches
            await this.sendBatch(batch);
        } catch (error) {
            console.warn('WPVN: Tracking failed', error);
        } finally {
            this.isProcessing = false;
            
            // Process remaining items
            if (this.queue.length > 0) {
                setTimeout(() => this.processQueue(), 1000);
            }
        }
    }
}
```

---

## 9. API Documentation

### 9.1 REST API Endpoints

#### 9.1.1 Analytics Endpoints

**GET `/wp-json/wpvn/v1/analytics/overview`**
```php
/**
 * Get analytics overview data
 * 
 * @param string $period 'today'|'week'|'month'|'year'|'custom'
 * @param string $start_date Y-m-d format (for custom period)
 * @param string $end_date Y-m-d format (for custom period)
 * @param string $device_type 'all'|'desktop'|'mobile'|'tablet'
 * @param bool $exclude_bots Default: true
 * 
 * @return array {
 *     @type int $total_visits
 *     @type int $unique_visitors  
 *     @type float $avg_session_duration
 *     @type float $bounce_rate
 *     @type array $device_breakdown
 *     @type array $hourly_traffic
 * }
 */
```

**GET `/wp-json/wpvn/v1/analytics/pages`**
```php
/**
 * Get page analytics data
 * 
 * @param int $limit Default: 10
 * @param string $period 'today'|'week'|'month'
 * @param string $order_by 'views'|'unique_visitors'|'avg_duration'
 * 
 * @return array[] {
 *     @type string $page_url
 *     @type string $page_title
 *     @type int $views
 *     @type int $unique_visitors
 *     @type float $avg_duration
 *     @type float $bounce_rate
 * }
 */
```

#### 9.1.2 Session Endpoints

**GET `/wp-json/wpvn/v1/sessions/live`**
```php
/**
 * Get currently active sessions
 * 
 * @param int $limit Default: 50
 * @param bool $include_location Default: false
 * 
 * @return array[] {
 *     @type string $session_id
 *     @type string $current_page
 *     @type string $device_type
 *     @type int $duration
 *     @type string $location (if requested)
 * }
 */
```

#### 9.1.3 Notification Endpoints

**POST `/wp-json/wpvn/v1/notifications/rules`**
```php
/**
 * Create notification rule
 * 
 * @param string $name Rule name
 * @param string $type 'threshold'|'scheduled'|'event'
 * @param array $conditions Rule conditions
 * @param array $recipients Email addresses
 * @param string $template Email template
 * 
 * @return array {
 *     @type int $id
 *     @type string $status
 *     @type string $message
 * }
 */
```

### 9.2 JavaScript API

#### 9.2.1 Frontend Tracking API
```javascript
// Global tracking object
window.WPVN = {
    // Track custom event
    track: function(event, data = {}) {
        this.tracker.track(event, data);
    },
    
    // Get current session info
    getSession: function() {
        return this.tracker.getSessionData();
    },
    
    // Set custom visitor properties
    setVisitorProperty: function(key, value) {
        this.tracker.setProperty(key, value);
    },
    
    // Track goal conversion
    trackGoal: function(goalId, value = null) {
        this.track('goal_conversion', { goal_id: goalId, value: value });
    }
};
```

#### 9.2.2 Admin Dashboard API
```javascript
// Dashboard widget API
window.WPVNAdmin = {
    // Refresh specific widget
    refreshWidget: function(widgetId) {
        return this.widgets.refresh(widgetId);
    },
    
    // Get real-time data
    getLiveData: function(callback) {
        return this.realtime.subscribe(callback);
    },
    
    // Export data
    exportData: function(format, filters = {}) {
        return this.export.download(format, filters);
    }
};
```

---

## 10. Testing Strategy

### 10.1 Unit Testing

#### 10.1.1 PHPUnit Configuration
```xml
<!-- phpunit.xml -->
<phpunit
    bootstrap="tests/bootstrap.php"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    stopOnFailure="false">
    
    <testsuites>
        <testsuite name="unit">
            <directory>tests/unit</directory>
        </testsuite>
        <testsuite name="integration">
            <directory>tests/integration</directory>
        </testsuite>
    </testsuites>
    
    <filter>
        <whitelist>
            <directory>includes/</directory>
            <directory>admin/</directory>
        </whitelist>
    </filter>
</phpunit>
```

#### 10.1.2 Test Examples
```php
class Test_WPVN_Tracker extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        $this->tracker = new WPVN_Tracker();
    }
    
    public function test_session_creation() {
        $session_id = $this->tracker->start_session();
        
        $this->assertNotEmpty($session_id);
        $this->assertEquals(64, strlen($session_id));
    }
    
    public function test_page_view_tracking() {
        $session_id = $this->tracker->start_session();
        
        $result = $this->tracker->track_page_view($session_id, [
            'page_url' => 'https://example.com/test',
            'page_title' => 'Test Page'
        ]);
        
        $this->assertTrue($result);
    }
}
```

### 10.2 Integration Testing

#### 10.2.1 Database Testing
```php
class Test_WPVN_Database extends WP_UnitTestCase {
    
    public function test_table_creation() {
        $db = new WPVN_DB();
        $result = $db->create_tables();
        
        $this->assertTrue($result);
        $this->assertTrue($this->table_exists('wpvn_sessions'));
        $this->assertTrue($this->table_exists('wpvn_page_views'));
    }
    
    private function table_exists(string $table): bool {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
}
```

### 10.3 End-to-End Testing

#### 10.3.1 Cypress Configuration
```javascript
// cypress.config.js
module.exports = {
    e2e: {
        baseUrl: 'http://localhost:8080',
        supportFile: 'tests/e2e/support/index.js',
        specPattern: 'tests/e2e/specs/**/*.cy.js',
        viewportWidth: 1280,
        viewportHeight: 720
    }
};
```

#### 10.3.2 E2E Test Example
```javascript
// tests/e2e/specs/dashboard.cy.js
describe('Admin Dashboard', () => {
    beforeEach(() => {
        cy.login('admin', 'password');
        cy.visit('/wp-admin/admin.php?page=wpvn-dashboard');
    });
    
    it('displays analytics metrics', () => {
        cy.get('[data-testid="total-visits"]').should('be.visible');
        cy.get('[data-testid="unique-visitors"]').should('be.visible');
        cy.get('[data-testid="avg-duration"]').should('be.visible');
    });
    
    it('loads chart data', () => {
        cy.get('#visits-chart').should('be.visible');
        cy.get('.chart-legend').should('contain', 'Visits');
    });
});
```

### 10.4 Performance Testing

#### 10.4.1 Load Testing Configuration
```yaml
# k6-load-test.js
import http from 'k6/http';
import { check } from 'k6';

export let options = {
    stages: [
        { duration: '5m', target: 100 },
        { duration: '10m', target: 200 },
        { duration: '5m', target: 0 }
    ]
};

export default function() {
    let response = http.get('http://localhost:8080/');
    
    check(response, {
        'status is 200': (r) => r.status === 200,
        'response time < 500ms': (r) => r.timings.duration < 500
    });
}
```

---

## 11. Installation & Migration

### 11.1 Plugin Installation Process

#### 11.1.1 Activation Sequence
```php
class WPVN_Activator {
    public static function activate(): void {
        // 1. Check system requirements
        self::check_requirements();
        
        // 2. Create database tables
        $db = new WPVN_DB();
        $db->create_tables();
        
        // 3. Set default options
        self::set_default_options();
        
        // 4. Create capabilities
        self::add_capabilities();
        
        // 5. Schedule cron events
        $cron = new WPVN_Cron();
        $cron->schedule_events();
        
        // 6. Flush rewrite rules
        flush_rewrite_rules();
        
        // 7. Log activation
        WPVN_Logger::log('Plugin activated successfully', 'info');
    }
    
    private static function check_requirements(): void {
        if (version_compare(PHP_VERSION, '8.2', '<')) {
            throw new Exception('PHP 8.2 or higher required');
        }
        
        if (version_compare(get_bloginfo('version'), '6.2', '<')) {
            throw new Exception('WordPress 6.2 or higher required');
        }
    }
}
```

#### 11.1.2 Database Migration System
```php
class WPVN_Migration {
    private const CURRENT_VERSION = '1.0.0';
    
    public function run_migrations(): void {
        $current_version = get_option('wpvn_db_version', '0.0.0');
        
        if (version_compare($current_version, self::CURRENT_VERSION, '<')) {
            $this->execute_migrations($current_version);
            update_option('wpvn_db_version', self::CURRENT_VERSION);
        }
    }
    
    private function execute_migrations(string $from_version): void {
        $migrations = [
            '1.0.0' => 'migration_1_0_0',
            '1.1.0' => 'migration_1_1_0'
        ];
        
        foreach ($migrations as $version => $method) {
            if (version_compare($from_version, $version, '<')) {
                $this->$method();
                WPVN_Logger::log("Migrated to version {$version}", 'info');
            }
        }
    }
}
```

### 11.2 Configuration Management

#### 11.2.1 Default Settings
```php
class WPVN_Default_Settings {
    public static function get_defaults(): array {
        return [
            // Tracking settings
            'tracking_enabled' => true,
            'track_logged_users' => false,
            'exclude_bots' => true,
            'anonymize_ips' => true,
            
            // Data retention
            'retention_days' => 365,
            'cleanup_frequency' => 'daily',
            
            // Privacy settings
            'respect_dnt' => true,
            'require_consent' => false,
            
            // Notification settings
            'email_notifications' => true,
            'notification_email' => get_option('admin_email'),
            
            // Performance settings
            'cache_analytics' => true,
            'cache_duration' => 3600,
            'batch_size' => 1000
        ];
    }
}
```

### 11.3 Upgrade Procedures

#### 11.3.1 Data Migration Scripts
```php
class WPVN_Upgrade_1_1_0 {
    public function upgrade(): void {
        // Add new columns to sessions table
        global $wpdb;
        
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}wpvn_sessions 
             ADD COLUMN country_code CHAR(2) DEFAULT NULL,
             ADD COLUMN city VARCHAR(100) DEFAULT NULL"
        );
        
        // Create index for new columns
        $wpdb->query(
            "CREATE INDEX idx_location 
             ON {$wpdb->prefix}wpvn_sessions (country_code, city)"
        );
        
        // Migrate existing data
        $this->migrate_geolocation_data();
    }
    
    private function migrate_geolocation_data(): void {
        // Background job to populate location data for existing sessions
        wp_schedule_single_event(time() + 60, 'wpvn_migrate_geolocation');
    }
}
```

---

## 12. Development Workflow

### 12.1 Git Workflow

#### 12.1.1 Branch Strategy
```
main                    # Production-ready code
├── develop            # Integration branch
│   ├── feature/*      # Feature branches
│   ├── bugfix/*       # Bug fix branches
│   └── hotfix/*       # Emergency fixes
└── release/*          # Release preparation
```

#### 12.1.2 Commit Conventions
```
feat: add session duration tracking
fix: resolve memory leak in analytics aggregation
docs: update API documentation
style: format code according to WPCS
refactor: optimize database queries
test: add unit tests for notification system
chore: update build dependencies
```

### 12.2 Code Quality Standards

#### 12.2.1 Pre-commit Hooks
```bash
#!/bin/sh
# .git/hooks/pre-commit

# Run PHP CodeSniffer
./vendor/bin/phpcs --standard=WordPress --extensions=php .

# Run PHPUnit tests
./vendor/bin/phpunit

# Run ESLint for JavaScript
npx eslint assets/src/js/

# Check for TODO/FIXME comments
if git diff --cached | grep -E "(TODO|FIXME)"; then
    echo "Warning: Code contains TODO/FIXME comments"
fi
```

#### 12.2.2 Continuous Integration
```yaml
# .github/workflows/ci.yml
name: CI

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: wordpress_test
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mysqli, zip, gd
        
    - name: Install dependencies
      run: composer install --no-dev --optimize-autoloader
      
    - name: Run tests
      run: ./vendor/bin/phpunit
      
    - name: Check coding standards
      run: ./vendor/bin/phpcs
```

### 12.3 Build Process

#### 12.3.1 Asset Compilation
```javascript
// webpack.config.js
const path = require('path');

module.exports = {
    entry: {
        admin: './assets/src/js/admin.js',
        dashboard: './assets/src/js/dashboard.js',
        public: './assets/src/js/public.js'
    },
    output: {
        path: path.resolve(__dirname, 'assets/dist'),
        filename: 'js/[name].min.js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: 'babel-loader'
            },
            {
                test: /\.scss$/,
                use: ['css-loader', 'sass-loader']
            }
        ]
    }
};
```

#### 12.3.2 Release Automation
```bash
#!/bin/bash
# bin/build-release.sh

VERSION=$1
if [ -z "$VERSION" ]; then
    echo "Usage: $0 <version>"
    exit 1
fi

# Update version in main plugin file
sed -i "s/Version: .*/Version: $VERSION/" wordpress-visitor-notify.php

# Build assets
npm run build

# Create deployment package
zip -r "wordpress-visitor-notify-$VERSION.zip" . \
    -x "*.git*" "node_modules/*" "tests/*" "*.md" "webpack.config.js"

echo "Release package created: wordpress-visitor-notify-$VERSION.zip"
```

---

## 13. Deployment & Maintenance

### 13.1 Production Deployment

#### 13.1.1 Deployment Checklist
- [ ] Run full test suite
- [ ] Update version numbers
- [ ] Compile and minify assets
- [ ] Generate language files
- [ ] Create deployment package
- [ ] Test on staging environment
- [ ] Backup production database
- [ ] Deploy to production
- [ ] Run post-deployment tests
- [ ] Monitor for errors

#### 13.1.2 Environment Configuration
```php
// Production environment detection
class WPVN_Environment {
    public static function is_production(): bool {
        return defined('WP_ENV') && WP_ENV === 'production';
    }
    
    public static function get_config(): array {
        if (self::is_production()) {
            return [
                'debug_mode' => false,
                'log_level' => 'error',
                'cache_duration' => 3600
            ];
        }
        
        return [
            'debug_mode' => true,
            'log_level' => 'debug',
            'cache_duration' => 60
        ];
    }
}
```

### 13.2 Monitoring & Logging

#### 13.2.1 Error Monitoring
```php
class WPVN_Error_Handler {
    public function __construct() {
        add_action('init', [$this, 'setup_error_handling']);
    }
    
    public function setup_error_handling(): void {
        if (WPVN_Environment::is_production()) {
            set_error_handler([$this, 'handle_error']);
            set_exception_handler([$this, 'handle_exception']);
        }
    }
    
    public function handle_error($severity, $message, $file, $line): void {
        WPVN_Logger::log(
            "PHP Error: {$message} in {$file}:{$line}",
            'error',
            ['severity' => $severity]
        );
    }
}
```

#### 13.2.2 Performance Monitoring
```php
class WPVN_Performance_Monitor {
    private float $start_time;
    private int $start_memory;
    
    public function start(): void {
        $this->start_time = microtime(true);
        $this->start_memory = memory_get_usage(true);
    }
    
    public function log_performance(string $operation): void {
        $execution_time = microtime(true) - $this->start_time;
        $memory_used = memory_get_usage(true) - $this->start_memory;
        
        WPVN_Logger::log(
            "Performance: {$operation}",
            'info',
            [
                'execution_time' => $execution_time,
                'memory_used' => $memory_used
            ]
        );
    }
}
```

### 13.3 Maintenance Procedures

#### 13.3.1 Regular Maintenance Tasks
```php
class WPVN_Maintenance {
    public function daily_maintenance(): void {
        // Clean up old data
        $this->cleanup_old_records();
        
        // Optimize database tables
        $this->optimize_tables();
        
        // Generate performance report
        $this->generate_performance_report();
        
        // Check for plugin updates
        $this->check_plugin_updates();
    }
    
    private function optimize_tables(): void {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'wpvn_sessions',
            $wpdb->prefix . 'wpvn_page_views',
            $wpdb->prefix . 'wpvn_logs'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("OPTIMIZE TABLE {$table}");
        }
    }
}
```

---

## Conclusion

This enhanced technical specification provides a comprehensive foundation for developing the WordPress Visitor Notify plugin. It addresses the original requirements while adding crucial details for:

- **Security**: Comprehensive security measures and privacy compliance
- **Performance**: Optimization strategies and monitoring
- **Maintainability**: Clear architecture and development workflows
- **Scalability**: Database design and caching strategies
- **Quality**: Testing strategies and code standards

The specification serves as both a development guide and a reference document for ongoing maintenance and feature development.

---

**Document Version**: 1.0  
**Last Updated**: June 8, 2025  
**Next Review**: June 8, 2026
