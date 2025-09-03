<?php
/**
 * Data Widget Class
 *
 * Handles KPI metrics and list displays with n8n webhook integration.
 *
 * @since      1.2.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/widgets
 * @author     Amit Anand Niraj <queryamit@gmail.com>
 * @author     https://anandtech.in
 * @author     https://github.com/queryamit
 * @author     https://www.linkedin.com/in/queryamit/
 */
class N8nDash_Data_Widget extends N8nDash_Widget {

    /**
     * Get widget type
     *
     * @since    1.0.0
     * @return   string    Widget type identifier
     */
    public function get_type() {
        return 'data';
    }

    /**
     * Get widget title
     *
     * @since    1.0.0
     * @return   string    Widget title
     */
    public function get_title() {
        return $this->config['title'] ?? __( 'Data Widget', 'n8ndash-pro' );
    }

    /**
     * Get default icon
     *
     * @since    1.0.0
     * @return   string    Default icon
     */
    protected function get_default_icon() {
        return 'dashicons-chart-line';
    }

    /**
     * Get default configuration
     *
     * @since    1.0.0
     * @return   array    Default configuration
     */
    public function get_default_config() {
        return array(
            'title'       => __( 'KPI Metric', 'n8ndash-pro' ),
            'subtitle'    => __( 'Updated just now', 'n8ndash-pro' ),
            'mode'        => 'kpi', // kpi or list
            'icon'        => 'dashicons-chart-line',
            'value1Path'  => 'value1', // matches n8ndash-pro
            'value2Path'  => 'value2', // matches n8ndash-pro
            'value3UrlPath' => 'value3Url', // matches n8ndash-pro
            'listPath'    => 'items', // matches n8ndash-pro
            'itemLabelPath' => 'title', // matches n8ndash-pro
            'itemUrlPath' => 'url', // matches n8ndash-pro
            'demo_value1' => '$0',
            'demo_value2' => '+0%',
            'demo_list'   => array(),
            'refresh_interval' => 0, // 0 = manual only
            'show_last_updated' => true,
            'use_demo_data' => true,
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
        $data = $this->get_data();
        
        // Data widget render debug info
        // Widget ID, Type, Webhook status, Config, Data count processed
        
        // Determine if we should use real data or demo data
        $use_real_data = !empty($data) && !empty($data['value1']);
        // Use real data or demo data based on availability
        
        ob_start();
        ?>
        <div class="n8n-data-widget" data-mode="<?php echo esc_attr( $config['mode'] ); ?>">
            <?php if ( $config['mode'] === 'kpi' ) : ?>
                <div class="n8n-kpi">
                    <div class="n8n-kpi__value">
                        <span class="n8n-kpi__main" id="value1-<?php echo esc_attr( $this->id ); ?>">
                            <?php echo $use_real_data ? 
                                esc_html($data['value1']) : 
                                esc_html($config['demo_value1']); ?>
                        </span>
                        <?php 
                        $value2 = $use_real_data ? ($data['value2'] ?? '') : $config['demo_value2'];
                        $delta_class = 'n8n-kpi__delta';
                        if ( $value2 && strpos($value2, '-') === 0 ) {
                            $delta_class .= ' n8n-kpi__delta--down';
                        } elseif ( $value2 && $value2 !== '0' ) {
                            $delta_class .= ' n8n-kpi__delta--up';
                        }
                        ?>
                        <span class="<?php echo esc_attr( $delta_class ); ?>" 
                              id="value2-<?php echo esc_attr( $this->id ); ?>">
                            <?php echo esc_html($value2); ?>
                        </span>
                    </div>
                    <div class="n8n-kpi__subtitle">
                        <?php echo esc_html( $config['subtitle'] ); ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="n8n-list">
                    <ul class="n8n-list__items" id="list-<?php echo esc_attr( $this->id ); ?>">
                        <?php 
                        $items = $use_real_data ? ($data['items'] ?? []) : $config['demo_list'];
                        if ( ! empty($items) && is_array($items) ) : 
                            foreach ( $items as $item ) : ?>
                                <li class="n8n-list__item">
                                    <a href="<?php echo esc_url( $item['url'] ?? '#' ); ?>" 
                                       target="_blank"
                                       rel="noopener noreferrer">
                                        <?php echo esc_html($item['label'] ?? $item['text'] ?? __( 'Item', 'n8ndash-pro' )); ?>
                                    </a>
                                </li>
                            <?php endforeach; 
                        else : ?>
                            <li class="n8n-list__item n8n-list__item--empty">
                                <?php esc_html_e( 'No items yet. Configure webhook to load data.', 'n8ndash-pro' ); ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if ( $config['show_last_updated'] ) : ?>
                <div class="n8n-widget__footer">
                    <span class="n8n-widget__last-updated" id="updated-<?php echo esc_attr( $this->id ); ?>">
                        <?php 
                        if ($use_real_data && !empty($data['timestamp'])) {
                            echo esc_html(sprintf(__('Updated %s ago', 'n8ndash-pro'), 
                                human_time_diff($data['timestamp'])));
                        } else {
                            esc_html_e( 'Not updated yet', 'n8ndash-pro' );
                        }
                        ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <!-- Debug Info (only show in debug mode) -->
            <?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
                <div class="n8n-widget-debug" style="background:#f0f8ff;padding:10px;border:1px solid #0066cc;border-radius:5px;margin:10px 0;font-size:11px;">
                    <strong>Debug Info:</strong><br>
                    Has Webhook: <?php echo $this->has_webhook() ? 'Yes' : 'No'; ?><br>
                    Webhook URL: <?php echo esc_html($this->config['webhook']['url'] ?? 'Not set'); ?><br>
                    Data Count: <?php echo count($data); ?><br>
                    Use Real Data: <?php echo $use_real_data ? 'Yes' : 'No'; ?><br>
                    Mode: <?php echo esc_html($config['mode']); ?><br>
                    Value1 Path: <?php echo esc_html($config['value1Path']); ?><br>
                    Value2 Path: <?php echo esc_html($config['value2Path']); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        
        $content = ob_get_clean();
        // Data widget render completed
        return $this->get_widget_wrapper( $content );
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
            <input type="hidden" name="widget_type" value="data" />
            
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
                    <label for="widget-icon-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Icon', 'n8ndash-pro' ); ?>
                    </label>
                    <select id="widget-icon-<?php echo esc_attr( $this->id ); ?>" 
                            name="config[icon]" 
                            class="regular-text">
                        <option value="dashicons-chart-line" <?php selected( $config['icon'], 'dashicons-chart-line' ); ?>>
                            <?php esc_html_e( 'Chart Line', 'n8ndash-pro' ); ?>
                        </option>
                        <option value="dashicons-chart-bar" <?php selected( $config['icon'], 'dashicons-chart-bar' ); ?>>
                            <?php esc_html_e( 'Chart Bar', 'n8ndash-pro' ); ?>
                        </option>
                        <option value="dashicons-chart-pie" <?php selected( $config['icon'], 'dashicons-chart-pie' ); ?>>
                            <?php esc_html_e( 'Chart Pie', 'n8ndash-pro' ); ?>
                        </option>
                        <option value="dashicons-money-alt" <?php selected( $config['icon'], 'dashicons-money-alt' ); ?>>
                            <?php esc_html_e( 'Money', 'n8ndash-pro' ); ?>
                        </option>
                        <option value="dashicons-groups" <?php selected( $config['icon'], 'dashicons-groups' ); ?>>
                            <?php esc_html_e( 'Users', 'n8ndash-pro' ); ?>
                        </option>
                        <option value="dashicons-cart" <?php selected( $config['icon'], 'dashicons-cart' ); ?>>
                            <?php esc_html_e( 'Cart', 'n8ndash-pro' ); ?>
                        </option>
                        <option value="dashicons-analytics" <?php selected( $config['icon'], 'dashicons-analytics' ); ?>>
                            <?php esc_html_e( 'Analytics', 'n8ndash-pro' ); ?>
                        </option>
                    </select>
                </div>
                
                <div class="n8n-form-group">
                    <label for="widget-mode-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Display Mode', 'n8ndash-pro' ); ?>
                    </label>
                    <select id="widget-mode-<?php echo esc_attr( $this->id ); ?>" 
                            name="config[mode]" 
                            class="n8n-widget-mode-select regular-text">
                        <option value="kpi" <?php selected( $config['mode'], 'kpi' ); ?>>
                            <?php esc_html_e( 'KPI (Key Performance Indicator)', 'n8ndash-pro' ); ?>
                        </option>
                        <option value="list" <?php selected( $config['mode'], 'list' ); ?>>
                            <?php esc_html_e( 'Link List', 'n8ndash-pro' ); ?>
                        </option>
                    </select>
                </div>
                
                <div class="n8n-form-group">
                    <label>
                        <input type="checkbox" 
                               name="config[show_last_updated]" 
                               value="1" 
                               <?php checked( $config['show_last_updated'] ); ?> />
                        <?php esc_html_e( 'Show last updated timestamp', 'n8ndash-pro' ); ?>
                    </label>
                </div>
            </div>
            
            <!-- Webhook configuration is handled by the admin dashboard modal -->
            <!-- Removed duplicate webhook section to avoid duplication -->
            
            <div class="n8n-form-section n8n-data-mapping" data-mode="kpi" style="<?php echo $config['mode'] !== 'kpi' ? 'display:none;' : ''; ?>">
                <h3><?php esc_html_e( 'KPI Data Mapping', 'n8ndash-pro' ); ?></h3>
                
                <div class="n8n-form-group">
                    <label for="widget-value1-path-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Main Value Path', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-value1-path-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[value1Path]" 
                           value="<?php echo esc_attr( $config['value1Path'] ); ?>" 
                           class="regular-text" 
                           placeholder="value1" />
                    <p class="description">
                        <?php esc_html_e( 'JSON path to the main value (e.g., "data.revenue" or "stats.total")', 'n8ndash-pro' ); ?>
                    </p>
                </div>
                
                <div class="n8n-form-group">
                    <label for="widget-value2-path-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Delta/Change Path', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-value2-path-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[value2Path]" 
                           value="<?php echo esc_attr( $config['value2Path'] ); ?>" 
                           class="regular-text" 
                           placeholder="value2" />
                    <p class="description">
                        <?php esc_html_e( 'JSON path to the change value (e.g., "data.change" or "stats.delta")', 'n8ndash-pro' ); ?>
                    </p>
                </div>
                
                <div class="n8n-form-group">
                    <label for="widget-value3-url-path-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Link URL Path (Optional)', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-value3-url-path-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[value3UrlPath]" 
                           value="<?php echo esc_attr( $config['value3UrlPath'] ); ?>" 
                           class="regular-text" 
                           placeholder="value3Url" />
                    <p class="description">
                        <?php esc_html_e( 'JSON path to make the main value clickable', 'n8ndash-pro' ); ?>
                    </p>
                </div>
            </div>
            
            <div class="n8n-form-section n8n-data-mapping" data-mode="list" style="<?php echo $config['mode'] !== 'list' ? 'display:none;' : ''; ?>">
                <h3><?php esc_html_e( 'List Data Mapping', 'n8ndash-pro' ); ?></h3>
                
                <div class="n8n-form-group">
                    <label for="widget-list-path-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'List Array Path', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-list-path-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[listPath]" 
                           value="<?php echo esc_attr( $config['listPath'] ); ?>" 
                           class="regular-text" 
                           placeholder="items" />
                    <p class="description">
                        <?php esc_html_e( 'JSON path to the array of items (e.g., "data.items" or "results")', 'n8ndash-pro' ); ?>
                    </p>
                </div>
                
                <div class="n8n-form-group">
                    <label for="widget-item-label-path-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Item Label Path', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-item-label-path-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[itemLabelPath]" 
                           value="<?php echo esc_attr( $config['itemLabelPath'] ); ?>" 
                           class="regular-text" 
                           placeholder="title" />
                    <p class="description">
                        <?php esc_html_e( 'Path within each item for the display text', 'n8ndash-pro' ); ?>
                    </p>
                </div>
                
                <div class="n8n-form-group">
                    <label for="widget-item-url-path-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Item URL Path', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-item-url-path-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[itemUrlPath]" 
                           value="<?php echo esc_attr( $config['itemUrlPath'] ); ?>" 
                           class="regular-text" 
                           placeholder="url" />
                    <p class="description">
                        <?php esc_html_e( 'Path within each item for the link URL', 'n8ndash-pro' ); ?>
                    </p>
                </div>
            </div>
            
            <div class="n8n-form-section">
                <h3><?php esc_html_e( 'Demo Data', 'n8ndash-pro' ); ?></h3>
                <p class="description">
                    <?php esc_html_e( 'This data is shown before the first webhook call', 'n8ndash-pro' ); ?>
                </p>
                
                <div class="n8n-demo-data" data-mode="kpi" style="<?php echo $config['mode'] !== 'kpi' ? 'display:none;' : ''; ?>">
                    <div class="n8n-form-group">
                        <label for="widget-demo-value1-<?php echo esc_attr( $this->id ); ?>">
                            <?php esc_html_e( 'Demo Main Value', 'n8ndash-pro' ); ?>
                        </label>
                        <input type="text" 
                               id="widget-demo-value1-<?php echo esc_attr( $this->id ); ?>" 
                               name="config[demo_value1]" 
                               value="<?php echo esc_attr( $config['demo_value1'] ); ?>" 
                               class="regular-text" />
                    </div>
                    
                    <div class="n8n-form-group">
                        <label for="widget-demo-value2-<?php echo esc_attr( $this->id ); ?>">
                            <?php esc_html_e( 'Demo Delta Value', 'n8ndash-pro' ); ?>
                        </label>
                        <input type="text" 
                               id="widget-demo-value2-<?php echo esc_attr( $this->id ); ?>" 
                               name="config[demo_value2]" 
                               value="<?php echo esc_attr( $config['demo_value2'] ); ?>" 
                               class="regular-text" />
                    </div>
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
        
        // Validate webhook URL if provided
        if ( ! empty( $config['webhook']['url'] ) && ! filter_var( $config['webhook']['url'], FILTER_VALIDATE_URL ) ) {
            $errors->add( 'invalid_webhook_url', __( 'Invalid webhook URL', 'n8ndash-pro' ) );
        }
        
        // Validate mode
        if ( ! in_array( $config['mode'] ?? 'kpi', array( 'kpi', 'list' ), true ) ) {
            $errors->add( 'invalid_mode', __( 'Invalid display mode', 'n8ndash-pro' ) );
        }
        
        if ( $errors->has_errors() ) {
            return $errors;
        }
        
        // Sanitize and return
        $clean = array(
            'title'       => sanitize_text_field( $config['title'] ),
            'subtitle'    => sanitize_text_field( $config['subtitle'] ?? '' ),
            'mode'        => sanitize_text_field( $config['mode'] ?? 'kpi' ),
            'icon'        => sanitize_text_field( $config['icon'] ?? $this->get_default_icon() ),
            'value1Path'  => sanitize_text_field( $config['value1Path'] ?? 'value1' ),
            'value2Path'  => sanitize_text_field( $config['value2Path'] ?? 'value2' ),
            'value3UrlPath' => sanitize_text_field( $config['value3UrlPath'] ?? 'value3Url' ),
            'listPath'    => sanitize_text_field( $config['listPath'] ?? 'items' ),
            'itemLabelPath' => sanitize_text_field( $config['itemLabelPath'] ?? 'title' ),
            'itemUrlPath' => sanitize_text_field( $config['itemUrlPath'] ?? 'url' ),
            'demo_value1' => sanitize_text_field( $config['demo_value1'] ?? '$0' ),
            'demo_value2' => sanitize_text_field( $config['demo_value2'] ?? '+0%' ),
            'show_last_updated' => ! empty( $config['show_last_updated'] ),
            'refresh_interval' => intval( $config['refresh_interval'] ?? 0 ),
            'use_demo_data' => ! empty( $config['use_demo_data'] ),
        );
        
        // Handle demo list for list mode
        if ( $clean['mode'] === 'list' && ! empty( $config['demo_list'] ) && is_array( $config['demo_list'] ) ) {
            $clean['demo_list'] = array();
            foreach ( $config['demo_list'] as $item ) {
                if ( is_array( $item ) ) {
                    $clean['demo_list'][] = array(
                        'text' => sanitize_text_field( $item['text'] ?? '' ),
                        'url'  => esc_url_raw( $item['url'] ?? '#' ),
                    );
                }
            }
        } else {
            $clean['demo_list'] = array();
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
        
        $processed = array(
            'success' => true,
            'timestamp' => current_time( 'timestamp' ),
        );
        
        if ( $config['mode'] === 'kpi' ) {
            // Extract KPI values
            $processed['value1'] = $this->get_value_by_path( $response, $config['value1Path'] );
            $processed['value2'] = $this->get_value_by_path( $response, $config['value2Path'] );
            $processed['value3_url'] = $this->get_value_by_path( $response, $config['value3UrlPath'] );
            
            // Format values if needed
            if ( $processed['value1'] === null ) {
                $processed['value1'] = __( 'N/A', 'n8ndash-pro' );
            }
            
            if ( $processed['value2'] !== null && is_numeric( $processed['value2'] ) ) {
                // Add + sign for positive numbers
                if ( $processed['value2'] > 0 ) {
                    $processed['value2'] = '+' . $processed['value2'];
                }
            }
        } else {
            // Extract list items
            $items = $this->get_value_by_path( $response, $config['listPath'] );
            
            $processed['items'] = array();
            
            if ( is_array( $items ) ) {
                foreach ( $items as $index => $item ) {
                    $label = $this->get_value_by_path( $item, $config['itemLabelPath'] );
                    $url = $this->get_value_by_path( $item, $config['itemUrlPath'] );
                    
                    if ( $label ) {
                        $processed['items'][] = array(
                            'label' => sanitize_text_field( $label ),
                            'url'   => esc_url_raw( $url ?: '#' ),
                        );
                    }
                }
            }
            
            $processed['item_count'] = count( $processed['items'] );
        }
        
        return $processed;
    }
}