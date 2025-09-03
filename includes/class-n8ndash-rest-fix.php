<?php
/**
 * REST API Route Registration Fix
 *
 * This class ensures REST routes are properly registered
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/includes
 */
class N8nDash_REST_Fix {
    
    /**
     * Initialize the fix
     */
    public static function init() {
        // Hook into init with high priority to ensure routes are registered
        add_action( 'init', array( __CLASS__, 'ensure_routes_registered' ), 999 );
        
        // Also hook into rest_api_init as a backup
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes_backup' ), 5 );
    }
    
    /**
     * Ensure REST routes are registered
     */
    public static function ensure_routes_registered() {
        // Check if REST API is available
        if ( ! function_exists( 'rest_get_server' ) ) {
            return;
        }
        
        // Check if our routes are already registered
        $server = rest_get_server();
        $routes = $server->get_routes();
        
        $has_routes = false;
        foreach ( $routes as $route => $handlers ) {
            if ( $route && strpos( $route, '/n8ndash/v1' ) !== false ) {
                $has_routes = true;
                break;
            }
        }
        
        // If routes are not registered, register them now
        if ( ! $has_routes && class_exists( 'N8nDash_REST_Controller' ) ) {
            $controller = new N8nDash_REST_Controller();
            $controller->register_routes();
        }
    }
    
    /**
     * Backup registration during rest_api_init
     */
    public static function register_routes_backup() {
        // Double-check and register if needed
        self::ensure_routes_registered();
    }
}

// Initialize the fix
N8nDash_REST_Fix::init();