# WP Visitor Notify - Справочник API

*Полный справочник всех методов и функций в плагине на основе анализа реального кода*

## 📄 Main Plugin File Functions

### `wp-visitor-notify.php` (4 functions)

| Function | Parameters | Returns | Purpose |
|----------|------------|---------|----------|
| `wpvn_init()` | `void` | `void` | Initialize plugin via Singleton |
| `wpvn_activate()` | `void` | `void` | Plugin activation handler |
| `wpvn_deactivate()` | `void` | `void` | Plugin deactivation handler |
| **Autoloader** | `string $class` | `void` | PSR-4 class loading |

## 🗄️ Database Class Methods

### `WPVN\Database` (17 methods)

#### Table Management
| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `__construct()` | - | - | Initialize database connection |
| `create_tables()` | - | `bool` | Create all 5 plugin tables |
| `drop_tables()` | - | `bool` | Remove all plugin tables |
| `tables_exist()` | - | `bool` | Verify all tables exist |
| `get_tables()` | - | `array<string, string>` | Get table names with prefix |
| `get_db_version()` | - | `string` | Get current schema version |

#### Private Table Creation Methods
| Method | Returns | Table Created |
|--------|---------|---------------|
| `create_sessions_table()` | `bool` | `wp_wpvn_sessions` (21 fields) |
| `create_page_views_table()` | `bool` | `wp_wpvn_page_views` (10 fields) |
| `create_notification_rules_table()` | `bool` | `wp_wpvn_notification_rules` (15 fields) |
| `create_notification_history_table()` | `bool` | `wp_wpvn_notification_history` (10 fields) |
| `create_logs_table()` | `bool` | `wp_wpvn_logs` (9 fields) |

#### Data Operations
| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `insert_session()` | `array $session_data` | `string\|false` | Create new visitor session |
| `insert_page_view()` | `array $page_data` | `bool` | Record page view |
| `get_session()` | `string $session_id` | `array\|null` | Retrieve session by ID |
| `get_active_sessions()` | `int $minutes_active = 30` | `array` | Get real-time visitors |
| `get_analytics_data()` | `string $start_date`, `string $end_date` | `array` | Dashboard analytics data |

#### Maintenance
| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `cleanup_old_records()` | `int $retention_days = 365` | `int` | Remove old data, return count |
| `update_session_activity()` | `string $session_id` | `bool` | Update last activity time |

## 📊 Logger Class Methods

### `WPVN\Logger` (17 methods)

#### Core Logging
| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `__construct()` | - | - | Initialize with WordPress options |
| `set_database()` | `Database $database` | `void` | Dependency injection |
| `log()` | `string $message`, `string $level = 'info'`, `array $context = []`, `?string $component = null` | `bool` | Main logging method |

#### Level-Specific Methods
| Method | Parameters | Returns | PSR-3 Level |
|--------|------------|---------|-------------|
| `debug()` | `string $message`, `array $context = []`, `?string $component = null` | `bool` | Debug information |
| `info()` | `string $message`, `array $context = []`, `?string $component = null` | `bool` | Interesting events |
| `warning()` | `string $message`, `array $context = []`, `?string $component = null` | `bool` | Exceptional occurrences |
| `error()` | `string $message`, `array $context = []`, `?string $component = null` | `bool` | Runtime errors |
| `critical()` | `string $message`, `array $context = []`, `?string $component = null` | `bool` | Critical conditions |

#### Data Retrieval
| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `get_logs()` | `array $filters = []` | `array` | Query logs with filters |
| `get_log_stats()` | `string $date_from = ''`, `string $date_to = ''` | `array` | Statistics by level |
| `cleanup_old_logs()` | `int $retention_days = 90` | `int` | Remove old logs |

#### Private Utility Methods
| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `should_log()` | `string $level` | `bool` | Check level threshold |
| `store_in_database()` | `array $log_data` | `bool` | Database storage |
| `store_in_error_log()` | `array $log_data` | `bool` | Fallback storage |
| `get_client_ip()` | - | `string\|null` | Get client IP (proxy-aware) |

### Log Level Constants
```php
private const LOG_LEVELS = [
    'debug',     // Detailed debug information
    'info',      // Interesting events
    'warning',   // Exceptional occurrences that are not errors
    'error',     // Runtime errors
    'critical'   // Critical conditions
];
```

## 🔧 Plugin Class Methods

### `WPVN\Plugin` (16 methods)

#### Singleton Pattern
| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `get_instance()` | - | `Plugin` | **Static** - Get singleton instance |
| `__construct()` | - | - | **Private** - Prevent direct instantiation |
| `__clone()` | - | `void` | **Private** - Prevent cloning |
| `__wakeup()` | - | `void` | Prevent unserialization |

#### Initialization
| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `init()` | - | `void` | Main initialization method |
| `init_logger()` | - | `void` | **Private** - Initialize logging system |
| `init_database()` | - | `void` | **Private** - Initialize database |

#### WordPress Integration
| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `setup_admin_menu()` | - | `void` | **Public** - Create admin menu |
| `enqueue_admin_assets()` | `string $hook` | `void` | **Public** - Load CSS/JS |
| `register_settings()` | - | `void` | **Public** - Register settings API |
| `setup_basic_hooks()` | - | `void` | **Private** - WordPress hooks |

#### Admin Page Renderers
| Method | Parameters | Returns | Page Rendered |
|--------|------------|---------|---------------|
| `render_dashboard_page()` | - | `void` | Main dashboard |
| `render_settings_page()` | - | `void` | Settings page |
| `render_notifications_page()` | - | `void` | Notifications page |
| `render_logs_page()` | - | `void` | Logs page |

#### Lifecycle & Utilities
| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `on_activation()` | - | `void` | **Public** - Plugin activation |
| `on_deactivation()` | - | `void` | **Public** - Plugin deactivation |
| `get_component()` | `string $component` | `object\|null` | Get component instance |
| `get_version()` | - | `string` | Get plugin version |
| `is_initialized()` | - | `bool` | Check initialization status |

## 🎯 Method Usage Examples

### Basic Logging
```php
$plugin = WPVN\Plugin::get_instance();
$logger = $plugin->get_component('logger');

$logger->info('User visited homepage', ['user_id' => 123]);
$logger->error('Database connection failed', ['error' => $e->getMessage()]);
```

### Database Operations
```php
$database = $plugin->get_component('database');

// Insert session
$session_id = $database->insert_session([
    'session_id' => 'unique_session_123',
    'ip_hash' => hash('sha256', $ip),
    'device_type' => 'mobile',
    'browser' => 'Chrome'
]);

// Get analytics
$data = $database->get_analytics_data('2024-01-01', '2024-01-31');
```

### Plugin Lifecycle
```php
// Get singleton instance
$plugin = WPVN\Plugin::get_instance();

// Initialize if not done
if (!$plugin->is_initialized()) {
    $plugin->init();
}

// Get components
$database = $plugin->get_component('database');
$logger = $plugin->get_component('logger');
```

## 🔄 Method Call Flow

### Plugin Initialization
1. `wp-visitor-notify.php` → `wpvn_init()`
2. `Plugin::get_instance()` → creates singleton
3. `Plugin::init()` → initializes components
4. `init_logger()` → creates Logger instance
5. `init_database()` → creates Database + tables
6. `logger->set_database()` → dependency injection
7. `setup_basic_hooks()` → WordPress integration

### Data Logging Flow
1. Component calls `logger->info()` (or other level)
2. `log()` method → validates level and filters
3. `should_log()` → checks threshold
4. `store_in_database()` → primary storage attempt
5. `store_in_error_log()` → fallback if database fails

### Session Tracking Flow (When Implemented)
1. Visitor loads page → triggers tracking
2. `insert_session()` → creates new session
3. `insert_page_view()` → records page
4. `update_session_activity()` → updates last activity
5. `get_active_sessions()` → real-time display

## 📊 Return Type Patterns

### Boolean Returns (Success/Failure)
- Database operations: `insert_*()`, `create_*()`, `tables_exist()`
- Logging: All log level methods
- Validation: `should_log()`

### Array Returns (Data Collections)
- Analytics: `get_analytics_data()`, `get_active_sessions()`
- Logs: `get_logs()`, `get_log_stats()`
- Configuration: `get_tables()`

### String/ID Returns
- Session creation: `insert_session()` returns session_id
- Version info: `get_version()`, `get_db_version()`

### Object Returns
- Singleton: `get_instance()` returns Plugin instance
- Components: `get_component()` returns component or null

---

*All method signatures verified against actual code implementation*
