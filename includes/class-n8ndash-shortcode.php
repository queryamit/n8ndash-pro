<?php
/**
 * Shortcode functionality
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/includes
 * @author     Amit Anand Niraj <n8ndash@gmail.com>
 */

class N8nDash_Shortcode {

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
     * Register shortcodes
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        // FIX: Avoid conflicts with public shortcodes; register only the legacy 'n8ndash' tag
        add_shortcode( 'n8ndash', array( $this, 'render_dashboard_shortcode' ) );
    }

    /**
     * Render dashboard shortcode
     *
     * @since    1.0.0
     * @param    array     $atts    Shortcode attributes
     * @param    string    $content Shortcode content
     * @return   string             HTML output
     */
    public function render_dashboard_shortcode( $atts, $content = null ) {
        // FIX: Delegate rendering to public-facing class to avoid duplication and keep schema aligned
        if ( class_exists( 'N8nDash_Public' ) ) {
            $public = new N8nDash_Public( $this->plugin_name, $this->version );
            // Reuse the modern shortcode that supports id/height/theme
            return $public->render_dashboard_shortcode( $atts );
        }
        return '';
    }

    /**
     * Render widget shortcode
     *
     * @since    1.0.0
     * @param    array     $atts    Shortcode attributes
     * @param    string    $content Shortcode content
     * @return   string             HTML output
     */
    public function render_widget_shortcode( $atts, $content = null ) {
        // FIX: Delegate rendering to public-facing class to avoid duplication and keep schema aligned
        if ( class_exists( 'N8nDash_Public' ) ) {
            $public = new N8nDash_Public( $this->plugin_name, $this->version );
            return $public->render_widget_shortcode( $atts );
        }
        return '';
    }

    /**
     * Render a widget
     *
     * @since    1.0.0
     * @param    object    $widget_data    Widget data from database
     * @return   string                    HTML output
     */
    private function render_widget( $widget_data ) {
        // Get widget class
        $widget_class = $this->get_widget_class( $widget_data->type );
        if ( ! $widget_class ) {
            return '<div class="n8ndash-widget-error">' . 
                   sprintf( esc_html__( 'Widget type "%s" not found.', 'n8ndash-pro' ), $widget_data->type ) . 
                   '</div>';
        }

        // Create widget instance
        $widget = new $widget_class(
            $widget_data->id,
            json_decode( $widget_data->config, true ),
            json_decode( $widget_data->webhook_config, true )
        );

        // Build widget wrapper
        $classes = array( 'n8ndash-widget', 'n8ndash-widget-' . $widget_data->type );
        if ( ! empty( $widget_data->custom_class ) ) {
            $classes[] = $widget_data->custom_class;
        }

        $styles = array();
        if ( empty( $widget_data->standalone ) ) {
            $styles[] = 'position: absolute';
            $styles[] = 'left: ' . intval( $widget_data->position_x ) . 'px';
            $styles[] = 'top: ' . intval( $widget_data->position_y ) . 'px';
        }
        $styles[] = 'width: ' . intval( $widget_data->width ) . 'px';
        $styles[] = 'height: ' . intval( $widget_data->height ) . 'px';

        $output = '<div id="n8ndash-widget-' . esc_attr( $widget_data->id ) . '" ';
        $output .= 'class="' . esc_attr( implode( ' ', $classes ) ) . '" ';
        $output .= 'data-widget-id="' . esc_attr( $widget_data->id ) . '" ';
        $output .= 'data-widget-type="' . esc_attr( $widget_data->type ) . '" ';
        $output .= 'style="' . esc_attr( implode( '; ', $styles ) ) . '">';
        $output .= $widget->render();
        $output .= '</div>';

        return $output;
    }

    /**
     * Get widget class name
     *
     * @since    1.0.0
     * @param    string    $type    Widget type
     * @return   string|false      Class name or false if not found
     */
    private function get_widget_class( $type ) {
        $widget_classes = array(
            'data'   => 'N8nDash_Data_Widget',
            'chart'  => 'N8nDash_Chart_Widget',
            'custom' => 'N8nDash_Custom_Widget',
        );

        /**
         * Filter widget classes
         *
         * @since    1.0.0
         * @param    array    $widget_classes    Array of widget type => class name
         */
        $widget_classes = apply_filters( 'n8ndash_widget_classes', $widget_classes );

        return isset( $widget_classes[ $type ] ) ? $widget_classes[ $type ] : false;
    }

    /**
     * Enqueue dashboard assets
     *
     * @since    1.0.0
     */
    private function enqueue_dashboard_assets() {
        // Core styles
        wp_enqueue_style( 
            'n8ndash-public', 
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/public/n8ndash-public.css', 
            array(), 
            $this->version, 
            'all' 
        );

        // Widget styles
        wp_enqueue_style( 
            'n8ndash-widgets', 
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/public/n8ndash-widgets.css', 
            array( 'n8ndash-public' ), 
            $this->version, 
            'all' 
        );

        // Core scripts
        wp_enqueue_script( 
            'n8ndash-public', 
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/public/n8ndash-public.js', 
            array( 'jquery' ), 
            $this->version, 
            true 
        );

        // Widget scripts
        wp_enqueue_script( 
            'n8ndash-widgets', 
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/public/n8ndash-widgets.js', 
            array( 'n8ndash-public' ), 
            $this->version, 
            true 
        );

        // Chart.js for chart widgets
        wp_enqueue_script( 
            'chartjs', 
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js', 
            array(), 
            '4.4.0', 
            true 
        );

        // Localize script
        wp_localize_script( 'n8ndash-public', 'n8ndash_public', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'rest_url' => rest_url( 'n8ndash/v1/' ),
            'nonce'    => wp_create_nonce( 'n8ndash_public_nonce' ),
            'i18n'     => array(
                'loading'    => __( 'Loading...', 'n8ndash-pro' ),
                'error'      => __( 'Error loading data', 'n8ndash-pro' ),
                'no_data'    => __( 'No data available', 'n8ndash-pro' ),
                'refresh'    => __( 'Refresh', 'n8ndash-pro' ),
                'updated'    => __( 'Updated', 'n8ndash-pro' ),
            ),
        ) );
    }
}