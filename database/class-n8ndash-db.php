<?php
/**
 * Database handler class
 *
 * This class handles all database operations for the plugin including
 * table creation, queries, and data management.
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/database
 * @author     Amit Anand Niraj <n8ndash@gmail.com>
 */
class N8nDash_DB {

    /**
     * Database version
     *
     * @var string
     */
    private static $db_version = '1.2.0';

    /**
     * Get table names with proper prefix
     *
     * @since    1.0.0
     * @return   array    Array of table names
     */
    public static function get_table_names() {
        global $wpdb;
        
        return array(
            'dashboards'   => $wpdb->prefix . 'n8ndash_dashboards',
            'widgets'      => $wpdb->prefix . 'n8ndash_widgets',
            'webhooks'     => $wpdb->prefix . 'n8ndash_webhooks',
            'permissions'  => $wpdb->prefix . 'n8ndash_permissions',
        );
    }

    /**
     * Create database tables
     *
     * @since    1.0.0
     * @return   void
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $tables = self::get_table_names();
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // Dashboards table
        $sql = "CREATE TABLE {$tables['dashboards']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            title varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            layout longtext,
            settings longtext,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY slug (slug),
            KEY status (status)
        ) $charset_collate;";
        
        dbDelta( $sql );

        // Widgets table
        $sql = "CREATE TABLE {$tables['widgets']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            dashboard_id bigint(20) UNSIGNED NOT NULL,
            widget_type varchar(50) NOT NULL,
            title varchar(255) NOT NULL,
            config longtext,
            position longtext,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY dashboard_id (dashboard_id),
            KEY widget_type (widget_type)
        ) $charset_collate;";
        
        dbDelta( $sql );

        // Webhooks table
        $sql = "CREATE TABLE {$tables['webhooks']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            widget_id bigint(20) UNSIGNED NOT NULL,
            url text NOT NULL,
            method varchar(10) DEFAULT 'POST',
            headers longtext,
            body longtext,
            last_called datetime,
            last_response longtext,
            call_count bigint(20) DEFAULT 0,
            PRIMARY KEY (id),
            KEY widget_id (widget_id)
        ) $charset_collate;";
        
        dbDelta( $sql );

        // Permissions table
        $sql = "CREATE TABLE {$tables['permissions']} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            dashboard_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            role varchar(255) DEFAULT NULL,
            capability varchar(50) NOT NULL,
            PRIMARY KEY (id),
            KEY dashboard_id (dashboard_id),
            KEY user_id (user_id),
            KEY role (role)
        ) $charset_collate;";
        
        dbDelta( $sql );

        // Store database version
        update_option( 'n8ndash_db_version', self::$db_version );
    }

    /**
     * Get dashboard by ID
     *
     * @since    1.0.0
     * @param    int    $dashboard_id    Dashboard ID
     * @return   object|null             Dashboard object or null
     */
    public static function get_dashboard( $dashboard_id ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $dashboard = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$tables['dashboards']} WHERE id = %d AND status = 'active'",
            $dashboard_id
        ) );
        
        if ( $dashboard ) {
            $dashboard->layout = json_decode( $dashboard->layout ?? '', true ) ?: array();
            $dashboard->settings = json_decode( $dashboard->settings ?? '', true ) ?: array();
        }
        
        return $dashboard;
    }

    /**
     * Get dashboard by slug
     *
     * @since    1.0.0
     * @param    string    $slug    Dashboard slug
     * @return   object|null        Dashboard object or null
     */
    public static function get_dashboard_by_slug( $slug ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $dashboard = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$tables['dashboards']} WHERE slug = %s AND status = 'active'",
            $slug
        ) );
        
        if ( $dashboard ) {
            $dashboard->layout = json_decode( $dashboard->layout ?? '', true ) ?: array();
            $dashboard->settings = json_decode( $dashboard->settings ?? '', true ) ?: array();
        }
        
        return $dashboard;
    }

    /**
     * Get dashboards for a user
     *
     * @since    1.0.0
     * @param    int    $user_id    User ID (0 for current user)
     * @param    array  $args       Query arguments
     * @return   array              Array of dashboard objects
     */
    public static function get_user_dashboards( $user_id = 0, $args = array() ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        
        $defaults = array(
            'status' => 'active',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 20,
            'offset' => 0,
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        // Build query
        $query = "SELECT * FROM {$tables['dashboards']} WHERE 1=1";
        $query_args = array();
        
        // Add user condition
        if ( ! current_user_can( 'n8ndash_edit_others_dashboards' ) ) {
            $query .= " AND user_id = %d";
            $query_args[] = $user_id;
        }
        
        // Add status condition
        if ( $args['status'] ) {
            $query .= " AND status = %s";
            $query_args[] = $args['status'];
        }
        
        // Add ordering
        $allowed_orderby = array( 'id', 'title', 'created_at', 'updated_at' );
        $orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'created_at';
        $order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
        $query .= " ORDER BY $orderby $order";
        
        // Add limit
        $query .= " LIMIT %d OFFSET %d";
        $query_args[] = $args['limit'];
        $query_args[] = $args['offset'];
        
        // Execute query
        $dashboards = $wpdb->get_results( 
            empty( $query_args ) ? $query : $wpdb->prepare( $query, $query_args ) 
        );
        
        // Decode JSON fields
        foreach ( $dashboards as &$dashboard ) {
            $dashboard->layout = json_decode( $dashboard->layout ?? '', true ) ?: array();
            $dashboard->settings = json_decode( $dashboard->settings ?? '', true ) ?: array();
        }
        
        return $dashboards;
    }

    /**
     * Get dashboard widgets with complete webhook data (for export)
     *
     * @since    1.0.0
     * @param    int    $dashboard_id    Dashboard ID
     * @return   array                   Widgets with webhook data
     */
    public static function get_dashboard_widgets_with_webhooks( $dashboard_id ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $widgets = $wpdb->get_results( $wpdb->prepare(
            "SELECT w.*, wh.url as webhook_url, wh.method as webhook_method, wh.headers as webhook_headers, wh.body as webhook_body 
             FROM {$tables['widgets']} w
             LEFT JOIN {$tables['webhooks']} wh ON w.id = wh.widget_id
             WHERE w.dashboard_id = %d AND w.status = 'active'
             ORDER BY w.id ASC",
            $dashboard_id
        ) );
        
        foreach ( $widgets as &$widget ) {
            $widget->config = json_decode( $widget->config ?? '', true ) ?: array();
            $widget->position = json_decode( $widget->position ?? '', true ) ?: array();
            $widget->webhook_headers = json_decode( $widget->webhook_headers ?? '', true ) ?: array();
            $widget->webhook_body = json_decode( $widget->webhook_body ?? '', true ) ?: array();
        }
        
        return $widgets;
    }

    /**
     * Get widgets for a dashboard
     *
     * @since    1.0.0
     * @param    int    $dashboard_id    Dashboard ID
     * @return   array                   Array of widget objects
     */
    public static function get_dashboard_widgets( $dashboard_id ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $widgets = $wpdb->get_results( $wpdb->prepare(
            "SELECT w.*, wh.url as webhook_url, wh.method as webhook_method, wh.headers as webhook_headers, wh.body as webhook_body 
             FROM {$tables['widgets']} w
             LEFT JOIN {$tables['webhooks']} wh ON w.id = wh.widget_id
             WHERE w.dashboard_id = %d AND w.status = 'active'
             ORDER BY w.id ASC",
            $dashboard_id
        ) );
        
        foreach ( $widgets as &$widget ) {
            $widget->config = json_decode( $widget->config ?? '', true ) ?: array();
            $widget->position = json_decode( $widget->position ?? '', true ) ?: array();
            $widget->webhook_headers = json_decode( $widget->webhook_headers ?? '', true ) ?: array();
            $widget->webhook_body = json_decode( $widget->webhook_body ?? '', true ) ?: array();
            
            // Integrate webhook data into config array for proper access
            if ( ! empty( $widget->webhook_url ) ) {
                $widget->config['webhook'] = array(
                    'url' => $widget->webhook_url,
                    'method' => $widget->webhook_method ?: 'POST',
                    'headers' => $widget->webhook_headers ?: array(),
                    'body' => $widget->webhook_body ?: array()
                );
            }
        }
        
        return $widgets;
    }

    /**
     * Get widget by ID
     *
     * @since    1.0.0
     * @param    int    $widget_id    Widget ID
     * @return   object|null           Widget object or null
     */
    public static function get_widget( $widget_id ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $widget = $wpdb->get_row( $wpdb->prepare(
            "SELECT w.*, wh.url as webhook_url, wh.method as webhook_method, wh.headers as webhook_headers, wh.body as webhook_body
             FROM {$tables['widgets']} w
             LEFT JOIN {$tables['webhooks']} wh ON w.id = wh.widget_id
             WHERE w.id = %d AND w.status = 'active'",
            $widget_id
        ) );
        
        if ( $widget ) {
            $widget->config = json_decode( $widget->config ?? '', true ) ?: array();
            $widget->position = json_decode( $widget->position ?? '', true ) ?: array();
            $widget->webhook_headers = json_decode( $widget->webhook_headers ?? '', true ) ?: array();
            $widget->webhook_body = json_decode( $widget->webhook_body ?? '', true ) ?: array();
            
            // Integrate webhook data into config array for proper access
            if ( ! empty( $widget->webhook_url ) ) {
                $widget->config['webhook'] = array(
                                    'url' => $widget->webhook_url,
                'method' => $widget->webhook_method ?: 'POST',
                'headers' => $widget->webhook_headers ?: array(),
                'body' => $widget->webhook_body ?: array()
                );
            }
        }
        
        return $widget;
    }

    /**
     * Save dashboard
     *
     * @since    1.0.0
     * @param    array    $data    Dashboard data
     * @return   int|false         Dashboard ID or false on failure
     */
    public static function save_dashboard( $data ) {
        global $wpdb;
        $tables = self::get_table_names();
        

        
        // Ensure we have a title
        $title = isset( $data['title'] ) && $data['title'] !== '' ? $data['title'] : __( 'Untitled Dashboard', 'n8ndash-pro' );
        
        // Ownership protection: Preserve original creator on updates, set current user for new dashboards
        $dashboard_data = array(
            'user_id'     => isset( $data['id'] ) ? self::get_dashboard_owner( $data['id'] ) : get_current_user_id(),
            'title'       => sanitize_text_field( $title ),
            'slug'        => sanitize_title( isset( $data['slug'] ) && $data['slug'] !== '' ? $data['slug'] : $title ),
            'description' => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '',
            'layout'      => wp_json_encode( isset( $data['layout'] ) ? $data['layout'] : array() ),
            'settings'    => wp_json_encode( isset( $data['settings'] ) ? $data['settings'] : array() ),
            'status'      => isset( $data['status'] ) && in_array( $data['status'], array( 'active', 'inactive' ) ) ? $data['status'] : 'active',
        );
        

        
        if ( ! empty( $data['id'] ) ) {
            // Update existing

            
            $dashboard_data['updated_at'] = current_time( 'mysql' );
            $result = $wpdb->update(
                $tables['dashboards'],
                $dashboard_data,
                array( 'id' => intval( $data['id'] ) ),
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );
            

            
            return $result !== false ? $data['id'] : false;
        } else {
            // Insert new

            
            $dashboard_data['created_at'] = current_time( 'mysql' );
            $dashboard_data['updated_at'] = current_time( 'mysql' );
            

            
            $result = $wpdb->insert(
                $tables['dashboards'],
                $dashboard_data,
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );
            
            if ( $result === false ) {
                return false;
            }
            
            return $result !== false ? $wpdb->insert_id : false;
        }
    }

    /**
     * Delete dashboard
     *
     * @since    1.0.0
     * @param    int    $dashboard_id    Dashboard ID
     * @return   bool                    Success status
     */
    public static function delete_dashboard( $dashboard_id ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        // Check permissions
        if ( ! self::user_can_access_dashboard( $dashboard_id, 'delete' ) ) {
            return false;
        }
        
        // Delete widgets first
        $wpdb->delete( $tables['widgets'], array( 'dashboard_id' => $dashboard_id ), array( '%d' ) );
        
        // Delete dashboard
        $result = $wpdb->delete( $tables['dashboards'], array( 'id' => $dashboard_id ), array( '%d' ) );
        
        return $result !== false;
    }

    /**
     * Duplicate dashboard with all widgets
     *
     * @since    1.0.0
     * @param    int    $dashboard_id    Dashboard ID to duplicate
     * @return   int|false               New dashboard ID or false on failure
     */
    public static function duplicate_dashboard( $dashboard_id ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        // Get original dashboard
        $dashboard = self::get_dashboard( $dashboard_id );
        if ( ! $dashboard ) {
            return false;
        }
        
        // Check permissions
        if ( ! self::user_can_access_dashboard( $dashboard_id, 'view' ) ) {
            return false;
        }
        
        // Create new dashboard data
        $new_dashboard_data = array(
            'user_id'     => get_current_user_id(),
            'title'       => $dashboard->title . ' (Copy)',
            'slug'        => sanitize_title( $dashboard->title . '-copy-' . time() ),
            'description' => $dashboard->description,
            'layout'      => $dashboard->layout,
            'settings'    => $dashboard->settings,
            'status'      => 'active',
        );
        
        // Insert new dashboard
        $result = $wpdb->insert( $tables['dashboards'], $new_dashboard_data );
        if ( ! $result ) {
            return false;
        }
        
        $new_dashboard_id = $wpdb->insert_id;
        
        // Get all widgets from original dashboard with webhook data
        $widgets = self::get_dashboard_widgets( $dashboard_id );
        
        // Duplicate each widget
        foreach ( $widgets as $widget ) {
            // Ensure config and position are properly formatted for database insertion
            $config_data = is_array( $widget->config ) ? $widget->config : array();
            $position_data = is_array( $widget->position ) ? $widget->position : array();
            
            $new_widget_data = array(
                'dashboard_id' => $new_dashboard_id,
                'widget_type'  => $widget->widget_type,
                'title'        => $widget->title,
                'config'       => wp_json_encode( $config_data ),
                'position'     => wp_json_encode( $position_data ),
                'status'       => 'active',
                'created_at'   => current_time( 'mysql' ),
                'updated_at'   => current_time( 'mysql' ),
            );
            
            $result = $wpdb->insert( $tables['widgets'], $new_widget_data );
            if ( $result ) {
                $new_widget_id = $wpdb->insert_id;
                
                // Duplicate webhook data if it exists
                if ( ! empty( $widget->webhook_url ) ) {
                    $webhook_data = array(
                        'url' => $widget->webhook_url,
                        'method' => $widget->webhook_method ?: 'POST',
                        'headers' => $widget->webhook_headers ?: array(),
                        'body' => $widget->webhook_body ?: array(),
                    );
                    self::save_webhook( $new_widget_id, $webhook_data );
                }
            }
        }
        
        return $new_dashboard_id;
    }

    /**
     * Save widget
     *
     * @since    1.0.0
     * @param    array    $data    Widget data
     * @return   int|false         Widget ID or false on failure
     */
    public static function save_widget( $data ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        // Set default position if not provided
        $default_position = array(
            'x' => 50,
            'y' => 50,
            'width' => 300,
            'height' => 200
        );
        $position = isset( $data['position'] ) && is_array( $data['position'] )
            ? array_merge( $default_position, $data['position'] )
            : $default_position;
        
        $widget_data = array(
            'dashboard_id' => intval( $data['dashboard_id'] ),
            'widget_type'  => sanitize_text_field( $data['widget_type'] ?? '' ),
            'title'        => sanitize_text_field( $data['title'] ?? '' ),
            'config'       => wp_json_encode( isset( $data['config'] ) ? $data['config'] : array() ),
            'position'     => wp_json_encode( $position ),
            'status'       => ( isset( $data['status'] ) && in_array( $data['status'], array( 'active', 'inactive' ) ) ) ? $data['status'] : 'active',
        );
        

        
        if ( ! empty( $data['id'] ) ) {
            // Update existing
            $widget_data['updated_at'] = current_time( 'mysql' );
            $result = $wpdb->update(
                $tables['widgets'],
                $widget_data,
                array( 'id' => intval( $data['id'] ) ),
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );
            $widget_id = $result !== false ? $data['id'] : false;
        } else {
            // Insert new
            $widget_data['created_at'] = current_time( 'mysql' );
            $widget_data['updated_at'] = current_time( 'mysql' );
            
            $result = $wpdb->insert(
                $tables['widgets'],
                $widget_data,
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
            );
            
            $widget_id = $result !== false ? $wpdb->insert_id : false;
        }
        
        // Save webhook data if present
        if ( $widget_id && ! empty( $data['webhook'] ) ) {
            self::save_webhook( $widget_id, $data['webhook'] );
        }

        // If widget type or chart type changed, ensure title and status remain valid
        if ( $widget_id ) {
            // Force status back to active to avoid disappearing due to any external toggles
            $wpdb->update( $tables['widgets'], array( 'status' => 'active' ), array( 'id' => $widget_id ), array( '%s' ), array( '%d' ) );
        }
        
        return $widget_id;
    }

    /**
     * Delete widget
     *
     * @since    1.0.0
     * @param    int    $widget_id    Widget ID
     * @return   bool                 Success status
     */
    public static function delete_widget( $widget_id ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        // Delete webhook first
        $wpdb->delete( $tables['webhooks'], array( 'widget_id' => $widget_id ), array( '%d' ) );
        
        // Delete widget
        return $wpdb->delete( $tables['widgets'], array( 'id' => $widget_id ), array( '%d' ) ) !== false;
    }

    /**
     * Save webhook configuration
     *
     * @since    1.0.0
     * @param    int    $widget_id       Widget ID
     * @param    array  $webhook_data    Webhook configuration
     * @return   bool                    Success status
     */
    private static function save_webhook( $widget_id, $webhook_data ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        // Check if webhook exists
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$tables['webhooks']} WHERE widget_id = %d",
            $widget_id
        ) );
        
        $data = array(
            'widget_id' => $widget_id,
            'url'       => esc_url_raw( $webhook_data['url'] ?? '' ),
            'method'    => in_array( $webhook_data['method'] ?? 'POST', array( 'GET', 'POST', 'PUT', 'DELETE' ) ) ? $webhook_data['method'] : 'POST',
            'headers'   => wp_json_encode( $webhook_data['headers'] ?? array() ),
            'body'      => wp_json_encode( $webhook_data['body'] ?? array() ), // Add body field
        );
        
        if ( $existing ) {
            return $wpdb->update( 
                $tables['webhooks'], 
                $data, 
                array( 'id' => $existing ),
                array( '%d', '%s', '%s', '%s', '%s' ), // Update headers and body
                array( '%d' )
            ) !== false;
        } else {
            return $wpdb->insert( 
                $tables['webhooks'], 
                $data,
                array( '%d', '%s', '%s', '%s', '%s', '%s' ) // Insert headers and body
            ) !== false;
        }
    }

    /**
     * Update webhook statistics
     *
     * @since    1.0.0
     * @param    int    $widget_id    Widget ID
     * @param    mixed  $response     Response data
     * @return   void
     */
    public static function update_webhook_stats( $widget_id, $response ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$tables['webhooks']} 
             SET last_called = %s, last_response = %s, call_count = call_count + 1 
             WHERE widget_id = %d",
            current_time( 'mysql' ),
            wp_json_encode( $response ),
            $widget_id
        ) );
    }

    /**
     * Update widget position
     *
     * @since    1.0.0
     * @param    int    $widget_id    Widget ID
     * @param    string $position     Position JSON string
     * @return   bool                 Success status
     */
    public static function update_widget_position( $widget_id, $position ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        return $wpdb->update(
            $tables['widgets'],
            array( 'position' => $position ),
            array( 'id' => $widget_id ),
            array( '%s' ),
            array( '%d' )
        ) !== false;
    }

    /**
     * Check user permission for dashboard
     *
     * @since    1.0.0
     * @param    int     $dashboard_id    Dashboard ID
     * @param    string  $capability      Capability to check
     * @param    int     $user_id         User ID (0 for current user)
     * @return   bool                     Permission status
     */
    public static function user_can_access_dashboard( $dashboard_id, $capability = 'view', $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        
        // Admin can do everything
        if ( user_can( $user_id, 'manage_options' ) ) {
            return true;
        }
        
        global $wpdb;
        $tables = self::get_table_names();
        
        // Check if user owns the dashboard
        $owner_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT user_id FROM {$tables['dashboards']} WHERE id = %d",
            $dashboard_id
        ) );
        
        if ( $owner_id == $user_id ) {
            return true;
        }
        
        // Check specific permissions
        $permission = $wpdb->get_var( $wpdb->prepare(
            "SELECT capability FROM {$tables['permissions']} 
             WHERE dashboard_id = %d AND user_id = %d AND capability = %s",
            $dashboard_id, $user_id, $capability
        ) );
        
        return ! empty( $permission );
    }

    /**
     * Grant permission to user for dashboard
     *
     * @since    1.0.0
     * @param    int     $dashboard_id    Dashboard ID
     * @param    int     $user_id         User ID
     * @param    string  $capability      Capability to grant
     * @return   bool                     Success status
     */
    public static function grant_dashboard_permission( $dashboard_id, $user_id, $capability ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        // Check if permission already exists
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$tables['permissions']} 
             WHERE dashboard_id = %d AND user_id = %d AND capability = %s",
            $dashboard_id, $user_id, $capability
        ) );
        
        if ( $existing ) {
            return true;
        }
        
        return $wpdb->insert(
            $tables['permissions'],
            array(
                'dashboard_id' => $dashboard_id,
                'user_id'      => $user_id,
                'capability'   => $capability,
            ),
            array( '%d', '%d', '%s' )
        ) !== false;
    }

    /**
     * Revoke permission from user for dashboard
     *
     * @since    1.0.0
     * @param    int     $dashboard_id    Dashboard ID
     * @param    int     $user_id         User ID
     * @param    string  $capability      Capability to revoke
     * @return   bool                     Success status
     */
    public static function revoke_dashboard_permission( $dashboard_id, $user_id, $capability ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        return $wpdb->delete(
            $tables['permissions'],
            array(
                'dashboard_id' => $dashboard_id,
                'user_id'      => $user_id,
                'capability'   => $capability,
            ),
            array( '%d', '%d', '%s' )
        ) !== false;
    }

    /**
     * Get dashboard owner (original creator)
     *
     * @since    1.0.0
     * @param    int    $dashboard_id    Dashboard ID
     * @return   int                     User ID of dashboard owner
     */
    public static function get_dashboard_owner( $dashboard_id ) {
        global $wpdb;
        $tables = self::get_table_names();
        
        $owner = $wpdb->get_var( $wpdb->prepare(
            "SELECT user_id FROM {$tables['dashboards']} WHERE id = %d",
            $dashboard_id
        ) );
        
        return $owner ? intval( $owner ) : get_current_user_id();
    }
}