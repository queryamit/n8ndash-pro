
<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class N8nDash_Frontend {

    public function __construct() {
        add_action( 'init', array( $this, 'add_rewrite' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_filter( 'template_include', array( $this, 'template_loader' ) );
    }

    public function add_rewrite() {
        add_rewrite_rule( '^dashboard/([^/]+)/?$', 'index.php?n8dash_view=dashboard&n8dash_slug=$matches[1]', 'top' );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'n8dash_view';
        $vars[] = 'n8dash_slug';
        $vars[] = 'n8dash_id';
        return $vars;
    }

    public function template_loader( $template ) {
        $view = get_query_var( 'n8dash_view' );
        if ( 'dashboard' === $view ) {
            // FIX: Use plugin root constant to reference templates directory reliably
            $tpl = trailingslashit( N8NDASH_PLUGIN_DIR ) . 'templates/dashboard-view.php';
            if ( file_exists( $tpl ) ) {
                return $tpl;
            }
        }
        return $template;
    }

    /**
     * Build public/preview URL for a dashboard record (array|object)
     * Always generates ID-based URLs for reliable sharing
     * 
     * @param array|object $dashboard Dashboard data
     * @return string Public URL
     */
    public static function get_public_url( $dashboard ) {
        $id = 0;
        
        if ( is_array( $dashboard ) ) {
            $id = isset( $dashboard['id'] ) ? intval( $dashboard['id'] ) : 0;
        } elseif ( is_object( $dashboard ) ) {
            $id = isset( $dashboard->id ) ? intval( $dashboard->id ) : 0;
        }
        
        // Always use ID-based URLs for reliable sharing
        if ( $id && is_numeric( $id ) ) {
            return add_query_arg( array( 
                'n8dash_view' => 'dashboard', 
                'n8dash_id' => intval( $id ) 
            ), home_url( '/' ) );
        }
        
        return home_url( '/' );
    }
}
