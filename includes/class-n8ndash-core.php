<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/includes
 * @author     Amit Anand Niraj <n8ndash@gmail.com>
 */
class N8nDash_Core {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      N8nDash_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'N8NDASH_VERSION' ) ) {
            $this->version = N8NDASH_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'n8ndash-pro';

        $this->load_dependencies();
        $this->set_locale();
        
        // FIX: Only register admin hooks when in admin context to prevent activation output
        if ( is_admin() ) {
            $this->define_admin_hooks();
        }
        
        $this->define_public_hooks();
        $this->define_api_hooks(); // FIX: Always register REST API hooks
		if ( class_exists( 'N8nDash_Frontend' ) ) { new N8nDash_Frontend(); }
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - N8nDash_Loader. Orchestrates the hooks of the plugin.
     * - N8nDash_i18n. Defines internationalization functionality.
     * - N8nDash_Admin. Defines all hooks for the admin area.
     * - N8nDash_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once N8NDASH_PLUGIN_DIR . 'includes/class-n8ndash-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once N8NDASH_PLUGIN_DIR . 'includes/class-n8ndash-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once N8NDASH_PLUGIN_DIR . 'admin/class-n8ndash-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once N8NDASH_PLUGIN_DIR . 'public/class-n8ndash-public.php';

        // FIX: Ensure frontend routing glue is loaded
        require_once N8NDASH_PLUGIN_DIR . 'includes/class-n8ndash-frontend.php';

        /**
         * Database handler
         */
        require_once N8NDASH_PLUGIN_DIR . 'database/class-n8ndash-db.php';

        /**
         * Widget classes
         */
        require_once N8NDASH_PLUGIN_DIR . 'widgets/abstract-n8ndash-widget.php';
        require_once N8NDASH_PLUGIN_DIR . 'widgets/class-n8ndash-data-widget.php';
        require_once N8NDASH_PLUGIN_DIR . 'widgets/class-n8ndash-chart-widget.php';
        require_once N8NDASH_PLUGIN_DIR . 'widgets/class-n8ndash-custom-widget.php';

        /**
         * REST API controllers
         */
        require_once N8NDASH_PLUGIN_DIR . 'api/class-n8ndash-rest-controller.php';

        /**
         * REST API route registration fix
         */
        require_once N8NDASH_PLUGIN_DIR . 'includes/class-n8ndash-rest-fix.php';

        $this->loader = new N8nDash_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the N8nDash_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new N8nDash_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new N8nDash_Admin( $this->get_plugin_name(), $this->get_version() );

        // Admin scripts and styles
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        // Admin menu
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );

        // Settings
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

        // AJAX handlers
        $this->loader->add_action( 'wp_ajax_n8ndash_save_layout', $plugin_admin, 'ajax_save_layout' );
        $this->loader->add_action( 'wp_ajax_n8ndash_get_widgets', $plugin_admin, 'ajax_get_widgets' );
        $this->loader->add_action( 'wp_ajax_n8ndash_save_widget', $plugin_admin, 'ajax_save_widget' );
        $this->loader->add_action( 'wp_ajax_n8ndash_get_widget', $plugin_admin, 'ajax_get_widget' );
        $this->loader->add_action( 'wp_ajax_n8ndash_delete_widget', $plugin_admin, 'ajax_delete_widget' );
        $this->loader->add_action( 'wp_ajax_n8ndash_refresh_widget', $plugin_admin, 'ajax_refresh_widget' );
        $this->loader->add_action( 'wp_ajax_n8ndash_save_widget_position', $plugin_admin, 'ajax_save_widget_position' );
        $this->loader->add_action( 'wp_ajax_n8ndash_save_all_widget_positions', $plugin_admin, 'ajax_save_all_widget_positions' );
        $this->loader->add_action( 'wp_ajax_n8ndash_get_user_dashboards', $plugin_admin, 'ajax_get_user_dashboards' );

        // Fallback AJAX handlers
        $this->loader->add_action( 'wp_ajax_n8ndash_save_dashboard_fallback', $plugin_admin, 'ajax_save_dashboard_fallback' );

        // Settings AJAX
        $this->loader->add_action( 'wp_ajax_n8ndash_save_settings', $plugin_admin, 'ajax_save_settings' );
        $this->loader->add_action( 'wp_ajax_n8ndash_clear_cache', $plugin_admin, 'ajax_clear_cache' );
        $this->loader->add_action( 'wp_ajax_n8ndash_execute_uninstall', $plugin_admin, 'ajax_execute_uninstall' );
        $this->loader->add_action( 'wp_ajax_n8ndash_restore_all_data', $plugin_admin, 'ajax_restore_all_data' );
        $this->loader->add_action( 'wp_ajax_n8ndash_get_debug_logs', $plugin_admin, 'ajax_get_debug_logs' );

        // Add plugin action links
        $this->loader->add_filter( 'plugin_action_links_' . N8NDASH_PLUGIN_BASENAME, $plugin_admin, 'add_action_links' );

        // Add admin notices
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices' );
        
        // Import/Export functionality
        require_once N8NDASH_PLUGIN_DIR . 'includes/class-n8ndash-import-export.php';
        $import_export = new N8nDash_Import_Export( $this->get_plugin_name(), $this->get_version() );
        $import_export->register_ajax_handlers();
        
        // Dashboard delete AJAX
        $this->loader->add_action( 'wp_ajax_n8ndash_delete_dashboard', $plugin_admin, 'ajax_delete_dashboard' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new N8nDash_Public( $this->get_plugin_name(), $this->get_version() );

        // Public scripts and styles
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        // Shortcode functionality
        require_once N8NDASH_PLUGIN_DIR . 'includes/class-n8ndash-shortcode.php';
        $shortcode = new N8nDash_Shortcode( $this->get_plugin_name(), $this->get_version() );
        $shortcode->register_shortcodes();

        // FIX: Also register modern shortcodes provided by public class
        $this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );

        // FIX: Register Gutenberg blocks on init (only if not already registered)
        if ( ! has_action( 'init', array( $plugin_public, 'register_blocks' ) ) ) {
            $this->loader->add_action( 'init', $plugin_public, 'register_blocks' );
        }
        
        // Gutenberg block
        if ( file_exists( N8NDASH_PLUGIN_DIR . 'blocks/dashboard/index.php' ) ) {
            require_once N8NDASH_PLUGIN_DIR . 'blocks/dashboard/index.php';
        }

        // AJAX handlers for public-facing functionality
        $this->loader->add_action( 'wp_ajax_n8ndash_public_refresh_widget', $plugin_public, 'ajax_refresh_widget' );
        $this->loader->add_action( 'wp_ajax_nopriv_n8ndash_public_refresh_widget', $plugin_public, 'ajax_refresh_widget' );
        
        // Widget position saving from frontend
        $this->loader->add_action( 'wp_ajax_n8ndash_save_widget_position', $plugin_public, 'ajax_save_widget_position' );
        
        // Custom widget AJAX - REMOVED
        // Custom widgets now use direct webhook calls instead of WordPress AJAX
        // This eliminates the WordPress AJAX dependency and potential 400 errors
        
        // Dashboard preview handler
        $this->loader->add_action( 'init', $plugin_public, 'handle_dashboard_preview' );
    }

    /**
     * Register REST API endpoints
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_api_hooks() {
        $rest_controller = new N8nDash_REST_Controller();
        $this->loader->add_action( 'rest_api_init', $rest_controller, 'register_routes' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        // FIX: Ensure admin hooks are registered if we're in admin context
        if ( is_admin() && ! has_action( 'admin_menu' ) ) {
            $this->define_admin_hooks();
        }
        
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    N8nDash_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}