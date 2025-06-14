# WP Visitor Notify - Project Structure and Development Rules

## Production File Structure

```
wp-visitor-notify/
├── wp-visitor-notify.php               # ✅ Main plugin file
├── includes/                           # ✅ Core PHP classes
│   ├── class-plugin.php                # ✅ Main class (Singleton)
│   ├── class-database.php              # ✅ Database operations
│   ├── class-logger.php                # ✅ Logging system
│   ├── class-uninstaller.php           # ✅ Uninstallation logic
│   ├── class-tracker.php               # ✅ Visitor tracking & sessions
│   ├── class-analytics.php             # ✅ Complete analytics system
│   ├── class-detector.php              # ✅ Device & browser detection
│   ├── class-notifier.php              # ❌ Notification system
│   ├── class-cleanup.php               # ❌ Cleanup utilities
│   └── class-validator.php             # ❌ Data validation
├── admin/                              # ✅ Complete admin interface
│   ├── class-admin.php                 # ✅ Main controller
│   ├── class-dashboard.php             # ✅ Dashboard controller
│   ├── class-settings.php              # ✅ Complete settings system
│   ├── class-notifications.php         # ❌ Notifications controller
│   ├── class-logs.php                  # ❌ Logs controller
│   ├── templates/                      # ✅ HTML templates
│   │   ├── dashboard.php               # ✅ Dashboard with analytics
│   │   ├── settings.php                # ✅ Complete settings form
│   │   ├── notifications.php           # ❌ Notification templates
│   │   └── logs.php                    # ❌ Logs view
│   └── assets/                         # ✅ Complete assets
│       ├── css/admin.css               # ✅ Full admin styles
│       └── js/admin.js                 # ✅ Admin JavaScript
├── languages/                          # ✅ Translations
│   └── wp-visitor-notify.pot           # ❌ Translation template
└── README.md                           # ✅ Project description
```

## Project Files Functions

### `wp-visitor-notify.php` ✅
- Plugin metadata (name, version, author)
- PHP 8.2+ version check
- WordPress 6.2+ version check
- Custom class autoloader (no Composer)
- Plugin constants (WPVN_VERSION, WPVN_PLUGIN_DIR, etc.)
- Initialization function wpvn_init()
- Hook wrappers: wpvn_activate() -> Plugin::on_activation()
- Hook wrappers: wpvn_deactivate() -> Plugin::on_deactivation()
- Register uninstall hook

### `includes/class-plugin.php` ✅
- Singleton pattern (get_instance, __construct, __clone, __wakeup)
- Constants VERSION and PLUGIN_SLUG
- Component initialization (init_logger, init_database)
- Initialization check (is_initialized)
- Basic WordPress hooks setup
- Admin menu creation
- Admin assets enqueue
- Settings registration
- Render admin pages (dashboard, settings, notifications, logs)
- Lifecycle methods: on_activation(), on_deactivation() (called from main file)
- Get components (get_component)
- Get plugin version

### `includes/class-database.php` ✅
- Database connection initialization
- Table names with prefix
- Plugin table creation
- Table existence check
- Table deletion methods (controlled - not used during uninstallation)
- Get DB version
- Get table list
- Get $wpdb object

### `includes/class-logger.php` ✅
- Constructor with log level settings
- Main log() method
- Methods debug(), info(), error()
- Log level check (should_log)
- Message formatting (format_message)

### `includes/class-uninstaller.php` ✅
- Main uninstall() method
- Remove plugin options (remove_plugin_options)
- IMPORTANT: Does NOT delete visitor data (for security!)
- Clear cron events (clear_cron_events)
- Remove capabilities (remove_capabilities)
- Clear transients (clear_transients)
- Method drop_database_tables_dangerous() (development/testing only - NEVER call in production and never delete visitor data)

### `includes/class-tracker.php` ✅
- ✅ Visit tracking
- ✅ IP address detection & hashing
- ✅ User Agent detection
- ✅ Visitor data recording
- ✅ Session management
- ✅ Page view counting
- ⚠️ Bot filtering (basic)
- ✅ Admin exclusion
- ❌ AJAX handling (future)

### `includes/class-analytics.php` ⚠️
- ✅ Daily/weekly/monthly stats
- ✅ Top pages analytics
- ✅ Device statistics
- ✅ Browser statistics
- ❌ Geo statistics
- ✅ Hourly activity
- ❌ Referrer stats
- ❌ Bounce rate
- ❌ Time on site
- ⚠️ Data aggregation

### `includes/class-detector.php` ✅
- Browser detection
- Operating system detection
- Device type detection (desktop/mobile/tablet)
- Browser version detection
- Bot detection
- Screen resolution detection
- Language detection
- User Agent parsing

### `includes/class-notifier.php` ❌
- Create notification rule
- Edit rule
- Delete rule
- Rule condition checking
- Send email notifications
- Notification history recording
- Get rule list
- Get notification history
- Email validation

### `includes/class-cleanup.php` ❌
- Delete old records (if allowed)
- Temporary file cleanup
- DB table optimization
- Old data compression
- Expired transients cleanup

### `includes/class-validator.php` ❌
- IP address validation
- Email address validation
- Input data sanitization
- Settings validation
- Access rights check
- Form validation

### `admin/class-admin.php` ❌
- Admin interface initialization
- Styles and scripts enqueue
- AJAX request handling
- Common admin methods
- Access rights check
- Render common elements

### `admin/class-dashboard.php` ❌
- Render Dashboard page
- Get data for statistics cards
- Prepare data for charts
- Build top pages table
- Build recent visitors table
- AJAX data update handling

### `admin/class-settings.php` ❌
- Render settings page
- Register settings fields
- Settings validation
- Save settings
- Reset settings to default
- Export/import settings

### `admin/class-notifications.php` ❌
- Render notifications page
- Rule creation form
- Notification rules table
- Notification history table
- Edit rules
- Test notifications

### `admin/class-logs.php` ❌
- Render logs page
- Log filtering
- Log search
- Export logs to CSV
- Clear logs
- Log pagination

### `admin/templates/dashboard.php` ❌
- Dashboard HTML markup
- Statistics cards
- Activity chart
- Top pages table
- Recent visitors table

### `admin/templates/settings.php` ❌
- Settings form HTML
- Settings sections
- Form fields
- Save buttons

### `admin/templates/notifications.php` ❌
- Rules table HTML
- Rule creation form HTML
- Notification history table HTML

### `admin/templates/logs.php` ❌
- Filter panel HTML
- Logs table HTML
- Control buttons HTML

### `admin/assets/css/admin.css` ❌
- Statistics card styles
- Table styles
- Form styles
- Button styles
- Responsive styles

### `admin/assets/js/admin.js` ❌
- Chart.js initialization
- DataTables initialization
- AJAX data update
- Form handling
- Element interactivity

### `languages/wp-visitor-notify.pot` ❌
- Translation strings
- Translation context
- Pluralization

### Main Files

#### `wp-visitor-notify.php` ✅
**Main plugin file - entry point**
- Plugin metadata (name, version, author, requirements)
- PHP (8.2+) and WordPress (6.2+) version checks
- Custom class autoloader without Composer (namespace WPVN)
- Global constants (WPVN_VERSION, WPVN_PLUGIN_DIR, etc.)
- Lifecycle hook wrappers (delegate to Plugin and Uninstaller classes)
- Plugin singleton initialization via `wpvn_init()`

#### `README.md` ✅
**Project description for GitHub**
- Short plugin description
- System requirements
- Key features
- File structure with status marks (✅/❌)
- Installation and development instructions

### `includes/` folder - Core classes

#### `class-plugin.php` ✅
**Main plugin class (Singleton)**
- Constants: VERSION='1.0.0', PLUGIN_SLUG='wp-visitor-notify'
- Singleton pattern with `get_instance()`, cloning forbidden
- Dependency injection for components (database, logger)
- Basic WordPress hooks (admin_menu, enqueue_scripts)
- Admin page stubs (dashboard, settings, notifications, logs)
- Lifecycle methods: `on_activation()`, `on_deactivation()` (actual activation/deactivation logic)

#### `class-database.php` ✅
**Simplified database class**
- Constant DB_VERSION='1.0.0'
- Table names with WordPress prefix
- Table schemas prepared (sessions, page_views, notification_rules, etc.)
- Simple `tables_exist()` check via `wpvn_db_version` option
- Methods `create_tables()` and `drop_tables()` (simplified for now)

#### `class-logger.php` ✅
**Logging system without database**
- Levels: debug, info, error
- Write to PHP error_log (visible in Docker)
- Format: `[timestamp] WPVN.LEVEL[component]: message | Context: {json}`
- Log level filtering
- Methods: `log()`, `debug()`, `info()`, `error()`

#### `class-uninstaller.php` ✅
**Careful cleanup on plugin removal**
- Static method `uninstall()` for register_uninstall_hook
- Remove all plugin options
- IMPORTANT: Does NOT delete visitor data tables (for security analysis!)
- Clear cron events (2 planned events):
  - `wpvn_hourly_aggregation` - hourly statistics aggregation
  - `wpvn_notification_check` - notification rules check every 5 minutes
- Clear transients with wpvn_ prefix

### `admin/` folder - Admin interface

#### `assets/css/admin.css` ❌ (planned)
**Admin styles**
- Admin page styling

#### `assets/js/admin.js` ❌ (planned)
**Admin JavaScript**
- Admin interactivity

#### `templates/` ❌ (planned, empty)
**Admin page HTML templates**
- Planned: dashboard.php, settings.php, notifications.php, logs.php

## Admin Pages Content

### Dashboard (main page)
**Widgets and Tables:**
- **Statistics Cards** (4 blocks): Today, This Week, This Month - visitor count
- **Activity Graph** (Chart.js): Hourly activity for the last 24 hours
- **Top Pages Table** (DataTable): URL, Title, Views
- **Recent Visitors Table** (DataTable): Time, IP, Page, Device, Browser, City, Country
- **Notifications Block** (WordPress notices): Important alerts and system messages

### Settings
**WordPress Settings API Form:**
- **Tracking Section**: Checkbox enable/disable, Select recording frequency
- **Privacy Section**: Checkbox hash IP, Select anonymization level
- **Notifications Section**: Email field for alerts, Number input check frequency
- **Data Storage Section**: Number input days to keep (display only), Info text "Data is stored permanently for security"
- **Exceptions Section**: Textarea for IP, Checkbox exclude admins/bots
- **Buttons**: Save Settings, Reset to Defaults

### Notifications
**Two main parts:**
- **Notification Rules Table** (DataTable): Rule Name, Condition, Email, Status, Actions (Edit/Delete)
- **Rule Creation Form**: Select event type, Number field threshold, Email field recipient, Textarea message template
- **Notification History Table** (DataTable): Date/Time, Rule, Recipient, Delivery Status, Message

### Logs
**Filters and Table:**
- **Filter Panel**: Select log level, Date picker period, Text input message search
- **Logs Table** (DataTable): Time, Level (colored badges), Component, Message, Context (JSON in modal window)
- **Control Buttons**: Clear All Logs, Export CSV, Refresh
- **Pagination**: WordPress standard pagination

### `languages/` folder ✅ (empty)
**Translation files**
- Planned: wp-visitor-notify.pot for internationalization

## Architectural Project Rules

### 1. Custom Class Autoloader
- **NO Composer** – use our own autoloader
- Classes are named: `class-name.php`
- Namespace: `WPVN\ClassName`
- Loading: `includes/class-{class-name}.php`

### 2. Singleton Pattern for Main Class
- `Plugin::get_instance()` – the only way to get an instance
- Cloning and serialization are forbidden
- Initialization only via the `init()` method

### 3. Logging System
- **NO database** – logs go to `error_log`
- Levels: `debug`, `info`, `error`
- Format: `[timestamp] WPVN.LEVEL[component]: message | Context: {json}`
- Visible in Docker logs

### 4. Simplified Database
- For now, only the version is created in options
- Table schemas are ready but not used
- `Database::tables_exist()` checks the `wpvn_db_version` option

### 5. Version Requirements
- **PHP 8.2+** – strict typing
- **WordPress 6.2+** – modern functions
- `declare(strict_types=1);` in all files

### 6. Admin Structure
- Menu: "Visitor Analytics" with submenus
- Pages: Dashboard, Settings, Notifications, Logs
- Currently stubs with basic HTML
- Capability: `manage_options`

### 7. Lifecycle Hooks
- **Activation**: `register_activation_hook() -> wpvn_activate() -> Plugin::on_activation()`
- **Deactivation**: `register_deactivation_hook() -> wpvn_deactivate() -> Plugin::on_deactivation()`
- **Uninstall**: `register_uninstall_hook() -> Uninstaller::uninstall()` (removes settings, preserves data)

### 8. Security System
- `ABSPATH` check in all files
- Escape all outputs
- Capability checks
- Protection from direct access

### 9. Production Environment
- Only WordPress site
- Standard installation via admin panel
- Hosting support

### 10. Testing
- Custom test runner
- Logger and other components tests
- No PHPUnit - simple asserts

## Project Constants

```php
WPVN_VERSION = '1.0.0'
WPVN_PLUGIN_FILE = __FILE__
WPVN_PLUGIN_DIR = plugin_dir_path(__FILE__)
WPVN_PLUGIN_URL = plugin_dir_url(__FILE__)
WPVN_PLUGIN_BASENAME = plugin_basename(__FILE__)
```

## Git Rules

- Development files are excluded (.vscode/, test/, docker-*)
- Logs are excluded
- System files are excluded

## Next Development Stages

1. **Enhance Analytics** ⚠️
   - Add geolocation support
   - Implement bounce rate calculation
   - Add referrer tracking
   - Add time on site tracking

2. **Complete Admin Interface** ⚠️
   - Enhance settings page
   - Add more tracking options
   - Improve CSS styling
   - Add JavaScript charts
   - Add DataTables support

3. **Add New Features** ❌
   - Create notification system
   - Implement log viewer
   - Add data cleanup tools
   - Add export functionality

4. **Performance & Security** ⚠️
   - Optimize database queries
   - Add rate limiting
   - Enhance bot detection
   - Add data anonymization options

5. **Documentation & Localization** ❌
   - Create user documentation
   - Add inline help
   - Create .pot file
   - Add translations

## Code Principles

- **Simplicity** - no complex patterns without necessity
- **Readability** - extensive comments in English
- **Security** - escape, validate, sanitize
- **Performance** - lazy loading of components
- **Testability** - dependency injection through constructors
