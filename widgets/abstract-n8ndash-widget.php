<?php
/**
 * Abstract base class for all widget types
 *
 * This class provides the foundation for all widget types in the plugin.
 * It defines the common interface and functionality that all widgets must implement.
 *
 * @since      1.2.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/widgets
 * @author     Amit Anand Niraj <queryamit@gmail.com>
 * @author     https://anandtech.in
 * @author     https://github.com/queryamit
 * @author     https://www.linkedin.com/in/queryamit/
 */
abstract class N8nDash_Widget {

    /**
     * Widget ID
     *
     * @since    1.0.0
     * @access   protected
     * @var      int    $id    Widget ID from database
     */
    protected $id;

    /**
     * Widget type
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $type    Widget type identifier
     */
    protected $type;

    /**
     * Widget configuration
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $config    Widget configuration array
     */
    protected $config;

    /**
     * Widget position
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $position    Widget position data
     */
    protected $position;

    /**
     * Dashboard ID
     *
     * @since    1.0.0
     * @access   protected
     * @var      int    $dashboard_id    Parent dashboard ID
     */
    protected $dashboard_id;



    /**
     * Widget data
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $data    Processed data from webhook
     */
    protected $data;

    /**
     * Constructor
     *
     * @since    1.0.0
     * @param    array    $data    Widget data from database
     */
    public function __construct( $data = array() ) {
        if ( ! empty( $data['id'] ) ) {
            $this->id = intval( $data['id'] );
        }
        
        $this->type = $data['widget_type'] ?? $this->get_type();
        $this->config = $data['config'] ?? array();
        $this->position = $data['position'] ?? array();
        $this->dashboard_id = intval( $data['dashboard_id'] ?? 0 );



        // Initialize data storage
        $this->data = array();
    }

    /**
     * Set widget data
     *
     * @since    1.0.0
     * @param    array    $data    Processed data from webhook
     */
    public function set_data($data) {
        $this->data = is_array($data) ? $data : array();
    }

    /**
     * Get widget data
     *
     * @since    1.0.0
     * @return   array    Stored widget data
     */
    public function get_data() {
        return $this->data;
    }

    /**
     * Load data from webhook
     *
     * @since    1.0.0
     * @return   array|WP_Error    Processed data or error
     */
    public function load_data() {
        if (!$this->has_webhook()) {
            return new WP_Error('no_webhook', 'No webhook configured');
        }

        $response = $this->call_webhook();
        
        if (is_wp_error($response)) {
            return $response;
        }

        $processed_data = $this->process_webhook_response($response);
        
        if (is_wp_error($processed_data)) {
            return $processed_data;
        }
        
        $this->set_data($processed_data);
        
        return $processed_data;
    }

    /**
     * Get widget type identifier
     *
     * @since    1.0.0
     * @return   string    Widget type
     */
    abstract public function get_type();

    /**
     * Get widget title
     *
     * @since    1.0.0
     * @return   string    Widget title
     */
    abstract public function get_title();

    /**
     * Render the widget HTML
     *
     * @since    1.0.0
     * @return   string    HTML output
     */
    abstract public function render();

    /**
     * Get configuration form HTML
     *
     * @since    1.0.0
     * @return   string    HTML form
     */
    abstract public function get_config_form();

    /**
     * Validate configuration data
     *
     * @since    1.0.0
     * @param    array    $config    Configuration data to validate
     * @return   array|WP_Error      Validated config or error
     */
    abstract public function validate_config( $config );

    /**
     * Process webhook response data
     *
     * @since    1.0.0
     * @param    mixed    $response    Raw response data
     * @return   array                 Processed data
     */
    abstract public function process_webhook_response( $response );

    /**
     * Get default configuration
     *
     * @since    1.0.0
     * @return   array    Default configuration
     */
    abstract public function get_default_config();

    /**
     * Get default icon for widget type
     *
     * @since    1.0.0
     * @return   string    Icon identifier
     */
    abstract protected function get_default_icon();

    /**
     * Enqueue assets for widget rendering
     *
     * @since    1.0.0
     * @return   void
     */
    public function enqueue_assets() {
        // FIX: Provide no-op default to avoid fatal calls from child classes
    }

    /**
     * Save widget to database
     *
     * @since    1.0.0
     * @return   int|false    Widget ID or false on failure
     */
    public function save() {
        $data = array(
            'id'           => $this->id,
            'dashboard_id' => $this->dashboard_id,
            'widget_type'  => $this->type,
            'title'        => $this->get_title(),
            'config'       => $this->config,
            'position'     => $this->position,
        );
        
        if ( ! empty( $this->config['webhook'] ) ) {
            $data['webhook'] = $this->config['webhook'];
        }
        
        $widget_id = N8nDash_DB::save_widget( $data );
        
        if ( $widget_id ) {
            $this->id = $widget_id;
        }
        
        return $widget_id;
    }

    /**
     * Load widget from database
     *
     * @since    1.0.0
     * @param    int    $id    Widget ID
     * @return   bool          Success status
     */
    public function load( $id ) {
        global $wpdb;
        $tables = N8nDash_DB::get_table_names();
        
        $widget = $wpdb->get_row( $wpdb->prepare(
            "SELECT w.*, wh.url, wh.method, wh.headers 
             FROM {$tables['widgets']} w
             LEFT JOIN {$tables['webhooks']} wh ON w.id = wh.widget_id
             WHERE w.id = %d",
            $id
        ) );
        
        if ( ! $widget ) {
            return false;
        }
        
        $this->id = intval( $widget->id );
        $this->dashboard_id = intval( $widget->dashboard_id );
        $this->type = $widget->widget_type;
        $this->config = json_decode( $widget->config, true ) ?: array();
        $this->position = json_decode( $widget->position, true ) ?: array();
        
        // Add webhook data to config if exists
        if ( ! empty( $widget->url ) ) {
            $this->config['webhook'] = array(
                'url'     => $widget->url,
                'method'  => $widget->method,
                'headers' => json_decode( $widget->headers, true ) ?: array(),
            );
        }
        
        return true;
    }

    /**
     * Delete widget
     *
     * @since    1.0.0
     * @return   bool    Success status
     */
    public function delete() {
        if ( ! $this->id ) {
            return false;
        }
        
        return N8nDash_DB::delete_widget( $this->id );
    }

    /**
     * Check if widget has a valid webhook configured
     * @return bool
     */
    public function has_webhook() {
        return !empty($this->config['webhook']['url']);
    }

    /**
     * Call webhook and get response
     *
     * @since    1.0.0
     * @param    array    $data    Optional data to send
     * @return   array|WP_Error     Response data or error
     */
    public function call_webhook( $data = array() ) {
        if ( empty( $this->config['webhook']['url'] ) ) {
            return new WP_Error( 'no_webhook', __( 'No webhook URL configured', 'n8ndash-pro' ) );
        }
        
        $url = $this->config['webhook']['url'];
        $method = $this->config['webhook']['method'] ?? 'POST';
        $headers = $this->config['webhook']['headers'] ?? array();
        
        // Smart content-type detection for custom widgets
        $has_files = false;
        if ( $this->get_type() === 'custom' && ! empty( $data ) ) {
            $has_files = $this->detect_file_uploads( $data );
        }
        
        // Prepare request arguments
        $args = array(
            'method'  => $method,
            'headers' => array(),
            'timeout' => 30,
            'sslverify' => apply_filters( 'n8ndash_webhook_sslverify', true ),
        );
        
        // Add custom headers
        if ( ! empty( $headers ) && is_array( $headers ) ) {
            foreach ( $headers as $header ) {
                if ( ! empty( $header['key'] ) && ! empty( $header['value'] ) ) {
                    $args['headers'][ $header['key'] ] = $header['value'];
                }
            }
        }
        
        // Add CORS headers if needed
        $args['headers']['Access-Control-Allow-Origin'] = '*';
        
        // Handle request body with smart content-type detection
        if ( $method === 'POST' || $method === 'PUT' ) {
            if ( ! empty( $data ) ) {
                if ( $has_files ) {
                    // For file uploads, use FormData equivalent
                    $args['body'] = $this->prepare_form_data_body( $data );
                    // Don't set Content-Type - let WordPress set it with boundary
                } else {
                    // Default to JSON for custom widgets
                    $args['headers']['Content-Type'] = 'application/json';
                    $args['body'] = wp_json_encode( $data );
                }
            } else {
                // Empty body for POST/PUT requests
                $args['body'] = '';
            }
        } elseif ( $method === 'GET' && ! empty( $data ) && is_array( $data ) ) {
            $url = add_query_arg( $data, $url );
        }
        
        // Make the request
        $response = wp_remote_request( $url, $args );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body( $response );
        $code = wp_remote_retrieve_response_code( $response );
        
        if ( $code >= 200 && $code < 300 ) {
            $content_type = wp_remote_retrieve_header( $response, 'content-type' );
            
            if ( $content_type && strpos( $content_type, 'application/json' ) !== false ) {
                $data = json_decode( $body, true );
                if ( json_last_error() === JSON_ERROR_NONE ) {
                    return $data;
                }
            }
            
            return $body;
        }
        
        return new WP_Error( 
            'webhook_error', 
            sprintf( __( 'Webhook returned %d: %s', 'n8ndash-pro' ), $code, $body ),
            array( 'status' => $code )
        );
    }
    
    /**
     * Detect if data contains file uploads
     *
     * @since    1.0.0
     * @param    array    $data    Data to check
     * @return   bool               True if files are present
     */
    private function detect_file_uploads( $data ) {
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) && isset( $value['tmp_name'] ) && isset( $value['error'] ) ) {
                if ( $value['error'] === UPLOAD_ERR_OK && ! empty( $value['tmp_name'] ) ) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Prepare form data body for file uploads
     *
     * @since    1.0.0
     * @param    array    $data    Form data with files
     * @return   array              Prepared body data
     */
    private function prepare_form_data_body( $data ) {
        $body = array();
        
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) && isset( $value['tmp_name'] ) ) {
                // Handle file upload
                if ( $value['error'] === UPLOAD_ERR_OK && ! empty( $value['tmp_name'] ) ) {
                    // Create file handle for WordPress
                    $body[ $key ] = array(
                        'name'     => $value['name'],
                        'type'     => $value['type'],
                        'tmp_name' => $value['tmp_name'],
                        'error'    => $value['error'],
                        'size'     => $value['size']
                    );
                }
            } else {
                // Handle regular form fields
                $body[ $key ] = $value;
            }
        }
        
        return $body;
    }

    /**
     * Get widget icon HTML
     *
     * @since    1.0.0
     * @return   string    Icon HTML
     */
    protected function get_icon() {
        $icon = $this->config['icon'] ?? $this->get_default_icon();
        
        // Handle dashicons
        if ( $icon && strpos( $icon, 'dashicons-' ) === 0 ) {
            return sprintf( '<span class="dashicons %s"></span>', esc_attr( $icon ) );
        }
        
        // Handle lucide icons (for compatibility with original)
        if ( $icon && strpos( $icon, 'lucide-' ) === 0 ) {
            $icon_name = str_replace( 'lucide-', '', $icon );
            return sprintf( '<i data-lucide="%s"></i>', esc_attr( $icon_name ) );
        }
        
        // Support for custom SVG icons
        if ( $icon && strpos( $icon, '<svg' ) !== false ) {
            return wp_kses( $icon, array(
                'svg'  => array( 
                    'class' => true, 
                    'viewBox' => true, 
                    'xmlns' => true,
                    'width' => true,
                    'height' => true,
                    'fill' => true,
                    'stroke' => true,
                ),
                'path' => array( 
                    'd' => true, 
                    'fill' => true,
                    'stroke' => true,
                    'stroke-width' => true,
                    'stroke-linecap' => true,
                    'stroke-linejoin' => true,
                ),
                'circle' => array(
                    'cx' => true,
                    'cy' => true,
                    'r' => true,
                    'fill' => true,
                    'stroke' => true,
                ),
                'rect' => array(
                    'x' => true,
                    'y' => true,
                    'width' => true,
                    'height' => true,
                    'rx' => true,
                    'ry' => true,
                    'fill' => true,
                    'stroke' => true,
                ),
            ) );
        }
        
        // Default icon
        return '<span class="dashicons dashicons-admin-generic"></span>';
    }

    /**
     * Get value by path from nested array/object
     *
     * @since    1.0.0
     * @param    mixed    $data    Data to search in
     * @param    string   $path    Dot notation path (e.g., 'user.profile.name')
     * @return   mixed              Value at path or null
     */
    protected function get_value_by_path( $data, $path ) {
        if ( empty( $path ) || empty( $data ) ) {
            return null;
        }
        
        $keys = explode( '.', $path );
        $current = $data;
        
        foreach ( $keys as $key ) {
            if ( is_array( $current ) && isset( $current[ $key ] ) ) {
                $current = $current[ $key ];
            } elseif ( is_object( $current ) && isset( $current->$key ) ) {
                $current = $current->$key;
            } else {
                return null;
            }
        }
        
        return $current;
    }

    /**
     * Render webhook configuration fields
     *
     * @since    1.0.0
     * @return   void
     */
    protected function render_webhook_fields() {
        $webhook = $this->config['webhook'] ?? array();
        ?>
        <div class="n8n-form-group">
            <label for="webhook-url-<?php echo esc_attr( $this->id ); ?>">
                <?php esc_html_e( 'Webhook URL', 'n8ndash-pro' ); ?>
            </label>
            <input type="url" 
                   id="webhook-url-<?php echo esc_attr( $this->id ); ?>" 
                   name="webhook[url]" 
                   value="<?php echo esc_url( $webhook['url'] ?? '' ); ?>" 
                   class="large-text" 
                   placeholder="https://your-n8n-instance.com/webhook/..." />
            <p class="description">
                <?php esc_html_e( 'Enter your n8n webhook URL to fetch data', 'n8ndash-pro' ); ?>
            </p>
        </div>
        
        <div class="n8n-form-row">
            <div class="n8n-form-group n8n-form-group--half">
                <label for="webhook-method-<?php echo esc_attr( $this->id ); ?>">
                    <?php esc_html_e( 'Method', 'n8ndash-pro' ); ?>
                </label>
                <select id="webhook-method-<?php echo esc_attr( $this->id ); ?>" 
                        name="webhook[method]"
                        class="regular-text">
                    <option value="POST" <?php selected( $webhook['method'] ?? 'POST', 'POST' ); ?>>POST</option>
                    <option value="GET" <?php selected( $webhook['method'] ?? '', 'GET' ); ?>>GET</option>
                    <option value="PUT" <?php selected( $webhook['method'] ?? '', 'PUT' ); ?>>PUT</option>
                    <option value="DELETE" <?php selected( $webhook['method'] ?? '', 'DELETE' ); ?>>DELETE</option>
                </select>
            </div>
            
            <div class="n8n-form-group n8n-form-group--half">
                <label>
                    <input type="checkbox" 
                           name="webhook[send_as_json]" 
                           value="1" 
                           <?php checked( ! empty( $webhook['send_as_json'] ) ); ?> />
                    <?php esc_html_e( 'Send body as JSON', 'n8ndash-pro' ); ?>
                </label>
            </div>
        </div>
        
        <div class="n8n-form-group">
            <label><?php esc_html_e( 'Headers', 'n8ndash-pro' ); ?></label>
            <div class="n8n-headers-list" id="headers-<?php echo esc_attr( $this->id ); ?>">
                <?php
                $headers = $webhook['headers'] ?? array();
                if ( empty( $headers ) ) {
                    $headers = array( array( 'key' => '', 'value' => '' ) );
                }
                foreach ( $headers as $index => $header ) :
                ?>
                    <div class="n8n-header-row">
                        <input type="text" 
                               name="webhook[headers][<?php echo $index; ?>][key]" 
                               value="<?php echo esc_attr( $header['key'] ?? '' ); ?>" 
                               placeholder="<?php esc_attr_e( 'Header name', 'n8ndash-pro' ); ?>" 
                               class="regular-text" />
                        <input type="text" 
                               name="webhook[headers][<?php echo $index; ?>][value]" 
                               value="<?php echo esc_attr( $header['value'] ?? '' ); ?>" 
                               placeholder="<?php esc_attr_e( 'Header value', 'n8ndash-pro' ); ?>" 
                               class="regular-text" />
                        <button type="button" class="button n8n-remove-header">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button n8n-add-header">
                <span class="dashicons dashicons-plus"></span>
                <?php esc_html_e( 'Add Header', 'n8ndash-pro' ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Validate webhook configuration
     *
     * @since    1.0.0
     * @param    array    $webhook    Webhook data
     * @return   array                Validated webhook data
     */
    protected function validate_webhook_config( $webhook ) {
        $clean = array();
        
        if ( ! empty( $webhook['url'] ) ) {
            $clean['url'] = esc_url_raw( $webhook['url'] );
        }
        
        $clean['method'] = in_array( $webhook['method'] ?? '', array( 'GET', 'POST', 'PUT', 'DELETE' ), true ) 
            ? $webhook['method'] 
            : 'POST';
        
        $clean['send_as_json'] = ! empty( $webhook['send_as_json'] );
        
        // Validate headers
        $clean['headers'] = array();
        if ( ! empty( $webhook['headers'] ) && is_array( $webhook['headers'] ) ) {
            foreach ( $webhook['headers'] as $header ) {
                if ( ! empty( $header['key'] ) ) {
                    $clean['headers'][] = array(
                        'key'   => sanitize_text_field( $header['key'] ),
                        'value' => sanitize_text_field( $header['value'] ?? '' ),
                    );
                }
            }
        }
        
        return $clean;
    }

    /**
     * Debug method to show current widget state
     *
     * @since    1.0.0
     * @return   string    Debug information
     */
    public function debug_info() {
        $info = array(
            'widget_id' => $this->id,
            'widget_type' => $this->get_type(),
            'has_webhook' => $this->has_webhook(),
            'webhook_url' => $this->config['webhook']['url'] ?? 'none',
            'current_data' => $this->get_data(),
            'config_keys' => array_keys($this->config)
        );
        return '<pre style="background:#f5f5f5;padding:10px;font-size:12px;">' .
               esc_html(json_encode($info, JSON_PRETTY_PRINT)) . '</pre>';
    }

    /**
     * Get widget HTML wrapper
     *
     * @since    1.0.0
     * @param    string    $content    Widget content HTML
     * @return   string                Complete widget HTML
     */
    protected function get_widget_wrapper( $content ) {
        $classes = array(
            'n8n-widget',
            'n8n-widget--' . $this->type,
            'n8n-widget--' . ( $this->position['size'] ?? 'regular' ),
        );
        
        if ( ! empty( $this->config['custom_class'] ) ) {
            $classes[] = sanitize_html_class( $this->config['custom_class'] );
        }
        
        // Set default position if not set
        $position = wp_parse_args( $this->position, array(
            'x' => 50,
            'y' => 50,
            'width' => 300,
            'height' => 200
        ) );
        
        $attributes = array(
            'id'              => 'n8n-widget-' . $this->id,
            'class'           => implode( ' ', $classes ),
            'data-widget-id'  => $this->id,
            'data-widget-type' => $this->type,
            'style'           => sprintf(
                'left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
                $position['x'],
                $position['y'],
                $position['width'],
                $position['height']
            ),
        );
        
        if ( ! empty( $this->position ) ) {
            $attributes['data-position'] = esc_attr( wp_json_encode( $this->position ) );
        }
        
        $attr_string = '';
        foreach ( $attributes as $key => $value ) {
            $attr_string .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
        }
        
        ob_start();
        ?>
        <div<?php echo $attr_string; ?>>
            <div class="n8n-widget__header">
                <div class="n8n-widget__title">
                    <div class="n8n-widget__icon">
                        <?php echo $this->get_icon(); ?>
                    </div>
                    <span><?php echo esc_html( $this->get_title() ); ?></span>
                </div>
                <div class="n8n-widget__actions">
                    <span class="badge-main"><?php echo esc_html( $this->config['typeLabel'] ?? ( $this->type === 'custom' ? 'App' : 'Data' ) ); ?></span>
                    <?php $__n8n_has_webhook = $this->has_webhook(); ?>
                        <button class="n8n-widget__action n8n-widget__refresh <?php echo $__n8n_has_webhook ? 'accent' : ''; ?>"
                                data-action="refresh"
                                title="<?php echo esc_attr($__n8n_has_webhook ? __('Refresh','n8ndash-pro') : __('Set webhook URL to enable refresh','n8ndash-pro')); ?>"
                                <?php echo $__n8n_has_webhook ? '' : 'disabled'; ?>>
                            <span class="dashicons dashicons-update"></span>
                        </button>
                    <button class="n8n-widget__action n8n-widget__settings"
                            data-action="settings"
                            title="<?php esc_attr_e( 'Settings', 'n8ndash-pro' ); ?>">
                        <span class="dashicons dashicons-admin-generic"></span>
                    </button>
                    <?php if ( ! current_user_can( 'subscriber' ) ) : ?>
                    <button class="n8n-widget__action n8n-widget__delete"
                            data-action="delete"
                            title="<?php esc_attr_e( 'Delete', 'n8ndash-pro' ); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                    <?php endif; ?>
                    <span class="n8n-widget__drag-handle" title="<?php esc_attr_e( 'Drag to move', 'n8ndash-pro' ); ?>">
                        <span class="dashicons dashicons-move"></span>
                    </span>
                </div>
            </div>
            
            <div class="n8n-widget__body">
                <div class="n8n-widget__content">
                    <?php echo $content; ?>
                </div>
                <div class="n8n-widget__status">
                    <span class="n8n-widget__status-indicator"></span>
                    <span class="n8n-widget__status-text"></span>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}