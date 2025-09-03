# n8nDash Pro - Complete Plugin Documentation

**Version**: 1.2.0  
**Author**: Amit Anand Niraj  
**Author Email**: queryamit@gmail.com  
**Author Website**: https://anandtech.in  
**Author GitHub**: https://github.com/queryamit  
**Author LinkedIn**: https://www.linkedin.com/in/queryamit/  
**License**: GPL v2 or later  
**Requires**: WordPress 5.8+, PHP 8.1+  
**Last Updated**: December 2024

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Architecture & Structure](#architecture--structure)
3. [Features & Capabilities](#features--capabilities)
4. [Installation & Setup](#installation--setup)
5. [User Guide](#user-guide)
6. [Developer Guide](#developer-guide)
7. [API Reference](#api-reference)
8. [Troubleshooting](#troubleshooting)
9. [Performance & Security](#performance--security)
10. [Changelog](#changelog)

---

## 🎯 Overview

### What is n8nDash Pro?

n8nDash Pro is a professional WordPress plugin that transforms your WordPress site into a powerful dashboard for monitoring and controlling n8n automation workflows. It provides a modern, responsive interface for creating custom dashboards with real-time data visualization and interactive widgets.

### Key Benefits

- **🚀 Seamless Integration**: Direct n8n webhook integration
- **🎨 Modern UI/UX**: Drag-and-drop dashboard builder with responsive design
- **📊 Rich Widgets**: Multiple widget types for different data visualization needs
- **🔒 Enterprise Security**: Role-based access control and data sanitization
- **📱 Mobile Responsive**: Works perfectly on all devices
- **🔧 Developer Friendly**: Extensive hooks, filters, and customization options

### Use Cases

- **Business Intelligence**: Real-time KPI dashboards
- **Process Monitoring**: Workflow status and performance tracking
- **Data Visualization**: Charts and graphs for analytics
- **Form Submissions**: Custom data collection forms
- **System Monitoring**: Server and application health dashboards

---

## 🏗️ Architecture & Structure

### Plugin Architecture

```
n8nDash Pro Plugin
├── Core System (includes/)
│   ├── Plugin Loader & Hooks
│   ├── Internationalization
│   ├── REST API Integration
│   └── Import/Export System
├── Admin Interface (admin/)
│   ├── Dashboard Management
│   ├── Widget Configuration
│   ├── Settings & Configuration
│   └── Import/Export Tools
├── Public Interface (public/)
│   ├── Frontend Display
│   ├── AJAX Handlers
│   └── Shortcode Processing
├── Widget System (widgets/)
│   ├── Abstract Base Class
│   ├── Data Widgets (KPI/List)
│   ├── Chart Widgets
│   └── Custom Form Widgets
├── Database Layer (database/)
│   ├── Schema Management
│   ├── CRUD Operations
│   └── Data Validation
└── Assets (assets/)
    ├── JavaScript (Admin/Public)
    ├── CSS Styling
    └── Images & Icons
```

### Core Classes

#### 1. **N8nDash_Core** (`includes/class-n8ndash-core.php`)
- **Purpose**: Main plugin orchestrator
- **Responsibilities**: 
  - Plugin initialization
  - Hook registration
  - Dependency loading
  - Admin and public interface setup

#### 2. **N8nDash_Admin** (`admin/class-n8ndash-admin.php`)
- **Purpose**: Admin interface management
- **Responsibilities**:
  - Dashboard CRUD operations
  - Widget management
  - Settings configuration
  - AJAX request handling

#### 3. **N8nDash_Widget** (`widgets/abstract-n8ndash-widget.php`)
- **Purpose**: Abstract base class for all widgets
- **Responsibilities**:
  - Common widget functionality
  - Webhook communication
  - Data processing
  - Rendering templates

#### 4. **N8nDash_Database** (`database/class-n8ndash-db.php`)
- **Purpose**: Database operations and schema management
- **Responsibilities**:
  - Table creation/updates
  - Data persistence
  - Query optimization
  - Data validation

### Database Schema

#### Tables

1. **`wp_n8ndash_dashboards`**
   ```sql
   - id (BIGINT, Primary Key)
   - title (VARCHAR 255)
   - description (TEXT)
   - settings (LONGTEXT, JSON)
   - user_id (BIGINT)
   - is_public (TINYINT)
   - created_at (DATETIME)
   - updated_at (DATETIME)
   ```

2. **`wp_n8ndash_widgets`**
   ```sql
   - id (BIGINT, Primary Key)
   - dashboard_id (BIGINT, Foreign Key)
   - type (VARCHAR 50)
   - title (VARCHAR 255)
   - configuration (LONGTEXT, JSON)
   - position (LONGTEXT, JSON)
   - size (LONGTEXT, JSON)
   - created_at (DATETIME)
   - updated_at (DATETIME)
   ```

---

## ✨ Features & Capabilities

### Widget Types

#### 1. **Data Widgets**

##### KPI Widget
- **Purpose**: Display key performance indicators
- **Features**:
  - Multiple value display
  - Trend indicators
  - Clickable URLs
  - Custom styling
- **Data Format**:
  ```json
  {
    "value1": "$82,440",
    "value2": "+4.3%",
    "value3Url": "https://example.com/details"
  }
  ```

##### List Widget
- **Purpose**: Display lists of items with links
- **Features**:
  - Dynamic item lists
  - Clickable URLs
  - Custom item styling
  - Pagination support
- **Data Format**:
  ```json
  {
    "items": [
      {"title": "Item 1", "url": "https://example.com/1"},
      {"title": "Item 2", "url": "https://example.com/2"}
    ]
  }
  ```

#### 2. **Chart Widgets**

##### Line Chart
- **Purpose**: Time-series data visualization
- **Features**:
  - Multiple datasets
  - Custom colors
  - Interactive tooltips
  - Responsive design

##### Bar Chart
- **Purpose**: Categorical data comparison
- **Features**:
  - Horizontal/vertical orientation
  - Stacked bars
  - Custom styling
  - Animation effects

##### Pie Chart
- **Purpose**: Proportional data representation
- **Features**:
  - Custom colors
  - Interactive segments
  - Legend display
  - Animation effects

#### 3. **Custom Form Widget**
- **Purpose**: Dynamic form creation and submission
- **Features**:
  - Dynamic field generation
  - Form validation
  - AJAX submission
  - n8n webhook integration
- **Configuration**:
  ```json
  {
    "fields": [
      {"type": "text", "label": "Name", "required": true},
      {"type": "email", "label": "Email", "required": true}
    ],
    "submit_url": "https://n8n.example.com/webhook",
    "submit_method": "POST"
  }
  ```

### Dashboard Features

#### 1. **Drag & Drop Interface**
- **Technology**: Interact.js library
- **Features**:
  - Smooth widget movement
  - Resize handles
  - Grid snapping
  - Position persistence

#### 2. **Responsive Design**
- **Breakpoints**:
  - Desktop: 1200px+
  - Tablet: 768px - 1199px
  - Mobile: 320px - 767px
- **Features**:
  - Adaptive layouts
  - Touch-friendly controls
  - Mobile-optimized UI

#### 3. **Theme System**
- **Available Themes**:
  - Ocean (Default)
  - Emerald
  - Orchid
  - Citrus
- **Customization**:
  - Color schemes
  - Typography
  - Spacing
  - Border styles

### n8n Integration

#### 1. **Webhook Communication**
- **HTTP Methods**: GET, POST, PUT, DELETE
- **Data Formats**: JSON, Form Data, XML
- **Headers**: Custom header support
- **Authentication**: Basic Auth, Bearer Token

#### 2. **Data Processing**
- **JSON Path**: Dynamic data extraction
- **Data Transformation**: Format conversion
- **Error Handling**: Graceful fallbacks
- **Caching**: Optional response caching

---

## 🚀 Installation & Setup

### System Requirements

- **WordPress**: 5.8 or higher
- **PHP**: 8.1 or higher
- **MySQL**: 5.7 or higher
- **Memory**: 128MB minimum (256MB recommended)
- **Browser**: Modern browsers with ES6 support

### Installation Steps

#### 1. **Plugin Upload**
```bash
# Option 1: WordPress Admin
1. Go to Plugins → Add New
2. Click "Upload Plugin"
3. Choose n8ndash-pro.zip
4. Click "Install Now"

# Option 2: FTP/File Manager
1. Extract n8ndash-pro.zip
2. Upload to /wp-content/plugins/
3. Ensure folder name is 'n8ndash-pro'
```

#### 2. **Plugin Activation**
```bash
1. Go to Plugins → Installed Plugins
2. Find "n8nDash Pro"
3. Click "Activate"
4. Check for activation messages
```

#### 3. **Database Setup**
```bash
# Automatic (Recommended)
- Tables created during activation
- No manual intervention required

# Manual (If needed)
- Check wp_n8ndash_dashboards table exists
- Check wp_n8ndash_widgets table exists
- Verify database permissions
```

### Initial Configuration

#### 1. **Access Permissions**
```php
// Default roles with access
$allowed_roles = array(
    'administrator',
    'editor'
);

// Customize in Settings → Access Control
```

#### 2. **n8n Webhook Setup**
```bash
1. Create webhook node in n8n
2. Set HTTP method (POST recommended)
3. Configure response headers:
   Content-Type: application/json
   Access-Control-Allow-Origin: *
4. Copy webhook URL for widget configuration
```

#### 3. **First Dashboard Creation**
```bash
1. Go to n8nDash → Add New Dashboard
2. Enter dashboard name and description
3. Click "Create Dashboard"
4. Add your first widget
5. Configure webhook URL and data mapping
6. Save and preview
```

---

## 📖 User Guide

### Dashboard Management

#### Creating a Dashboard

1. **Navigate to n8nDash → Add New Dashboard**
2. **Fill in basic information**:
   - Dashboard Title
   - Description (optional)
   - Public Access (if enabled)
3. **Click "Create Dashboard"**
4. **Add widgets** using the "Add Widget" button

#### Dashboard Settings

- **General**: Title, description, visibility
- **Layout**: Grid settings, spacing, themes
- **Permissions**: User access control
- **Export**: Dashboard backup

### Widget Management

#### Adding Widgets

1. **Click "Add Widget"** in dashboard edit mode
2. **Select widget type**:
   - Data Widget (KPI/List)
   - Chart Widget (Line/Bar/Pie)
   - Custom Form Widget
3. **Configure widget**:
   - Title and description
   - Webhook URL and method
   - Data mapping (JSON paths)
   - Styling options
4. **Position and size** the widget
5. **Save configuration**

#### Widget Configuration

##### Data Widget Configuration
```json
{
  "webhook_url": "https://n8n.example.com/webhook",
  "webhook_method": "GET",
  "refresh_interval": 300,
  "data_mapping": {
    "value1": "$.data.kpi1",
    "value2": "$.data.kpi2",
    "value3Url": "$.data.link"
  }
}
```

##### Chart Widget Configuration
```json
{
  "webhook_url": "https://n8n.example.com/webhook",
  "chart_type": "line",
  "data_mapping": {
    "labels": "$.data.labels",
    "datasets": "$.data.datasets"
  },
  "chart_options": {
    "responsive": true,
    "animation": true
  }
}
```

##### Custom Form Widget Configuration
```json
{
  "webhook_url": "https://n8n.example.com/webhook",
  "submit_method": "POST",
  "fields": [
    {
      "type": "text",
      "label": "Full Name",
      "name": "full_name",
      "required": true,
      "placeholder": "Enter your full name"
    },
    {
      "type": "email",
      "label": "Email Address",
      "name": "email",
      "required": true,
      "validation": "email"
    }
  ]
}
```

#### Widget Positioning

- **Drag**: Click and drag to move widgets
- **Resize**: Use resize handles to adjust size
- **Grid**: Widgets snap to grid for alignment
- **Save**: Positions automatically saved

### Settings Configuration

#### General Settings

- **Public Dashboards**: Enable/disable public access
- **Default Refresh Interval**: Global widget refresh timing
- **Maximum Widgets**: Per-dashboard widget limit
- **Widget Animations**: Enable/disable smooth transitions

#### Access Control

- **Allowed Roles**: Select user roles with access
- **Dashboard Ownership**: User-specific dashboard access
- **Public Visibility**: Control public dashboard access

#### Display Settings

- **Date Format**: Customize date display format
- **Time Format**: Customize time display format
- **Theme**: Default dashboard theme selection

### Import/Export

#### Exporting Dashboards

1. **Go to n8nDash → Import/Export**
2. **Click "Export All Dashboards"** (Quick export)
3. **Download JSON file**
4. **Backup your configuration**



#### Importing Dashboards

1. **Go to n8nDash → Import/Export**
2. **Click "Import All Dashboards"** (Quick import for multiple dashboards)
3. **Or use the detailed import form below**
4. **Select exported JSON file**
5. **Click "Import"**
6. **Verify imported dashboards**

#### Importing All Data

1. **Go to n8nDash → Settings**
2. **Click "Restore All Data"**
3. **Select exported JSON file**
4. **Click "Restore Data"**
5. **Complete data restoration including dashboards, widgets, and settings**

---

## 👨‍💻 Developer Guide

### Plugin Architecture

#### Hook System

The plugin uses WordPress hooks extensively for extensibility:

```php
// Actions
do_action( 'n8ndash_before_render_widget', $widget_id, $widget_data );
do_action( 'n8ndash_after_render_widget', $widget_id, $widget_data );
do_action( 'n8ndash_before_save_dashboard', $dashboard_id, $dashboard_data );
do_action( 'n8ndash_after_save_dashboard', $dashboard_id, $dashboard_data );

// Filters
$widget_types = apply_filters( 'n8ndash_widget_types', $default_types );
$widget_config = apply_filters( 'n8ndash_widget_config', $config, $widget_id );
$webhook_args = apply_filters( 'n8ndash_webhook_args', $args, $webhook_url );
$webhook_response = apply_filters( 'n8ndash_webhook_response', $response, $widget_id );
```

#### Class Extension

Extend the abstract widget class to create custom widgets:

```php
class My_Custom_Widget extends N8nDash_Widget {
    
    public function get_type() {
        return 'my_custom';
    }
    
    public function get_title() {
        return 'My Custom Widget';
    }
    
    public function render() {
        $data = $this->get_processed_data();
        $config = $this->get_configuration();
        
        // Your rendering logic here
        include $this->get_template_path();
    }
    
    protected function get_template_path() {
        return N8NDASH_PLUGIN_DIR . 'templates/widgets/my-custom.php';
    }
    
    protected function process_data( $raw_data ) {
        // Custom data processing logic
        return $processed_data;
    }
}

// Register the widget
add_filter( 'n8ndash_widget_types', function( $types ) {
    $types['my_custom'] = 'My_Custom_Widget';
    return $types;
});
```

### Customization Hooks

#### Widget Rendering

```php
// Modify widget HTML before rendering
add_action( 'n8ndash_before_render_widget', function( $widget_id, $widget_data ) {
    // Add custom classes, attributes, or content
}, 10, 2 );

// Modify widget HTML after rendering
add_action( 'n8ndash_after_render_widget', function( $widget_id, $widget_data ) {
    // Add additional content or scripts
}, 10, 2 );
```

#### Data Processing

```php
// Modify webhook request arguments
add_filter( 'n8ndash_webhook_args', function( $args, $webhook_url ) {
    // Add custom headers, authentication, etc.
    $args['headers']['X-Custom-Header'] = 'Custom Value';
    return $args;
}, 10, 2 );

// Modify webhook response data
add_filter( 'n8ndash_webhook_response', function( $response, $widget_id ) {
    // Transform, validate, or enhance response data
    return $response;
}, 10, 2 );
```

#### Dashboard Management

```php
// Modify dashboard data before saving
add_filter( 'n8ndash_before_save_dashboard', function( $dashboard_data ) {
    // Validate or modify dashboard configuration
    return $dashboard_data;
}, 10, 1 );

// Perform actions after dashboard save
add_action( 'n8ndash_after_save_dashboard', function( $dashboard_id, $dashboard_data ) {
    // Log, notify, or perform additional operations
}, 10, 2 );
```

### Template System

#### Widget Templates

Create custom widget templates in your theme:

```php
// In your theme's functions.php
add_filter( 'n8ndash_widget_template_path', function( $template_path, $widget_type ) {
    $theme_template = get_template_directory() . '/n8ndash/widgets/' . $widget_type . '.php';
    
    if ( file_exists( $theme_template ) ) {
        return $theme_template;
    }
    
    return $template_path;
}, 10, 2 );
```

#### Template Structure

```php
<!-- templates/widgets/data-widget.php -->
<div class="n8n-widget n8n-widget--data" 
     data-widget-id="<?php echo esc_attr( $this->get_id() ); ?>"
     data-widget-type="<?php echo esc_attr( $this->get_type() ); ?>">
    
    <div class="n8n-widget__header">
        <h3 class="n8n-widget__title"><?php echo esc_html( $this->get_title() ); ?></h3>
        <div class="n8n-widget__controls">
            <button class="n8n-widget__refresh" title="Refresh Data">
                <span class="dashicons dashicons-update"></span>
            </button>
        </div>
    </div>
    
    <div class="n8n-widget__body">
        <?php $this->render_content(); ?>
    </div>
    
    <div class="n8n-widget__footer">
        <span class="n8n-widget__last-updated">
            Last updated: <?php echo esc_html( $this->get_last_updated() ); ?>
        </span>
    </div>
</div>
```

### JavaScript Customization

#### Admin JavaScript

```javascript
// Extend admin functionality
jQuery(document).ready(function($) {
    
    // Custom widget initialization
    $(document).on('n8ndash_widget_added', function(e, widgetData) {
        console.log('Widget added:', widgetData);
        // Add custom initialization logic
    });
    
    // Custom dashboard save handling
    $(document).on('n8ndash_dashboard_saving', function(e, dashboardData) {
        console.log('Saving dashboard:', dashboardData);
        // Add custom validation or processing
    });
    
    // Custom widget configuration
    $(document).on('n8ndash_widget_configuring', function(e, widgetElement) {
        // Add custom configuration options
    });
});
```

#### Public JavaScript

```javascript
// Extend public functionality
jQuery(document).ready(function($) {
    
    // Custom widget refresh handling
    $(document).on('n8ndash_widget_refreshing', function(e, widgetId) {
        console.log('Refreshing widget:', widgetId);
        // Add custom refresh logic
    });
    
    // Custom data processing
    $(document).on('n8ndash_data_processed', function(e, data, widgetId) {
        console.log('Data processed:', data, widgetId);
        // Add custom data transformation
    });
    
    // Custom error handling
    $(document).on('n8ndash_widget_error', function(e, error, widgetId) {
        console.error('Widget error:', error, widgetId);
        // Add custom error handling
    });
});
```

### Database Operations

#### Custom Queries

```php
// Get dashboards by user role
function get_dashboards_by_role( $role ) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'n8ndash_dashboards';
    $users = get_users( array( 'role' => $role ) );
    $user_ids = wp_list_pluck( $users, 'ID' );
    
    if ( empty( $user_ids ) ) {
        return array();
    }
    
    $placeholders = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
    $query = $wpdb->prepare(
        "SELECT * FROM {$table} WHERE user_id IN ({$placeholders}) ORDER BY created_at DESC",
        $user_ids
    );
    
    return $wpdb->get_results( $query );
}

// Get widget statistics
function get_widget_statistics() {
    global $wpdb;
    
    $table = $wpdb->prefix . 'n8ndash_widgets';
    
    $stats = $wpdb->get_results("
        SELECT 
            type,
            COUNT(*) as count,
            AVG(JSON_EXTRACT(size, '$.width') * JSON_EXTRACT(size, '$.height')) as avg_size
        FROM {$table}
        GROUP BY type
    ");
    
    return $stats;
}
```

#### Data Validation

```php
// Validate dashboard data
function validate_dashboard_data( $data ) {
    $errors = array();
    
    if ( empty( $data['title'] ) ) {
        $errors[] = 'Dashboard title is required';
    }
    
    if ( strlen( $data['title'] ) > 255 ) {
        $errors[] = 'Dashboard title must be less than 255 characters';
    }
    
    if ( ! empty( $data['max_widgets'] ) && ! is_numeric( $data['max_widgets'] ) ) {
        $errors[] = 'Maximum widgets must be a number';
    }
    
    return $errors;
}

// Validate widget configuration
function validate_widget_config( $config, $type ) {
    $errors = array();
    
    if ( empty( $config['webhook_url'] ) ) {
        $errors[] = 'Webhook URL is required';
    }
    
    if ( ! filter_var( $config['webhook_url'], FILTER_VALIDATE_URL ) ) {
        $errors[] = 'Invalid webhook URL format';
    }
    
    // Type-specific validation
    switch ( $type ) {
        case 'data':
            if ( empty( $config['data_mapping'] ) ) {
                $errors[] = 'Data mapping is required for data widgets';
            }
            break;
            
        case 'chart':
            if ( empty( $config['chart_type'] ) ) {
                $errors[] = 'Chart type is required for chart widgets';
            }
            break;
            
        case 'custom':
            if ( empty( $config['fields'] ) ) {
                $errors[] = 'Form fields are required for custom widgets';
            }
            break;
    }
    
    return $errors;
}
```

---

## 🔌 API Reference

### REST API Endpoints

#### Dashboard Endpoints

```php
// Get all dashboards
GET /wp-json/n8ndash/v1/dashboards

// Get specific dashboard
GET /wp-json/n8ndash/v1/dashboards/{id}

// Create dashboard
POST /wp-json/n8ndash/v1/dashboards

// Update dashboard
PUT /wp-json/n8ndash/v1/dashboards/{id}

// Delete dashboard
DELETE /wp-json/n8ndash/v1/dashboards/{id}
```

#### Widget Endpoints

```php
// Get dashboard widgets
GET /wp-json/n8ndash/v1/dashboards/{id}/widgets

// Get specific widget
GET /wp-json/n8ndash/v1/widgets/{id}

// Create widget
POST /wp-json/n8ndash/v1/widgets

// Update widget
PUT /wp-json/n8ndash/v1/widgets/{id}

// Delete widget
DELETE /wp-json/n8ndash/v1/widgets/{id}
```

#### Authentication

```php
// Nonce-based authentication
$nonce = wp_create_nonce( 'wp_rest' );

// Headers
'X-WP-Nonce': nonce_value
'Authorization': 'Bearer token_value' // If using JWT
```

### AJAX Endpoints

#### Admin AJAX

```php
// Save dashboard
action: 'n8ndash_save_dashboard'
nonce: 'n8ndash_admin_nonce'

// Delete dashboard
action: 'n8ndash_delete_dashboard'
nonce: 'n8ndash_admin_nonce'

// Save widget
action: 'n8ndash_save_widget'
nonce: 'n8ndash_admin_nonce'

// Delete widget
action: 'n8ndash_delete_widget'
nonce: 'n8ndash_admin_nonce'

// Save settings
action: 'n8ndash_save_settings'
nonce: 'n8ndash_settings_nonce'
```

#### Public AJAX

```php
// Custom widget form submission
action: 'n8ndash_custom_widget_submit'
nonce: 'n8ndash_public_nonce'

// Widget refresh
action: 'n8ndash_refresh_widget'
nonce: 'n8ndash_public_nonce'
```

### Shortcodes

#### Dashboard Shortcode

```php
// Display complete dashboard
[n8ndash_dashboard id="123"]

// Display dashboard with custom settings
[n8ndash_dashboard id="123" theme="ocean" height="600px"]

// Available attributes:
// - id: Dashboard ID (required)
// - theme: Color theme
// - height: Custom height
// - width: Custom width
// - responsive: Enable/disable responsive behavior
```

#### Widget Shortcode

```php
// Display single widget
[n8ndash_widget id="456"]

// Display widget with custom settings
[n8ndash_widget id="456" height="300px" width="400px"]

// Available attributes:
// - id: Widget ID (required)
// - height: Custom height
// - width: Custom width
// - theme: Widget theme
// - refresh: Auto-refresh interval
```

### JavaScript API

#### Admin API

```javascript
// Widget management
N8nDash.Admin.addWidget( dashboardId, widgetType );
N8nDash.Admin.removeWidget( widgetId );
N8nDash.Admin.updateWidget( widgetId, config );
N8nDash.Admin.refreshWidget( widgetId );

// Dashboard management
N8nDash.Admin.saveDashboard( dashboardId );
N8nDash.Admin.deleteDashboard( dashboardId );
N8nDash.Admin.exportDashboard( dashboardId );

// Settings management
N8nDash.Admin.saveSettings( settings );
N8nDash.Admin.getSettings();
```

#### Public API

```javascript
// Widget interaction
N8nDash.Public.refreshWidget( widgetId );
N8nDash.Public.getWidgetData( widgetId );
N8nDash.Public.submitForm( widgetId, formData );

// Dashboard interaction
N8nDash.Public.getDashboard( dashboardId );
N8nDash.Public.exportDashboard( dashboardId );
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. **Webhook Connection Issues**

**Symptoms**: Widgets show "Connection failed" or "No data"
**Causes**:
- CORS headers missing in n8n
- Invalid webhook URL
- Network connectivity issues
- SSL certificate problems

**Solutions**:
```php
// Add CORS headers in n8n webhook response
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE
Access-Control-Allow-Headers: Content-Type, Authorization

// Verify webhook URL accessibility
// Test with curl or Postman
curl -X GET "https://your-n8n-url.com/webhook"

// Check SSL certificate validity
// Ensure proper HTTPS configuration
```

#### 2. **Data Display Issues**

**Symptoms**: Widgets show "No data" or incorrect information
**Causes**:
- JSON structure mismatch
- Incorrect JSON path configuration
- Data format incompatibility
- Webhook response errors

**Solutions**:
```php
// Verify JSON structure matches widget configuration
// Use browser developer tools to inspect webhook response
// Check JSON path syntax (e.g., $.data.items[0].title)
// Enable debug mode for detailed error information
```

#### 3. **Permission Errors**

**Symptoms**: "Access denied" or "Permission denied" messages
**Causes**:
- Insufficient user capabilities
- Dashboard ownership restrictions
- Role permission misconfiguration
- Nonce verification failure

**Solutions**:
```php
// Check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Insufficient permissions' );
}

// Verify dashboard ownership
if ( $dashboard->user_id !== get_current_user_id() ) {
    wp_die( 'Access denied' );
}

// Check nonce validity
if ( ! wp_verify_nonce( $_POST['nonce'], 'n8ndash_action' ) ) {
    wp_die( 'Security check failed' );
}
```

#### 4. **Performance Issues**

**Symptoms**: Slow loading, high memory usage, timeouts
**Causes**:
- Large webhook responses
- Inefficient database queries
- Memory leaks in JavaScript
- Excessive AJAX requests

**Solutions**:
```php
// Optimize database queries
$widgets = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$table} WHERE dashboard_id = %d",
    $dashboard_id
) );

// Implement response caching
$cached_data = get_transient( 'n8ndash_widget_' . $widget_id );
if ( false === $cached_data ) {
    $cached_data = $this->fetch_webhook_data();
    set_transient( 'n8ndash_widget_' . $widget_id, $cached_data, 300 );
}

// Limit concurrent requests
$max_concurrent = 5;
$current_requests = get_transient( 'n8ndash_concurrent_requests' );
if ( $current_requests >= $max_concurrent ) {
    wp_die( 'Too many concurrent requests' );
}
```

### Debug Mode

#### Enabling Debug Mode

```php
// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

// In plugin settings
// Go to n8nDash → Settings → General Settings
// Enable "Debug Mode" checkbox
```

#### Debug Information

```php
// Check debug log
$log_file = WP_CONTENT_DIR . '/debug.log';
if ( file_exists( $log_file ) ) {
    $logs = file_get_contents( $log_file );
    // Filter n8nDash related entries
    $n8n_logs = array_filter( explode( "\n", $logs ), function( $line ) {
        return stripos( $line, 'n8ndash' ) !== false;
    } );
}
```

#### Common Debug Messages

```php
// Webhook request
[2024-01-01 12:00:00] [INFO] n8nDash: Webhook request to https://n8n.example.com/webhook

// Webhook response
[2024-01-01 12:00:01] [INFO] n8nDash: Webhook response received (200 OK)

// Data processing
[2024-01-01 12:00:02] [INFO] n8nDash: Widget data processed successfully

// Error messages
[2024-01-01 12:00:03] [ERROR] n8nDash: Webhook connection failed: cURL error 28
```

### Performance Optimization

#### Database Optimization

```php
// Use proper indexes
CREATE INDEX idx_dashboard_user ON wp_n8ndash_dashboards(user_id);
CREATE INDEX idx_widget_dashboard ON wp_n8ndash_widgets(dashboard_id);
CREATE INDEX idx_widget_type ON wp_n8ndash_widgets(type);

// Optimize queries
// Before (inefficient)
$widgets = $wpdb->get_results( "SELECT * FROM wp_n8ndash_widgets" );

// After (optimized)
$widgets = $wpdb->get_results( $wpdb->prepare(
    "SELECT id, title, type, configuration FROM wp_n8ndash_widgets 
     WHERE dashboard_id = %d ORDER BY created_at ASC",
    $dashboard_id
) );
```

#### JavaScript Optimization

```javascript
// Debounce frequent operations
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Optimize widget refresh
const debouncedRefresh = debounce(function(widgetId) {
    N8nDash.Public.refreshWidget(widgetId);
}, 300);

// Use event delegation
$(document).on('click', '.n8n-widget__refresh', function() {
    const widgetId = $(this).closest('.n8n-widget').data('widget-id');
    debouncedRefresh(widgetId);
});
```

---

## 🚀 Performance & Security

### Performance Best Practices

#### 1. **Database Optimization**

```php
// Use prepared statements
$stmt = $wpdb->prepare(
    "SELECT * FROM {$table} WHERE user_id = %d AND status = %s",
    $user_id,
    $status
);

// Implement pagination
$per_page = 20;
$offset = ( $page - 1 ) * $per_page;
$widgets = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$table} LIMIT %d OFFSET %d",
    $per_page,
    $offset
) );

// Use database indexes
// Ensure proper indexing on frequently queried columns
```

#### 2. **Caching Strategies**

```php
// Transient caching for webhook responses
$cache_key = 'n8ndash_widget_' . $widget_id;
$cached_data = get_transient( $cache_key );

if ( false === $cached_data ) {
    $cached_data = $this->fetch_webhook_data();
    set_transient( $cache_key, $cached_data, 300 ); // 5 minutes
}

// Object caching for dashboard data
$dashboard_cache_key = 'n8ndash_dashboard_' . $dashboard_id;
$dashboard_data = wp_cache_get( $dashboard_cache_key );

if ( false === $dashboard_data ) {
    $dashboard_data = $this->get_dashboard_data( $dashboard_id );
    wp_cache_set( $dashboard_cache_key, $dashboard_data, '', 3600 ); // 1 hour
}
```

#### 3. **Asset Optimization**

```php
// Minify CSS and JavaScript in production
if ( ! WP_DEBUG ) {
    wp_enqueue_script( 'n8ndash-admin', 
        N8NDASH_PLUGIN_URL . 'assets/js/admin/n8ndash-admin.min.js',
        array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-resizable' ),
        N8NDASH_VERSION,
        true
    );
}

// Use CDN for external libraries when possible
wp_enqueue_script( 'chartjs', 
    'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
    array(),
    '3.9.1',
    true
);
```

### Security Measures

#### 1. **Input Validation & Sanitization**

```php
// Validate and sanitize all inputs
$dashboard_title = sanitize_text_field( $_POST['dashboard_title'] );
$webhook_url = esc_url_raw( $_POST['webhook_url'] );
$widget_config = wp_kses_post( $_POST['widget_config'] );

// Validate data types
if ( ! is_numeric( $_POST['max_widgets'] ) ) {
    wp_die( 'Invalid max widgets value' );
}

// Validate JSON data
$config = json_decode( $_POST['config'], true );
if ( json_last_error() !== JSON_ERROR_NONE ) {
    wp_die( 'Invalid JSON configuration' );
}
```

#### 2. **Nonce Verification**

```php
// Verify nonce for all forms
if ( ! wp_verify_nonce( $_POST['nonce'], 'n8ndash_action' ) ) {
    wp_die( 'Security check failed' );
}

// Create nonce for forms
wp_nonce_field( 'n8ndash_action', 'n8ndash_nonce' );

// Verify AJAX nonce
if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
    wp_send_json_error( array( 'message' => 'Security check failed' ) );
}
```

#### 3. **Capability Checks**

```php
// Check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Insufficient permissions' );
}

// Check dashboard ownership
if ( $dashboard->user_id !== get_current_user_id() && 
     ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied' );
}

// Role-based access control
$allowed_roles = get_option( 'n8ndash_allowed_roles', array( 'administrator' ) );
$user_roles = wp_get_current_user()->roles;

if ( ! array_intersect( $allowed_roles, $user_roles ) ) {
    wp_die( 'Access denied' );
}
```

#### 4. **SQL Injection Prevention**

```php
// Use prepared statements
$stmt = $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}n8ndash_dashboards 
     WHERE user_id = %d AND status = %s",
    $user_id,
    $status
);

// Escape output
echo esc_html( $dashboard_title );
echo esc_url( $webhook_url );
echo wp_kses_post( $widget_description );

// Validate database queries
$allowed_tables = array( 'dashboards', 'widgets' );
$table = sanitize_key( $_GET['table'] );

if ( ! in_array( $table, $allowed_tables ) ) {
    wp_die( 'Invalid table specified' );
}
```

#### 5. **XSS Prevention**

```php
// Sanitize HTML output
echo wp_kses( $html_content, array(
    'div' => array( 'class' => array() ),
    'span' => array( 'class' => array() ),
    'a' => array( 'href' => array(), 'target' => array() ),
    'img' => array( 'src' => array(), 'alt' => array() )
) );

// Escape JavaScript output
echo '<script>var config = ' . wp_json_encode( $config ) . ';</script>';

// Sanitize CSS
echo 'style="' . esc_attr( $css_styles ) . '"';
```

### Role-Based Access Control

#### 1. **User Role Hierarchy**

The plugin implements a comprehensive role-based access control system:

- **🔐 Administrator**: Full access to all features and settings
- **📝 Editor**: Advanced dashboard management with Import/Export access
- **✍️ Author**: Own dashboard management with Import/Export access
- **📋 Contributor**: Limited dashboard management with Import/Export access
- **👀 Subscriber**: View-only access to public dashboards

#### 2. **Capability System**

```php
// Core capabilities for all roles
$capabilities = array(
    'n8ndash_view_dashboards',      // View dashboards
    'n8ndash_create_dashboards',    // Create new dashboards
    'n8ndash_edit_dashboards',      // Edit dashboards
    'n8ndash_delete_dashboards',    // Delete dashboards
    'n8ndash_export_dashboards',    // Export dashboards
    'n8ndash_import_dashboards',    // Import dashboards
    'n8ndash_manage_settings',      // Manage plugin settings
);

// Role-specific capabilities
$role_capabilities = array(
    'administrator' => array(
        'n8ndash_edit_others_dashboards',   // Edit any dashboard
        'n8ndash_delete_others_dashboards', // Delete any dashboard
    ),
    'author' => array(
        'n8ndash_edit_own_dashboards',      // Edit own dashboards only
        'n8ndash_delete_own_dashboards',    // Delete own dashboards only
    ),
    'contributor' => array(
        'n8ndash_edit_own_dashboards',      // Edit own dashboards only
        'n8ndash_delete_own_dashboards',    // Delete own dashboards only
    ),
);
```

#### 3. **Import/Export Access Control**

```php
// Check export permission
if ( ! current_user_can( 'n8ndash_export_dashboards' ) ) {
    wp_send_json_error( array( 
        'message' => 'Permission denied. You do not have permission to export dashboards.' 
    ) );
}

// Check import permission
if ( ! current_user_can( 'n8ndash_import_dashboards' ) ) {
    wp_send_json_error( array( 
        'message' => 'Permission denied. You do not have permission to import dashboards.' 
    ) );
}

// Ensure data ownership
$user_id = get_current_user_id();
$dashboards = N8nDash_DB::get_user_dashboards( $user_id );
```

#### 4. **Security Features**

- **Data Isolation**: Users can only export/import their own data
- **Menu Access**: Import/Export tab accessible based on export capability
- **Ownership Assignment**: Imported dashboards automatically assigned to current user
- **Permission Validation**: All operations verify user capabilities
- **Nonce Verification**: CSRF protection on all import/export operations

### Error Handling

#### 1. **Graceful Error Handling**

```php
// Try-catch blocks for critical operations
try {
    $response = wp_remote_get( $webhook_url, $args );
    
    if ( is_wp_error( $response ) ) {
        throw new Exception( $response->get_error_message() );
    }
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        throw new Exception( 'Invalid JSON response' );
    }
    
} catch ( Exception $e ) {
    error_log( 'n8nDash webhook error: ' . $e->getMessage() );
    return new WP_Error( 'webhook_error', $e->getMessage() );
}
```

#### 2. **User-Friendly Error Messages**

```php
// Provide helpful error messages
if ( is_wp_error( $result ) ) {
    $error_code = $result->get_error_code();
    
    switch ( $error_code ) {
        case 'webhook_error':
            $message = 'Unable to connect to n8n webhook. Please check the URL and try again.';
            break;
            
        case 'permission_denied':
            $message = 'You do not have permission to perform this action.';
            break;
            
        case 'invalid_data':
            $message = 'The provided data is invalid. Please check your configuration.';
            break;
            
        default:
            $message = 'An unexpected error occurred. Please try again.';
    }
    
    wp_send_json_error( array( 'message' => $message ) );
}
```

---

## 📝 Changelog

### Version 1.0.0 (December 2024)

#### 🎉 Initial Release
- **Core Dashboard System**: Complete dashboard creation and management
- **Widget System**: Data, Chart, and Custom form widgets
- **n8n Integration**: Webhook-based data communication
- **Admin Interface**: Full-featured dashboard builder
- **Public Display**: Shortcode and block support
- **Security**: Role-based access control and data sanitization
- **Performance**: Optimized database queries and asset loading

#### ✨ Key Features
- Drag-and-drop widget positioning
- Real-time data updates via webhooks
- Multiple chart types (Line, Bar, Pie)
- Custom form creation and submission
- Import/Export functionality
- Responsive design for all devices
- Multiple color themes
- REST API endpoints

#### 🔧 Technical Improvements
- WordPress coding standards compliance
- Comprehensive error handling
- Performance optimization
- Security hardening
- Extensive documentation
- Developer-friendly hooks and filters

#### 🗑️ Uninstall System (Latest Update)
- **User-Controlled Uninstall**: Three uninstall options with user consent
- **WordPress Standard**: Follows WordPress.org plugin guidelines
- **Safety Features**: Multiple confirmations and data export options
- **Admin Interface**: Uninstall options integrated into plugin settings
- **Comprehensive Cleanup**: Proper database, options, and capability cleanup

### Version 1.2.0 (December 2024)

#### 🔐 Enhanced Role-Based Access Control
- **Extended Import/Export Access**: Contributor, Author, and Editor roles now have Import/Export functionality
- **Enhanced Capabilities**: Added `n8ndash_import_dashboards` capability to all user roles
- **Improved Security**: Proper permission checks in all AJAX handlers
- **Menu Access**: Import/Export tab accessible based on export capability
- **Data Isolation**: Users can only export/import their own data

#### ✨ User Experience Improvements
- **Better Data Portability**: All roles can now backup and restore their dashboards
- **Team Collaboration**: Enhanced functionality for multi-user environments
- **Security Maintained**: User data isolation with automatic ownership assignment
- **Consistent Interface**: Unified access control across all user roles

---

## Uninstall System

### User-Controlled Data Cleanup
- **Uninstall Modes**: Keep data, clean data, or remove all data
- **User Consent**: Explicit confirmation required for destructive actions
- **Data Backup**: Users directed to Import/Export tab for data backup
- **Data Restoration**: Import All Dashboards feature handles data restoration
- **Plugin Isolation**: Only affects n8nDash Pro data, not other plugins

### Uninstall Options
1. **Keep Data**: Plugin deactivated, all data preserved
2. **Clean Data**: Remove dashboards and widgets, keep settings
3. **Remove All**: Complete cleanup of all plugin data

### Data Management
- **Backup Location**: Import/Export tab → Quick Actions section
- **Backup Method**: Use "Export All Dashboards" button
- **Restore Method**: Use "Import All Dashboards" feature in Import/Export tab
- **Purpose**: Backup dashboards before plugin removal
- **Note**: Settings page directs users to Import/Export tab for all data operations

## 📞 Support & Resources

### Getting Help

- **Documentation**: This comprehensive guide
- **GitHub Repository**: [n8nDash Pro](https://github.com/AmitAnandNiraj/n8ndash-pro)
- **Issues**: [GitHub Issues](https://github.com/AmitAnandNiraj/n8ndash-pro/issues)
- **Email Support**: n8ndash@gmail.com

### Community Resources

- **n8n Community**: [n8n.io/community](https://n8n.io/community)
- **WordPress Support**: [wordpress.org/support](https://wordpress.org/support)
- **Developer Forums**: Various WordPress development communities

### Contributing

We welcome contributions! Please see our contributing guidelines:

1. **Fork the repository**
2. **Create a feature branch**
3. **Make your changes**
4. **Test thoroughly**
5. **Submit a pull request**

### License

This plugin is licensed under the GPL v2 or later. See the [LICENSE](LICENSE) file for details.

---

## 🎯 Conclusion

n8nDash Pro represents a powerful, professional-grade solution for integrating n8n automation dashboards into WordPress. With its comprehensive feature set, robust architecture, and developer-friendly design, it provides everything needed to create sophisticated monitoring and control interfaces.

The plugin follows WordPress best practices, implements enterprise-grade security, and offers extensive customization options through hooks, filters, and template overrides. Whether you're a business user looking to monitor KPIs or a developer building custom automation interfaces, n8nDash Pro provides the tools and flexibility needed for success.

For ongoing development and support, please refer to the GitHub repository and community resources. We're committed to maintaining and improving this plugin to meet the evolving needs of the n8n and WordPress communities.

---

**Made with ❤️ for the n8n community**

*Documentation Version: 1.0.0 | Last Updated: December 2024*
