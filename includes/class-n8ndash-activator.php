<?php
/**
 * Fired during plugin activation
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.2.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/includes
 * @author     Amit Anand Niraj <queryamit@gmail.com>
 * @author     https://anandtech.in
 * @author     https://github.com/queryamit
 * @author     https://www.linkedin.com/in/queryamit/
 */
class N8nDash_Activator {

    /**
     * Plugin activation handler.
     *
     * Create database tables, set default options, and perform any other
     * initialization tasks required when the plugin is activated.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Create database tables
        self::create_tables();
        
        // Add default options
        self::add_default_options();
        
        // Add capabilities
        self::add_capabilities();
        
        // Create default pages
        self::create_default_pages();
        
        // Schedule cron events
        self::schedule_events();
        
        // Clear rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create plugin database tables.
     *
     * @since    1.0.0
     */
    private static function create_tables() {
        require_once N8NDASH_PLUGIN_DIR . 'database/class-n8ndash-db.php';
        N8nDash_DB::create_tables();
    }

    /**
     * Add default plugin options.
     *
     * @since    1.0.0
     */
    private static function add_default_options() {
        // General settings
        // Plugin settings - only keep working ones
        add_option( 'n8ndash_settings', array(
            'enable_public_dashboards' => false,
        ) );

        // Widget defaults
        add_option( 'n8ndash_widget_defaults', array(
            'data' => array(
                'refresh_interval' => 0, // Manual refresh only
                'show_last_updated' => true,
            ),
            'chart' => array(
                'animation_duration' => 750,
                'responsive' => true,
            ),
            'custom' => array(
                'timeout' => 30, // seconds
            ),
        ) );

        // Version tracking
        add_option( 'n8ndash_version', N8NDASH_VERSION );
        add_option( 'n8ndash_db_version', N8NDASH_DB_VERSION );
        
        // Installation timestamp
        add_option( 'n8ndash_installed', time() );
    }

    /**
     * Add plugin capabilities to roles.
     *
     * @since    1.0.0
     */
    private static function add_capabilities() {
        // Get roles
        $administrator = get_role( 'administrator' );
        $editor = get_role( 'editor' );
        $author = get_role( 'author' );
        $contributor = get_role( 'contributor' );
        $subscriber = get_role( 'subscriber' );
        
        // Administrator capabilities (Full Access - Keeping As-Is)
        if ( $administrator ) {
            $administrator->add_cap( 'n8ndash_view_dashboards' );
            $administrator->add_cap( 'n8ndash_create_dashboards' );
            $administrator->add_cap( 'n8ndash_edit_dashboards' );
            $administrator->add_cap( 'n8ndash_delete_dashboards' );
            $administrator->add_cap( 'n8ndash_manage_settings' );
            $administrator->add_cap( 'n8ndash_export_dashboards' );
            $administrator->add_cap( 'n8ndash_import_dashboards' );
            $administrator->add_cap( 'n8ndash_edit_others_dashboards' );
            $administrator->add_cap( 'n8ndash_delete_others_dashboards' );
        }
        
        // Editor capabilities (Dashboard Management - Keeping As-Is)
        if ( $editor ) {
            $editor->add_cap( 'n8ndash_view_dashboards' );
            $editor->add_cap( 'n8ndash_create_dashboards' );
            $editor->add_cap( 'n8ndash_edit_dashboards' );
            $editor->add_cap( 'n8ndash_delete_dashboards' );
            $editor->add_cap( 'n8ndash_export_dashboards' );
            $editor->add_cap( 'n8ndash_import_dashboards' );
        }
        
        // Author capabilities (Limited - Own Dashboards Only)
        if ( $author ) {
            $author->add_cap( 'n8ndash_view_dashboards' );
            $author->add_cap( 'n8ndash_create_dashboards' );
            $author->add_cap( 'n8ndash_edit_own_dashboards' );
            $author->add_cap( 'n8ndash_delete_own_dashboards' );
            $author->add_cap( 'n8ndash_export_dashboards' );
            $author->add_cap( 'n8ndash_import_dashboards' );
        }
        
        // Contributor capabilities (Limited - Own Dashboards Only)
        if ( $contributor ) {
            $contributor->add_cap( 'n8ndash_view_dashboards' );
            $contributor->add_cap( 'n8ndash_create_dashboards' );
            $contributor->add_cap( 'n8ndash_edit_own_dashboards' );
            $contributor->add_cap( 'n8ndash_delete_own_dashboards' );
            $contributor->add_cap( 'n8ndash_export_dashboards' );
            $contributor->add_cap( 'n8ndash_import_dashboards' );
        }
        
        // Subscriber capabilities (View Only)
        if ( $subscriber ) {
            $subscriber->add_cap( 'n8ndash_view_dashboards' );
            $subscriber->add_cap( 'n8ndash_view_public_dashboards' );
        }
    }

    /**
     * Create default pages for public dashboard display.
     *
     * @since    1.0.0
     */
    private static function create_default_pages() {
        // Check if dashboard page exists
        $dashboard_page = get_page_by_path( 'n8n-dashboards' );
        
        if ( ! $dashboard_page ) {
            // Create dashboard listing page
            $page_id = wp_insert_post( array(
                'post_title'    => __( 'Dashboards', 'n8ndash-pro' ),
                'post_name'     => 'n8n-dashboards',
                'post_content'  => '[n8ndash_dashboards]',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_author'   => get_current_user_id(),
                'comment_status' => 'closed',
                'ping_status'   => 'closed',
            ) );
            
            if ( $page_id ) {
                update_option( 'n8ndash_dashboard_page_id', $page_id );
            }
        }
    }

    /**
     * Schedule cron events.
     *
     * @since    1.0.0
     */
    private static function schedule_events() {
        // Cron events removed - no handlers implemented
        // Will be re-implemented when proper handlers are added
    }
}