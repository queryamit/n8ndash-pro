<?php
/**
 * Chart Widget Class
 *
 * Handles line, bar, and pie charts with n8n webhook integration.
 *
 * @since      1.2.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/widgets
 * @author     Amit Anand Niraj <queryamit@gmail.com>
 * @author     https://anandtech.in
 * @author     https://github.com/queryamit
 * @author     https://www.linkedin.com/in/queryamit/
 */
class N8nDash_Chart_Widget extends N8nDash_Widget {

    public function __construct( $id, $config = array() ) {
        // Ensure ID is properly handled - could be array or direct value
        if (is_array($id)) {
            $this->id = isset($id['id']) ? $id['id'] : (isset($id[0]) ? $id[0] : 0);
            // If config is empty, the ID array might contain the config
            if (empty($config) && isset($id['config'])) {
                $config = $id['config'];
            }
        } else {
            $this->id = $id;
        }
        
        $this->config = wp_parse_args( $config, $this->get_default_config() );
    }

    /**
     * Get widget type
     *
     * @since    1.0.0
     * @return   string    Widget type identifier
     */
    public function get_type() {
        return 'chart';
    }

    /**
     * Get widget title
     *
     * @since    1.0.0
     * @return   string    Widget title
     */
    public function get_title() {
        return $this->config['title'] ?? __( 'Chart Widget', 'n8ndash-pro' );
    }

    /**
     * Get default icon
     *
     * @since    1.0.0
     * @return   string    Default icon
     */
    protected function get_default_icon() {
        return 'dashicons-chart-area';
    }

    /**
     * Get default configuration
     *
     * @since    1.0.0
     * @return   array    Default configuration
     */
    public function get_default_config() {
        return array(
            'title'       => __( 'Chart', 'n8ndash-pro' ),
            'subtitle'    => '',
            'chart_type'  => 'line', // line, bar, pie
            'icon'        => 'dashicons-chart-area',
            'labels_path' => 'labels',
            'data_path'   => 'data',
            'dataset_label' => 'Series',
            'y_max_path'  => 'yMax',
            'colors'      => array(),
            'show_legend' => true,
            'show_grid'   => true,
            'animation_duration' => 750,
            'responsive'  => true,
            'demo_labels' => array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun' ),
            'demo_data'   => array( 65, 59, 80, 81, 56, 55 ),
        );
    }

    /**
     * Render the widget
     *
     * @since    1.0.0
     * @return   string    HTML output
     */
    public function render() {
        $config = wp_parse_args( $this->config, $this->get_default_config() );
        
        // Try to load real data from webhook first
        $real_data = null;
        if ( $this->has_webhook() ) {
            $real_data = $this->load_data();
        }
        
        // Use real data if available, otherwise fall back to demo data
        $chart_data = $real_data;
        $data_source = 'webhook';
        
        if ( ! $real_data || is_wp_error( $real_data ) ) {
            $chart_data = $this->get_demo_data( $config );
            $data_source = 'demo';
        }
        
        // Generate unique canvas ID
        $canvas_id = 'n8n-chart-' . $this->id . '-' . wp_rand( 1000, 9999 );
        
        ob_start();
        ?>
        <div class="n8n-chart-widget" 
             data-chart-type="<?php echo esc_attr( $config['chart_type'] ); ?>"
             data-chart-id="<?php echo esc_attr( $canvas_id ); ?>"
             data-widget-id="<?php echo esc_attr( $this->id ); ?>"
             data-data-source="<?php echo esc_attr( $data_source ); ?>">
            
            <div class="n8n-chart-container">
                <canvas id="<?php echo esc_attr( $canvas_id ); ?>"></canvas>
            </div>
            
            <?php if ( ! empty( $config['subtitle'] ) ) : ?>
                <div class="n8n-chart-subtitle">
                    <?php echo esc_html( $config['subtitle'] ); ?>
                </div>
            <?php endif; ?>
            
            <!-- Data source indicator -->
            <div class="n8n-chart-data-source">
                <small class="text-muted">
                    <?php if ( $data_source === 'webhook' ) : ?>
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e( 'Live data from n8n', 'n8ndash-pro' ); ?>
                    <?php else : ?>
                        <span class="dashicons dashicons-chart-line"></span>
                        <?php esc_html_e( 'Demo data', 'n8ndash-pro' ); ?>
                    <?php endif; ?>
                </small>
            </div>
            
            <script type="application/json" class="n8n-chart-config">
                <?php echo wp_json_encode( $this->get_chart_config( $config, $chart_data ) ); ?>
            </script>
        </div>
        <?php
        
        $content = ob_get_clean();
        return $this->get_widget_wrapper( $content );
    }

    /**
     * Get demo data for fallback
     *
     * @since    1.0.0
     * @param    array    $config    Widget configuration
     * @return   array               Demo data structure
     */
    private function get_demo_data( $config ) {
        // Normalize labels and data to arrays
        $labels = isset($config['demo_labels']) ? $config['demo_labels'] : array();
        if (!is_array($labels)) {
            $decoded = json_decode($labels, true);
            if (is_array($decoded)) { 
                $labels = $decoded; 
            } else { 
                $labels = array_filter(array_map('trim', explode(',', (string)$labels))); 
            }
        }
        
        $data_vals = isset($config['demo_data']) ? $config['demo_data'] : array();
        if (!is_array($data_vals)) {
            $decoded = json_decode($data_vals, true);
            if (is_array($decoded)) { 
                $data_vals = $decoded; 
            } else {
                $tmp = array_filter(array_map('trim', explode(',', (string)$data_vals)));
                $data_vals = array_map('floatval', $tmp);
            }
        }
        
        // Fallbacks
        if (empty($labels)) { 
            $labels = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'); 
        }
        if (empty($data_vals)) { 
            $data_vals = array(65, 59, 80, 81, 56, 55); 
        }
        
        return array(
            'labels' => $labels,
            'data' => $data_vals,
            'yMax' => max($data_vals) * 1.2, // 20% above max for better visualization
        );
    }

    /**
     * Get chart configuration for Chart.js
     *
     * @since    1.0.0
     * @param    array    $config    Widget configuration
     * @param    array    $chart_data Chart data (real or demo)
     * @return   array               Chart.js configuration
     */
    private function get_chart_config( $config, $chart_data = null ) {
        // Use provided chart data or fall back to demo data
        if ( ! $chart_data ) {
            $chart_data = $this->get_demo_data( $config );
        }
        
        $labels = $chart_data['labels'] ?? array();
        $data_vals = $chart_data['data'] ?? array();
        $yMax = $chart_data['yMax'] ?? null;
        $dataset_label = !empty($config['dataset_label']) ? $config['dataset_label'] : 'Series';

        $chart_config = array(
            'type' => $config['chart_type'],
            'data' => array(
                'labels' => $labels,
                'datasets' => array(),
            ),
            'options' => array(
                'responsive' => $config['responsive'],
                'maintainAspectRatio' => false,
                'animation' => array(
                    'duration' => $config['animation_duration'],
                ),
                'plugins' => array(
                    'legend' => array(
                        'display' => $config['show_legend'],
                        'position' => 'bottom',
                    ),
                ),
            ),
        );

        // Configure based on chart type
        switch ( $config['chart_type'] ) {
            case 'line':
                $chart_config['data']['datasets'][] = array(
                    'label' => $dataset_label,
                    'data' => $data_vals,
                    'borderColor' => $this->get_accent_color(),
                    'backgroundColor' => $this->get_accent_color( 0.1 ),
                    'tension' => 0.35,
                    'fill' => true,
                );
                $chart_config['options']['scales'] = $this->get_scales_config( $config, $yMax );
                break;

            case 'bar':
                $chart_config['data']['datasets'][] = array(
                    'label' => $dataset_label,
                    'data' => $data_vals,
                    'backgroundColor' => $this->get_accent_color( 0.8 ),
                    'borderColor' => $this->get_accent_color(),
                    'borderWidth' => 1,
                );
                $chart_config['options']['scales'] = $this->get_scales_config( $config, $yMax );
                break;

            case 'pie':
                $chart_config['data']['datasets'][] = array(
                    'data' => $data_vals,
                    'backgroundColor' => $this->get_pie_colors( count( $data_vals ) ),
                    'borderColor' => '#fff',
                    'borderWidth' => 2,
                );
                break;
        }

        return $chart_config;
    }

    /**
     * Get scales configuration
     *
     * @since    1.0.0
     * @param    array    $config    Widget configuration
     * @param    float    $yMax      Optional Y-axis maximum
     * @return   array               Scales configuration
     */
    private function get_scales_config( $config, $yMax = null ) {
        $scales = array(
            'x' => array(
                'grid' => array(
                    'display' => $config['show_grid'],
                    'color' => 'rgba(0, 0, 0, 0.05)',
                ),
                'ticks' => array(
                    'color' => '#666',
                ),
            ),
            'y' => array(
                'grid' => array(
                    'display' => $config['show_grid'],
                    'color' => 'rgba(0, 0, 0, 0.05)',
                ),
                'ticks' => array(
                    'color' => '#666',
                ),
                'beginAtZero' => true,
            ),
        );
        
        // Set Y-axis maximum if provided
        if ( $yMax !== null && is_numeric( $yMax ) ) {
            $scales['y']['suggestedMax'] = floatval( $yMax );
        }
        
        return $scales;
    }

    /**
     * Get accent color based on theme
     *
     * @since    1.0.0
     * @param    float    $alpha    Alpha transparency
     * @return   string             Color value
     */
    private function get_accent_color( $alpha = 1 ) {
        $settings = get_option( 'n8ndash_settings', array() );
        $theme = $settings['default_theme'] ?? 'ocean';
        
        $colors = array(
            'ocean' => '14, 165, 233',
            'emerald' => '34, 197, 94',
            'orchid' => '168, 85, 247',
            'citrus' => '245, 158, 11',
        );
        
        $rgb = $colors[ $theme ] ?? $colors['ocean'];
        return "rgba($rgb, $alpha)";
    }

    /**
     * Get colors for pie chart
     *
     * @since    1.0.0
     * @param    int    $count    Number of colors needed
     * @return   array            Array of colors
     */
    private function get_pie_colors( $count ) {
        $base_colors = array(
            'rgba(14, 165, 233, 0.8)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(107, 114, 128, 0.8)',
        );
        
        $colors = array();
        for ( $i = 0; $i < $count; $i++ ) {
            $colors[] = $base_colors[ $i % count( $base_colors ) ];
        }
        
        return $colors;
    }

    /**
     * Get smart default data paths based on chart type
     *
     * @since    1.0.0
     * @param    string    $chart_type    Chart type (line, bar, pie)
     * @return   array                    Default paths for chart type
     */
    private function get_smart_default_paths( $chart_type ) {
        switch ( $chart_type ) {
            case 'line':
                return array(
                    'labels_path' => 'xLabels',
                    'data_path' => 'series[0].data',
                    'dataset_label' => 'Series'
                );
            case 'bar':
                return array(
                    'labels_path' => 'labels',
                    'data_path' => 'data',
                    'dataset_label' => 'Data'
                );
            case 'pie':
                return array(
                    'labels_path' => 'labels',
                    'data_path' => 'values',
                    'dataset_label' => ''
                );
            default:
                return array(
                    'labels_path' => 'labels',
                    'data_path' => 'data',
                    'dataset_label' => 'Series'
                );
        }
    }

    /**
     * Get configuration form
     *
     * @since    1.0.0
     * @return   string    HTML form
     */
    public function get_config_form() {
        $config = wp_parse_args( $this->config, $this->get_default_config() );
        
        ob_start();
        ?>
        <form class="n8n-widget-config-form" data-widget-id="<?php echo esc_attr( $this->id ); ?>">
            <?php wp_nonce_field( 'n8ndash_save_widget', 'n8ndash_widget_nonce' ); ?>
            <input type="hidden" name="widget_id" value="<?php echo esc_attr( $this->id ); ?>" />
            <input type="hidden" name="widget_type" value="chart" />
            
            <div class="n8n-form-section">
                <h3><?php esc_html_e( 'Basic Settings', 'n8ndash-pro' ); ?></h3>
                
                <div class="n8n-form-group">
                    <label for="widget-title-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Title', 'n8ndash-pro' ); ?>
                        <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="widget-title-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[title]" 
                           value="<?php echo esc_attr( $config['title'] ); ?>" 
                           class="regular-text" 
                           required />
                </div>
                
                <div class="n8n-form-group">
                    <label for="widget-subtitle-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Subtitle', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-subtitle-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[subtitle]" 
                           value="<?php echo esc_attr( $config['subtitle'] ); ?>" 
                           class="regular-text" />
                </div>
                
                <div class="n8n-form-group">
                    <label for="widget-chart-type-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Chart Type', 'n8ndash-pro' ); ?>
                    </label>
                    <select id="widget-chart-type-<?php echo esc_attr( $this->id ); ?>" 
                            name="config[chart_type]" 
                            class="n8n-chart-type-select regular-text">
                        <option value="line" <?php selected( $config['chart_type'], 'line' ); ?>>
                            <?php esc_html_e( 'Line Chart', 'n8ndash-pro' ); ?>
                        </option>
                        <option value="bar" <?php selected( $config['chart_type'], 'bar' ); ?>>
                            <?php esc_html_e( 'Bar Chart', 'n8ndash-pro' ); ?>
                        </option>
                        <option value="pie" <?php selected( $config['chart_type'], 'pie' ); ?>>
                            <?php esc_html_e( 'Pie Chart', 'n8ndash-pro' ); ?>
                        </option>
                    </select>
                </div>
                
                <div class="n8n-form-group">
                    <label>
                        <input type="checkbox" 
                               name="config[show_legend]" 
                               value="1" 
                               <?php checked( $config['show_legend'] ); ?> />
                        <?php esc_html_e( 'Show legend', 'n8ndash-pro' ); ?>
                    </label>
                </div>
                
                <div class="n8n-form-group n8n-chart-grid-option" style="<?php echo $config['chart_type'] === 'pie' ? 'display:none;' : ''; ?>">
                    <label>
                        <input type="checkbox" 
                               name="config[show_grid]" 
                               value="1" 
                               <?php checked( $config['show_grid'] ); ?> />
                        <?php esc_html_e( 'Show grid lines', 'n8ndash-pro' ); ?>
                    </label>
                </div>
            </div>
            
            <!-- Webhook configuration is handled by the admin dashboard modal -->
            <!-- Removed duplicate webhook section to avoid duplication -->
            
            <div class="n8n-form-section n8n-data-mapping">
                <h3><?php esc_html_e( 'Data Mapping', 'n8ndash-pro' ); ?></h3>
                
                <div class="n8n-form-group">
                    <label for="widget-labels-path-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Labels Path', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-labels-path-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[labels_path]" 
                           value="<?php echo esc_attr( $config['labels_path'] ); ?>" 
                           class="regular-text" 
                           placeholder="labels" />
                    <p class="description">
                        <?php esc_html_e( 'JSON path to the array of labels (e.g., "data.labels" or "months")', 'n8ndash-pro' ); ?>
                    </p>
                </div>
                
                <div class="n8n-form-group">
                    <label for="widget-data-path-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Data Path', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-data-path-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[data_path]" 
                           value="<?php echo esc_attr( $config['data_path'] ); ?>" 
                           class="regular-text" 
                           placeholder="data" />
                    <p class="description">
                        <?php esc_html_e( 'JSON path to the data array (e.g., "data.values" or "series[0].data")', 'n8ndash-pro' ); ?>
                    </p>
                </div>
                
                <div class="n8n-form-group n8n-chart-dataset-label" style="<?php echo $config['chart_type'] === 'pie' ? 'display:none;' : ''; ?>">
                    <label for="widget-dataset-label-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Dataset Label', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-dataset-label-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[dataset_label]" 
                           value="<?php echo esc_attr( $config['dataset_label'] ); ?>" 
                           class="regular-text" />
                </div>
                
                <div class="n8n-form-group n8n-chart-ymax" style="<?php echo $config['chart_type'] === 'pie' ? 'display:none;' : ''; ?>">
                    <label for="widget-ymax-path-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Y-Axis Max Path (Optional)', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-ymax-path-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[y_max_path]" 
                           value="<?php echo esc_attr( $config['y_max_path'] ); ?>" 
                           class="regular-text" 
                           placeholder="yMax" />
                </div>
            </div>
            
            <div class="n8n-form-section">
                <h3><?php esc_html_e( 'Demo Data', 'n8ndash-pro' ); ?></h3>
                <p class="description">
                    <?php esc_html_e( 'This data is shown before the first webhook call', 'n8ndash-pro' ); ?>
                </p>
                
                <div class="n8n-form-group">
                    <label for="widget-demo-labels-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Demo Labels (comma-separated)', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-demo-labels-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[demo_labels]" 
                           value="<?php echo esc_attr( implode( ', ', $config['demo_labels'] ) ); ?>" 
                           class="large-text" />
                </div>
                
                <div class="n8n-form-group">
                    <label for="widget-demo-data-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Demo Data (comma-separated numbers)', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-demo-data-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[demo_data]" 
                           value="<?php echo esc_attr( implode( ', ', $config['demo_data'] ) ); ?>" 
                           class="large-text" />
                </div>
            </div>
            
            <div class="n8n-form-actions">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'Save Widget', 'n8ndash-pro' ); ?>
                </button>
                <button type="button" class="button n8n-cancel-config">
                    <?php esc_html_e( 'Cancel', 'n8ndash-pro' ); ?>
                </button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Validate configuration
     *
     * @since    1.0.0
     * @param    array    $config    Configuration data
     * @return   array|WP_Error      Validated config or error
     */
    public function validate_config( $config ) {
        $errors = new WP_Error();
        
        // Validate title
        if ( empty( $config['title'] ) ) {
            $errors->add( 'title_required', __( 'Widget title is required', 'n8ndash-pro' ) );
        }
        
        // Validate chart type
        if ( ! in_array( $config['chart_type'] ?? 'line', array( 'line', 'bar', 'pie' ), true ) ) {
            $errors->add( 'invalid_chart_type', __( 'Invalid chart type', 'n8ndash-pro' ) );
        }
        
        if ( $errors->has_errors() ) {
            return $errors;
        }
        
        // Sanitize and return
        $clean = array(
            'title'       => sanitize_text_field( $config['title'] ),
            'subtitle'    => sanitize_text_field( $config['subtitle'] ?? '' ),
            'chart_type'  => sanitize_text_field( $config['chart_type'] ?? 'line' ),
            'icon'        => sanitize_text_field( $config['icon'] ?? $this->get_default_icon() ),
            'labels_path' => sanitize_text_field( $config['labels_path'] ?? 'labels' ),
            'data_path'   => sanitize_text_field( $config['data_path'] ?? 'data' ),
            'dataset_label' => sanitize_text_field( $config['dataset_label'] ?? 'Series' ),
            'y_max_path'  => sanitize_text_field( $config['y_max_path'] ?? 'yMax' ),
            'show_legend' => ! empty( $config['show_legend'] ),
            'show_grid'   => ! empty( $config['show_grid'] ),
            'animation_duration' => intval( $config['animation_duration'] ?? 750 ),
            'responsive'  => ! empty( $config['responsive'] ?? true ),
        );
        
        // Handle demo labels
        if ( ! empty( $config['demo_labels'] ) ) {
            if ( is_string( $config['demo_labels'] ) ) {
                $labels = array_map( 'trim', explode( ',', $config['demo_labels'] ) );
            } else {
                $labels = (array) $config['demo_labels'];
            }
            $clean['demo_labels'] = array_map( 'sanitize_text_field', $labels );
        } else {
            $clean['demo_labels'] = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun' );
        }
        
        // Handle demo data
        if ( ! empty( $config['demo_data'] ) ) {
            if ( is_string( $config['demo_data'] ) ) {
                $data = array_map( 'trim', explode( ',', $config['demo_data'] ) );
            } else {
                $data = (array) $config['demo_data'];
            }
            $clean['demo_data'] = array_map( 'floatval', $data );
        } else {
            $clean['demo_data'] = array( 65, 59, 80, 81, 56, 55 );
        }
        
        // Validate webhook if present
        if ( ! empty( $config['webhook'] ) ) {
            $clean['webhook'] = $this->validate_webhook_config( $config['webhook'] );
        }
        
        return $clean;
    }

    /**
     * Process webhook response
     *
     * @since    1.0.0
     * @param    mixed    $response    Response data
     * @return   array                 Processed data
     */
    public function process_webhook_response( $response ) {
        $config = wp_parse_args( $this->config, $this->get_default_config() );
        
        // Get smart default paths for this chart type
        $smart_paths = $this->get_smart_default_paths( $config['chart_type'] );
        
        // Use configured paths or fall back to smart defaults
        $labels_path = $config['labels_path'] ?: $smart_paths['labels_path'];
        $data_path = $config['data_path'] ?: $smart_paths['data_path'];
        
        $processed = array(
            'success' => true,
            'timestamp' => current_time( 'timestamp' ),
        );
        
        // Extract labels using the correct path
        $labels = $this->get_value_by_path( $response, $labels_path );
        if ( is_array( $labels ) ) {
            $processed['labels'] = array_map( 'sanitize_text_field', $labels );
        } else {
            // Fallback: try alternative paths
            $fallback_paths = array( 'labels', 'xLabels', 'months', 'days' );
            foreach ( $fallback_paths as $fallback_path ) {
                $labels = $this->get_value_by_path( $response, $fallback_path );
                if ( is_array( $labels ) ) {
                    $processed['labels'] = array_map( 'sanitize_text_field', $labels );
                    break;
                }
            }
            
            if ( empty( $processed['labels'] ) ) {
                $processed['labels'] = array();
            }
        }
        
        // Extract data using the correct path
        $data = $this->get_value_by_path( $response, $data_path );
        if ( is_array( $data ) ) {
            $processed['data'] = array_map( 'floatval', $data );
        } else {
            // Fallback: try alternative paths based on chart type
            $fallback_paths = array();
            switch ( $config['chart_type'] ) {
                case 'line':
                    $fallback_paths = array( 'series[0].data', 'data', 'values', 'revenue' );
                    break;
                case 'bar':
                    $fallback_paths = array( 'data', 'values', 'traffic', 'visits' );
                    break;
                case 'pie':
                    $fallback_paths = array( 'values', 'data', 'percentages' );
                    break;
            }
            
            foreach ( $fallback_paths as $fallback_path ) {
                $data = $this->get_value_by_path( $response, $fallback_path );
                if ( is_array( $data ) ) {
                    $processed['data'] = array_map( 'floatval', $data );
                    break;
                }
            }
            
            if ( empty( $processed['data'] ) ) {
                $processed['data'] = array();
            }
        }
        
        // Extract Y-axis max if specified
        if ( ! empty( $config['y_max_path'] ) ) {
            $yMax = $this->get_value_by_path( $response, $config['y_max_path'] );
            if ( is_numeric( $yMax ) ) {
                $processed['yMax'] = floatval( $yMax );
            }
        }
        
        // For pie charts, ensure labels and data arrays have same length
        if ( $config['chart_type'] === 'pie' ) {
            $count = min( count( $processed['labels'] ), count( $processed['data'] ) );
            $processed['labels'] = array_slice( $processed['labels'], 0, $count );
            $processed['data'] = array_slice( $processed['data'], 0, $count );
        }
        
        // Processing complete
        
        return $processed;
    }
}