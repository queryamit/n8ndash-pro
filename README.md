# n8nDash Pro - WordPress Plugin

**Version**: 1.2.0  
**Author**: Amit Anand Niraj  
**Author Email**: queryamit@gmail.com  
**Author Website**: https://anandtech.in  
**Author GitHub**: https://github.com/queryamit  
**Author LinkedIn**: https://www.linkedin.com/in/queryamit/  
**License**: GPL v2 or later  
**Requires**: WordPress 5.8+, PHP 8.1+

## Description

n8nDash Pro is a professional WordPress plugin that brings the power of n8n automation dashboards to WordPress. Create beautiful, responsive dashboards to monitor and control your n8n workflows directly from your WordPress admin panel.

## Features

- 🎯 **Multiple Widget Types**
  - Data Widgets (KPIs and Lists)
  - Chart Widgets (Line, Bar, Pie)
  - Custom Form Widgets
  
- 🔄 **n8n Integration**
  - Webhook-based data fetching
  - Support for GET/POST/PUT/DELETE methods
  - Custom headers configuration
  - JSON and form data support

- 🎨 **Modern UI/UX**
  - Drag-and-drop dashboard builder
  - Responsive design
  - Multiple color themes
  - Dark mode support
  - Accessibility compliant (WCAG 2.1 AA)

- 🔒 **Security & Permissions**
  - Role-based access control
  - Nonce verification
  - Data sanitization
  - SQL injection prevention

- 📊 **Advanced Features**
  - Real-time data updates
  - Import/Export dashboards
  - Shortcode support
  - Gutenberg blocks
  - REST API
  - Multi-language ready
  - User-controlled uninstall system

## Installation

1. Upload the `n8ndash-pro` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **n8nDash** in your WordPress admin menu
4. Start creating your first dashboard!

## Quick Start

### Creating Your First Dashboard

1. Go to **n8nDash → Add New Dashboard**
2. Give your dashboard a name
3. Click **Add Widget** to add your first widget
4. Configure the widget with your n8n webhook URL
5. Save and view your dashboard

### Widget Configuration

#### Data Widget (KPI)
```json
{
  "value1": "$82,440",
  "value2": "+4.3%",
  "value3Url": "https://example.com/details"
}
```

#### Data Widget (List)
```json
{
  "items": [
    {"title": "Item 1", "url": "https://example.com/1"},
    {"title": "Item 2", "url": "https://example.com/2"}
  ]
}
```

### Using Shortcodes

Display a dashboard:
```
[n8ndash_dashboard id="123"]
```

Display a single widget:
```
[n8ndash_widget id="456"]
```

## 👥 User Roles and Capabilities

The plugin implements a comprehensive role-based access control system that ensures security while providing appropriate functionality to all user levels:

### **Role Hierarchy**

- **🔐 Administrator**: Full access to all features
  - Create, edit, delete any dashboard
  - Manage all users and settings
  - Import/export all data
  - Access to all plugin features

- **📝 Editor**: Advanced dashboard management
  - Create, edit, delete any dashboard
  - Import/export dashboards
  - Access to Import/Export tab
  - Cannot manage plugin settings

- **✍️ Author**: Own dashboard management
  - Create, edit, delete own dashboards
  - Import/export own dashboards
  - Access to Import/Export tab
  - Cannot access other users' dashboards

- **📋 Contributor**: Limited dashboard management
  - Create, edit, delete own dashboards
  - Import/export own dashboards
  - Access to Import/Export tab
  - Cannot access other users' dashboards

- **👀 Subscriber**: View-only access
  - View public dashboards only
  - Cannot create or modify dashboards

### **Import/Export Access**
All user roles (Contributor, Author, Editor, Administrator) now have access to the Import/Export functionality:

- **📤 Export Capabilities**:
  - Individual dashboard export
  - Bulk export of all owned dashboards
  - Settings export for backup purposes

- **📥 Import Capabilities**:
  - Import dashboards to user's own account
  - Automatic ownership assignment
  - Data validation and sanitization

- **🔒 Security Features**:
  - Users can only export their own dashboards
  - Imported dashboards are automatically assigned to current user
  - Nonce verification for all operations
  - Capability-based access control

## 🗑️ Uninstall System

The plugin includes a comprehensive, user-controlled uninstall system that follows WordPress best practices:

### **Uninstall Options**
1. **Keep All Data** (Default) - Safely deactivates plugin with all data preserved
2. **Clean Data Only** - Removes dashboards and widgets but keeps settings
3. **Remove Everything** - Complete plugin cleanup with multiple confirmations

### **Safety Features**
- **User Consent Required**: No automatic deletion without user choice
- **Data Export**: Backup option before any removal operations
- **Data Restoration**: Restore functionality from backup files
- **Multiple Confirmations**: Clear warnings about what will be removed
- **WordPress Standard**: Follows WordPress.org plugin guidelines
- **Safe by Default**: Default option preserves all data

Access uninstall options in **n8nDash → Settings → Uninstall Options**

## n8n Webhook Setup

### 1. Create a Webhook Node
In your n8n workflow, add a Webhook node with:
- **Webhook URL**: Copy this URL to use in the widget configuration
- **HTTP Method**: POST (recommended)
- **Response Mode**: "When last node finishes"

### 2. Process Your Data
Add nodes to fetch and process your data (e.g., from databases, APIs, etc.)

### 3. Return JSON Response
Use an HTTP Response node at the end:
- **Response Code**: 200
- **Response Headers**:
  ```
  Content-Type: application/json
  Access-Control-Allow-Origin: *
  ```
- **Response Body**: Your JSON data

## Development

### File Structure
```
n8ndash-pro/
├── n8ndash-pro.php          # Main plugin file
├── includes/                # Core functionality
├── admin/                   # Admin interface
├── public/                  # Public-facing functionality
├── widgets/                 # Widget classes
├── database/                # Database operations
├── api/                     # REST API endpoints
├── assets/                  # CSS, JS, images
└── languages/               # Translation files
```

### Hooks and Filters

#### Actions
- `n8ndash_before_render_widget` - Before widget rendering
- `n8ndash_after_render_widget` - After widget rendering
- `n8ndash_before_save_dashboard` - Before saving dashboard
- `n8ndash_after_save_dashboard` - After saving dashboard

#### Filters
- `n8ndash_widget_types` - Modify available widget types
- `n8ndash_widget_config` - Filter widget configuration
- `n8ndash_webhook_args` - Modify webhook request arguments
- `n8ndash_webhook_response` - Filter webhook response

### Creating Custom Widget Types

```php
class My_Custom_Widget extends N8nDash_Widget {
    public function get_type() {
        return 'my_custom';
    }
    
    public function render() {
        // Your rendering logic
    }
    
    // Implement other required methods
}

// Register the widget type
add_filter( 'n8ndash_widget_types', function( $types ) {
    $types['my_custom'] = 'My_Custom_Widget';
    return $types;
});
```

## Troubleshooting

### Common Issues

1. **Webhook not connecting**
   - Check CORS headers in n8n
   - Verify webhook URL is accessible
   - Check for SSL certificate issues

2. **Data not displaying**
   - Verify JSON structure matches configuration
   - Check browser console for errors
   - Enable debug mode in plugin settings

3. **Permission errors**
   - Ensure user has required capabilities
   - Check dashboard ownership
   - Verify role permissions

### Debug Mode

Enable debug mode in **n8nDash → Settings**:
- Shows detailed error messages
- Logs webhook requests/responses
- Displays JSON path helpers

## Support

- **Documentation**: [GitHub Wiki](https://github.com/queryamit/n8ndash-pro/wiki)
- **Issues**: [GitHub Issues](https://github.com/queryamit/n8ndash-pro/issues)
- **Email**: queryamit@gmail.com
- **Website**: https://anandtech.in
- **GitHub**: https://github.com/queryamit
- **LinkedIn**: https://www.linkedin.com/in/queryamit/

## Changelog

### [1.0.0] - 2024-01-XX
- **Initial Release**: Complete n8nDash Pro plugin
- **Core Features**: Dashboard management, widget system, n8n integration
- **Widget Types**: KPI, List, Chart, and Custom widgets
- **Import/Export**: Complete data portability system
- **Security**: Role-based access control and nonce verification
- **Uninstall System**: User-controlled data cleanup with consent

### [1.1.0] - 2024-12-19
- **Enhanced Import/Export**: 
  - Quick Actions section for bulk operations
  - Organized import/export layout for better user experience
- **Improved Uninstall System**: User-controlled data cleanup with clear backup instructions
- **Streamlined Interface**: Removed duplicate export and restore functionality, cleaner uninstall section
- **Better User Guidance**: Clear direction to Import/Export tab for all data operations
- **Eliminated Redundancy**: Removed confusing "Restore All Data" button (functionality exists in Import/Export tab)

### [1.2.0] - 2024-12-19
- **Enhanced Role-Based Access Control**:
  - Extended Import/Export access to Contributor, Author, and Editor roles
  - Added `n8ndash_import_dashboards` capability to all user roles
  - Updated menu access for Import/Export tab based on export capability
  - Enhanced security with proper permission checks in all AJAX handlers
- **Improved User Experience**:
  - All roles can now backup and restore their dashboards
  - Better data portability for team collaboration
  - Maintained security with user data isolation
  - Automatic ownership assignment for imported dashboards

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

## Credits

- Original n8nDash by Solomon Christ
- WordPress Plugin conversion by Amit Anand Niraj
- **Developer**: Amit Anand Niraj (queryamit@gmail.com)
- **Website**: https://anandtech.in
- **GitHub**: https://github.com/queryamit
- **LinkedIn**: https://www.linkedin.com/in/queryamit/
- Icons by Dashicons and Lucide
- Charts powered by Chart.js

---

Made with ❤️ for the n8n community