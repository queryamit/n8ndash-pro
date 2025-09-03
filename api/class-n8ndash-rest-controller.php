<?php
/**
 * REST API Controller
 *
 * Handles all REST API endpoints for the plugin.
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/api
 * @author     Amit Anand Niraj <n8ndash@gmail.com>
 */
class N8nDash_REST_Controller {

    /**
     * Namespace for REST API
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $namespace    API namespace
     */
    protected $namespace = 'n8ndash/v1';

    /**
     * Register REST API routes
     *
     * @since    1.0.0
     */
    public function register_routes() {
        // Dashboard routes
        register_rest_route( $this->namespace, '/dashboards', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_dashboards' ),
                'permission_callback' => array( $this, 'get_dashboards_permissions_check' ),
                'args'                => $this->get_collection_params(),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_dashboard' ),
                'permission_callback' => array( $this, 'create_dashboard_permissions_check' ),
                'args'                => $this->get_dashboard_args(),
            ),
        ) );

        register_rest_route( $this->namespace, '/dashboards/(?P<id>\d+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_dashboard' ),
                'permission_callback' => array( $this, 'get_dashboard_permissions_check' ),
                'args'                => array(
                    'id' => array(
                        'validate_callback' => function( $param, $request, $key ) {
                            return is_numeric( $param );
                        }
                    ),
                ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_dashboard' ),
                'permission_callback' => array( $this, 'update_dashboard_permissions_check' ),
                'args'                => $this->get_dashboard_args(),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_dashboard' ),
                'permission_callback' => array( $this, 'delete_dashboard_permissions_check' ),
            ),
        ) );

        // Duplicate dashboard route
        register_rest_route( $this->namespace, '/dashboards/(?P<id>\d+)/duplicate', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'duplicate_dashboard' ),
            'permission_callback' => array( $this, 'create_dashboard_permissions_check' ),
            'args'                => array(
                'id' => array(
                    'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param );
                    }
                ),
            ),
        ) );

        // Widget routes
        register_rest_route( $this->namespace, '/widgets', array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_widget' ),
                'permission_callback' => array( $this, 'create_widget_permissions_check' ),
                'args'                => $this->get_widget_args(),
            ),
        ) );

        register_rest_route( $this->namespace, '/widgets/(?P<id>\d+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_widget' ),
                'permission_callback' => array( $this, 'get_widget_permissions_check' ),
                'args'                => array(
                    'id' => array(
                        'validate_callback' => function( $param, $request, $key ) {
                            return is_numeric( $param );
                        }
                    ),
                ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_widget' ),
                'permission_callback' => array( $this, 'update_widget_permissions_check' ),
                'args'                => $this->get_widget_args(),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_widget' ),
                'permission_callback' => array( $this, 'delete_widget_permissions_check' ),
            ),
        ) );

        // Widget action routes
        register_rest_route( $this->namespace, '/widgets/(?P<id>\d+)/refresh', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'refresh_widget' ),
            'permission_callback' => array( $this, 'refresh_widget_permissions_check' ),
        ) );

        // Import/Export routes
        register_rest_route( $this->namespace, '/export/dashboard/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'export_dashboard' ),
            'permission_callback' => array( $this, 'export_dashboard_permissions_check' ),
        ) );

        register_rest_route( $this->namespace, '/import', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'import_dashboard' ),
            'permission_callback' => array( $this, 'import_dashboard_permissions_check' ),
        ) );

        // Settings route
        register_rest_route( $this->namespace, '/settings', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_settings' ),
                'permission_callback' => array( $this, 'settings_permissions_check' ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_settings' ),
                'permission_callback' => array( $this, 'settings_permissions_check' ),
                'args'                => $this->get_settings_args(),
            ),
        ) );
    }

    /**
     * Get dashboards
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function get_dashboards( $request ) {
        $args = array(
            'status' => $request->get_param( 'status' ) ?: 'active',
            'orderby' => $request->get_param( 'orderby' ) ?: 'created_at',
            'order' => $request->get_param( 'order' ) ?: 'DESC',
            'limit' => $request->get_param( 'per_page' ) ?: 20,
            'offset' => ( $request->get_param( 'page' ) - 1 ) * $request->get_param( 'per_page' ),
        );

        $dashboards = N8nDash_DB::get_user_dashboards( 0, $args );

        $response = array();
        foreach ( $dashboards as $dashboard ) {
            $response[] = $this->prepare_dashboard_for_response( $dashboard );
        }

        return new WP_REST_Response( $response, 200 );
    }

    /**
     * Get single dashboard
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function get_dashboard( $request ) {
        $dashboard_id = $request->get_param( 'id' );
        $dashboard = N8nDash_DB::get_dashboard( $dashboard_id );

        if ( ! $dashboard ) {
            return new WP_Error( 'not_found', __( 'Dashboard not found', 'n8ndash-pro' ), array( 'status' => 404 ) );
        }

        $response = $this->prepare_dashboard_for_response( $dashboard, true );
        return new WP_REST_Response( $response, 200 );
    }

    /**
     * Create dashboard
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function create_dashboard( $request ) {
        // Log incoming request data
        // Create dashboard request received
        
        $data = array(
            'title' => $request->get_param( 'title' ),
            'description' => $request->get_param( 'description' ),
            'layout' => $request->get_param( 'layout' ) ?: array(),
            'settings' => $request->get_param( 'settings' ) ?: array(),
            'status' => $request->get_param( 'status' ) ?: 'active',
        );
        
        // Data to save prepared

        $dashboard_id = N8nDash_DB::save_dashboard( $data );
        
        // Save result received

        if ( ! $dashboard_id ) {
            // Dashboard creation failed
            return new WP_Error( 'create_failed', __( 'Failed to create dashboard', 'n8ndash-pro' ), array( 'status' => 500 ) );
        }

        $dashboard = N8nDash_DB::get_dashboard( $dashboard_id );
        
        if ( ! $dashboard ) {
            // Dashboard created but could not retrieve it
            return new WP_Error( 'retrieve_failed', __( 'Dashboard created but could not retrieve it', 'n8ndash-pro' ), array( 'status' => 500 ) );
        }
        
        $response = $this->prepare_dashboard_for_response( $dashboard );
        
        // Dashboard created successfully

        return new WP_REST_Response( $response, 201 );
    }

    /**
     * Update dashboard
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function update_dashboard( $request ) {
        $dashboard_id = $request->get_param( 'id' );
        
        $data = array( 'id' => $dashboard_id );
        
        if ( $request->has_param( 'title' ) ) {
            $data['title'] = $request->get_param( 'title' );
        }
        if ( $request->has_param( 'description' ) ) {
            $data['description'] = $request->get_param( 'description' );
        }
        if ( $request->has_param( 'layout' ) ) {
            $data['layout'] = $request->get_param( 'layout' );
        }
        if ( $request->has_param( 'settings' ) ) {
            $data['settings'] = $request->get_param( 'settings' );
        }
        if ( $request->has_param( 'status' ) ) {
            $data['status'] = $request->get_param( 'status' );
        }

        $result = N8nDash_DB::save_dashboard( $data );

        if ( ! $result ) {
            return new WP_Error( 'update_failed', __( 'Failed to update dashboard', 'n8ndash-pro' ), array( 'status' => 500 ) );
        }

        $dashboard = N8nDash_DB::get_dashboard( $dashboard_id );
        $response = $this->prepare_dashboard_for_response( $dashboard );

        return new WP_REST_Response( $response, 200 );
    }

    /**
     * Delete dashboard
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function delete_dashboard( $request ) {
        $dashboard_id = $request->get_param( 'id' );
        $result = N8nDash_DB::delete_dashboard( $dashboard_id );

        if ( ! $result ) {
            return new WP_Error( 'delete_failed', __( 'Failed to delete dashboard', 'n8ndash-pro' ), array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array( 'deleted' => true ), 200 );
    }

    /**
     * Duplicate dashboard
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function duplicate_dashboard( $request ) {
        $dashboard_id = $request->get_param( 'id' );
        $dashboard = N8nDash_DB::get_dashboard( $dashboard_id );

        if ( ! $dashboard ) {
            return new WP_Error( 'not_found', __( 'Dashboard not found', 'n8ndash-pro' ), array( 'status' => 404 ) );
        }

        $new_dashboard_id = N8nDash_DB::duplicate_dashboard( $dashboard_id );

        if ( ! $new_dashboard_id ) {
            return new WP_Error( 'duplicate_failed', __( 'Failed to duplicate dashboard', 'n8ndash-pro' ), array( 'status' => 500 ) );
        }

        $new_dashboard = N8nDash_DB::get_dashboard( $new_dashboard_id );

        if ( ! $new_dashboard ) {
            return new WP_Error( 'retrieve_failed', __( 'Dashboard duplicated but could not retrieve it', 'n8ndash-pro' ), array( 'status' => 500 ) );
        }

        return new WP_REST_Response( $this->prepare_dashboard_for_response( $new_dashboard ), 201 );
    }

    /**
     * Create widget
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function create_widget( $request ) {
        $data = array(
            'dashboard_id' => $request->get_param( 'dashboard_id' ),
            'widget_type' => $request->get_param( 'widget_type' ),
            'title' => $request->get_param( 'title' ),
            'config' => $request->get_param( 'config' ) ?: array(),
            'position' => $request->get_param( 'position' ) ?: array(),
            'webhook' => $request->get_param( 'webhook' ) ?: array(),
        );

        // Validate widget type
        $widget_types = apply_filters( 'n8ndash_widget_types', array(
            'data' => 'N8nDash_Data_Widget',
            'chart' => 'N8nDash_Chart_Widget',
            'custom' => 'N8nDash_Custom_Widget',
        ) );
        
        // Add debug logging
        // Widget type validation
        
        if ( ! in_array( $data['widget_type'], array_keys( $widget_types ), true ) ) {
            // Widget type validation failed
            return new WP_Error( 'invalid_widget_type', __( 'Invalid widget type', 'n8ndash-pro' ), array( 'status' => 400 ) );
        }
        
        // Widget type validation passed

        $widget_id = N8nDash_DB::save_widget( $data );

        if ( ! $widget_id ) {
            return new WP_Error( 'create_failed', __( 'Failed to create widget', 'n8ndash-pro' ), array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array( 'id' => $widget_id ), 201 );
    }

    /**
     * Update widget
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function update_widget( $request ) {
        $widget_id = $request->get_param( 'id' );
        
        $data = array( 'id' => $widget_id );
        
        // Allow updating dashboard_id and widget_type when provided
        if ( $request->has_param( 'dashboard_id' ) ) {
            $data['dashboard_id'] = $request->get_param( 'dashboard_id' );
        }
        if ( $request->has_param( 'widget_type' ) ) {
            $data['widget_type'] = $request->get_param( 'widget_type' );
        }

        if ( $request->has_param( 'title' ) ) {
            $data['title'] = $request->get_param( 'title' );
        }
        if ( $request->has_param( 'config' ) ) {
            $data['config'] = $request->get_param( 'config' );
        }
        if ( $request->has_param( 'position' ) ) {
            $data['position'] = $request->get_param( 'position' );
        }
        if ( $request->has_param( 'webhook' ) ) {
            $data['webhook'] = $request->get_param( 'webhook' );
        }

        $result = N8nDash_DB::save_widget( $data );

        if ( ! $result ) {
            return new WP_Error( 'update_failed', __( 'Failed to update widget', 'n8ndash-pro' ), array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array( 'updated' => true ), 200 );
    }

    /**
     * Delete widget
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function delete_widget( $request ) {
        $widget_id = $request->get_param( 'id' );
        $result = N8nDash_DB::delete_widget( $widget_id );

        if ( ! $result ) {
            return new WP_Error( 'delete_failed', __( 'Failed to delete widget', 'n8ndash-pro' ), array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array( 'deleted' => true ), 200 );
    }

    /**
     * Get widget
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function get_widget( $request ) {
        $widget_id = $request->get_param( 'id' );
        
        // REST get_widget called
        
        // Check if DB class exists
        if ( ! class_exists( 'N8nDash_DB' ) ) {
            // N8nDash_DB class not found
            return new WP_Error( 'db_unavailable', __( 'Database functionality not available', 'n8ndash-pro' ), array( 'status' => 500 ) );
        }
        
        // Load widget data
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
            // Widget not found in database
            return new WP_Error( 'not_found', __( 'Widget not found', 'n8ndash-pro' ), array( 'status' => 404 ) );
        }

        // Widget data loaded

        // Decode JSON fields
        $widget_data->config = json_decode( $widget_data->config, true ) ?: array();
        $widget_data->position = json_decode( $widget_data->position, true ) ?: array();
        $widget_data->webhook_headers = json_decode( $widget_data->webhook_headers, true ) ?: array();
        $widget_data->webhook_body = json_decode( $widget_data->webhook_body, true ) ?: array();
        
        // Prepare webhook data
        $webhook = array(
            'url' => $widget_data->webhook_url ?: '',
            'method' => $widget_data->webhook_method ?: 'GET',
            'headers' => $widget_data->webhook_headers,
            'body' => $widget_data->webhook_body
        );

        $response = array(
            'id' => $widget_data->id,
            'dashboard_id' => $widget_data->dashboard_id,
            'widget_type' => $widget_data->widget_type,
            'title' => $widget_data->title,
            'config' => $widget_data->config,
            'position' => $widget_data->position,
            'webhook' => $webhook,
            'status' => $widget_data->status,
            'created_at' => $widget_data->created_at,
            'updated_at' => $widget_data->updated_at
        );

        // Returning widget response

        return new WP_REST_Response( $response, 200 );
    }

    /**
     * Refresh widget data
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function refresh_widget( $request ) {
        $widget_id = $request->get_param( 'id' );
        
        // Load widget
        global $wpdb;
        $tables = N8nDash_DB::get_table_names();
        $widget_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT w.*, wh.url, wh.method, wh.headers 
             FROM {$tables['widgets']} w
             LEFT JOIN {$tables['webhooks']} wh ON w.id = wh.widget_id
             WHERE w.id = %d",
            $widget_id
        ) );

        if ( ! $widget_data ) {
            return new WP_Error( 'not_found', __( 'Widget not found', 'n8ndash-pro' ), array( 'status' => 404 ) );
        }

        // Create widget instance
        $widget_data->config = json_decode( $widget_data->config, true );
        $widget_data->position = json_decode( $widget_data->position, true );
        $widget_data->headers = json_decode( $widget_data->headers, true );
        
        if ( ! empty( $widget_data->url ) ) {
            $widget_data->config['webhook'] = array(
                'url' => $widget_data->url,
                'method' => $widget_data->method,
                'headers' => $widget_data->headers,
            );
        }

        $widget = $this->create_widget_instance( $widget_data );
        if ( ! $widget ) {
            return new WP_Error( 'invalid_widget', __( 'Invalid widget type', 'n8ndash-pro' ), array( 'status' => 400 ) );
        }

        // Call webhook
        $response = $widget->call_webhook();
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return new WP_REST_Response( $response, 200 );
    }

    /**
     * Export dashboard
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function export_dashboard( $request ) {
        $dashboard_id = $request->get_param( 'id' );
        
        $dashboard = N8nDash_DB::get_dashboard( $dashboard_id );
        if ( ! $dashboard ) {
            return new WP_Error( 'not_found', __( 'Dashboard not found', 'n8ndash-pro' ), array( 'status' => 404 ) );
        }

        $widgets = N8nDash_DB::get_dashboard_widgets( $dashboard_id );

        $export_data = array(
            'version' => N8NDASH_VERSION,
            'exported_at' => current_time( 'mysql' ),
            'dashboard' => array(
                'title' => $dashboard->title,
                'description' => $dashboard->description,
                'layout' => $dashboard->layout,
                'settings' => $dashboard->settings,
            ),
            'widgets' => array(),
        );

        foreach ( $widgets as $widget ) {
            $export_data['widgets'][] = array(
                'widget_type' => $widget->widget_type,
                'title' => $widget->title,
                'config' => $widget->config,
                'position' => $widget->position,
                'webhook' => array(
                    'url' => $widget->url,
                    'method' => $widget->method,
                    'headers' => $widget->headers,
                ),
            );
        }

        return new WP_REST_Response( $export_data, 200 );
    }

    /**
     * Import dashboard
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function import_dashboard( $request ) {
        // FIX: Use the proper import logic from N8nDash_Import_Export class
        $import_data = $request->get_json_params();
        
        // REST API import - Received data
        
        // Use the import/export class for proper import logic
        $import_export = new N8nDash_Import_Export( 'n8ndash-pro', '1.0.0' );
        $result = $import_export->import_data( $import_data );
        
        if ( is_wp_error( $result ) ) {
            // REST API import - Error
            return new WP_Error( 'import_failed', $result->get_error_message(), array( 'status' => 400 ) );
        }
        
        // REST API import - Success
        
        return new WP_REST_Response( $result, 201 );
    }

    /**
     * Get settings
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function get_settings( $request ) {
        $settings = get_option( 'n8ndash_settings', array() );
        $widget_defaults = get_option( 'n8ndash_widget_defaults', array() );

        return new WP_REST_Response( array(
            'general' => $settings,
            'widget_defaults' => $widget_defaults,
        ), 200 );
    }

    /**
     * Update settings
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   WP_REST_Response              Response
     */
    public function update_settings( $request ) {
        if ( $request->has_param( 'general' ) ) {
            update_option( 'n8ndash_settings', $request->get_param( 'general' ) );
        }

        if ( $request->has_param( 'widget_defaults' ) ) {
            update_option( 'n8ndash_widget_defaults', $request->get_param( 'widget_defaults' ) );
        }

        return $this->get_settings( $request );
    }

    /**
     * Prepare dashboard for response
     *
     * @since    1.0.0
     * @param    object    $dashboard       Dashboard object
     * @param    bool      $include_widgets Include widgets in response
     * @return   array                      Prepared dashboard
     */
    private function prepare_dashboard_for_response( $dashboard, $include_widgets = false ) {
        $data = array(
            'id' => $dashboard->id,
            'title' => $dashboard->title,
            'description' => $dashboard->description,
            'layout' => $dashboard->layout,
            'settings' => $dashboard->settings,
            'status' => $dashboard->status,
            'created_at' => $dashboard->created_at,
            'updated_at' => $dashboard->updated_at,
            'author' => array(
                'id' => $dashboard->user_id,
                'name' => get_the_author_meta( 'display_name', $dashboard->user_id ),
            ),
            '_links' => array(
                'self' => array(
                    'href' => rest_url( $this->namespace . '/dashboards/' . $dashboard->id ),
                ),
                'collection' => array(
                    'href' => rest_url( $this->namespace . '/dashboards' ),
                ),
            ),
        );

        if ( $include_widgets ) {
            $data['widgets'] = N8nDash_DB::get_dashboard_widgets( $dashboard->id );
        }

        return $data;
    }

    /**
     * Create widget instance
     *
     * @since    1.0.0
     * @param    object    $widget_data    Widget data
     * @return   N8nDash_Widget|null       Widget instance
     */
    private function create_widget_instance( $widget_data ) {
        $widget_types = apply_filters( 'n8ndash_widget_types', array(
            'data' => 'N8nDash_Data_Widget',
            'chart' => 'N8nDash_Chart_Widget',
            'custom' => 'N8nDash_Custom_Widget',
        ) );

        if ( ! isset( $widget_types[ $widget_data->widget_type ] ) ) {
            return null;
        }

        $class_name = $widget_types[ $widget_data->widget_type ];
        if ( ! class_exists( $class_name ) ) {
            return null;
        }

        return new $class_name( (array) $widget_data );
    }

    /**
     * Get collection parameters
     *
     * @since    1.0.0
     * @return   array    Collection parameters
     */
    private function get_collection_params() {
        return array(
            'page' => array(
                'default' => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'default' => 20,
                'sanitize_callback' => 'absint',
            ),
            'orderby' => array(
                'default' => 'created_at',
                'enum' => array( 'id', 'title', 'created_at', 'updated_at' ),
            ),
            'order' => array(
                'default' => 'DESC',
                'enum' => array( 'ASC', 'DESC' ),
            ),
            'status' => array(
                'default' => 'active',
                'enum' => array( 'active', 'inactive' ),
            ),
        );
    }

    /**
     * Get dashboard arguments
     *
     * @since    1.0.0
     * @return   array    Dashboard arguments
     */
    private function get_dashboard_args() {
        return array(
            'title' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'description' => array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
            ),
            'layout' => array(
                'type' => 'object',
            ),
            'settings' => array(
                'type' => 'object',
            ),
            'status' => array(
                'type' => 'string',
                'enum' => array( 'active', 'inactive' ),
            ),
        );
    }

    /**
     * Get widget arguments
     *
     * @since    1.0.0
     * @return   array    Widget arguments
     */
    private function get_widget_args() {
        return array(
            'dashboard_id' => array(
                'required' => true,
                'type' => 'integer',
                'sanitize_callback' => 'absint',
            ),
            'widget_type' => array(
                'required' => true,
                'type' => 'string',
            ),
            'title' => array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'config' => array(
                'type' => 'object',
            ),
            'position' => array(
                'type' => 'object',
            ),
            'webhook' => array(
                'type' => 'object',
            ),
        );
    }

    /**
     * Get settings arguments
     *
     * @since    1.0.0
     * @return   array    Settings arguments
     */
    private function get_settings_args() {
        return array(
            'general' => array(
                'type' => 'object',
            ),
            'widget_defaults' => array(
                'type' => 'object',
            ),
        );
    }

    /**
     * Check permissions for getting dashboards
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function get_dashboards_permissions_check( $request ) {
        if ( ! current_user_can( 'n8ndash_view_dashboards' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'You do not have permission to view dashboards', 'n8ndash-pro' ), array( 'status' => 403 ) );
        }
        return true;
    }

    /**
     * Check permissions for getting a dashboard
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function get_dashboard_permissions_check( $request ) {
        $dashboard_id = $request->get_param( 'id' );
        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'view' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'You do not have permission to view this dashboard', 'n8ndash-pro' ), array( 'status' => 403 ) );
        }
        return true;
    }

    /**
     * Check permissions for creating dashboards
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function create_dashboard_permissions_check( $request ) {
        if ( ! current_user_can( 'n8ndash_create_dashboards' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'You do not have permission to create dashboards', 'n8ndash-pro' ), array( 'status' => 403 ) );
        }
        return true;
    }

    /**
     * Check permissions for updating dashboards
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function update_dashboard_permissions_check( $request ) {
        $dashboard_id = $request->get_param( 'id' );
        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'You do not have permission to edit this dashboard', 'n8ndash-pro' ), array( 'status' => 403 ) );
        }
        return true;
    }

    /**
     * Check permissions for deleting dashboards
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function delete_dashboard_permissions_check( $request ) {
        $dashboard_id = $request->get_param( 'id' );
        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'delete' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'You do not have permission to delete this dashboard', 'n8ndash-pro' ), array( 'status' => 403 ) );
        }
        return true;
    }

    /**
     * Check permissions for creating widgets
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function create_widget_permissions_check( $request ) {
        $dashboard_id = $request->get_param( 'dashboard_id' );
        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'You do not have permission to add widgets to this dashboard', 'n8ndash-pro' ), array( 'status' => 403 ) );
        }
        return true;
    }

    /**
     * Check permissions for getting a widget
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function get_widget_permissions_check( $request ) {
        $widget_id = $request->get_param( 'id' );
        
        // Permission check for widget ID
        
        // Check if DB class exists
        if ( ! class_exists( 'N8nDash_DB' ) ) {
            // N8nDash_DB class not found
            return new WP_Error( 'db_unavailable', __( 'Database functionality not available', 'n8ndash-pro' ), array( 'status' => 500 ) );
        }
        
        $widget = N8nDash_DB::get_widget( $widget_id );
        
        if ( ! $widget ) {
            // Widget not found in permission check
            return new WP_Error( 'not_found', __( 'Widget not found', 'n8ndash-pro' ), array( 'status' => 404 ) );
        }
        
                    // Widget found, checking dashboard access
        
        // Skip permission check for admins
        if ( current_user_can( 'manage_options' ) ) {
            // Admin user, permission granted
            return true;
        }
        
        if ( ! N8nDash_DB::user_can_access_dashboard( $widget->dashboard_id, 'view' ) ) {
            // Permission denied for dashboard
            return new WP_Error( 'rest_forbidden', __( 'You do not have permission to view this widget', 'n8ndash-pro' ), array( 'status' => 403 ) );
        }
        
        // Permission check passed
        return true;
    }

    /**
     * Check permissions for updating widgets
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function update_widget_permissions_check( $request ) {
        $widget_id = $request->get_param( 'id' );
        $widget = N8nDash_DB::get_widget( $widget_id );
        
        if ( ! $widget ) {
            return new WP_Error( 'not_found', __( 'Widget not found', 'n8ndash-pro' ), array( 'status' => 404 ) );
        }
        
        if ( ! N8nDash_DB::user_can_access_dashboard( $widget->dashboard_id, 'edit' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'You do not have permission to edit this widget', 'n8ndash-pro' ), array( 'status' => 403 ) );
        }
        return true;
    }

    /**
     * Check permissions for deleting widgets
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function delete_widget_permissions_check( $request ) {
        $widget_id = $request->get_param( 'id' );
        $widget = N8nDash_DB::get_widget( $widget_id );
        
        if ( ! $widget ) {
            return new WP_Error( 'not_found', __( 'Widget not found', 'n8ndash-pro' ), array( 'status' => 404 ) );
        }
        
        if ( ! N8nDash_DB::user_can_access_dashboard( $widget->dashboard_id, 'edit' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'You do not have permission to delete this widget', 'n8ndash-pro' ), array( 'status' => 403 ) );
        }
        return true;
    }

    /**
     * Check permissions for refreshing widgets
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function refresh_widget_permissions_check( $request ) {
        return $this->get_widget_permissions_check( $request );
    }

    /**
     * Check permissions for exporting dashboards
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function export_dashboard_permissions_check( $request ) {
        return $this->get_dashboard_permissions_check( $request );
    }

    /**
     * Check permissions for importing dashboards
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function import_dashboard_permissions_check( $request ) {
        return $this->create_dashboard_permissions_check( $request );
    }

    /**
     * Check permissions for settings
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Request object
     * @return   bool|WP_Error                  Permission check result
     */
    public function settings_permissions_check( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'You do not have permission to manage settings', 'n8ndash-pro' ), array( 'status' => 403 ) );
        }
        return true;
    }
}