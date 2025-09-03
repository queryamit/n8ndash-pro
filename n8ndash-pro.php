<?php
/**
 * Plugin Name:       n8nDash Pro
 * Plugin URI:        https://github.com/queryamit/n8ndash-pro
 * Description:       Professional dashboard for n8n automations with advanced widget management. Create beautiful, responsive dashboards to control and monitor your n8n workflows.
 * Version:           1.2.0
 * Requires at least: 5.8
 * Requires PHP:      8.1
 * Author:            Amit Anand Niraj
 * Author URI:        https://anandtech.in
 * Author Email:      queryamit@gmail.com
 * Author GitHub:     https://github.com/queryamit
 * Author LinkedIn:   https://www.linkedin.com/in/queryamit/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       n8ndash-pro
 * Domain Path:       /languages
 *
 * @package N8nDash_Pro
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 * Start at version 1.2.0 and use SemVer - https://semver.org
 * Update this as you release new versions.
 */
define( 'N8NDASH_VERSION', '1.2.0' );

/**
 * Plugin directory path
 */
define( 'N8NDASH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL
 */
define( 'N8NDASH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename
 */
define( 'N8NDASH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Database version
 */
define( 'N8NDASH_DB_VERSION', '1.2.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-n8ndash-activator.php
 */
function n8ndash_activate() {
    require_once N8NDASH_PLUGIN_DIR . 'includes/class-n8ndash-activator.php';
    N8nDash_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-n8ndash-deactivator.php
 */
function n8ndash_deactivate() {
    require_once N8NDASH_PLUGIN_DIR . 'includes/class-n8ndash-deactivator.php';
    N8nDash_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'n8ndash_activate' );
register_deactivation_hook( __FILE__, 'n8ndash_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require N8NDASH_PLUGIN_DIR . 'includes/class-n8ndash-core.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function n8ndash_run() {
    $plugin = new N8nDash_Core();
    $plugin->run();
}

// FIX: Only run plugin when WordPress is ready, not during activation
add_action( 'plugins_loaded', 'n8ndash_run' );

if ( ! function_exists( 'n8ndash_flush_rewrite' ) ) {
    function n8ndash_flush_rewrite() {
        flush_rewrite_rules();
    }
}
register_activation_hook( __FILE__, 'n8ndash_flush_rewrite' );
register_deactivation_hook( __FILE__, 'n8ndash_flush_rewrite' );
