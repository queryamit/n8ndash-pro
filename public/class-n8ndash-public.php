<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the public-facing side
 * of the site including enqueuing the public-facing stylesheet and JavaScript.
 *
 * @since      1.2.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/public
 * @author     Amit Anand Niraj <queryamit@gmail.com>
 * @author     https://anandtech.in
 * @author     https://github.com/queryamit
 * @author     https://www.linkedin.com/in/queryamit/
 */
class N8nDash_Public {

/**
 * Safely normalize a JSON-like value (string|array|object|null) to array.
 */
private function normalize_json( $val ) {
    if ( is_null( $val ) ) return array();
    if ( is_string( $val ) && $val !== '' ) {
        $d = json_decode( $val, true );
        return is_array( $d ) ? $d : array();
    }
    if ( is_array( $val ) ) return $val;
    if ( is_object( $val ) ) return (array) $val;
    return array();
}


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
     * @param    string    $plugin_name    The name of the plugin.
     * @param    string    $version        The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // AJAX actions are no longer needed since custom widgets use direct webhook calls
        // This eliminates the WordPress AJAX dependency and potential 400 errors
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Check if we're on a page that needs our styles
        if ( ! $this->should_load_assets() ) {
            return;
        }

        // Bootstrap CSS (only if not already loaded)
        if ( ! wp_style_is( 'bootstrap', 'registered' ) ) {
            wp_enqueue_style( 
                'n8ndash-bootstrap', 
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', 
                array(), 
                '5.3.3' 
            );
        }

        // Plugin public styles
        wp_enqueue_style( 
            $this->plugin_name, 
            N8NDASH_PLUGIN_URL . 'assets/css/public/n8ndash-public.css', 
            array(), 
            $this->version, 
            'all' 
        );

        // Add inline styles for theme
        $settings = get_option( 'n8ndash_settings', array() );
        $theme = $settings['default_theme'] ?? 'ocean';
        $theme_colors = array(
            'ocean' => array( 'accent' => '#0ea5e9', 'accent_rgb' => '14,165,233' ),
            'emerald' => array( 'accent' => '#22c55e', 'accent_rgb' => '34,197,94' ),
            'orchid' => array( 'accent' => '#a855f7', 'accent_rgb' => '168,85,247' ),
            'citrus' => array( 'accent' => '#f59e0b', 'accent_rgb' => '245,158,11' ),
        );

        if ( isset( $theme_colors[ $theme ] ) ) {
            $colors = $theme_colors[ $theme ];
            $inline_css = sprintf(
                ':root { --accent: %s; --accent-rgb: %s; }',
                $colors['accent'],
                $colors['accent_rgb']
            );
            wp_add_inline_style( $this->plugin_name, $inline_css );
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Check if we're on a page that needs our scripts
        if ( ! $this->should_load_assets() ) {
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

        // Lucide icons
        wp_enqueue_script( 
            'n8ndash-lucide', 
            'https://unpkg.com/lucide@latest', 
            array(), 
            'latest', 
            true 
        );

        // Bootstrap JS (only if not already loaded)
        if ( ! wp_script_is( 'bootstrap', 'registered' ) ) {
            wp_enqueue_script( 
                'n8ndash-bootstrap', 
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', 
                array(), 
                '5.3.3', 
                true 
            );
        }

        // Plugin public scripts
        wp_enqueue_script( 
            $this->plugin_name, 
            N8NDASH_PLUGIN_URL . 'assets/js/public/n8ndash-public.js', 
            array( 'jquery', 'n8ndash-chartjs' ), 
            $this->version, 
            true 
        );

        // Localize script
        wp_localize_script( $this->plugin_name, 'n8ndash_public', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'api_url' => rest_url( 'n8ndash/v1' ),
            'nonce' => wp_create_nonce( 'n8ndash_public_nonce' ),
            'api_nonce' => wp_create_nonce( 'wp_rest' ),
            'strings' => array(
                'loading' => __( 'Loading...', 'n8ndash-pro' ),
                'error' => __( 'An error occurred', 'n8ndash-pro' ),
                'no_data' => __( 'No data available', 'n8ndash-pro' ),
                'last_updated' => __( 'Last updated', 'n8ndash-pro' ),
                'ago' => __( 'ago', 'n8ndash-pro' ),
                'just_now' => __( 'Just now', 'n8ndash-pro' ),
            ),
            'settings' => array(
                'enable_animations' => true,
                'refresh_interval' => 0, // Manual refresh only by default
            ),
        ) );
    }

    /**
     * Register shortcodes.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode( 'n8ndash_dashboard', array( $this, 'render_dashboard_shortcode' ) );
        add_shortcode( 'n8ndash_widget', array( $this, 'render_widget_shortcode' ) );
        add_shortcode( 'n8ndash_dashboards', array( $this, 'render_dashboards_list_shortcode' ) );
    }

    /**
     * Handle dashboard preview requests.
     *
     * @since    1.0.0
     */
    public function handle_dashboard_preview() {
        // Check if this is a dashboard preview request
        if ( isset( $_GET['n8ndash_dashboard'] ) ) {
            $dashboard_id = intval( $_GET['n8ndash_dashboard'] );
            
            if ( $dashboard_id ) {
                // Check if dashboard exists and user has permission
                if ( class_exists( 'N8nDash_DB' ) ) {
                    $dashboard = N8nDash_DB::get_dashboard( $dashboard_id );
                    if ( ! $dashboard ) {
                        wp_die( __( 'Dashboard not found', 'n8ndash-pro' ) );
                    }
                    
                    // Check if dashboard is public
                    $settings = $dashboard->settings ?: array();
                    if ( is_string( $settings ) ) {
                        $settings = json_decode( $settings, true ) ?: array();
                    }
                    $is_public = ! empty( $settings['is_public'] );
                    
                    // Allow access if:
                    // 1. Dashboard is marked as public, OR
                    // 2. User has permission to view
                    if ( ! $is_public && ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'view' ) ) {
                        wp_die( __( 'You do not have permission to view this dashboard', 'n8ndash-pro' ) );
                    }
                }
                
                // Force load assets
                add_filter( 'n8ndash_should_load_assets', '__return_true' );
                
                // Output simple dashboard preview
                $this->render_simple_dashboard_preview( $dashboard_id );
                exit;
            }
        }
    }

    /**
     * Render simple dashboard preview.
     *
     * @since    1.0.0
     * @param    int    $dashboard_id    Dashboard ID
     */
    private function render_simple_dashboard_preview( $dashboard_id ) {
        // Simple preview without full page structure
        echo '<div style="padding: 20px; font-family: Arial, sans-serif;">';
        echo '<div style="background: #0073aa; color: white; padding: 10px; margin-bottom: 20px; border-radius: 4px;">';
        echo '<strong>Dashboard Preview - ID: ' . esc_html( $dashboard_id ) . '</strong>';
        echo '</div>';
        
        // Try to render dashboard content
        if ( class_exists( 'N8nDash_DB' ) ) {
            $dashboard_content = $this->render_dashboard_shortcode( array( 'id' => $dashboard_id ) );
            echo $dashboard_content;
        } else {
            echo '<p>Dashboard functionality is loading...</p>';
        }
        
        echo '</div>';
    }

    /**
     * AJAX handler for saving widget position from frontend
     */
    public function ajax_save_widget_position() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_public_nonce', 'nonce', false ) ) {
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

        // Get widget to check permissions
        $widget = N8nDash_DB::get_widget( $widget_id );
        if ( ! $widget ) {
            wp_send_json_error( array( 'message' => __( 'Widget not found', 'n8ndash-pro' ) ) );
        }

        // Check if user can edit the dashboard
        if ( ! N8nDash_DB::user_can_access_dashboard( $widget->dashboard_id, 'edit' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Save position
        $position_data = array(
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height
        );

        $position_json = json_encode( $position_data );
        $result = N8nDash_DB::update_widget_position( $widget_id, $position_json );

        if ( $result !== false ) {
            wp_send_json_success( array( 'message' => __( 'Widget position saved', 'n8ndash-pro' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to save widget position', 'n8ndash-pro' ) ) );
        }
    }

    /**
     * Register Gutenberg blocks.
     *
     * @since    1.0.0
     */
    public function register_blocks() {
        // Check if Gutenberg is available
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        // Block registration is now handled by individual block files
        // This prevents duplicate registration errors
        
        // Enqueue block editor assets
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
    }

    /**
     * Enqueue block editor assets.
     *
     * @since    1.0.0
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'n8ndash-block-editor',
            N8NDASH_PLUGIN_URL . 'blocks/build/index.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
            $this->version
        );

        wp_enqueue_style(
            'n8ndash-block-editor',
            N8NDASH_PLUGIN_URL . 'blocks/build/editor.css',
            array( 'wp-edit-blocks' ),
            $this->version
        );

        // Localize block editor script
        wp_localize_script( 'n8ndash-block-editor', 'n8ndash_blocks', array(
            'api_url' => rest_url( 'n8ndash/v1' ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
        ) );
    }

    /**
     * Render dashboard shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes
     * @return   string           HTML output
     */
    public function render_dashboard_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
            'height' => 'auto',
            'theme' => '',
            'edit' => 'false',
        ), $atts, 'n8ndash_dashboard' );

        $dashboard_id = intval( $atts['id'] );
        if ( ! $dashboard_id ) {
            return '<div class="n8ndash-error">' . esc_html__( 'Please specify a dashboard ID', 'n8ndash-pro' ) . '</div>';
        }

        // Check if DB class is available
        if ( ! class_exists( 'N8nDash_DB' ) ) {
            return '<div class="n8ndash-error">' . esc_html__( 'Dashboard functionality is not available', 'n8ndash-pro' ) . '</div>';
        }

        // Check if dashboard exists and user has permission
        $dashboard = N8nDash_DB::get_dashboard( $dashboard_id );
        if ( ! $dashboard ) {
            return '<div class="n8ndash-error">' . esc_html__( 'Dashboard not found', 'n8ndash-pro' ) . '</div>';
        }

        // Check if dashboard is public
        $settings = $dashboard->settings ?: array();
        if ( is_string( $settings ) ) {
            $settings = json_decode( $settings, true ) ?: array();
        }
        $is_public = ! empty( $settings['is_public'] );
        
        // Check if user has permission to view (skip for admins)
        if ( ! current_user_can( 'manage_options' ) && ! $is_public && ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'view' ) ) {
            return '<div class="n8ndash-error">' . esc_html__( 'You do not have permission to view this dashboard', 'n8ndash-pro' ) . '</div>';
        }

        // Check if edit mode is requested and user has permission
        $edit_mode = ( $atts['edit'] === 'true' && N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) );

        // Prepare dashboard data
        $dashboard->config = json_decode( $dashboard->config, true );
        $_dash_cfg = $dashboard->config ?? ( $dashboard->settings ?? array() );
        $_dash_cfg = $this->normalize_json( $_dash_cfg );
        $theme = ! empty( $atts['theme'] ) ? $atts['theme'] : ( $_dash_cfg['theme'] ?? 'ocean' );

        // Get widgets for this dashboard
        $widgets = N8nDash_DB::get_dashboard_widgets( $dashboard_id );

        // Render dashboard
        ob_start();
        ?>
        <div class="n8ndash-dashboard" 
             data-dashboard-id="<?php echo esc_attr( $dashboard_id ); ?>"
             data-theme="<?php echo esc_attr( $theme ); ?>"
             data-edit-mode="<?php echo esc_attr( $edit_mode ? 'true' : 'false' ); ?>"
             style="<?php echo $atts['height'] !== 'auto' ? 'height: ' . esc_attr( $atts['height'] ) : ''; ?>">
            
            <div class="n8ndash-dashboard__header">
                <h2 class="n8ndash-dashboard__title"><?php echo esc_html( $dashboard->title ); ?></h2>
                <?php if ( $dashboard->description ) : ?>
                    <p class="n8ndash-dashboard__description"><?php echo esc_html( $dashboard->description ); ?></p>
                <?php endif; ?>
                <?php if ( $edit_mode ) : ?>
                    <div class="n8ndash-dashboard__actions">
                        <button class="n8ndash-btn n8ndash-btn--primary n8ndash-add-widget">
                            <i data-lucide="plus"></i>
                            <?php esc_html_e( 'Add Widget', 'n8ndash-pro' ); ?>
                        </button>
                        <button class="n8ndash-btn n8ndash-btn--secondary n8ndash-dashboard-settings">
                            <i data-lucide="settings"></i>
                            <?php esc_html_e( 'Settings', 'n8ndash-pro' ); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="n8ndash-dashboard__grid" data-columns="<?php echo esc_attr( $dashboard->config['columns'] ?? 12 ); ?>">
                <?php foreach ( $widgets as $widget_data ) : ?>
                    <?php
                    $cfg = $widget_data->config ?? array(); if ( is_string( $cfg ) ) { $cfg = json_decode( $cfg, true ); }
                    $widget_data->config = is_array( $cfg ) ? $cfg : array();
                    $pos = $widget_data->position ?? array(); if ( is_string( $pos ) ) { $pos = json_decode( $pos, true ); }
                    $widget_data->position = is_array( $pos ) ? $pos : array();
                    $widget = $this->create_widget_instance( $widget_data );
                    if ( $widget ) {
                        // Ensure widget data is loaded before rendering
                        if ( method_exists( $widget, 'load_data' ) ) {
                            $widget->load_data();
                        }
                        echo $widget->render( $edit_mode );
                    }
                    ?>
                <?php endforeach; ?>
            </div>

            <?php if ( empty( $widgets ) && ! $edit_mode ) : ?>
                <div class="n8ndash-empty">
                    <?php esc_html_e( 'No widgets added to this dashboard yet.', 'n8ndash-pro' ); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Render widget shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes
     * @return   string           HTML output
     */
    public function render_widget_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
            'width' => '100%',
            'height' => 'auto',
        ), $atts, 'n8ndash_widget' );

        $widget_id = intval( $atts['id'] );
        if ( ! $widget_id ) {
            return '<div class="n8ndash-error">' . esc_html__( 'Please specify a widget ID', 'n8ndash-pro' ) . '</div>';
        }

        // Load widget data
        global $wpdb;
        $tables = N8nDash_DB::get_table_names();
        $widget_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT w.*, wh.url, wh.method, wh.headers
             FROM {$tables['widgets']} w
             LEFT JOIN {$tables['webhooks']} wh ON w.id = wh.widget_id
             WHERE w.id = %d AND w.status = 'active'",
            $widget_id
        ) );

        if ( ! $widget_data ) {
            return '<div class="n8ndash-error">' . esc_html__( 'Widget not found', 'n8ndash-pro' ) . '</div>';
        }

        // Check dashboard permissions
        if ( class_exists( 'N8nDash_DB' ) && ! current_user_can( 'manage_options' ) && ! N8nDash_DB::user_can_access_dashboard( $widget_data->dashboard_id, 'view' ) ) {
            return '<div class="n8ndash-error">' . esc_html__( 'You do not have permission to view this widget', 'n8ndash-pro' ) . '</div>';
        }

        // Prepare widget data
        $cfg = $widget_data->config ?? array(); if ( is_string( $cfg ) ) { $cfg = json_decode( $cfg, true ); }
                    $widget_data->config = is_array( $cfg ) ? $cfg : array();
        $pos = $widget_data->position ?? array(); if ( is_string( $pos ) ) { $pos = json_decode( $pos, true ); }
                    $widget_data->position = is_array( $pos ) ? $pos : array();
        $widget_data->headers = json_decode( $widget_data->headers, true );
        
        if ( ! empty( $widget_data->url ) ) {
            $widget_data->config['webhook'] = array(
                'url' => $widget_data->url,
                'method' => $widget_data->method,
                'headers' => $widget_data->headers,
            );
        }

        // Create widget instance
        $widget = $this->create_widget_instance( $widget_data );
        if ( ! $widget ) {
            return '<div class="n8ndash-error">' . esc_html__( 'Invalid widget type', 'n8ndash-pro' ) . '</div>';
        }

        // Wrap widget with custom dimensions
        $style = array();
        if ( $atts['width'] !== '100%' ) {
            $style[] = 'width: ' . esc_attr( $atts['width'] );
        }
        if ( $atts['height'] !== 'auto' ) {
            $style[] = 'height: ' . esc_attr( $atts['height'] );
        }

        $wrapper_style = ! empty( $style ) ? 'style="' . implode( '; ', $style ) . '"' : '';

        return sprintf(
            '<div class="n8ndash-widget-wrapper" %s>%s</div>',
            $wrapper_style,
            $widget->render()
        );
    }

    /**
     * Render dashboards list shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes
     * @return   string           HTML output
     */
    public function render_dashboards_list_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'user' => 'current',
            'limit' => 10,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'show_description' => 'true',
            'show_author' => 'false',
            'link_target' => '_self',
        ), $atts, 'n8ndash_dashboards' );

        // Determine user ID
        $user_id = 0;
        if ( $atts['user'] === 'current' ) {
            $user_id = get_current_user_id();
        } elseif ( is_numeric( $atts['user'] ) ) {
            $user_id = intval( $atts['user'] );
        }

        // Check if DB class is available
        if ( ! class_exists( 'N8nDash_DB' ) ) {
            return '<div class="n8ndash-error">' . esc_html__( 'Dashboard functionality is not available', 'n8ndash-pro' ) . '</div>';
        }

        // Get dashboards
        $dashboards = N8nDash_DB::get_user_dashboards( $user_id, array(
            'limit' => intval( $atts['limit'] ),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        ) );

        if ( empty( $dashboards ) ) {
            return '<div class="n8ndash-empty">' . esc_html__( 'No dashboards found.', 'n8ndash-pro' ) . '</div>';
        }

        // Get dashboard page URL
        $dashboard_page_id = get_option( 'n8ndash_dashboard_page_id' );
        $base_url = $dashboard_page_id ? get_permalink( $dashboard_page_id ) : home_url( '/n8n-dashboards/' );

        ob_start();
        ?>
        <div class="n8ndash-dashboards-list">
            <?php foreach ( $dashboards as $dashboard ) : ?>
                <div class="n8ndash-dashboard-item">
                    <h3 class="n8ndash-dashboard-item__title">
                        <a href="<?php echo esc_url( add_query_arg( 'dashboard', $dashboard->id ?? 0, $base_url ) ); ?>"
                           target="<?php echo esc_attr( $atts['link_target'] ); ?>">
                            <?php echo esc_html( $dashboard->title ); ?>
                        </a>
                    </h3>
                    
                    <?php if ( $atts['show_description'] === 'true' && $dashboard->description ) : ?>
                        <p class="n8ndash-dashboard-item__description">
                            <?php echo esc_html( $dashboard->description ); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ( $atts['show_author'] === 'true' ) : ?>
                        <div class="n8ndash-dashboard-item__meta">
                            <?php
                            $author = get_userdata( $dashboard->user_id );
                            if ( $author ) {
                                printf(
                                    esc_html__( 'By %s', 'n8ndash-pro' ),
                                    '<span class="n8ndash-dashboard-item__author">' . esc_html( $author->display_name ) . '</span>'
                                );
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }



    /**
     * AJAX handler for refreshing widget (public).
     *
     * @since    1.0.0
     */
    public function ajax_refresh_widget() {
        // Check nonce
        if ( ! check_ajax_referer( 'n8ndash_public_nonce', 'nonce', false ) ) {
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
            "SELECT w.*, wh.url, wh.method, wh.headers
             FROM {$tables['widgets']} w
             LEFT JOIN {$tables['webhooks']} wh ON w.id = wh.widget_id
             WHERE w.id = %d AND w.status = 'active'",
            $widget_id
        ) );

        if ( ! $widget_data ) {
            wp_send_json_error( array( 'message' => __( 'Widget not found', 'n8ndash-pro' ) ) );
        }

        // Check permissions
        $settings = get_option( 'n8ndash_settings', array() );
        if ( empty( $settings['enable_public_dashboards'] ) && ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Authentication required', 'n8ndash-pro' ) ) );
        }

        if ( class_exists( 'N8nDash_DB' ) && ! current_user_can( 'manage_options' ) && ! N8nDash_DB::user_can_access_dashboard( $widget_data->dashboard_id, 'view' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Create widget instance
        $cfg = $widget_data->config ?? array(); if ( is_string( $cfg ) ) { $cfg = json_decode( $cfg, true ); }
                    $widget_data->config = is_array( $cfg ) ? $cfg : array();
        $pos = $widget_data->position ?? array(); if ( is_string( $pos ) ) { $pos = json_decode( $pos, true ); }
                    $widget_data->position = is_array( $pos ) ? $pos : array();
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
            wp_send_json_error( array( 'message' => __( 'Invalid widget type', 'n8ndash-pro' ) ) );
        }

        // Call webhook and return processed data
        $webhook_response = $widget->call_webhook();
        
        if ( is_wp_error( $webhook_response ) ) {
            wp_send_json_error( array(
                'message' => $webhook_response->get_error_message(),
                'code' => $webhook_response->get_error_code(),
            ) );
        }

        // Note: call_webhook() already updates webhook stats and processes the response
        wp_send_json_success( array(
            'message' => __( 'Widget refreshed', 'n8ndash-pro' ),
            'data' => $webhook_response,
            'timestamp' => current_time( 'timestamp' ),
        ) );
    }

    /**
     * AJAX handler for custom widget form submission.
     *
     * @since    1.0.0
     */
    public function ajax_custom_widget_submit() {
        try {
            // Check nonce
            if ( ! check_ajax_referer( 'n8ndash_public_nonce', 'nonce', false ) ) {
                wp_send_json_error( array( 'message' => 'Security check failed' ) );
            }

        $widget_id = isset( $_POST['widget_id'] ) ? intval( $_POST['widget_id'] ) : 0;

        if ( ! $widget_id ) {
            wp_send_json_error( array( 'message' => 'Invalid widget ID' ) );
        }

        // Load widget
        global $wpdb;
        $tables = N8nDash_DB::get_table_names();
        
        $query = $wpdb->prepare(
            "SELECT w.*, wh.url as webhook_url, wh.method as webhook_method, wh.headers as webhook_headers, wh.body as webhook_body
             FROM {$tables['widgets']} w
             LEFT JOIN {$tables['webhooks']} wh ON w.id = wh.widget_id
             WHERE w.id = %d AND w.status = 'active' AND w.widget_type = 'custom'",
            $widget_id
        );
        
        $widget_data = $wpdb->get_row( $query );

        if ( ! $widget_data ) {
            wp_send_json_error( array( 'message' => 'Custom widget not found' ) );
        }

        // Check permissions
        $settings = get_option( 'n8ndash_settings', array() );
        if ( empty( $settings['enable_public_dashboards'] ) && ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'Authentication required', 'n8ndash-pro' ) ) );
        }

        if ( class_exists( 'N8nDash_DB' ) && ! current_user_can( 'manage_options' ) && ! N8nDash_DB::user_can_access_dashboard( $widget_data->dashboard_id, 'view' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'n8ndash-pro' ) ) );
        }

        // Create widget instance
        $cfg = $widget_data->config ?? array(); if ( is_string( $cfg ) ) { $cfg = json_decode( $cfg, true ); }
                    $widget_data->config = is_array( $cfg ) ? $cfg : array();
        $pos = $widget_data->position ?? array(); if ( is_string( $pos ) ) { $pos = json_decode( $pos, true ); }
                    $widget_data->position = is_array( $pos ) ? $pos : array();
        // Headers are now handled in the webhook config section
        
        if ( ! empty( $widget_data->webhook_url ) ) {
            $widget_data->config['webhook'] = array(
                'url' => $widget_data->webhook_url,
                'method' => $widget_data->webhook_method,
                'headers' => json_decode( $widget_data->webhook_headers, true ) ?: array(),
                'body' => json_decode( $widget_data->webhook_body, true ) ?: array(),
            );
        }

        $widget = $this->create_widget_instance( $widget_data );
        if ( ! $widget ) {
            wp_send_json_error( array( 'message' => __( 'Invalid widget type', 'n8ndash-pro' ) ) );
        }

        // Enhanced form data processing with file upload support
        $form_data = $this->prepare_custom_form_data( $_POST, $_FILES );
        
        // Submit to webhook with enhanced form data
        $webhook_response = $widget->call_webhook( $form_data );
        
        if ( is_wp_error( $webhook_response ) ) {
            wp_send_json_error( array(
                'message' => $webhook_response->get_error_message(),
                'code' => $webhook_response->get_error_code(),
            ) );
        }

        // Process response for enhanced display
        $processed_response = $this->process_custom_widget_response( $webhook_response );
        
        wp_send_json_success( $processed_response );
        
    } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => 'Internal server error: ' . $e->getMessage() ) );
        }
    }
    
    /**
     * Prepare custom form data with file upload support
     *
     * @since    1.0.0
     * @param    array    $post_data    POST data
     * @param    array    $files        FILES data
     * @return   array                 Prepared form data
     */
    private function prepare_custom_form_data( $post_data, $files ) {
        $form_data = array();
        
        // Check if we have file uploads
        $has_files = ! empty( $files ) && array_filter( $files, function( $file ) {
            return ! empty( $file['name'] ) && $file['error'] === UPLOAD_ERR_OK;
        });
        
        if ( $has_files ) {
            // Handle file uploads - preserve original structure
            foreach ( $post_data as $key => $value ) {
                if ( $key !== 'nonce' && $key !== 'action' && $key !== 'widget_id' ) {
                    $form_data[ $key ] = $value;
                }
            }
            
            // Add file information
            foreach ( $files as $key => $file ) {
                if ( $file['error'] === UPLOAD_ERR_OK ) {
                    $form_data[ $key ] = array(
                        'name'     => $file['name'],
                        'type'     => $file['type'],
                        'tmp_name' => $file['tmp_name'],
                        'error'    => $file['error'],
                        'size'     => $file['size']
                    );
                }
            }
            
        } else {
            // Text-only data - clean up
            foreach ( $post_data as $key => $value ) {
                if ( $key !== 'nonce' && $key !== 'action' && $key !== 'widget_id' ) {
                    $form_data[ $key ] = sanitize_text_field( $value );
                }
            }
        }
        
        return $form_data;
    }
    
    /**
     * Process custom widget response for enhanced display
     *
     * @since    1.0.0
     * @param    mixed    $response    Webhook response
     * @return   array                 Processed response data
     */
    private function process_custom_widget_response( $response ) {
        $processed = array(
            'message' => __( 'Form submitted successfully', 'n8ndash-pro' ),
            'data' => $response,
        );
        
        // Handle different response types
        if ( is_array( $response ) ) {
            // Extract common response fields
            $processed['html'] = $response['html'] ?? null;
            $processed['redirect'] = $response['redirect'] ?? null;
            $processed['preserve_form'] = $response['preserve_form'] ?? false;
            
            // Check for file download links
            if ( isset( $response['download_url'] ) ) {
                $processed['download_url'] = $response['download_url'];
            }
            
            // Check for custom messages
            if ( isset( $response['message'] ) ) {
                $processed['message'] = $response['message'];
            }
        } elseif ( is_string( $response ) ) {
            // Handle string responses
            $processed['html'] = $response;
        }
        
        return $processed;
    }

    /**
     * Create widget instance from data.
     *
     * @since    1.0.0
     * @param    object    $widget_data    Widget data
     * @return   N8nDash_Widget|null       Widget instance or null
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
     * Check if we should load assets.
     *
     * @since    1.0.0
     * @return   bool    Should load assets
     */
    private function should_load_assets() {
        global $post;
        
        // Always load on dashboard page
        $dashboard_page_id = get_option( 'n8ndash_dashboard_page_id' );
        if ( $dashboard_page_id && is_page( $dashboard_page_id ) ) {
            return true;
        }

        // Check if post contains our shortcodes
        if ( is_singular() && $post ) {
            if ( has_shortcode( $post->post_content, 'n8ndash' ) ||
                 has_shortcode( $post->post_content, 'n8ndash_dashboard' ) ||
                 has_shortcode( $post->post_content, 'n8ndash_widget' ) ||
                 has_shortcode( $post->post_content, 'n8ndash_dashboards' ) ) {
                return true;
            }

            // Check for Gutenberg blocks
            if ( has_blocks( $post ) ) {
                if ( has_block( 'n8ndash/dashboard', $post ) ||
                     has_block( 'n8ndash/widget', $post ) ) {
                    return true;
                }
            }
        }

        // Check for dashboard preview requests
        if ( isset( $_GET['n8ndash_dashboard'] ) ) {
            return true;
        }

        // Allow filtering - this allows manual override
        return apply_filters( 'n8ndash_should_load_assets', false );
    }
}
        