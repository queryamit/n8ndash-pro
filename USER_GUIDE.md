# 📚 n8nDash Pro - Complete User Guide

**Your Complete Guide to Building Professional n8n Automation Dashboards in WordPress**

---

## 📖 **Table of Contents**

1. [Getting Started](#getting-started)
2. [Creating Your First Dashboard](#creating-your-first-dashboard)
3. [Understanding Widgets](#understanding-widgets)
4. [n8n Integration Setup](#n8n-integration-setup)
5. [Dashboard Management](#dashboard-management)
6. [Import/Export System](#importexport-system)
7. [User Roles & Permissions](#user-roles--permissions)
8. [Advanced Features](#advanced-features)
9. [Troubleshooting](#troubleshooting)
10. [Best Practices](#best-practices)

---

## 🚀 **Getting Started**

### **What is n8nDash Pro?**
n8nDash Pro transforms your WordPress website into a powerful automation control center. It allows you to create beautiful, interactive dashboards that connect directly to your n8n workflows, displaying real-time data and providing control interfaces.

### **Before You Begin**
- ✅ WordPress 5.8 or higher installed
- ✅ PHP 8.1 or higher
- ✅ n8n instance running (for integration)
- ✅ Administrator access to WordPress

### **Installation Steps**
1. **Upload Plugin**: Upload the `n8ndash-pro` folder to `/wp-content/plugins/`
2. **Activate**: Go to **Plugins → Installed Plugins** and activate "n8nDash Pro"
3. **Access Menu**: You'll see a new **n8nDash** menu in your WordPress admin
4. **First Dashboard**: Click **n8nDash → Add New Dashboard** to begin

---

## 🎯 **Creating Your First Dashboard**

### **Step 1: Dashboard Basics**
1. Navigate to **n8nDash → Add New Dashboard**
2. **Dashboard Title**: Give your dashboard a descriptive name (e.g., "Sales Performance Dashboard")
3. **Description**: Add a brief description of what this dashboard displays
4. **Visibility**: Choose between private (only you) or public (anyone can view)
5. **Theme**: Select from Ocean, Emerald, Orchid, or Citrus themes

### **Step 2: Adding Your First Widget**
1. Click **Add Widget** button
2. Choose **Widget Type**:
   - **Data Widget**: For displaying KPIs and lists
   - **Chart Widget**: For data visualization
   - **Custom Form Widget**: For user input and form submission
3. **Widget Title**: Give your widget a name (e.g., "Monthly Sales")
4. **Position**: Drag the widget to your desired location on the dashboard

### **Step 3: Widget Configuration**
Each widget type has specific configuration options:

#### **Data Widget Configuration**
- **Data Source**: Enter your n8n webhook URL
- **HTTP Method**: Choose GET, POST, PUT, or DELETE
- **Refresh Interval**: How often to update data (in seconds)
- **Data Mapping**: Configure how to display the data

#### **Chart Widget Configuration**
- **Chart Type**: Line, Bar, or Pie chart
- **Data Source**: Your n8n webhook URL
- **Chart Options**: Colors, labels, animations
- **Data Series**: Configure multiple data series

#### **Custom Form Widget Configuration**
- **Form Fields**: Add text, email, number, select, or textarea fields
- **Validation Rules**: Set required fields and validation
- **Submit Action**: Configure where form data goes (n8n webhook)
- **Success Message**: Custom message after successful submission

### **Step 4: Save and Test**
1. Click **Save Dashboard** to store your configuration
2. Click **Preview** to see how your dashboard looks
3. Test your widgets by checking if they display data correctly
4. Make adjustments as needed

---

## 🎨 **Understanding Widgets**

### **Data Widgets - Your Information Display**

#### **KPI Display Widget**
Perfect for showing key performance indicators like sales numbers, conversion rates, or system status.

**Example Configuration:**
```json
{
  "value1": "$82,440",
  "value2": "+4.3%",
  "value3Url": "https://example.com/details"
}
```

**How to Use:**
1. Add a Data Widget to your dashboard
2. Set **Widget Type** to "KPI"
3. Enter your n8n webhook URL
4. Configure the display format (currency, percentage, etc.)
5. Set refresh interval for live updates

#### **List Widget**
Ideal for displaying dynamic lists like recent orders, user activities, or system alerts.

**Example Configuration:**
```json
{
  "items": [
    {"title": "Order #1234", "url": "https://example.com/order/1234"},
    {"title": "Order #1235", "url": "https://example.com/order/1235"}
  ]
}
```

**How to Use:**
1. Add a Data Widget
2. Set **Widget Type** to "List"
3. Configure your n8n webhook
4. Set up clickable links for each list item
5. Customize the appearance with CSS

### **Chart Widgets - Data Visualization**

#### **Line Chart**
Best for showing trends over time - sales over months, website traffic, or system performance.

**Setup Steps:**
1. Add a Chart Widget
2. Choose **Line Chart** type
3. Configure your data source (n8n webhook)
4. Set X-axis (time periods) and Y-axis (values)
5. Customize colors and styling

#### **Bar Chart**
Perfect for comparing categories - monthly sales by product, user activity by region, or performance metrics.

**Setup Steps:**
1. Add a Chart Widget
2. Choose **Bar Chart** type
3. Configure data categories and values
4. Set colors for different data series
5. Add legends and tooltips

#### **Pie Chart**
Great for showing proportions - market share, resource allocation, or survey results.

**Setup Steps:**
1. Add a Chart Widget
2. Choose **Pie Chart** type
3. Configure your data with labels and values
4. Set custom colors for each segment
5. Add percentage displays

### **Custom Form Widgets - User Interaction**

#### **Creating Interactive Forms**
Build forms that users can fill out to trigger n8n workflows or submit data.

**Field Types Available:**
- **Text**: Single line text input
- **Email**: Email address with validation
- **Number**: Numeric input with min/max values
- **Select**: Dropdown selection from options
- **Textarea**: Multi-line text input

**Form Configuration:**
1. Add a Custom Form Widget
2. **Add Fields**: Click "Add Field" for each form element
3. **Field Properties**: Set label, type, validation rules
4. **Submit Action**: Configure where form data goes
5. **Success Handling**: Set success messages and redirects

---

## 🔌 **n8n Integration Setup**

### **Understanding Webhooks**
Webhooks are the bridge between your n8n workflows and your WordPress dashboard. They allow real-time data flow and user interaction.

### **Setting Up n8n Webhooks**

#### **Step 1: Create Webhook Node**
1. In your n8n workflow, add a **Webhook** node
2. **Webhook URL**: Copy the generated URL
3. **HTTP Method**: Choose POST (recommended for most cases)
4. **Response Mode**: Set to "When last node finishes"

#### **Step 2: Process Your Data**
Add nodes to fetch and process your data:
- **HTTP Request**: Get data from external APIs
- **Database**: Query your databases
- **Function**: Transform and format data
- **Set**: Organize data structure

#### **Step 3: Return JSON Response**
Use an **HTTP Response** node at the end:
- **Response Code**: 200 (success)
- **Response Headers**:
  ```
  Content-Type: application/json
  Access-Control-Allow-Origin: *
  ```
- **Response Body**: Your formatted JSON data

### **Data Format Examples**

#### **For KPI Widgets**
```json
{
  "value1": "1250",
  "value2": "+12%",
  "value3": "Active Users"
}
```

#### **For Chart Widgets**
```json
{
  "labels": ["Jan", "Feb", "Mar", "Apr"],
  "datasets": [
    {
      "label": "Sales",
      "data": [1200, 1900, 3000, 5000]
    }
  ]
}
```

#### **For List Widgets**
```json
{
  "items": [
    {"title": "Item 1", "url": "https://example.com/1"},
    {"title": "Item 2", "url": "https://example.com/2"}
  ]
}
```

### **Advanced Webhook Features**

#### **Custom Headers**
Add authentication or custom headers:
- **Authorization**: Bearer token authentication
- **API Key**: Custom API key headers
- **Content-Type**: Specify data format

#### **Request Methods**
- **GET**: Retrieve data (read-only)
- **POST**: Submit data or trigger actions
- **PUT**: Update existing data
- **DELETE**: Remove data or stop processes

---

## 📊 **Dashboard Management**

### **Editing Existing Dashboards**

#### **Access Edit Mode**
1. Go to **n8nDash → Dashboards**
2. Find your dashboard in the list
3. Click **Edit** button
4. You'll enter the dashboard builder interface

#### **Modifying Widgets**
- **Move Widgets**: Drag and drop to new positions
- **Resize Widgets**: Use resize handles to adjust size
- **Edit Widgets**: Click widget settings to modify configuration
- **Delete Widgets**: Remove unwanted widgets

#### **Dashboard Settings**
- **Theme Changes**: Switch between different visual themes
- **Layout Adjustments**: Modify grid settings and spacing
- **Visibility Updates**: Change public/private status
- **Title/Description**: Update dashboard information

### **Duplicating Dashboards**

#### **Why Duplicate?**
- **Templates**: Create dashboard templates for different use cases
- **Testing**: Test changes without affecting the original
- **Variations**: Create similar dashboards with slight modifications

#### **How to Duplicate**
1. Go to **n8nDash → Dashboards**
2. Find the dashboard you want to duplicate
3. Click **Duplicate** button
4. Give the new dashboard a name
5. Modify as needed

### **Organizing Multiple Dashboards**

#### **Naming Conventions**
Use descriptive names that indicate purpose:
- "Sales Performance Q1 2024"
- "Customer Support Dashboard"
- "System Health Monitor"
- "Marketing Campaign Tracker"

#### **Dashboard Categories**
Group related dashboards:
- **Business Metrics**: Sales, marketing, finance
- **Operational**: Support, development, operations
- **Analytics**: User behavior, performance, trends
- **Control Panels**: System management, automation control

---

## 📤📥 **Import/Export System**

### **Exporting Your Data**

#### **Individual Dashboard Export**
1. Go to **n8nDash → Import/Export**
2. Find the dashboard you want to export
3. Click **Export Dashboard**
4. Download the JSON file
5. Store safely for backup or sharing

#### **Bulk Export All Dashboards**
1. Go to **n8nDash → Import/Export**
2. Click **Export All Dashboards** button
3. Download the complete backup file
4. Contains all your dashboards and widgets

#### **Settings Export**
1. In the Import/Export tab
2. Click **Export Settings**
3. Download your plugin configuration
4. Useful for migrating between sites

### **Importing Data**

#### **Single Dashboard Import**
1. Go to **n8nDash → Import/Export**
2. Click **Import Dashboard**
3. Choose your JSON file
4. Review the preview
5. Click **Import** to add to your site

#### **Bulk Import**
1. Use **Import All Dashboards** for multiple dashboards
2. Select a file containing multiple dashboards
3. Review the import preview
4. Confirm the import process

#### **Data Restoration**
1. Use import to restore from backups
2. All imported dashboards become yours
3. Widgets and configurations are preserved
4. Webhook URLs may need updating

### **Import/Export Best Practices**

#### **Regular Backups**
- **Weekly**: Export all dashboards
- **Before Changes**: Backup before major modifications
- **After Updates**: Backup after plugin updates
- **Site Migration**: Export before moving to new hosting

#### **File Organization**
- **Naming**: Use descriptive filenames with dates
- **Storage**: Keep backups in multiple locations
- **Version Control**: Track different versions of dashboards
- **Documentation**: Note what each backup contains

---

## 👥 **User Roles & Permissions**

### **Understanding WordPress Roles**

#### **Administrator**
- **Full Access**: Can do everything with the plugin
- **User Management**: Manage all users and their dashboards
- **Plugin Settings**: Configure global plugin options
- **System Access**: Access to all features and data

#### **Editor**
- **Dashboard Management**: Create, edit, delete any dashboard
- **Import/Export**: Can export and import dashboards
- **User Access**: Can manage dashboards for other users
- **No Settings**: Cannot modify plugin settings

#### **Author**
- **Own Dashboards**: Create, edit, delete their own dashboards
- **Import/Export**: Can export and import their own data
- **Limited Access**: Cannot access other users' dashboards
- **Widget Management**: Full control over their widgets

#### **Contributor**
- **Own Dashboards**: Create, edit, delete their own dashboards
- **Import/Export**: Can export and import their own data
- **Limited Creation**: Cannot publish dashboards publicly
- **Widget Control**: Manage their own widgets

#### **Subscriber**
- **View Only**: Can view public dashboards
- **No Creation**: Cannot create or modify dashboards
- **Read Access**: Limited to viewing published content
- **No Admin**: No access to admin features

### **Permission System**

#### **Dashboard Ownership**
- **Creator Rights**: Dashboard creator has full control
- **User Isolation**: Users can only see their own dashboards
- **Public Sharing**: Public dashboards visible to everyone
- **Access Control**: Private dashboards restricted to owner

#### **Import/Export Access**
- **Export Capability**: Users can export their own dashboards
- **Import Capability**: Users can import dashboards to their account
- **Data Isolation**: Imported dashboards become user's property
- **Security**: Nonce verification for all operations

---

## 🚀 **Advanced Features**

### **Shortcode Integration**

#### **Display Dashboards Anywhere**
Use shortcodes to embed dashboards in posts, pages, or widgets:

**Basic Dashboard Display:**
```
[n8ndash_dashboard id="123"]
```

**With Custom Attributes:**
```
[n8ndash_dashboard id="123" theme="ocean" height="600px"]
```

#### **Individual Widget Display**
Show specific widgets anywhere on your site:

**Basic Widget Display:**
```
[n8ndash_widget id="456"]
```

**With Styling:**
```
[n8ndash_widget id="456" width="100%" height="300px"]
```

### **Gutenberg Block Integration**

#### **Dashboard Block**
1. In the block editor, search for "n8nDash Dashboard"
2. Add the block to your page
3. Select which dashboard to display
4. Configure display options
5. Preview and publish

#### **Widget Block**
1. Search for "n8nDash Widget" in blocks
2. Add to your page
3. Choose specific widget to display
4. Set display parameters
5. Save and view

### **Custom Styling**

#### **CSS Customization**
Add custom CSS to modify appearance:

```css
/* Custom dashboard styling */
.n8ndash-dashboard {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

/* Custom widget styling */
.n8ndash-widget {
    border: 2px solid #e1e5e9;
    transition: all 0.3s ease;
}

.n8ndash-widget:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}
```

#### **Theme Customization**
- **Color Schemes**: Modify theme colors
- **Typography**: Change fonts and text styling
- **Spacing**: Adjust margins and padding
- **Animations**: Customize transition effects

---

## 🔧 **Troubleshooting**

### **Common Issues & Solutions**

#### **Widget Not Displaying Data**
**Problem**: Widget shows loading or no data
**Solutions**:
1. Check n8n webhook URL is correct
2. Verify webhook is accessible from your server
3. Check browser console for JavaScript errors
4. Test webhook URL directly in browser
5. Verify JSON response format matches widget configuration

#### **Permission Errors**
**Problem**: "Permission denied" or access errors
**Solutions**:
1. Check user role and capabilities
2. Verify dashboard ownership
3. Ensure user has required permissions
4. Check if dashboard is public or private
5. Verify nonce verification is working

#### **Import/Export Issues**
**Problem**: Import fails or export doesn't work
**Solutions**:
1. Check file format (must be valid JSON)
2. Verify file size isn't too large
3. Check server upload limits
4. Ensure proper permissions on upload directory
5. Verify JSON structure matches expected format

#### **Performance Issues**
**Problem**: Dashboard loads slowly or widgets lag
**Solutions**:
1. Reduce refresh intervals for widgets
2. Optimize n8n webhook response times
3. Check database query performance
4. Minimize widget count per dashboard
5. Use caching where appropriate

### **Debug Mode**

#### **Enabling Debug**
1. Go to **n8nDash → Settings**
2. Enable debug mode
3. Check browser console for detailed logs
4. Review WordPress debug log
5. Monitor webhook requests and responses

#### **Debug Information**
- **Webhook Calls**: See all HTTP requests
- **Response Data**: View raw data from n8n
- **Error Logs**: Detailed error information
- **Performance Metrics**: Load times and resource usage

---

## 💡 **Best Practices**

### **Dashboard Design**

#### **Layout Principles**
- **Grid System**: Use consistent grid alignment
- **Visual Hierarchy**: Important widgets should be prominent
- **White Space**: Don't overcrowd your dashboard
- **Responsive Design**: Test on different screen sizes
- **Color Consistency**: Use consistent color schemes

#### **Widget Organization**
- **Logical Grouping**: Group related widgets together
- **Information Flow**: Arrange widgets in logical order
- **Priority Placement**: Most important widgets at top
- **Size Balance**: Balance widget sizes appropriately
- **Visual Flow**: Guide user's eye through the dashboard

### **Data Management**

#### **Webhook Optimization**
- **Response Time**: Keep webhook responses under 2 seconds
- **Data Size**: Minimize unnecessary data in responses
- **Caching**: Use appropriate caching strategies
- **Error Handling**: Provide meaningful error messages
- **Rate Limiting**: Implement rate limiting if needed

#### **Performance Tips**
- **Refresh Intervals**: Set appropriate refresh rates
- **Widget Count**: Limit widgets per dashboard (recommend 10-15)
- **Data Complexity**: Keep data structures simple
- **Image Optimization**: Optimize any images used
- **CDN Usage**: Use CDN for static assets

### **Security Considerations**

#### **Webhook Security**
- **HTTPS Only**: Always use secure connections
- **Authentication**: Implement proper authentication
- **Input Validation**: Validate all incoming data
- **Rate Limiting**: Prevent abuse and overload
- **Monitoring**: Monitor webhook usage and errors

#### **User Access Control**
- **Principle of Least Privilege**: Give minimum necessary access
- **Regular Reviews**: Review user permissions regularly
- **Audit Logging**: Log important user actions
- **Data Isolation**: Ensure users can't access others' data
- **Secure Storage**: Encrypt sensitive information

---

## 🎯 **Getting Help**

### **Support Resources**

#### **Documentation**
- **This Guide**: Complete user documentation
- **Feature Documentation**: Detailed feature explanations
- **API Reference**: Technical API documentation
- **Code Examples**: Sample code and configurations
- **Video Tutorials**: Step-by-step video guides

#### **Community Support**
- **GitHub Issues**: Report bugs and request features
- **Community Forum**: Get help from other users
- **Discord Channel**: Real-time community support
- **Email Support**: Direct support for complex issues
- **Knowledge Base**: Searchable help articles

### **Reporting Issues**

#### **Before Reporting**
1. **Check Documentation**: Review relevant documentation
2. **Search Issues**: Look for similar existing issues
3. **Test Steps**: Reproduce the issue consistently
4. **Gather Information**: Collect error messages and logs
5. **Environment Details**: Note WordPress version, PHP version, etc.

#### **Issue Report Format**
```
**Issue Description**: Brief description of the problem

**Steps to Reproduce**:
1. Step 1
2. Step 2
3. Step 3

**Expected Behavior**: What should happen

**Actual Behavior**: What actually happens

**Environment**:
- WordPress Version: X.X.X
- PHP Version: X.X.X
- Plugin Version: X.X.X
- Browser: X.X.X

**Error Messages**: Any error messages or logs

**Additional Context**: Any other relevant information
```

---

## 🎉 **Congratulations!**

You've now mastered n8nDash Pro! You can:

✅ **Create Professional Dashboards** - Build beautiful, interactive dashboards  
✅ **Integrate with n8n** - Connect to your automation workflows  
✅ **Manage User Access** - Control who sees and modifies what  
✅ **Import/Export Data** - Safely backup and restore your work  
✅ **Customize Everything** - Tailor the plugin to your needs  
✅ **Troubleshoot Issues** - Solve problems independently  

### **Next Steps**
1. **Build Your First Dashboard** - Start with a simple KPI dashboard
2. **Explore Widget Types** - Try different widget configurations
3. **Set Up n8n Integration** - Connect your first webhook
4. **Customize Themes** - Make your dashboards look perfect
5. **Share with Team** - Invite others to use your dashboards

### **Stay Updated**
- **Plugin Updates**: Keep your plugin updated for new features
- **Documentation**: Check for new guides and tutorials
- **Community**: Join the n8nDash community for tips and tricks
- **Feedback**: Share your experience and suggestions

---

**Happy Dashboard Building! 🚀**

*If you need help, remember: the community is here for you, and comprehensive documentation is always available.*
