# WP Visitor Notify Plugin - Руководство по реализации

*Документация создана на основе полного анализа кода всех 4 основных файлов плагина*

## 📁 Architecture Overview

Плагин использует объектно-ориентированную архитектуру с Singleton паттерном для основного класса и зависимости между компонентами.

### File Structure Analysis

| File | Lines | Classes | Methods | Purpose |
|------|-------|---------|---------|---------|
| `wp-visitor-notify.php` | 117 | 0 | 4 functions | Main plugin file, autoloader, hooks |
| `includes/class-database.php` | 667 | 1 | 17 | Database operations and schema |
| `includes/class-logger.php` | 440 | 1 | 17 | Logging system with multiple levels |
| `includes/class-plugin.php` | 697 | 1 | 16 | Main plugin orchestration (Singleton) |

**Total**: 1,921 lines of code, 3 classes, 54 methods/functions

## 🚀 Main Plugin File (`wp-visitor-notify.php`)

### Constants Defined
```php
WPVN_VERSION = '1.0.0'
WPVN_PLUGIN_FILE = __FILE__
WPVN_PLUGIN_DIR = plugin_dir_path(__FILE__)
WPVN_PLUGIN_URL = plugin_dir_url(__FILE__)
WPVN_PLUGIN_BASENAME = plugin_basename(__FILE__)
```

### System Requirements
- **PHP**: >= 8.2 (строгая проверка)
- **WordPress**: >= 6.2 (строгая проверка)
- **PHP Features**: `declare(strict_types=1)` используется

### Autoloader Implementation
Собственный PSR-4 совместимый автозагрузчик:
- Namespace: `WPVN\`
- Pattern: `includes/class-{lowercase-classname}.php`
- Example: `WPVN\Database` → `includes/class-database.php`

### WordPress Hooks Registered
- `plugins_loaded` → `wpvn_init()`
- `register_activation_hook` → `wpvn_activate()`
- `register_deactivation_hook` → `wpvn_deactivate()`
- `register_uninstall_hook` → `WPVN\Uninstaller::uninstall`

## 💾 Database Class (`includes/class-database.php`)

### Database Schema

**5 Tables Created:**

#### 1. `wp_wpvn_sessions` (Primary visitor data)
```sql
Columns: 21 fields
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- session_id (VARCHAR(64) UNIQUE)
- ip_address (VARCHAR(45) - supports IPv6)
- ip_hash (VARCHAR(64) - for privacy)
- device_type (ENUM: desktop|mobile|tablet|bot)
- user_agent, browser, operating_system
- first_visit, last_activity (DATETIME)
- page_count, total_duration (INT)
- UTM tracking: utm_source, utm_medium, utm_campaign
- Geolocation: country_code, city
- Indexes: 6 indexes including composite analytics index
```

#### 2. `wp_wpvn_page_views` (Page tracking)
```sql
Columns: 10 fields
- session_id (FK to sessions)
- post_id, page_url, page_title, page_type
- view_duration, scroll_depth (0-100)
- exit_page (BOOLEAN)
- viewed_at (DATETIME)
```

#### 3. `wp_wpvn_notification_rules` (Alert configuration)
```sql
Columns: 15 fields
- rule_type (ENUM: threshold|scheduled|event)
- conditions (JSON field for complex rules)
- threshold_value, threshold_period
- schedule_frequency, schedule_time
- recipients (JSON array)
- email_template (TEXT)
```

#### 4. `wp_wpvn_notification_history` (Notification tracking)
```sql
Columns: 10 fields
- rule_id (FK to notification_rules)
- recipient_email, subject, message
- data_snapshot (JSON)
- status (ENUM: sent|failed|pending)
- sent_at, created_at
```

#### 5. `wp_wpvn_logs` (System logging)
```sql
Columns: 9 fields
- level (ENUM: debug|info|warning|error|critical)
- message (TEXT), context (JSON)
- component, user_id, ip_address, user_agent
- created_at (DATETIME)
```

### Database Class Methods

#### Table Management
- `create_tables(): bool` - Creates all 5 tables using dbDelta
- `drop_tables(): bool` - Removes all tables (uninstall)
- `tables_exist(): bool` - Validates table existence
- `get_db_version(): string` - Returns schema version
- `get_tables(): array` - Returns table names with prefix

#### Data Operations
- `insert_session(array $data)` - Creates new visitor session
- `insert_page_view(array $data): bool` - Records page view
- `get_session(string $session_id): ?array` - Retrieves session data
- `get_active_sessions(int $minutes = 30): array` - Real-time visitors
- `get_analytics_data(string $start, string $end): array` - Dashboard data

#### Maintenance
- `cleanup_old_records(int $days = 365): int` - Data retention cleanup
- `update_session_activity(string $session_id): bool` - Updates last activity

### Data Validation & Security
- All inputs sanitized using WordPress functions
- Prepared statements for all queries
- Data type validation for ENUMs
- IP address validation and hashing for privacy

## 📊 Logger Class (`includes/class-logger.php`)

### Log Levels (PSR-3 Standard)
- `debug` - Detailed debug information
- `info` - Interesting events
- `warning` - Exceptional occurrences (not errors)
- `error` - Runtime errors
- `critical` - Critical conditions requiring immediate attention

### Logger Methods

#### Logging Operations
- `log(string $message, string $level, array $context, ?string $component): bool`
- `debug()`, `info()`, `warning()`, `error()`, `critical()` - Level-specific methods
- `set_database(Database $database): void` - Dependency injection

#### Data Retrieval
- `get_logs(array $filters = []): array` - Query logs with filters
- `get_log_stats(string $from, string $to): array` - Level statistics
- `cleanup_old_logs(int $days = 90): int` - Cleanup old entries

### Filtering System
- Level threshold filtering (only logs >= threshold level)
- Component-based filtering
- Date range filtering
- Maximum 1000 records per query limit

### Fallback Mechanism
- Primary: Database storage in `wp_wpvn_logs` table
- Fallback: WordPress `error_log()` if database fails
- Auto-detection of client IP (proxy-aware)

### Context Data Captured
- User ID (if logged in)
- Client IP address (with proxy support)
- User agent string
- Component name
- Custom context array (stored as JSON)

## 🔧 Plugin Class (`includes/class-plugin.php`)

### Singleton Pattern Implementation
- `get_instance(): Plugin` - Single instance access
- Private constructor prevents direct instantiation
- `__clone()` and `__wakeup()` prevented

### Component Management
```php
Private properties:
- ?Database $database
- ?Logger $logger
- ?object $tracker (TODO)
- ?object $analytics (TODO) 
- ?object $detector (TODO)
- ?object $notifier (TODO)
```

### Initialization Sequence
1. `init_logger()` - Logger first (others may need it)
2. `init_database()` - Database with table validation
3. Set logger-database dependency
4. Setup WordPress hooks
5. Mark as initialized

### WordPress Integration

#### Admin Interface
- Main menu: "Visitor Analytics" (position 30)
- Submenus: Settings, Notifications, Logs
- Hook: `admin_menu` → `setup_admin_menu()`

#### Admin Pages (Current Status - Basic Stubs)
- `render_dashboard_page()` - Shows database status
- `render_settings_page()` - Placeholder
- `render_notifications_page()` - Placeholder
- `render_logs_page()` - Includes logger test

#### WordPress Hooks Registered
- `admin_menu` → `setup_admin_menu()`
- `admin_enqueue_scripts` → `enqueue_admin_assets()`
- `admin_init` → `register_settings()`

### Lifecycle Management
- `on_activation()` - Creates default options, initializes database
- `on_deactivation()` - Logs deactivation (basic cleanup)
- Uninstall handled by separate `WPVN\Uninstaller` class

### Dependency Injection
- `get_component(string $name): ?object` - Component accessor
- Components: database, logger, tracker, analytics, detector, notifier

### Current Development Status
- ✅ **Core Infrastructure**: Database, Logger, Plugin orchestration
- 🔄 **In Development**: Basic admin interface (stubs)
- ⏳ **TODO**: Tracker, Analytics, Detector, Notifier, Settings API

## 🔒 Security Measures

### Data Protection
- IP addresses hashed for privacy (`ip_hash` field)
- All user inputs sanitized with WordPress functions
- Prepared statements prevent SQL injection
- Context data JSON-encoded safely

### Access Control
- Admin pages require `manage_options` capability
- Direct file access prevented (`ABSPATH` check)
- Namespace isolation (`WPVN\`)

### Error Handling
- Try-catch blocks for critical operations
- Graceful degradation (error_log fallback)
- Initialization failure handling with admin notices

## ⚡ Performance Optimizations

### Database Design
- Proper indexing on all query-heavy columns
- Composite indexes for analytics queries
- Foreign key relationships for data integrity
- Efficient data types (ENUMs, appropriate field sizes)

### Query Optimization
- Prepared statements for all queries
- Selective column retrieval
- Date-based partitioning considerations
- Configurable result limits

### Memory Management
- Singleton pattern prevents multiple instances
- Lazy loading of components
- Explicit null checks prevent memory leaks

## 🧪 Development & Testing

### Code Quality
- `declare(strict_types=1)` enforces type safety
- PHP 8.2+ type hints throughout
- PSR-4 autoloading standard
- Comprehensive error logging

### Documentation Standards
- All methods have PHPDoc comments
- Type hints for parameters and return values
- @since version tracking
- Inline code comments in Russian for learning

### Debugging Support
- Multiple log levels for granular debugging
- Component-based log categorization
- Admin interface for log viewing
- Database status validation

## 📈 Analytics Capabilities

### Data Collection Points
- Session tracking with device detection
- Page view recording with duration
- Scroll depth measurement (0-100%)
- UTM campaign tracking
- Geolocation data (country/city)

### Real-time Features
- Active visitor counting (30-minute window)
- Session activity updates
- Exit page detection

### Reporting Metrics
- Daily/weekly/monthly session aggregation
- Device type breakdowns
- Popular pages analysis
- Unique vs. returning visitor analysis

## 🔮 Future Extensions

### Planned Components
- **Tracker**: Frontend JavaScript integration
- **Analytics**: Advanced metrics processing  
- **Detector**: Enhanced device/browser detection
- **Notifier**: Email alert system
- **Settings API**: Full configuration interface

### Extensibility
- Component-based architecture allows easy additions
- JSON fields support complex configuration
- Hook system ready for third-party extensions
- Database schema versioning for migrations

---

*This documentation reflects the actual implementation as of version 1.0.0. All method signatures, database schemas, and architectural decisions are based on direct code analysis.*