<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the admin area including
 * enqueuing the admin-specific stylesheet and JavaScript.
 *
 * @since      1.2.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/admin
 * @author     Amit Anand Niraj <queryamit@gmail.com>
 * @author     https://anandtech.in
 * @author     https://github.com/queryamit
 * @author     https://www.linkedin.com/in/queryamit/
 */
class N8nDash_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name    The name of this plugin.
     * @param    string    $version        The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Only load on our plugin pages
        if ( ! $this->is_plugin_page() ) {
            return;
        }

        // Bootstrap CSS
        wp_enqueue_style( 
            'n8ndash-bootstrap', 
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', 
            array(), 
            '5.3.3' 
        );

        // Plugin admin styles
        wp_enqueue_style( 
            $this->plugin_name, 
            N8NDASH_PLUGIN_URL . 'assets/css/admin/n8ndash-admin.css', 
            array( 'n8ndash-bootstrap' ), 
            $this->version, 
            'all' 
        );

        // Enhanced preview styles
        wp_enqueue_style( 
            'n8ndash-admin-widgets', 
            N8NDASH_PLUGIN_URL . 'assets/css/admin/n8ndash-admin-widgets.css', 
            array( $this->plugin_name ), 
            $this->version, 
            'all' 
        );



        // WordPress color picker
        wp_enqueue_style( 'wp-color-picker' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Only load on our plugin pages
        if ( ! $this->is_plugin_page() ) {
            return;
        }

        // Chart.js
        wp_enqueue_script( 
            'n8ndash-chartjs', 
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', 
            array(), 
            '4.4.1', 
            true 
        );

        // Interact.js for drag and drop
        wp_enqueue_script( 
            'n8ndash-interact', 
            'https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js', 
            array(), 
            '1.10.19', 
            true 
        );

        // Lucide icons
        wp_enqueue_script( 
            'n8ndash-lucide', 
            'https://unpkg.com/lucide@latest', 
            array(), 
            'latest', 
            true 
        );

        // Bootstrap JS
        wp_enqueue_script( 
            'n8ndash-bootstrap', 
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', 
            array(), 
            '5.3.3', 
            true 
        );


        // Also load public widget logic so admin can update widget bodies with data
        wp_enqueue_script( 
            'n8ndash-public-runtime',
            N8NDASH_PLUGIN_URL . 'assets/js/public/n8ndash-public.js',
            array( 'jquery', 'n8ndash-chartjs' ),
            $this->version,
            true
        );

        // Plugin admin scripts
        wp_enqueue_script( 
            $this->plugin_name, 
            N8NDASH_PLUGIN_URL . 'assets/js/admin/n8ndash-admin.js', 
            array( 'jquery', 'wp-color-picker', 'n8ndash-chartjs', 'n8ndash-interact' ), 
            $this->version, 
            true 
        );

        // Localize script
        wp_localize_script( $this->plugin_name, 'n8ndash_admin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'admin_url' => admin_url(),
            'site_url' => site_url(),
            'nonce' => wp_create_nonce( 'n8ndash_admin_nonce' ),
            'api_url' => rest_url( 'n8ndash/v1/' ),
            'api_nonce' => wp_create_nonce( 'wp_rest' ),
            'userRole' => $this->get_current_user_role(),
            'strings' => array(
                'confirm_delete' => __( 'Are you sure you want to delete this?', 'n8ndash-pro' ),
                'saving' => __( 'Saving...', 'n8ndash-pro' ),
                'saved' => __( 'Saved!', 'n8ndash-pro' ),
                'error' => __( 'An error occurred. Please try again.', 'n8ndash-pro' ),
                'loading' => __( 'Loading...', 'n8ndash-pro' ),
                'no_data' => __( 'No data available', 'n8ndash-pro' ),
            ),
            'themes' => array(
                'ocean' => '#0ea5e9',
                'emerald' => '#22c55e',
                'orchid' => '#a855f7',
                'citrus' => '#f59e0b',
            ),
        ) );
    }

    /**
     * Add admin menu items.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        // Add main menu - Use view capability that all roles have
        add_menu_page(
            __( 'n8nDash Pro', 'n8ndash-pro' ),
            __( 'n8nDash Pro', 'n8ndash-pro' ),
            'n8ndash_view_dashboards',  // All roles can view dashboards
            'n8ndash',
            array( $this, 'display_dashboards_page' ),
            'dashicons-dashboard',
            30
        );

        // Add submenu pages with appropriate capabilities
        add_submenu_page(
            'n8ndash',
            __( 'Dashboards', 'n8ndash-pro' ),
            __( 'Dashboards', 'n8ndash-pro' ),
            'n8ndash_view_dashboards',  // All roles can view dashboards
            'n8ndash',
            array( $this, 'display_dashboards_page' )
        );

        add_submenu_page(
            'n8ndash',
            __( 'Add New Dashboard', 'n8ndash-pro' ),
            __( 'Add New', 'n8ndash-pro' ),
            'n8ndash_create_dashboards',  // Admin, Editor, Author, Contributor can create
            'n8ndash-new',
            array( $this, 'display_new_dashboard_page' )
        );

        add_submenu_page(
            'n8ndash',
            __( 'Edit Dashboard', 'n8ndash-pro' ),
            __( 'Edit Dashboard', 'n8ndash-pro' ),
            'n8ndash_view_dashboards',  // All roles can access edit page (permissions checked inside)
            'n8ndash-edit',
            array( $this, 'display_edit_dashboard_page' )
        );

        // Add preview page (hidden from menu) - Use view capability
        add_submenu_page(
            null, // No parent menu
            __( 'Preview Dashboard', 'n8ndash-pro' ),
            __( 'Preview Dashboard', 'n8ndash-pro' ),
            'n8ndash_view_dashboards',  // All roles can view dashboards
            'n8ndash-preview',
            array( $this, 'preview_dashboard_page' )
        );

        // Import/Export page - Available for users with export capability
        add_submenu_page(
            'n8ndash',
            __( 'Import/Export', 'n8ndash-pro' ),
            __( 'Import/Export', 'n8ndash-pro' ),
            'n8ndash_export_dashboards',  // All roles with export capability can access
            'n8ndash-import-export',
            array( $this, 'display_import_export_page' )
        );
        
        // Settings page - Only for users with manage settings capability
        add_submenu_page(
            'n8ndash',
            __( 'Settings', 'n8ndash-pro' ),
            __( 'Settings', 'n8ndash-pro' ),
            'n8ndash_manage_settings',  // Only administrators have this capability
            'n8ndash-settings',
            array( $this, 'display_settings_page' )
        );
    }

    /**
     * Register plugin settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // General settings - only keep working ones
        register_setting( 'n8ndash_settings_group', 'n8ndash_settings', array(
            'type' => 'array',
            'sanitize_callback' => array( $this, 'sanitize_settings' ),
            'default' => array(
                'enable_public_dashboards' => false,
            ),
        ) );

        // Widget defaults
        register_setting( 'n8ndash_widget_defaults_group', 'n8ndash_widget_defaults', array(
            'type' => 'array',
            'sanitize_callback' => array( $this, 'sanitize_widget_defaults' ),
        ) );
    }

    /**
     * Display dashboards listing page.
     *
     * @since    1.0.0
     */
    public function display_dashboards_page() {
        require_once N8NDASH_PLUGIN_DIR . 'admin/partials/n8ndash-admin-dashboards.php';
    }

    /**
     * Display new dashboard page.
     *
     * @since    1.0.0
     */
    public function display_new_dashboard_page() {
        // Check permissions - Allow both full edit and limited own edit capabilities
        if ( ! current_user_can( 'n8ndash_edit_dashboards' ) && ! current_user_can( 'n8ndash_edit_own_dashboards' ) ) {
            wp_die( __( 'Sorry, you are not allowed to access this page.', 'n8ndash-pro' ) );
        }
        
        // Use the edit dashboard page for new dashboards
        $_GET['dashboard_id'] = 0; // Set to 0 to indicate new dashboard
        require_once N8NDASH_PLUGIN_DIR . 'admin/partials/n8ndash-admin-edit-dashboard.php';
    }

    /**
     * Display edit dashboard page.
     *
     * @since    1.0.0
     */
    public function display_edit_dashboard_page() {
        // Check permissions - Allow both full edit and limited own edit capabilities
        if ( ! current_user_can( 'n8ndash_edit_dashboards' ) && ! current_user_can( 'n8ndash_edit_own_dashboards' ) ) {
            wp_die( __( 'Sorry, you are not allowed to access this page.', 'n8ndash-pro' ) );
        }
        
        // If user has limited capabilities, check dashboard ownership
        if ( current_user_can( 'n8ndash_edit_own_dashboards' ) && ! current_user_can( 'n8ndash_edit_dashboards' ) ) {
            $dashboard_id = isset( $_GET['dashboard_id'] ) ? intval( $_GET['dashboard_id'] ) : 0;
            if ( $dashboard_id > 0 ) {
                // Check if this is the user's own dashboard
                if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
                    wp_die( __( 'Sorry, you can only edit your own dashboards.', 'n8ndash-pro' ) );
                }
            }
        }
        
        require_once N8NDASH_PLUGIN_DIR . 'admin/partials/n8ndash-admin-edit-dashboard.php';
    }

    /**
     * Display widgets library page.
     *
     * @since    1.0.0
     */
    public function display_widgets_page() {
        require_once N8NDASH_PLUGIN_DIR . 'admin/partials/n8ndash-admin-widgets.php';
    }

    /**
     * Display import/export page.
     *
     * @since    1.0.0
     */
    public function display_import_export_page() {
        require_once N8NDASH_PLUGIN_DIR . 'admin/partials/n8ndash-admin-import-export.php';
    }

    /**
     * Display settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        require_once N8NDASH_PLUGIN_DIR . 'admin/partials/n8ndash-admin-settings.php';
    }


    /**
     * Display debug page.
     *
     * @since    1.0.0
     */
    public function display_debug_page() {
        echo '<div class="wrap">';
        echo '<h1>N8nDash Debug Tools</h1>';
        
        echo '<div style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4;">';
        echo '<h2>Widget Save Debugging</h2>';
        
        // Check database tables
        if (class_exists('N8nDash_DB')) {
            $tables = N8nDash_DB::get_table_names();
            global $wpdb;
            
            echo '<h3>Database Tables</h3>';
            foreach ($tables as $name => $table) {
                $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
                echo '<p>' . ($exists ? '✅' : '❌') . ' ' . ucfirst($name) . ': ' . $table . '</p>';
            }
            
            // Check widget classes
            echo '<h3>Widget Classes</h3>';
            $widget_classes = ['N8nDash_Chart_Widget', 'N8nDash_Data_Widget', 'N8nDash_Custom_Widget'];
            foreach ($widget_classes as $class) {
                $exists = class_exists($class);
                echo '<p>' . ($exists ? '✅' : '❌') . ' ' . $class . '</p>';
            }
            
            // Check AJAX handlers
            echo '<h3>AJAX Handlers</h3>';
            global $wp_filter;
            $ajax_actions = [
                'wp_ajax_n8ndash_save_widget',
                'wp_ajax_n8ndash_save_dashboard_fallback'
            ];
            
            foreach ($ajax_actions as $action) {
                $registered = isset($wp_filter[$action]) && !empty($wp_filter[$action]->callbacks);
                echo '<p>' . ($registered ? '✅' : '❌') . ' ' . $action . '</p>';
            }
        }
        
        echo '<h3>Common Issues & Solutions</h3>';
        echo '<ul>';
        echo '<li><strong>Widget save fails:</strong> Check browser console for JavaScript errors</li>';
        echo '<li><strong>REST API 404:</strong> Plugin will fallback to AJAX automatically</li>';
        echo '<li><strong>Permission denied:</strong> Ensure user has proper capabilities</li>';
        echo '<li><strong>Database errors:</strong> Check if tables exist and are accessible</li>';
        echo '</ul>';
        
        echo '<h3>Debug Steps</h3>';
        echo '<ol>';
        echo '<li>Open browser Developer Tools (F12)</li>';
        echo '<li>Go to Console tab</li>';
        echo '<li>Try adding a widget</li>';
        echo '<li>Look for error messages in console</li>';
        echo '<li>Check Network tab for failed requests</li>';
        echo '</ol>';
        
        echo '</div>';
        echo '<p><a href="' . admin_url('admin.php?page=n8ndash') . '" class="button button-secondary">← Back to N8nDash</a></p>';
        echo '</div>';
    }


    /**
     * Add custom capabilities to roles.
     *
     * @since    1.0.0
     */
    public function add_capabilities() {
        // This is handled in the activator, but we can add dynamic capability checks here
    }

    /**
     * Add plugin action links.
     *
     * @since    1.0.0
     * @param    array    $links    Existing links
     * @return   array              Modified links
     */
    public function add_action_links( $links ) {
        $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=n8ndash-settings' ) . '">' . __( 'Settings', 'n8ndash-pro' ) . '</a>',
            '<a href="' . admin_url( 'admin.php?page=n8ndash' ) . '">' . __( 'Dashboards', 'n8ndash-pro' ) . '</a>',
        );
        
        return array_merge( $plugin_links, $links );
    }

    /**
     * Display admin notices.
     *
     * @since    1.0.0
     */
    public function admin_notices() {
        // Check if we need to show any notices
        if ( ! $this->is_plugin_page() ) {
            return;
        }

        // Check for success messages
        if ( isset( $_GET['n8ndash_message'] ) ) {
            $message = '';
            switch ( $_GET['n8ndash_message'] ) {
                case 'dashboard_created':
                    $message = __( 'Dashboard created successfully!', 'n8ndash-pro' );
                    break;
                case 'dashboard_updated':
                    $message = __( 'Dashboard updated successfully!', 'n8ndash-pro' );
                    break;
                case 'dashboard_deleted':
                    $message = __( 'Dashboard deleted successfully!', 'n8ndash-pro' );
                    break;
                case 'settings_saved':
                    $message = __( 'Settings saved successfully!', 'n8ndash-pro' );
                    break;
            }
            
            if ( $message ) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
            }
        }

        // Check for error messages
        if ( isset( $_GET['n8ndash_error'] ) ) {
            $error = '';
            switch ( $_GET['n8ndash_error'] ) {
                case 'permission_denied':
                    $error = __( 'You do not have permission to perform this action.', 'n8ndash-pro' );
                    break;
                case 'invalid_dashboard':
                    $error = __( 'Invalid dashboard ID.', 'n8ndash-pro' );
                    break;
                case 'save_failed':
                    $error = __( 'Failed to save. Please try again.', 'n8ndash-pro' );
                    break;
            }
            
            if ( $error ) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $error ) . '</p></div>';
            }
        }
    }

    /**
     * AJAX handler for saving layout.
     *
     * @since    1.0.0
     */
    public function ajax_save_layout() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_die( __( 'Security check failed', 'n8ndash-pro' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'n8ndash_edit_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        $dashboard_id = isset( $_POST['dashboard_id'] ) ? intval( $_POST['dashboard_id'] ) : 0;
        $layout = isset( $_POST['layout'] ) ? json_decode( stripslashes( $_POST['layout'] ), true ) : array();

        if ( ! $dashboard_id || ! is_array( $layout ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid data', 'n8ndash-pro' ) ) );
        }

        // Check dashboard ownership
        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Update dashboard layout
        $result = N8nDash_DB::save_dashboard( array(
            'id' => $dashboard_id,
            'layout' => $layout,
        ) );

        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Layout saved', 'n8ndash-pro' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Save failed', 'n8ndash-pro' ) ) );
        }
    }

    /**
     * AJAX handler for getting widgets.
     *
     * @since    1.0.0
     */
    public function ajax_get_widgets() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_die( __( 'Security check failed', 'n8ndash-pro' ) );
        }

        $dashboard_id = isset( $_POST['dashboard_id'] ) ? intval( $_POST['dashboard_id'] ) : 0;

        if ( ! $dashboard_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid dashboard ID', 'n8ndash-pro' ) ) );
        }

        // Check permissions
        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'view' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        $widgets = N8nDash_DB::get_dashboard_widgets( $dashboard_id );
        
        // Render widgets HTML
        $widgets_html = array();
        foreach ( $widgets as $widget_data ) {
            $widget = $this->create_widget_instance( $widget_data );
            if ( $widget ) {
                $widgets_html[] = array(
                    'id' => $widget_data->id,
                    'html' => $widget->render(),
                    'config' => $widget_data->config,
                    'position' => $widget_data->position,
                );
            }
        }

        wp_send_json_success( array( 'widgets' => $widgets_html ) );
    }

    /**
     * AJAX handler for saving widget.
     *
     * @since    1.0.0
     */
    public function ajax_save_widget() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_die( __( 'Security check failed', 'n8ndash-pro' ) );
        }

        // Check permissions - Allow both full edit and limited own edit capabilities
        if ( ! current_user_can( 'n8ndash_edit_dashboards' ) && ! current_user_can( 'n8ndash_edit_own_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

                    // AJAX save_widget called

        // Handle both direct POST and nested widget_data
        $widget_data = isset( $_POST['widget_data'] ) ? $_POST['widget_data'] : $_POST;
        
                    // Widget data extracted
        
        $widget_id = isset( $_POST['widget_id'] ) ? intval( $_POST['widget_id'] ) : 0;
        $dashboard_id = isset( $widget_data['dashboard_id'] ) ? intval( $widget_data['dashboard_id'] ) : 0;
        $widget_type = isset( $widget_data['widget_type'] ) ? sanitize_text_field( $widget_data['widget_type'] ) : '';
        $title = isset( $widget_data['title'] ) ? sanitize_text_field( $widget_data['title'] ) : '';
        $config = isset( $widget_data['config'] ) ? $widget_data['config'] : array();
        $webhook = isset( $widget_data['webhook'] ) ? $widget_data['webhook'] : array();

        if ( ! $dashboard_id || ! $widget_type ) {
            wp_send_json_error( array( 'message' => __( 'Invalid data', 'n8ndash-pro' ) ) );
        }

        // Check dashboard ownership
        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Validate widget type by creating instance
        $widget_instance = $this->create_widget_instance( array(
            'widget_type' => $widget_type,
            'config' => $config,
            'webhook' => $webhook
        ) );
        if ( ! $widget_instance ) {
            // Failed to create widget instance
            wp_send_json_error( array( 'message' => __( 'Invalid widget type', 'n8ndash-pro' ) ) );
        }
        
                    // Widget instance created successfully

        // Prepare widget data for saving
        $save_data = array(
            'dashboard_id' => $dashboard_id,
            'widget_type' => $widget_type,
            'title' => $title,
            'config' => $config,
            'webhook' => $webhook,
        );
        
        if ( $widget_id ) {
            $save_data['id'] = $widget_id;
        }
        
                    // Saving widget data
        
        // Save widget directly to database
        $result = N8nDash_DB::save_widget( $save_data );

        if ( $result ) {
            // Widget saved successfully
            wp_send_json_success( array(
                'message' => __( 'Widget saved', 'n8ndash-pro' ),
                'widget_id' => $result,
            ) );
        } else {
            // Failed to save widget to database
            wp_send_json_error( array( 'message' => __( 'Save failed', 'n8ndash-pro' ) ) );
        }
    }

    /**
     * AJAX handler for deleting widget.
     *
     * @since    1.0.0
     */
    public function ajax_delete_widget() {
        // AJAX delete_widget called
        
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            // Nonce check failed
            wp_die( __( 'Security check failed', 'n8ndash-pro' ) );
        }
                    // Nonce check passed

        // Check permissions - Allow both full edit and limited own edit capabilities
        if ( ! current_user_can( 'n8ndash_edit_dashboards' ) && ! current_user_can( 'n8ndash_edit_own_dashboards' ) ) {
            // Permission check failed
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }
                    // Permission check passed

        $widget_id = isset( $_POST['widget_id'] ) ? intval( $_POST['widget_id'] ) : 0;
        // Widget ID extracted

        if ( ! $widget_id ) {
            // Invalid widget ID
            wp_send_json_error( array( 'message' => __( 'Invalid widget ID', 'n8ndash-pro' ) ) );
        }

        // Load widget to check dashboard ownership
        global $wpdb;
        $tables = N8nDash_DB::get_table_names();
                    // Tables found
        
        $dashboard_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT dashboard_id FROM {$tables['widgets']} WHERE id = %d",
            $widget_id
        ) );
                    // Dashboard ID for widget found
        
        if ( $wpdb->last_error ) {
            // SQL error occurred
        }

        if ( ! $dashboard_id || ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
            // Dashboard access denied
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }
                    // Dashboard access granted

        // Attempting to delete widget
        $result = N8nDash_DB::delete_widget( $widget_id );
        // Delete result logged

        if ( $result ) {
            // Widget deleted successfully
            wp_send_json_success( array( 'message' => __( 'Widget deleted', 'n8ndash-pro' ) ) );
        } else {
            // Widget deletion failed
            if ( $wpdb->last_error ) {
                // SQL error occurred
            }
            wp_send_json_error( array( 'message' => __( 'Delete failed', 'n8ndash-pro' ) ) );
        }
    }

    /**
     * AJAX handler for getting widget data.
     *
     * @since    1.0.0
     */
    public function ajax_get_widget() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_die( __( 'Security check failed', 'n8ndash-pro' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'n8ndash_edit_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        $widget_id = isset( $_POST['widget_id'] ) ? intval( $_POST['widget_id'] ) : 0;

        if ( ! $widget_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid widget ID', 'n8ndash-pro' ) ) );
        }

        // Load widget data
        global $wpdb;
        $tables = N8nDash_DB::get_table_names();
        $widget_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT w.*, wh.url as webhook_url, wh.method as webhook_method, wh.headers as webhook_headers, wh.body as webhook_body
             FROM {$tables['widgets']} w
             LEFT JOIN {$tables['webhooks']} wh ON w.id = wh.widget_id
             WHERE w.id = %d AND w.status = 'active'",
            $widget_id
        ) );

        if ( ! $widget_data ) {
            wp_send_json_error( array( 'message' => __( 'Widget not found', 'n8ndash-pro' ) ) );
        }

        // Check dashboard ownership
        if ( ! N8nDash_DB::user_can_access_dashboard( $widget_data->dashboard_id, 'edit' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Decode JSON fields
        $widget_data->config = json_decode( $widget_data->config, true ) ?: array();
        $widget_data->position = json_decode( $widget_data->position, true ) ?: array();
        $widget_data->webhook_headers = json_decode( $widget_data->webhook_headers, true ) ?: array();
        $widget_data->webhook_body = json_decode( $widget_data->webhook_body, true ) ?: array();
        
        // Set refresh interval from config
        $widget_data->refresh_interval = isset( $widget_data->config['refresh_interval'] ) ? $widget_data->config['refresh_interval'] : 300;

        wp_send_json_success( array(
            'message' => __( 'Widget data loaded', 'n8ndash-pro' ),
            'data' => $widget_data
        ) );
    }

    /**
     * AJAX handler for refreshing widget data.
     *
     * @since    1.0.0
     */
    public function ajax_refresh_widget() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_die( __( 'Security check failed', 'n8ndash-pro' ) );
        }

        $widget_id = isset( $_POST['widget_id'] ) ? intval( $_POST['widget_id'] ) : 0;

        if ( ! $widget_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid widget ID', 'n8ndash-pro' ) ) );
        }

        // Load widget
        global $wpdb;
        $tables = N8nDash_DB::get_table_names();
        $widget_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT w.*, wh.url as webhook_url, wh.method as webhook_method, wh.headers as webhook_headers, wh.body as webhook_body 
             FROM {$tables['widgets']} w
             LEFT JOIN {$tables['webhooks']} wh ON w.id = wh.widget_id
             WHERE w.id = %d",
            $widget_id
        ) );

        if ( ! $widget_data ) {
            wp_send_json_error( array( 'message' => __( 'Widget not found', 'n8ndash-pro' ) ) );
        }

        // Check permissions
        if ( ! N8nDash_DB::user_can_access_dashboard( $widget_data->dashboard_id, 'view' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Create widget instance
        $widget_data->config = json_decode( $widget_data->config, true );
        $widget_data->position = json_decode( $widget_data->position, true );
        $widget_data->webhook_headers = json_decode( $widget_data->webhook_headers, true );
        $widget_data->webhook_body = json_decode( $widget_data->webhook_body, true );
        
        if ( ! empty( $widget_data->webhook_url ) ) {
            $widget_data->config['webhook'] = array(
                'url' => $widget_data->webhook_url,
                'method' => $widget_data->webhook_method,
                'headers' => $widget_data->webhook_headers,
                'body' => $widget_data->webhook_body,
            );
        }

        $widget = $this->create_widget_instance( $widget_data );
        if ( ! $widget ) {
            wp_send_json_error( array( 'message' => __( 'Invalid widget type', 'n8ndash-pro' ) ) );
        }

        // Call webhook
        $response = $widget->call_webhook();
        
        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 
                'message' => $response->get_error_message(),
                'code' => $response->get_error_code(),
            ) );
        }

        wp_send_json_success( array( 
            'message' => __( 'Widget refreshed', 'n8ndash-pro' ),
            'data' => $response,
        ) );
    }

    /**
     * Create widget instance from data.
     *
     * @since    1.0.0
     * @param    mixed    $widget_data    Widget data (object or string widget type)
     * @return   N8nDash_Widget|null       Widget instance or null
     */
    private function create_widget_instance( $widget_data ) {
        $widget_types = apply_filters( 'n8ndash_widget_types', array(
            'data' => 'N8nDash_Data_Widget',
            'chart' => 'N8nDash_Chart_Widget',
            'custom' => 'N8nDash_Custom_Widget',
        ) );

        // Handle string widget type (for new widgets)
        if ( is_string( $widget_data ) ) {
            if ( ! isset( $widget_types[ $widget_data ] ) ) {
                // Invalid widget type string
                return null;
            }
            
            $class_name = $widget_types[ $widget_data ];
            if ( ! class_exists( $class_name ) ) {
                // Widget class not found
                return null;
            }
            
            // Create empty widget instance
            return new $class_name( array() );
        }
        
        // Handle object/array widget data
        $widget_type = '';
        if ( is_object( $widget_data ) && isset( $widget_data->widget_type ) ) {
            $widget_type = $widget_data->widget_type;
        } elseif ( is_array( $widget_data ) && isset( $widget_data['widget_type'] ) ) {
            $widget_type = $widget_data['widget_type'];
        }
        
        if ( ! $widget_type || ! isset( $widget_types[ $widget_type ] ) ) {
            // Invalid widget type
            return null;
        }

        $class_name = $widget_types[ $widget_type ];
                    if ( ! class_exists( $class_name ) ) {
                // Widget class not found
                return null;
            }

        return new $class_name( (array) $widget_data );
    }

    /**
     * Check if current page is plugin page.
     *
     * @since    1.0.0
     * @return   bool    Is plugin page
     */
    private function is_plugin_page() {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }

        return strpos( $screen->id ?? '', 'n8ndash' ) !== false || strpos( $screen->id ?? '', 'n8n-dash' ) !== false;
    }

    /**
     * Sanitize settings.
     *
     * @since    1.0.0
     * @param    array    $input    Raw settings
     * @return   array              Sanitized settings
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        // Only keep the working setting
        $sanitized['enable_public_dashboards'] = ! empty( $input['enable_public_dashboards'] );

        return $sanitized;
    }

    /**
     * Sanitize widget defaults.
     *
     * @since    1.0.0
     * @param    array    $input    Raw defaults
     * @return   array              Sanitized defaults
     */
    public function sanitize_widget_defaults( $input ) {
        $sanitized = array();

        if ( isset( $input['data'] ) ) {
            $sanitized['data'] = array(
                'refresh_interval' => intval( $input['data']['refresh_interval'] ?? 0 ),
                'show_last_updated' => ! empty( $input['data']['show_last_updated'] ),
            );
        }

        if ( isset( $input['chart'] ) ) {
            $sanitized['chart'] = array(
                'animation_duration' => intval( $input['chart']['animation_duration'] ?? 750 ),
                'responsive' => ! empty( $input['chart']['responsive'] ),
            );
        }

        if ( isset( $input['custom'] ) ) {
            $sanitized['custom'] = array(
                'timeout' => intval( $input['custom']['timeout'] ?? 30 ),
            );
        }

        return $sanitized;
    }

    /**
     * AJAX handler for fallback dashboard save.
     *
     * @since    1.0.0
     */
    public function ajax_save_dashboard_fallback() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'n8ndash_create_dashboards' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $dashboard_data = isset( $_POST['dashboard_data'] ) ? $_POST['dashboard_data'] : array();
        $is_new = isset( $_POST['is_new'] ) && $_POST['is_new'] === 'true';
        $dashboard_id = isset( $_POST['dashboard_id'] ) ? intval( $_POST['dashboard_id'] ) : 0;

        if ( ! $is_new && $dashboard_id ) {
            $dashboard_data['id'] = $dashboard_id;
            
            // Check edit permission
            if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
                wp_send_json_error( 'Permission denied' );
            }
        }

        // Save dashboard
        $result = N8nDash_DB::save_dashboard( $dashboard_data );

        if ( $result ) {
            wp_send_json_success( array( 'dashboard_id' => $result ) );
        } else {
            wp_send_json_error( 'Failed to save dashboard' );
        }
    }

    /**
     * AJAX handler for saving settings.
     *
     * @since    1.0.0
     */
    public function ajax_save_settings() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_save_settings', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Parse the serialized settings
        parse_str( $_POST['settings'], $form_data );
        
        if ( isset( $form_data['n8ndash_settings'] ) ) {
            $settings = $form_data['n8ndash_settings'];
            
            // Sanitize settings
            $sanitized = $this->sanitize_settings( $settings );
            
            // Save settings
            update_option( 'n8ndash_settings', $sanitized );
            
            wp_send_json_success( array( 'message' => __( 'Settings saved successfully', 'n8ndash-pro' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Invalid settings data', 'n8ndash-pro' ) ) );
        }
    }

    /**
     * AJAX handler for clearing cache.
     *
     * @since    1.0.0
     */
    public function ajax_clear_cache() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Clear transients
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_n8ndash_%'" );
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_n8ndash_%'" );

        wp_send_json_success( array( 'message' => __( 'Cache cleared successfully', 'n8ndash-pro' ) ) );
    }
    
    /**
     * AJAX handler for executing uninstall
     *
     * @since    1.0.0
     */
    public function ajax_execute_uninstall() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_uninstall', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }
        
        // Check user capabilities
        if ( ! current_user_can( 'n8ndash_manage_settings' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'n8ndash-pro' ) ) );
        }
        
        $mode = sanitize_text_field( $_POST['mode'] );
        
        switch ( $mode ) {
            case 'keep':
                wp_send_json_success( array( 'message' => __( 'No action needed. Data will be kept when plugin is deactivated.', 'n8ndash-pro' ) ) );
                break;
                
            case 'clean':
                $this->clean_plugin_data();
                wp_send_json_success( array( 'message' => __( 'Plugin data cleaned successfully. Dashboards and widgets removed, but settings and capabilities kept.', 'n8ndash-pro' ) ) );
                break;
                
            case 'remove':
                $this->remove_all_plugin_data();
                wp_send_json_success( array( 'message' => __( 'All plugin data removed successfully. Plugin can now be safely deleted.', 'n8ndash-pro' ) ) );
                break;
                
            default:
                wp_send_json_error( array( 'message' => __( 'Invalid uninstall mode.', 'n8ndash-pro' ) ) );
        }
    }
    
    /**
     * AJAX handler for restoring all data
     *
     * @since    1.0.0
     */
    public function ajax_restore_all_data() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }
        
        // Check user capabilities
        if ( ! current_user_can( 'n8ndash_manage_settings' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'n8ndash-pro' ) ) );
        }
        
        $data = sanitize_textarea_field( $_POST['data'] );
        $decoded_data = json_decode( $data, true );
        
        if ( ! $decoded_data || ! is_array( $decoded_data ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid data format. Please use a valid export file.', 'n8ndash-pro' ) ) );
        }
        
        try {
            // Import the data using the existing import/export class
            require_once N8NDASH_PLUGIN_PATH . 'includes/class-n8ndash-import-export.php';
            $import_export = new N8nDash_Import_Export();
            
            $result = $import_export->import_all_data( $decoded_data );
            
            if ( $result ) {
                wp_send_json_success( array( 'message' => __( 'All data restored successfully!', 'n8ndash-pro' ) ) );
            } else {
                wp_send_json_error( array( 'message' => __( 'Failed to restore data. Please check the file format.', 'n8ndash-pro' ) ) );
            }
            
        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => sprintf( __( 'Error during restore: %s', 'n8ndash-pro' ), $e->getMessage() ) ) );
        }
    }
    
    /**
     * Clean plugin data (remove dashboards and widgets only)
     *
     * @since    1.0.0
     */
    private function clean_plugin_data() {
        global $wpdb;
        
        // Get table names
        $tables = N8nDash_DB::get_table_names();
        
        // Remove dashboards and widgets
        $wpdb->query( "DELETE FROM {$tables['dashboards']}" );
        $wpdb->query( "DELETE FROM {$tables['widgets']}" );
        
        // Clear transients
        $wpdb->query( 
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_n8ndash_%' 
             OR option_name LIKE '_transient_timeout_n8ndash_%'"
        );
    }
    
    /**
     * Remove all plugin data completely
     *
     * @since    1.0.0
     */
    private function remove_all_plugin_data() {
        global $wpdb;
        
        // Get table names
        $tables = N8nDash_DB::get_table_names();
        
        // Remove all tables
        foreach ( $tables as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS $table" );
        }
        
        // Remove all options
        $options = array(
            'n8ndash_settings',
            'n8ndash_widget_defaults',
            'n8ndash_version',
            'n8ndash_db_version',
            'n8ndash_installed',
            'n8ndash_dashboard_page_id',
        );
        
        foreach ( $options as $option ) {
            delete_option( $option );
        }
        
        // Remove all transients
        $wpdb->query( 
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_n8ndash_%' 
             OR option_name LIKE '_transient_timeout_n8ndash_%'"
        );
        
        // Remove capabilities from all roles
        $capabilities = array(
            'n8ndash_view_dashboards',
            'n8ndash_create_dashboards',
            'n8ndash_edit_dashboards',
            'n8ndash_delete_dashboards',
            'n8ndash_manage_settings',
            'n8ndash_export_dashboards',
            'n8ndash_import_dashboards',
            'n8ndash_edit_others_dashboards',
            'n8ndash_delete_others_dashboards',
            'n8ndash_edit_own_dashboards',
            'n8ndash_delete_own_dashboards',
            'n8ndash_view_public_dashboards',
        );
        
        $roles = wp_roles();
        
        foreach ( $roles->roles as $role_name => $role_info ) {
            $role = get_role( $role_name );
            if ( $role ) {
                foreach ( $capabilities as $cap ) {
                    $role->remove_cap( $cap );
                }
            }
        }
        
        // Delete the dashboard page if it exists
        $page_id = get_option( 'n8ndash_dashboard_page_id' );
        if ( $page_id ) {
            wp_delete_post( $page_id, true );
        }
        
        // Clear any cached data
        wp_cache_flush();
    }

    /**
     * AJAX handler for getting debug logs.
     *
     * @since    1.0.0
     */
    public function ajax_get_debug_logs() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        $log_file = WP_CONTENT_DIR . '/debug.log';
        $logs = '';

        if ( file_exists( $log_file ) ) {
            $logs = file_get_contents( $log_file );
            // Get only n8ndash related logs
            $lines = explode( "\n", $logs );
            $filtered_lines = array();
            
            foreach ( $lines as $line ) {
                if ( stripos( $line, 'n8ndash' ) !== false ) {
                    $filtered_lines[] = $line;
                }
            }
            
            $logs = implode( "\n", array_slice( $filtered_lines, -100 ) ); // Last 100 n8ndash entries
        }

        wp_send_json_success( array( 'logs' => $logs ?: __( 'No debug logs found.', 'n8ndash-pro' ) ) );
    }

    /**
     * AJAX handler for saving widget position.
     *
     * @since    1.0.0
     */
    public function ajax_save_widget_position() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'n8ndash_edit_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        $widget_id = isset( $_POST['widget_id'] ) ? intval( $_POST['widget_id'] ) : 0;
        $x = isset( $_POST['x'] ) ? intval( $_POST['x'] ) : 0;
        $y = isset( $_POST['y'] ) ? intval( $_POST['y'] ) : 0;
        $width = isset( $_POST['width'] ) ? intval( $_POST['width'] ) : 300;
        $height = isset( $_POST['height'] ) ? intval( $_POST['height'] ) : 200;

        if ( ! $widget_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid widget ID', 'n8ndash-pro' ) ) );
        }

        // Load widget to check dashboard ownership
        global $wpdb;
        $tables = N8nDash_DB::get_table_names();
        
        $dashboard_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT dashboard_id FROM {$tables['widgets']} WHERE id = %d",
            $widget_id
        ) );

        if ( ! $dashboard_id || ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Update widget position
        $position_data = array(
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height
        );

        $position_json = json_encode( $position_data );
        $result = N8nDash_DB::update_widget_position( $widget_id, $position_json );

        if ( $result !== false ) {
            wp_send_json_success( array(
                'message' => __( 'Widget position saved', 'n8ndash-pro' ),
                'position' => $position_data
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to save position', 'n8ndash-pro' ) ) );
        }
    }

    /**
     * AJAX handler for saving all widget positions.
     *
     * @since    1.0.0
     */
    public function ajax_save_all_widget_positions() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'n8ndash_edit_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        $dashboard_id = isset( $_POST['dashboard_id'] ) ? intval( $_POST['dashboard_id'] ) : 0;
        $widgets = isset( $_POST['widgets'] ) ? $_POST['widgets'] : array();

        if ( ! $dashboard_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid dashboard ID', 'n8ndash-pro' ) ) );
        }

        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        if ( empty( $widgets ) || ! is_array( $widgets ) ) {
            wp_send_json_error( array( 'message' => __( 'No widget positions provided', 'n8ndash-pro' ) ) );
        }

        $success_count = 0;
        $errors = array();

        foreach ( $widgets as $widget_data ) {
            $widget_id = intval( $widget_data['widget_id'] );
            $x = intval( $widget_data['x'] );
            $y = intval( $widget_data['y'] );
            $width = intval( $widget_data['width'] );
            $height = intval( $widget_data['height'] );

            $position_data = array(
                'x' => $x,
                'y' => $y,
                'width' => $width,
                'height' => $height
            );

            $position_json = json_encode( $position_data );
            $result = N8nDash_DB::update_widget_position( $widget_id, $position_json );

            if ( $result !== false ) {
                $success_count++;
            } else {
                $errors[] = "Failed to update widget {$widget_id}";
            }
        }

        if ( $success_count > 0 ) {
            wp_send_json_success( array(
                'message' => sprintf( __( 'Successfully updated %d widget positions', 'n8ndash-pro' ), $success_count ),
                'updated_count' => $success_count,
                'errors' => $errors
            ) );
        } else {
            wp_send_json_error( array(
                'message' => __( 'Failed to update any widget positions', 'n8ndash-pro' ),
                'errors' => $errors
            ) );
        }
    }

    /**
     * AJAX: Delete dashboard (fallback when REST is unavailable)
     */
    public function ajax_delete_dashboard() {
        // Nonce: accept nonce or _wpnonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) && ! check_ajax_referer( 'n8ndash_admin_nonce', '_wpnonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'n8ndash-pro' ) ), 400 );
        }
        // Capability
        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'n8ndash_delete_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'n8ndash-pro' ) ), 403 );
        }
        // Dashboard ID
        $dashboard_id = 0;
        foreach ( array( 'dashboard_id', 'id' ) as $k ) {
            if ( isset( $_POST[ $k ] ) ) { $dashboard_id = intval( $_POST[ $k ] ); break; }
            if ( isset( $_REQUEST[ $k ] ) ) { $dashboard_id = intval( $_REQUEST[ $k ] ); break; }
        }
        if ( ! $dashboard_id ) {
            wp_send_json_error( array( 'message' => __( 'Missing dashboard ID', 'n8ndash-pro' ) ), 400 );
        }
        // Permissions for this dashboard
        if ( ! class_exists( 'N8nDash_DB' ) ) {
            $db_file = plugin_dir_path( __FILE__ ) . '../database/class-n8ndash-db.php';
            if ( file_exists( $db_file ) ) { require_once $db_file; }
        }
        $user_id = get_current_user_id();
        if ( method_exists( 'N8nDash_DB', 'user_can_access_dashboard' ) && ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, $user_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Not allowed', 'n8ndash-pro' ) ), 403 );
        }
        // Delete
        $deleted = false;
        if ( method_exists( 'N8nDash_DB', 'delete_dashboard' ) ) {
            $deleted = N8nDash_DB::delete_dashboard( $dashboard_id );
        }
        if ( $deleted ) {
            wp_send_json_success( array( 'dashboard_id' => $dashboard_id ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Delete failed', 'n8ndash-pro' ) ), 500 );
        }
    }
    
    /**
     * AJAX handler for getting user dashboards
     *
     * @since    1.0.0
     */
    public function ajax_get_user_dashboards() {
        // Verify nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'n8ndash_view_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Get user dashboards
        $dashboards = N8nDash_DB::get_user_dashboards();
        
        if ( is_wp_error( $dashboards ) ) {
            wp_send_json_error( array( 'message' => $dashboards->get_error_message() ) );
        }

        wp_send_json_success( $dashboards );
    }

    /**
     * AJAX handler for saving dashboard layout
     *
     * @since    1.0.0
     */
    public function ajax_save_dashboard_layout() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'n8ndash_edit_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        $dashboard_id = isset( $_POST['dashboard_id'] ) ? intval( $_POST['dashboard_id'] ) : 0;
        $layout = isset( $_POST['layout'] ) ? json_decode( stripslashes( $_POST['layout'] ), true ) : array();

        if ( ! $dashboard_id || ! is_array( $layout ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid data', 'n8ndash-pro' ) ) );
        }

        // Check dashboard ownership
        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Update dashboard layout
        $result = N8nDash_DB::save_dashboard( array(
            'id' => $dashboard_id,
            'layout' => $layout,
        ) );

        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Layout saved', 'n8ndash-pro' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Save failed', 'n8ndash-pro' ) ) );
        }
    }
    
    /**
     * Preview dashboard page
     */
    public function preview_dashboard_page() {
        $dashboard_id = isset( $_GET['dashboard_id'] ) ? intval( $_GET['dashboard_id'] ) : 0;
        
        if ( ! $dashboard_id ) {
            wp_die( __( 'Dashboard ID is required.', 'n8ndash-pro' ) );
        }

        // Get dashboard data
        $dashboard = N8nDash_DB::get_dashboard( $dashboard_id );
        if ( ! $dashboard ) {
            wp_die( __( 'Dashboard not found.', 'n8ndash-pro' ) );
        }

        // Check if dashboard is public - if so, allow any logged-in user to view
        // Note: $dashboard->settings is already decoded to array by get_dashboard()
        $settings = is_array( $dashboard->settings ) ? $dashboard->settings : array();
        $is_public = ! empty( $settings['is_public'] );
        
        // Debug logging
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    // Admin Preview Access Check
        }
        
        if ( $is_public ) {
            // Public dashboard - any logged-in user can view
            if ( ! is_user_logged_in() ) {
                wp_die( __( 'You must be logged in to view this dashboard.', 'n8ndash-pro' ) );
            }
            // Allow access for logged-in users
        } else {
            // Private dashboard - check specific permissions
            if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'view' ) ) {
                wp_die( __( 'You do not have permission to view this dashboard.', 'n8ndash-pro' ) );
            }
        }

        // Include the preview template
        include_once N8NDASH_PLUGIN_DIR . 'admin/partials/n8ndash-admin-preview.php';
    }
    
    /**
     * Get current user role
     *
     * @since    1.0.0
     * @return   string    User role
     */
    private function get_current_user_role() {
        $user = wp_get_current_user();
        if ( ! $user->exists() ) {
            return 'guest';
        }
        
        $roles = $user->roles;
        if ( empty( $roles ) ) {
            return 'guest';
        }
        
        // Return the first role (WordPress users typically have one primary role)
        return $roles[0];
    }
    
}