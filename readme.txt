=== n8nDash Pro ===
Contributors: queryamit
Tags: n8n, dashboard, automation, widgets, charts, webhooks, data visualization, workflow
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional dashboard for n8n automations with advanced widget management. Create beautiful, responsive dashboards to control and monitor your n8n workflows.

== Description ==

n8nDash Pro is a professional WordPress plugin that transforms your WordPress site into a powerful dashboard for monitoring and controlling n8n automation workflows. It provides a modern, responsive interface for creating custom dashboards with real-time data visualization and interactive widgets.

= Key Features =

* **Multiple Widget Types**: Data widgets (KPIs and lists), Chart widgets (line, bar, pie), and Custom form widgets
* **n8n Integration**: Webhook-based data fetching with support for GET/POST/PUT/DELETE methods
* **Modern UI/UX**: Drag-and-drop dashboard builder with responsive design
* **Security & Permissions**: Role-based access control with nonce verification and data sanitization
* **Advanced Features**: Real-time data updates, import/export dashboards, shortcode support, Gutenberg blocks, REST API
* **Multi-language Ready**: Full internationalization support
* **User-controlled Uninstall**: Safe data cleanup with user consent

= Perfect For =

* Business intelligence dashboards
* Process monitoring and control
* Team collaboration tools
* Real-time data visualization
* n8n workflow management
* Custom automation interfaces

= n8n Webhook Setup =

1. Create a Webhook node in your n8n workflow
2. Set HTTP Method to POST (recommended)
3. Copy the webhook URL to use in widget configuration
4. Return JSON response with your data

= User Roles & Capabilities =

* **Administrator**: Full access to all features
* **Editor**: Advanced dashboard management with import/export
* **Author**: Own dashboard management with import/export
* **Contributor**: Limited dashboard management with import/export
* **Subscriber**: View-only access to public dashboards

== Installation ==

1. Upload the `n8ndash-pro` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **n8nDash** in your WordPress admin menu
4. Start creating your first dashboard!

== Frequently Asked Questions ==

= How do I connect my n8n workflow? =

Create a webhook node in your n8n workflow and use the generated URL in your widget configuration. The plugin supports GET, POST, PUT, and DELETE methods.

= Can I use this with multiple users? =

Yes! The plugin includes comprehensive role-based access control. Each user can create their own dashboards, and administrators can manage all dashboards.

= Is my data secure? =

Absolutely. The plugin implements WordPress security best practices including nonce verification, data sanitization, SQL injection prevention, and XSS protection.

= Can I export my dashboards? =

Yes! The plugin includes a complete import/export system. You can export individual dashboards or all dashboards at once, and import them on other WordPress sites.

= Does it work on mobile devices? =

Yes! All dashboards are fully responsive and work perfectly on mobile devices, tablets, and desktops.

== Screenshots ==

1. Dashboard builder interface
2. Widget configuration panel
3. Real-time data visualization
4. Mobile responsive design
5. Import/export functionality

== Changelog ==

= 1.2.0 =
* Enhanced role-based access control
* Extended import/export access to all user roles
* Improved security with proper permission checks
* Better data portability for team collaboration

= 1.1.0 =
* Enhanced import/export with quick actions
* Improved uninstall system with user consent
* Streamlined interface and better user guidance
* Eliminated redundant functionality

= 1.0.0 =
* Initial release with complete n8nDash Pro functionality
* Core dashboard management and widget system
* n8n integration with webhook support
* Import/export system and security features

== Upgrade Notice ==

= 1.2.0 =
Enhanced role-based access control and improved import/export functionality for better team collaboration.

== Support ==

* Documentation: [GitHub Wiki](https://github.com/queryamit/n8ndash-pro/wiki)
* Issues: [GitHub Issues](https://github.com/queryamit/n8ndash-pro/issues)
* Email: queryamit@gmail.com
* Website: https://anandtech.in

== Credits ==

* Original n8nDash by Solomon Christ
* WordPress Plugin conversion by Amit Anand Niraj
* Charts powered by Chart.js
* Icons by Dashicons and Lucide

== Privacy Policy ==

This plugin does not collect, store, or transmit any personal data. All data remains on your WordPress installation and is not shared with third parties. The plugin only connects to your specified n8n webhook URLs for data retrieval.
