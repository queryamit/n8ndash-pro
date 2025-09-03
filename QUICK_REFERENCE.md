# n8nDash Pro - Quick Reference Guide

**Version**: 1.2.0 | **Last Updated**: December 2024

**Developer**: Amit Anand Niraj  
**Contact**: queryamit@gmail.com  
**Website**: https://anandtech.in  
**GitHub**: https://github.com/queryamit  
**LinkedIn**: https://www.linkedin.com/in/queryamit/

---

## 🚀 Quick Start

### Installation
```bash
1. Upload to /wp-content/plugins/
2. Activate plugin
3. Go to n8nDash → Add New Dashboard
4. Add widgets and configure webhooks
```

### Basic Usage
```php
// Display dashboard
[n8ndash_dashboard id="123"]

// Display single widget
[n8ndash_widget id="456"]
```

---

## 🔧 Core Functions

### Widget Types
- **Data Widget**: KPI displays, lists with links
- **Chart Widget**: Line, Bar, Pie charts
- **Custom Widget**: Dynamic forms with n8n integration

### Key Hooks
```php
// Widget rendering
do_action( 'n8ndash_before_render_widget', $widget_id, $widget_data );
do_action( 'n8ndash_after_render_widget', $widget_id, $widget_data );

// Dashboard management
do_action( 'n8ndash_before_save_dashboard', $dashboard_id, $dashboard_data );
do_action( 'n8ndash_after_save_dashboard', $dashboard_id, $dashboard_data );

// Data processing
$webhook_args = apply_filters( 'n8ndash_webhook_args', $args, $webhook_url );
$webhook_response = apply_filters( 'n8ndash_webhook_response', $response, $widget_id );
```

---

## 📊 Widget Configuration

### Data Widget
```json
{
  "webhook_url": "https://n8n.example.com/webhook",
  "webhook_method": "GET",
  "data_mapping": {
    "value1": "$.data.kpi1",
    "value2": "$.data.kpi2"
  }
}
```

### Chart Widget
```json
{
  "webhook_url": "https://n8n.example.com/webhook",
  "chart_type": "line",
  "data_mapping": {
    "labels": "$.data.labels",
    "datasets": "$.data.datasets"
  }
}
```

### Custom Form Widget
```json
{
  "webhook_url": "https://n8n.example.com/webhook",
  "submit_method": "POST",
  "fields": [
    {"type": "text", "label": "Name", "required": true},
    {"type": "email", "label": "Email", "required": true}
  ]
}
```

---

## 🗄️ Database Schema

### Tables
```sql
wp_n8ndash_dashboards
- id, title, description, settings, user_id, is_public, created_at, updated_at

wp_n8ndash_widgets  
- id, dashboard_id, type, title, configuration, position, size, created_at, updated_at
```

---

## 👥 User Roles & Capabilities

### Role Hierarchy
| Role | Dashboard Access | Settings Access | Import/Export |
|------|------------------|-----------------|---------------|
| **Administrator** | Full (All) | Full | Full |
| **Editor** | Full (All) | None | Full (All) |
| **Author** | Own Only | None | Own Only |
| **Contributor** | Own Only | None | Own Only |
| **Subscriber** | View Public Only | None | None |

### Capabilities
```php
// Core capabilities
'n8ndash_view_dashboards'      // View dashboards
'n8ndash_create_dashboards'    // Create new dashboards
'n8ndash_edit_dashboards'      // Edit dashboards
'n8ndash_delete_dashboards'    // Delete dashboards
'n8ndash_export_dashboards'    // Export dashboards
'n8ndash_import_dashboards'    // Import dashboards
'n8ndash_manage_settings'      // Manage plugin settings

// Role-specific capabilities
'n8ndash_edit_own_dashboards'      // Edit own dashboards only
'n8ndash_delete_own_dashboards'    // Delete own dashboards only
'n8ndash_edit_others_dashboards'   // Edit any dashboard
'n8ndash_delete_others_dashboards' // Delete any dashboard
```

### Import/Export Access
- **All Roles** (Contributor, Author, Editor, Administrator) can access Import/Export tab
- **Export**: Users can export their own dashboards individually or all at once
- **Import**: Users can import dashboards to their own account
- **Settings Export**: Available to all roles with export capability

### Key Queries
```php
// Get user dashboards
$dashboards = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}n8ndash_dashboards WHERE user_id = %d",
    get_current_user_id()
) );

// Get dashboard widgets
$widgets = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}n8ndash_widgets WHERE dashboard_id = %d",
    $dashboard_id
) );
```

---

## 🔌 API Endpoints

### REST API
```php
GET    /wp-json/n8ndash/v1/dashboards
POST   /wp-json/n8ndash/v1/dashboards
PUT    /wp-json/n8ndash/v1/dashboards/{id}
DELETE /wp-json/n8ndash/v1/dashboards/{id}

GET    /wp-json/n8ndash/v1/widgets/{id}
POST   /wp-json/n8ndash/v1/widgets
PUT    /wp-json/n8ndash/v1/widgets/{id}
DELETE /wp-json/n8ndash/v1/widgets/{id}
```

### AJAX Actions
```php
// Admin
'n8ndash_save_dashboard'
'n8ndash_delete_dashboard'
'n8ndash_save_widget'
'n8ndash_delete_widget'
'n8ndash_save_settings'

// Public
'n8ndash_custom_widget_submit'
'n8ndash_refresh_widget'
```

---

## 🎨 Customization

### Custom Widget Class
```php
class My_Custom_Widget extends N8nDash_Widget {
    
    public function get_type() {
        return 'my_custom';
    }
    
    public function render() {
        $data = $this->get_processed_data();
        include $this->get_template_path();
    }
}

// Register widget
add_filter( 'n8ndash_widget_types', function( $types ) {
    $types['my_custom'] = 'My_Custom_Widget';
    return $types;
});
```

### Template Override
```php
// In theme's functions.php
add_filter( 'n8ndash_widget_template_path', function( $template_path, $widget_type ) {
    $theme_template = get_template_directory() . '/n8ndash/widgets/' . $widget_type . '.php';
    return file_exists( $theme_template ) ? $theme_template : $template_path;
}, 10, 2 );
```

---

## 🗑️ Uninstall System

### Uninstall Operations
```php
// AJAX action for uninstall
action: 'n8ndash_execute_uninstall'
nonce: 'n8ndash_uninstall'
mode: 'keep' | 'clean' | 'remove'

// Available modes
'keep'    // No action, data preserved
'clean'   // Remove dashboards/widgets only
'remove'  // Complete cleanup
```

### Safety Features
- **User Consent Required**: No automatic deletion
- **Data Export**: Backup before removal
- **Multiple Confirmations**: Clear warnings
- **Capability Checks**: Only authorized users
- **Nonce Verification**: CSRF protection

## 🔒 Security

### Required Checks
```php
// Nonce verification
if ( ! wp_verify_nonce( $_POST['nonce'], 'n8ndash_action' ) ) {
    wp_die( 'Security check failed' );
}

// Capability check
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Insufficient permissions' );
}

// AJAX nonce
if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
    wp_send_json_error( array( 'message' => 'Security check failed' ) );
}
```

### Data Sanitization
```php
$title = sanitize_text_field( $_POST['title'] );
$url = esc_url_raw( $_POST['webhook_url'] );
$config = wp_kses_post( $_POST['config'] );
```

---

## 🚀 Performance

### Caching
```php
// Widget data caching
$cache_key = 'n8ndash_widget_' . $widget_id;
$cached_data = get_transient( $cache_key );

if ( false === $cached_data ) {
    $cached_data = $this->fetch_webhook_data();
    set_transient( $cache_key, $cached_data, 300 ); // 5 minutes
}
```

### Database Optimization
```sql
-- Add indexes for better performance
CREATE INDEX idx_dashboard_user ON wp_n8ndash_dashboards(user_id);
CREATE INDEX idx_widget_dashboard ON wp_n8ndash_widgets(dashboard_id);
CREATE INDEX idx_widget_type ON wp_n8ndash_widgets(type);
```

---

## 🐛 Troubleshooting

### Common Issues
1. **Webhook not connecting**: Check CORS headers, SSL certificates
2. **Data not displaying**: Verify JSON structure and JSON paths
3. **Permission errors**: Check user capabilities and role settings
4. **Performance issues**: Enable caching, optimize database queries

### Debug Mode
```php
// Enable in wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

// Check logs
$log_file = WP_CONTENT_DIR . '/debug.log';
$logs = file_get_contents( $log_file );
```

---

## 📱 Frontend

### JavaScript Events
```javascript
// Widget events
$(document).on('n8ndash_widget_added', function(e, widgetData) { });
$(document).on('n8ndash_widget_refreshing', function(e, widgetId) { });
$(document).on('n8ndash_data_processed', function(e, data, widgetId) { });

// Dashboard events
$(document).on('n8ndash_dashboard_saving', function(e, dashboardData) { });
$(document).on('n8ndash_dashboard_saved', function(e, dashboardId) { });
```

### CSS Classes
```css
.n8n-widget                    /* Base widget class */
.n8n-widget--data            /* Data widget variant */
.n8n-widget--chart           /* Chart widget variant */
.n8n-widget--custom          /* Custom widget variant */
.n8n-widget__header          /* Widget header */
.n8n-widget__body            /* Widget content */
.n8n-widget__footer          /* Widget footer */
```

---

## 🔧 Settings

### Available Options
```php
$settings = array(
    'enable_public_dashboards' => false,
    'default_refresh_interval' => 300,
    'max_widgets_per_dashboard' => 20,
    'enable_widget_animations' => true,
    'allowed_roles' => array( 'administrator', 'editor' ),
    'date_format' => get_option( 'date_format' ),
    'time_format' => get_option( 'time_format' )
);
```

### Uninstall & Data Management
```php
// Uninstall modes
'keep'    // Preserve all data (default)
'clean'   // Remove dashboards/widgets only
'remove'  // Complete cleanup

// AJAX actions
action: 'n8ndash_execute_uninstall'    // Uninstall operations
action: 'n8ndash_restore_all_data'     // Restore from backup
action: 'n8ndash_export_all'           // Export all data
```

### Uninstall Options
- **Keep All Data**: Safe default, no data loss
- **Clean Data Only**: Remove dashboards/widgets, keep settings
- **Remove Everything**: Complete cleanup with warnings
- **Data Export**: Backup option before removal
- **Multiple Confirmations**: Prevents accidental deletion

### Access Control
```php
// Check if user can access
$allowed_roles = get_option( 'n8ndash_allowed_roles', array( 'administrator' ) );
$user_roles = wp_get_current_user()->roles;

if ( ! array_intersect( $allowed_roles, $user_roles ) ) {
    wp_die( 'Access denied' );
}
```

---

## 📚 File Structure

```
n8ndash-pro/
├── n8ndash-pro.php              # Main plugin file
├── includes/                     # Core classes
│   ├── class-n8ndash-core.php
│   ├── class-n8ndash-loader.php
│   └── class-n8ndash-activator.php
├── admin/                        # Admin interface
│   ├── class-n8ndash-admin.php
│   └── partials/
├── public/                       # Public functionality
│   └── class-n8ndash-public.php
├── widgets/                      # Widget classes
│   ├── abstract-n8ndash-widget.php
│   ├── class-n8ndash-data-widget.php
│   ├── class-n8ndash-chart-widget.php
│   └── class-n8ndash-custom-widget.php
├── database/                     # Database operations
│   └── class-n8ndash-db.php
├── assets/                       # CSS, JS, images
│   ├── css/
│   └── js/
└── templates/                    # Widget templates
    └── widgets/
```

---

## 🆘 Support

- **Documentation**: See DOCUMENTATION.md for complete details
- **GitHub**: [n8nDash Pro Repository](https://github.com/AmitAnandNiraj/n8ndash-pro)
- **Email**: n8ndash@gmail.com
- **Issues**: [GitHub Issues](https://github.com/AmitAnandNiraj/n8ndash-pro/issues)

---

*Quick Reference Version: 1.0.0 | For complete documentation, see DOCUMENTATION.md*

## Import/Export

### AJAX Actions
- `n8ndash_export_dashboard` - Export single dashboard
- `n8ndash_export_all` - Export all dashboards
- `n8ndash_export_settings` - Export plugin settings
- `n8ndash_import_data` - Import dashboard data
- `n8ndash_restore_all_data` - Restore all plugin data

### Quick Export Buttons
- **Export All Dashboards**: One-click export of all dashboards

### Quick Import Buttons
- **Import All Dashboards**: One-click import for multiple dashboard files
- **Import All Data**: Comprehensive import for complete data restoration

### File Formats
- **Export**: JSON with metadata (version, type, exported date)
- **Import**: JSON files with dashboard, widget, and settings data
