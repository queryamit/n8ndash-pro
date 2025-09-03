<?php
/**
 * Import/Export functionality
 *
 * @since      1.2.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/includes
 * @author     Amit Anand Niraj <queryamit@gmail.com>
 * @author     https://anandtech.in
 * @author     https://github.com/queryamit
 * @author     https://www.linkedin.com/in/queryamit/
 */

class N8nDash_Import_Export {

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
     * Register AJAX handlers
     *
     * @since    1.0.0
     */
    public function register_ajax_handlers() {
        // Export handlers
        add_action( 'wp_ajax_n8ndash_export_dashboard', array( $this, 'ajax_export_dashboard' ) );
        add_action( 'wp_ajax_n8ndash_export_all', array( $this, 'ajax_export_all' ) );
        add_action( 'wp_ajax_n8ndash_export_settings', array( $this, 'ajax_export_settings' ) );
        
        // Import handlers
        add_action( 'wp_ajax_n8ndash_import_data', array( $this, 'ajax_import_data' ) );
    }

    /**
     * Export a single dashboard
     *
     * @since    1.0.0
     * @param    int    $dashboard_id    Dashboard ID
     * @return   array                   Export data
     */
    public function export_dashboard( $dashboard_id ) {
        $dashboard = N8nDash_DB::get_dashboard( $dashboard_id );
        if ( ! $dashboard ) {
            return new WP_Error( 'not_found', __( 'Dashboard not found', 'n8ndash-pro' ) );
        }

        // Check permissions
        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
            return new WP_Error( 'forbidden', __( 'You do not have permission to export this dashboard', 'n8ndash-pro' ) );
        }

        // Get widgets with complete webhook data for export
        $widgets = N8nDash_DB::get_dashboard_widgets_with_webhooks( $dashboard_id );

        // Prepare export data
        $export_data = array(
            'version'    => $this->version,
            'type'       => 'dashboard',
            'exported'   => current_time( 'mysql' ),
            'dashboard'  => array(
                'title'       => isset( $dashboard->title ) ? $dashboard->title : '',
                'slug'        => isset( $dashboard->slug ) ? $dashboard->slug : '',
                'description' => isset( $dashboard->description ) ? $dashboard->description : '',
                'status'      => isset( $dashboard->status ) ? $dashboard->status : 'active',
                'is_public'   => isset( $dashboard->is_public ) ? $dashboard->is_public : false,
                'settings'    => isset( $dashboard->settings ) ? ( is_string( $dashboard->settings ) ? json_decode( $dashboard->settings, true ) : $dashboard->settings ) : array(),
            ),
            'widgets'    => array(),
        );

        // Add widgets
        foreach ( $widgets as $widget ) {
            $export_data['widgets'][] = array(
                'widget_type'    => $widget->widget_type,
                'title'          => $widget->title,
                'config'         => is_string( $widget->config ) ? json_decode( $widget->config, true ) : $widget->config,
                'position'       => is_string( $widget->position ) ? json_decode( $widget->position, true ) : $widget->position,
                'webhook'        => array(
                    'url'     => $widget->webhook_url ?? '',
                    'method'  => $widget->webhook_method ?? 'GET',
                    'headers' => is_string( $widget->webhook_headers ) ? json_decode( $widget->webhook_headers, true ) : ( $widget->webhook_headers ?? array() ),
                    'body'    => $widget->webhook_body ?? array(),
                ),
                'status'         => $widget->status ?? 'active',
            );
        }

        return $export_data;
    }

    /**
     * Export all dashboards
     *
     * @since    1.0.0
     * @return   array    Export data
     */
    public function export_all_dashboards() {
        $user_id = get_current_user_id();
        $dashboards = N8nDash_DB::get_user_dashboards( $user_id );

        $export_data = array(
            'version'     => $this->version,
            'type'        => 'all_dashboards',
            'exported'    => current_time( 'mysql' ),
            'dashboards'  => array(),
        );

        foreach ( $dashboards as $dashboard ) {
            $dashboard_export = $this->export_dashboard( $dashboard->id );
            if ( ! is_wp_error( $dashboard_export ) ) {
                $export_data['dashboards'][] = $dashboard_export;
            }
        }

        return $export_data;
    }

    /**
     * Export plugin settings
     *
     * @since    1.0.0
     * @return   array    Export data
     */
    public function export_settings() {
        // Check permissions - allow users with export capability
        if ( ! current_user_can( 'n8ndash_export_dashboards' ) ) {
            return new WP_Error( 'forbidden', __( 'You do not have permission to export settings', 'n8ndash-pro' ) );
        }

        $settings = get_option( 'n8ndash_settings', array() );

        $export_data = array(
            'version'   => $this->version,
            'type'      => 'settings',
            'exported'  => current_time( 'mysql' ),
            'settings'  => $settings,
        );

        return $export_data;
    }

    /**
     * Import data
     *
     * @since    1.0.0
     * @param    array    $data    Import data
     * @return   array|WP_Error    Import results or error
     */
    public function import_data( $data ) {
        // FIX: Add comprehensive debugging to see what data is received
        error_log( '[N8nDash Debug] Import data received: ' . print_r( $data, true ) );
        error_log( '[N8nDash Debug] Data type: ' . gettype( $data ) );
        if ( is_array( $data ) ) {
            error_log( '[N8nDash Debug] Data keys: ' . implode( ', ', array_keys( $data ) ) );
        }
        
        // Validate data structure
        if ( ! is_array( $data ) ) {
            error_log( '[N8nDash Debug] Data is not an array' );
            return new WP_Error( 'invalid_data', __( 'Invalid import data format - data is not an array', 'n8ndash-pro' ) );
        }
        
        // FIX: Check if this is a legacy format (dashboard data without wrapper)
        if ( isset( $data['title'] ) && isset( $data['widgets'] ) && ! isset( $data['type'] ) ) {
            error_log( '[N8nDash Debug] Detected legacy format, wrapping in proper structure' );
            $data = array(
                'version'   => '1.0.0',
                'type'      => 'dashboard',
                'exported'  => current_time( 'mysql' ),
                'dashboard' => $data,
                'widgets'   => $data['widgets'],
            );
            unset( $data['dashboard']['widgets'] ); // Remove widgets from dashboard data
        }
        
        if ( ! isset( $data['type'] ) ) {
            error_log( '[N8nDash Debug] Data type is missing. Available keys: ' . implode( ', ', array_keys( $data ) ) );
            return new WP_Error( 'invalid_data', __( 'Invalid import data format - type is missing. Please ensure you are importing a valid n8nDash export file.', 'n8ndash-pro' ) );
        }
        
        if ( ! isset( $data['version'] ) ) {
            error_log( '[N8nDash Debug] Data version is missing' );
            // FIX: Set default version for legacy imports
            $data['version'] = '1.0.0';
            error_log( '[N8nDash Debug] Set default version to 1.0.0' );
        }

        // Check version compatibility
        if ( version_compare( $data['version'], '1.0.0', '<' ) ) {
            return new WP_Error( 'version_mismatch', __( 'Import data is from an incompatible version', 'n8ndash-pro' ) );
        }

        // Import based on type
        switch ( $data['type'] ) {
            case 'dashboard':
                return $this->import_dashboard( $data );
                
            case 'all_dashboards':
                return $this->import_all_dashboards( $data );
                
            case 'settings':
                return $this->import_settings( $data );
                
            default:
                return new WP_Error( 'unknown_type', sprintf( __( 'Unknown import type: %s', 'n8ndash-pro' ), $data['type'] ) );
        }
    }

    /**
     * Import all data (dashboards, widgets, and settings)
     *
     * @since    1.0.0
     * @param    array    $data    Import data
     * @return   array|WP_Error    Import results or error
     */
    public function import_all_data( $data ) {
        // Check permissions - allow users with import capability
        if ( ! current_user_can( 'n8ndash_import_dashboards' ) ) {
            return new WP_Error( 'forbidden', __( 'You do not have permission to import dashboards', 'n8ndash-pro' ) );
        }

        // Validate data structure
        if ( ! is_array( $data ) ) {
            return new WP_Error( 'invalid_data', __( 'Invalid import data format', 'n8ndash-pro' ) );
        }

        $results = array(
            'dashboards' => array(),
            'settings'   => array(),
            'errors'     => array(),
        );

        // Import settings if present
        if ( isset( $data['settings'] ) && is_array( $data['settings'] ) ) {
            $settings_result = $this->import_settings( $data );
            if ( is_wp_error( $settings_result ) ) {
                $results['errors'][] = 'Settings: ' . $settings_result->get_error_message();
            } else {
                $results['settings'] = $settings_result;
            }
        }

        // Import dashboards if present
        if ( isset( $data['dashboards'] ) && is_array( $data['dashboards'] ) ) {
            $dashboards_result = $this->import_all_dashboards( $data );
            if ( is_wp_error( $dashboards_result ) ) {
                $results['errors'][] = 'Dashboards: ' . $dashboards_result->get_error_message();
            } else {
                $results['dashboards'] = $dashboards_result;
            }
        }

        // Import single dashboard if present
        if ( isset( $data['dashboard'] ) && isset( $data['widgets'] ) ) {
            $dashboard_result = $this->import_dashboard( $data );
            if ( is_wp_error( $dashboard_result ) ) {
                $results['errors'][] = 'Dashboard: ' . $dashboard_result->get_error_message();
            } else {
                $results['dashboards'][] = $dashboard_result;
            }
        }

        // Check if any imports were successful
        if ( empty( $results['dashboards'] ) && empty( $results['settings'] ) && ! empty( $results['errors'] ) ) {
            return new WP_Error( 'import_failed', __( 'All imports failed', 'n8ndash-pro' ) );
        }

        return array(
            'success' => true,
            'results' => $results,
            'message' => __( 'Data imported successfully', 'n8ndash-pro' ),
        );
    }

    /**
     * Import a single dashboard
     *
     * @since    1.0.0
     * @param    array    $data    Dashboard data
     * @return   array|WP_Error    Import results or error
     */
    private function import_dashboard( $data ) {
        if ( ! isset( $data['dashboard'] ) || ! isset( $data['widgets'] ) ) {
            return new WP_Error( 'invalid_data', __( 'Invalid dashboard data', 'n8ndash-pro' ) );
        }

        $dashboard_data = $data['dashboard'];
        $user_id = get_current_user_id();

        // Create dashboard
        // FIX: Align with DB API (save_dashboard) instead of non-existent create_* methods
        $dashboard_id = N8nDash_DB::save_dashboard( array(
            'user_id'     => $user_id,
            'title'       => sanitize_text_field( $dashboard_data['title'] ),
            'slug'        => sanitize_title( $dashboard_data['slug'] ?? '' ),
            'description' => sanitize_textarea_field( $dashboard_data['description'] ?? '' ),
            'status'      => in_array( $dashboard_data['status'], array( 'active', 'inactive' ) ) ? $dashboard_data['status'] : 'active',
            // settings array will include visibility
            'settings'    => $dashboard_data['settings'] ?? array(), // save_dashboard json_encodes
        ) );

        if ( is_wp_error( $dashboard_id ) ) {
            return $dashboard_id;
        }

        // Import widgets
        $imported_widgets = 0;
        $widget_errors = array();

        foreach ( $data['widgets'] as $widget_data ) {
            $widget_id = N8nDash_DB::save_widget( array(
                'dashboard_id'   => $dashboard_id,
                // FIX: Map import key names to schema
                'widget_type'    => sanitize_key( $widget_data['widget_type'] ),
                'title'          => sanitize_text_field( $widget_data['title'] ),
                'config'         => $widget_data['config'] ?? array(),
                'position'       => array(
                    'x'      => intval( $widget_data['position']['x'] ?? 0 ),
                    'y'      => intval( $widget_data['position']['y'] ?? 0 ),
                    'width'  => intval( $widget_data['position']['width'] ?? 300 ),
                    'height' => intval( $widget_data['position']['height'] ?? 200 ),
                ),
                'webhook'        => array(
                    'url'     => sanitize_text_field( $widget_data['webhook']['url'] ?? '' ),
                    'method'  => sanitize_text_field( $widget_data['webhook']['method'] ?? 'GET' ),
                    'headers' => $widget_data['webhook']['headers'] ?? array(),
                    'body'    => $widget_data['webhook']['body'] ?? array(),
                ),
                'status'         => sanitize_text_field( $widget_data['status'] ?? 'active' ),
            ) );

            if ( is_wp_error( $widget_id ) ) {
                $widget_errors[] = $widget_id->get_error_message();
            } else {
                $imported_widgets++;
            }
        }

        return array(
            'success'          => true,
            'dashboard_id'     => $dashboard_id,
            'imported_widgets' => $imported_widgets,
            'widget_errors'    => $widget_errors,
            'message'          => sprintf(
                __( 'Dashboard "%s" imported successfully with %d widgets', 'n8ndash-pro' ),
                $dashboard_data['title'],
                $imported_widgets
            ),
        );
    }

    /**
     * Import all dashboards
     *
     * @since    1.0.0
     * @param    array    $data    Import data
     * @return   array             Import results
     */
    private function import_all_dashboards( $data ) {
        if ( ! isset( $data['dashboards'] ) || ! is_array( $data['dashboards'] ) ) {
            return new WP_Error( 'invalid_data', __( 'Invalid dashboards data', 'n8ndash-pro' ) );
        }

        $results = array(
            'imported'   => 0,
            'failed'     => 0,
            'dashboards' => array(),
            'errors'     => array(),
        );

        foreach ( $data['dashboards'] as $dashboard_data ) {
            $import_result = $this->import_dashboard( $dashboard_data );
            
            if ( is_wp_error( $import_result ) ) {
                $results['failed']++;
                $results['errors'][] = $import_result->get_error_message();
            } else {
                $results['imported']++;
                $results['dashboards'][] = $import_result;
            }
        }

        return array(
            'success' => true,
            'results' => $results,
            'message' => sprintf(
                __( 'Imported %d dashboards successfully, %d failed', 'n8ndash-pro' ),
                $results['imported'],
                $results['failed']
            ),
        );
    }

    /**
     * Import settings
     *
     * @since    1.0.0
     * @param    array    $data    Settings data
     * @return   array|WP_Error    Import results or error
     */
    private function import_settings( $data ) {
        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'forbidden', __( 'You do not have permission to import settings', 'n8ndash-pro' ) );
        }

        if ( ! isset( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
            return new WP_Error( 'invalid_data', __( 'Invalid settings data', 'n8ndash-pro' ) );
        }

        // Sanitize and validate settings
        $settings = $this->sanitize_settings( $data['settings'] );

        // Update settings
        update_option( 'n8ndash_settings', $settings );

        return array(
            'success' => true,
            'message' => __( 'Settings imported successfully', 'n8ndash-pro' ),
        );
    }

    /**
     * Sanitize settings
     *
     * @since    1.0.0
     * @param    array    $settings    Raw settings
     * @return   array                 Sanitized settings
     */
    private function sanitize_settings( $settings ) {
        $sanitized = array();

        // Define setting types for proper sanitization
        $setting_types = array(
            'enable_public_dashboards'  => 'boolean',
            'default_refresh_interval'  => 'integer',
            'enable_caching'            => 'boolean',
            'cache_duration'            => 'integer',
            'max_widgets_per_dashboard' => 'integer',
            'enable_debug_mode'         => 'boolean',
            'allowed_roles'             => 'array',
            'webhook_timeout'           => 'integer',
            'enable_widget_animations'  => 'boolean',
            'date_format'               => 'text',
            'time_format'               => 'text',
        );

        foreach ( $setting_types as $key => $type ) {
            if ( ! isset( $settings[ $key ] ) ) {
                continue;
            }

            switch ( $type ) {
                case 'boolean':
                    $sanitized[ $key ] = ! empty( $settings[ $key ] );
                    break;
                    
                case 'integer':
                    $sanitized[ $key ] = intval( $settings[ $key ] );
                    break;
                    
                case 'text':
                    $sanitized[ $key ] = sanitize_text_field( $settings[ $key ] );
                    break;
                    
                case 'array':
                    if ( is_array( $settings[ $key ] ) ) {
                        $sanitized[ $key ] = array_map( 'sanitize_text_field', $settings[ $key ] );
                    }
                    break;
            }
        }

        return $sanitized;
    }

    /**
     * AJAX handler for exporting a dashboard
     *
     * @since    1.0.0
     */
    public function ajax_export_dashboard() {
        // Verify nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }

        // Check export permission
        if ( ! current_user_can( 'n8ndash_export_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied. You do not have permission to export dashboards.', 'n8ndash-pro' ) ) );
        }

        $dashboard_id = isset( $_POST['dashboard_id'] ) ? intval( $_POST['dashboard_id'] ) : 0;
        if ( ! $dashboard_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid dashboard ID', 'n8ndash-pro' ) ) );
        }

        $export_data = $this->export_dashboard( $dashboard_id );
        
        if ( is_wp_error( $export_data ) ) {
            wp_send_json_error( array( 'message' => $export_data->get_error_message() ) );
        }

        wp_send_json_success( $export_data );
    }

    /**
     * AJAX handler for exporting all dashboards
     *
     * @since    1.0.0
     */
    public function ajax_export_all() {
        // Verify nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }

        // Check export permission
        if ( ! current_user_can( 'n8ndash_export_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied. You do not have permission to export dashboards.', 'n8ndash-pro' ) ) );
        }

        $export_data = $this->export_all_dashboards();
        
        if ( is_wp_error( $export_data ) ) {
            wp_send_json_error( array( 'message' => $export_data->get_error_message() ) );
        }

        // Send file download
        $filename = 'n8ndash-all-dashboards-' . date( 'Y-m-d' ) . '.json';
        
        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        
        echo wp_json_encode( $export_data, JSON_PRETTY_PRINT );
        exit;
    }

    /**
     * AJAX handler for exporting settings
     *
     * @since    1.0.0
     */
    public function ajax_export_settings() {
        // Verify nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }

        // Check export permission
        if ( ! current_user_can( 'n8ndash_export_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied. You do not have permission to export settings.', 'n8ndash-pro' ) ) );
        }

        $export_data = $this->export_settings();
        
        if ( is_wp_error( $export_data ) ) {
            wp_send_json_error( array( 'message' => $export_data->get_error_message() ) );
        }

        // Send file download
        $filename = 'n8ndash-settings-' . date( 'Y-m-d' ) . '.json';
        
        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        
        echo wp_json_encode( $export_data, JSON_PRETTY_PRINT );
        exit;
    }

    /**
     * AJAX handler for importing data
     *
     * @since    1.0.0
     */
    public function ajax_import_data() {
        // Verify nonce
        if ( ! check_ajax_referer( 'n8ndash_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'n8ndash-pro' ) ) );
        }

        // Check import permission
        if ( ! current_user_can( 'n8ndash_import_dashboards' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied. You do not have permission to import dashboards.', 'n8ndash-pro' ) ) );
        }

        $json_data = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
        if ( empty( $json_data ) ) {
            wp_send_json_error( array( 'message' => __( 'No data provided', 'n8ndash-pro' ) ) );
        }

        // FIX: Add debugging to see what JSON data is received
        error_log( '[N8nDash Debug] AJAX import - JSON data received: ' . $json_data );

        // Parse JSON
        $data = json_decode( $json_data, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( '[N8nDash Debug] AJAX import - JSON decode error: ' . json_last_error_msg() );
            wp_send_json_error( array( 'message' => __( 'Invalid JSON data', 'n8ndash-pro' ) ) );
        }

        // FIX: Add debugging to see parsed data
        error_log( '[N8nDash Debug] AJAX import - Parsed data: ' . print_r( $data, true ) );

        // Import data
        $result = $this->import_all_data( $data );
        
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }
}