<?php
/**
 * Custom Widget Class
 *
 * Handles form-based widgets with customizable fields and n8n webhook integration.
 *
 * @since      1.2.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/widgets
 * @author     Amit Anand Niraj <queryamit@gmail.com>
 * @author     https://anandtech.in
 * @author     https://github.com/queryamit
 * @author     https://www.linkedin.com/in/queryamit/
 */
class N8nDash_Custom_Widget extends N8nDash_Widget {

    /**
     * Get widget type
     *
     * @since    1.0.0
     * @return   string    Widget type identifier
     */
    public function get_type() {
        return 'custom';
    }

    /**
     * Get widget title
     *
     * @since    1.0.0
     * @return   string    Widget title
     */
    public function get_title() {
        return $this->config['title'] ?? __( 'Custom Widget', 'n8ndash-pro' );
    }

    /**
     * Get default icon
     *
     * @since    1.0.0
     * @return   string    Default icon
     */
    protected function get_default_icon() {
        return 'dashicons-admin-generic';
    }

    /**
     * Get default configuration
     *
     * @since    1.0.0
     * @return   array    Default configuration
     */
    public function get_default_config() {
        return array(
            'title'         => __( 'Custom Form', 'n8ndash-pro' ),
            'description'   => '',
            'icon'          => 'dashicons-admin-generic',
            'button_text'   => __( 'Submit', 'n8ndash-pro' ),
            'success_message' => __( 'Form submitted successfully!', 'n8ndash-pro' ),
            'response_only' => false,
            'show_response' => true,
            'fields'        => array(),
            'timeout'       => 30,
            'webhook'       => array(
                'url' => '',
                'method' => 'POST',
                'headers' => array(),
                'body' => array(),
            ),
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
        
        ob_start();
        ?>
        <div class="n8n-custom-widget" data-widget-id="<?php echo esc_attr( $this->id ); ?>">
            <?php if ( ! empty( $config['description'] ) ) : ?>
                <div class="n8n-custom-widget__description">
                    <?php echo wp_kses_post( $config['description'] ); ?>
                </div>
            <?php endif; ?>
            
            <form class="n8n-custom-form" id="n8n-form-<?php echo esc_attr( $this->id ); ?>" method="post">
                <?php wp_nonce_field( 'n8ndash_public_nonce', 'nonce' ); ?>
                <input type="hidden" name="widget_id" value="<?php echo esc_attr( $this->id ); ?>" />
                <input type="hidden" name="action" value="n8ndash_custom_widget_submit" />
                
                <?php if ( ! $config['response_only'] ) : ?>
                    <div class="n8n-form-fields">
                        <?php foreach ( $config['fields'] as $field ) : ?>
                            <?php echo $this->render_field( $field ); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="n8n-form-actions">
                    <button type="submit" class="button button-primary n8n-submit-button">
                        <span class="n8n-button-text"><?php echo esc_html( $config['button_text'] ); ?></span>
                        <span class="n8n-button-spinner" style="display:none;">
                            <span class="spinner is-active"></span>
                        </span>
                    </button>
                </div>
            </form>
            
            <?php if ( $config['show_response'] ) : ?>
                <div class="n8n-custom-response" id="n8n-response-<?php echo esc_attr( $this->id ); ?>" style="display:none;">
                    <div class="n8n-response-content"></div>
                </div>
            <?php endif; ?>
        </div>

        <script>
        (function() {
            const widgetId = <?php echo json_encode( $this->id ); ?>;
            const webhookConfig = <?php echo json_encode( array(
                'url' => $this->config['webhook']['url'] ?? '',
                'method' => $this->config['webhook']['method'] ?? 'POST',
                'headers' => $this->config['webhook']['headers'] ?? array(),
                'body' => $this->config['webhook']['body'] ?? array()
            ) ); ?>;
            
            // Expose webhook config globally so the old JavaScript can use it
            if (!window.n8nWebhookConfigs) {
                window.n8nWebhookConfigs = {};
            }
            window.n8nWebhookConfigs[widgetId] = webhookConfig;
            
            // Webhook config exposed for widget
            // Webhook URL, Method, Headers, Body configured
            
            // Ensure form submission is properly handled
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('n8n-form-' + widgetId);
                if (form) {
                    // Remove any action attribute that might cause WordPress submission
                    form.removeAttribute('action');
                    form.setAttribute('method', 'post');
                    
                                         // Add protection that allows main JavaScript to work
                     form.addEventListener('submit', function(e) {
                         // Form submit event caught in inline script for widget
                         
                         // Only prevent if it's trying to submit to WordPress
                         if (form.action && typeof form.action === 'string' && form.action.includes('admin.php')) {
                             // Blocking WordPress submission for widget
                             e.preventDefault();
                             e.stopPropagation();
                             return false;
                         }
                         
                         // Allow the main JavaScript to handle it
                         // Allowing main JavaScript to handle submission for widget
                     }, false); // Use bubbling phase, not capture
                }
            });
            
            // Immediate protection for forms that might already exist
            const existingForm = document.getElementById('n8n-form-' + widgetId);
            if (existingForm) {
                existingForm.addEventListener('submit', function(e) {
                    // Immediate form protection for widget
                    
                    // Only prevent if it's trying to submit to WordPress
                    if (existingForm.action && typeof existingForm.action === 'string' && existingForm.action.includes('admin.php')) {
                        // Blocking WordPress submission for widget
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    
                    // Allow the main JavaScript to handle it
                    // Allowing main JavaScript to handle submission for widget
                }, false); // Use bubbling phase, not capture
            }
            
            // Mark this widget as protected
            window.n8nWebhookConfigs[widgetId + '_protected'] = true;
            // Widget protection enabled
        })();
        </script>
        <?php
        
        $content = ob_get_clean();
        
        return $this->get_widget_wrapper( $content );
    }

    /**
     * Render a form field
     *
     * @since    1.0.0
     * @param    array    $field    Field configuration
     * @return   string             Field HTML
     */
    private function render_field( $field ) {
        $field = wp_parse_args( $field, array(
            'id'          => '',
            'type'        => 'text',
            'name'        => '',
            'label'       => '',
            'placeholder' => '',
            'required'    => false,
            'options'     => array(),
            'rows'        => 4,
            'accept'      => '',
            'min'         => '',
            'max'         => '',
            'step'        => '',
            'pattern'     => '',
            'help'        => '',
            'default'     => '',
        ) );

        $field_id = 'n8n-field-' . esc_attr( $field['id'] );
        $field_name = esc_attr( $field['name'] );
        $required = $field['required'] ? 'required' : '';

        ob_start();
        ?>
        <div class="n8n-form-field n8n-form-field--<?php echo esc_attr( $field['type'] ); ?>">
            <?php if ( ! empty( $field['label'] ) && $field['type'] !== 'checkbox' ) : ?>
                <label for="<?php echo esc_attr( $field_id ); ?>" class="n8n-field-label">
                    <?php echo esc_html( $field['label'] ); ?>
                    <?php if ( $field['required'] ) : ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            
            <?php
            switch ( $field['type'] ) {
                case 'text':
                case 'email':
                case 'url':
                case 'tel':
                    ?>
                    <input type="<?php echo esc_attr( $field['type'] ); ?>" 
                           id="<?php echo esc_attr( $field_id ); ?>" 
                           name="<?php echo esc_attr( $field_name ); ?>" 
                           class="n8n-field-input" 
                           placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
                           value="<?php echo esc_attr( $field['default'] ); ?>"
                           <?php echo $required; ?>
                           <?php if ( $field['pattern'] ) echo 'pattern="' . esc_attr( $field['pattern'] ) . '"'; ?> />
                    <?php
                    break;

                case 'number':
                    ?>
                    <input type="number" 
                           id="<?php echo esc_attr( $field_id ); ?>" 
                           name="<?php echo esc_attr( $field_name ); ?>" 
                           class="n8n-field-input" 
                           placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
                           value="<?php echo esc_attr( $field['default'] ); ?>"
                           <?php echo $required; ?>
                           <?php if ( $field['min'] !== '' ) echo 'min="' . esc_attr( $field['min'] ) . '"'; ?>
                           <?php if ( $field['max'] !== '' ) echo 'max="' . esc_attr( $field['max'] ) . '"'; ?>
                           <?php if ( $field['step'] ) echo 'step="' . esc_attr( $field['step'] ) . '"'; ?> />
                    <?php
                    break;

                case 'textarea':
                    ?>
                    <textarea id="<?php echo esc_attr( $field_id ); ?>" 
                              name="<?php echo esc_attr( $field_name ); ?>" 
                              class="n8n-field-textarea" 
                              rows="<?php echo esc_attr( $field['rows'] ); ?>"
                              placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
                              <?php echo $required; ?>><?php echo esc_textarea( $field['default'] ); ?></textarea>
                    <?php
                    break;

                case 'select':
                    ?>
                    <select id="<?php echo esc_attr( $field_id ); ?>" 
                            name="<?php echo esc_attr( $field_name ); ?>" 
                            class="n8n-field-select"
                            <?php echo $required; ?>>
                        <?php if ( ! empty( $field['placeholder'] ) ) : ?>
                            <option value=""><?php echo esc_html( $field['placeholder'] ); ?></option>
                        <?php endif; ?>
                        <?php foreach ( $field['options'] as $value => $label ) : ?>
                            <?php
                            if ( is_numeric( $value ) ) {
                                $value = $label;
                            }
                            ?>
                            <option value="<?php echo esc_attr( $value ); ?>" 
                                    <?php selected( $field['default'], $value ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php
                    break;

                case 'checkbox':
                    ?>
                    <label class="n8n-field-checkbox-label">
                        <input type="checkbox" 
                               id="<?php echo esc_attr( $field_id ); ?>" 
                               name="<?php echo esc_attr( $field_name ); ?>" 
                               class="n8n-field-checkbox"
                               value="1"
                               <?php checked( $field['default'] ); ?>
                               <?php echo $required; ?>
                        <?php echo esc_html( $field['label'] ); ?>
                        <?php if ( $field['required'] ) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <?php
                    break;

                case 'radio':
                    ?>
                    <div class="n8n-field-radio-group">
                        <?php foreach ( $field['options'] as $value => $label ) : ?>
                            <?php
                            if ( is_numeric( $value ) ) {
                                $value = $label;
                            }
                            $radio_id = $field_id . '-' . sanitize_key( $value );
                            ?>
                            <label class="n8n-field-radio-label">
                                <input type="radio" 
                                       id="<?php echo esc_attr( $radio_id ); ?>" 
                                       name="<?php echo esc_attr( $field_name ); ?>" 
                                       class="n8n-field-radio"
                                       value="<?php echo esc_attr( $value ); ?>"
                                       <?php checked( $field['default'], $value ); ?>
                                       <?php echo $required; ?>
                                <?php echo esc_html( $label ); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    break;

                case 'file':
                    ?>
                    <input type="file" 
                           id="<?php echo esc_attr( $field_id ); ?>" 
                           name="<?php echo esc_attr( $field_name ); ?>" 
                           class="n8n-field-file"
                           <?php if ( $field['accept'] ) echo 'accept="' . esc_attr( $field['accept'] ) . '"'; ?>
                           <?php echo $required; ?>
                    <?php
                    break;

                case 'date':
                    ?>
                    <input type="date" 
                           id="<?php echo esc_attr( $field_id ); ?>" 
                           name="<?php echo esc_attr( $field_name ); ?>" 
                           class="n8n-field-input" 
                           value="<?php echo esc_attr( $field['default'] ); ?>"
                           <?php echo $required; ?>
                           <?php if ( $field['min'] ) echo 'min="' . esc_attr( $field['min'] ) . '"'; ?>
                           <?php if ( $field['max'] ) echo 'max="' . esc_attr( $field['max'] ) . '"'; ?>
                    <?php
                    break;

                case 'time':
                    ?>
                    <input type="time" 
                           id="<?php echo esc_attr( $field_id ); ?>" 
                           name="<?php echo esc_attr( $field_name ); ?>" 
                           class="n8n-field-input" 
                           value="<?php echo esc_attr( $field['default'] ); ?>"
                           <?php echo $required; ?>
                    <?php
                    break;

                case 'color':
                    ?>
                    <input type="color" 
                           id="<?php echo esc_attr( $field_id ); ?>" 
                           name="<?php echo esc_attr( $field_name ); ?>" 
                           class="n8n-field-color" 
                           value="<?php echo esc_attr( $field['default'] ?: '#000000' ); ?>"
                           <?php echo $required; ?>
                    <?php
                    break;

                case 'range':
                    ?>
                    <input type="range" 
                           id="<?php echo esc_attr( $field_id ); ?>" 
                           name="<?php echo esc_attr( $field_name ); ?>" 
                           class="n8n-field-range" 
                           value="<?php echo esc_attr( $field['default'] ); ?>"
                           <?php if ( $field['min'] !== '' ) echo 'min="' . esc_attr( $field['min'] ) . '"'; ?>
                           <?php if ( $field['max'] !== '' ) echo 'max="' . esc_attr( $field['max'] ) . '"'; ?>
                           <?php if ( $field['step'] ) echo 'step="' . esc_attr( $field['step'] ) . '"'; ?>
                           <?php echo $required; ?>
                    <output for="<?php echo esc_attr( $field_id ); ?>" class="n8n-field-range-output">
                        <?php echo esc_html( $field['default'] ); ?>
                    </output>
                    <?php
                    break;
            }
            ?>
            
            <?php if ( ! empty( $field['help'] ) ) : ?>
                <p class="n8n-field-help"><?php echo esc_html( $field['help'] ); ?></p>
            <?php endif; ?>
        </div>
        <?php
        
        return ob_get_clean();
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
            <input type="hidden" name="widget_type" value="custom" />
            
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
                    <label for="widget-description-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Description', 'n8ndash-pro' ); ?>
                    </label>
                    <textarea id="widget-description-<?php echo esc_attr( $this->id ); ?>" 
                              name="config[description]" 
                              rows="3"
                              class="large-text"><?php echo esc_textarea( $config['description'] ); ?></textarea>
                    <p class="description">
                        <?php esc_html_e( 'Optional description shown above the form. Basic HTML allowed.', 'n8ndash-pro' ); ?>
                    </p>
                </div>
                
                <div class="n8n-form-group">
                    <label for="widget-button-text-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Button Text', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-button-text-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[button_text]" 
                           value="<?php echo esc_attr( $config['button_text'] ); ?>" 
                           class="regular-text" />
                </div>
                
                <div class="n8n-form-group">
                    <label for="widget-success-message-<?php echo esc_attr( $this->id ); ?>">
                        <?php esc_html_e( 'Success Message', 'n8ndash-pro' ); ?>
                    </label>
                    <input type="text" 
                           id="widget-success-message-<?php echo esc_attr( $this->id ); ?>" 
                           name="config[success_message]" 
                           value="<?php echo esc_attr( $config['success_message'] ); ?>" 
                           class="large-text" />
                </div>
                
                <div class="n8n-form-group">
                    <label>
                        <input type="checkbox" 
                               name="config[response_only]" 
                               value="1" 
                               <?php checked( $config['response_only'] ); ?> />
                        <?php esc_html_e( 'Response only (no form fields)', 'n8ndash-pro' ); ?>
                    </label>
                </div>
                
                <div class="n8n-form-group">
                    <label>
                        <input type="checkbox" 
                               name="config[show_response]" 
                               value="1" 
                               <?php checked( $config['show_response'] ); ?> />
                        <?php esc_html_e( 'Show webhook response', 'n8ndash-pro' ); ?>
                    </label>
                </div>
            </div>
            
            <!-- Webhook configuration is handled by the admin dashboard modal -->
            <!-- Removed duplicate webhook section to avoid duplication -->
            
            <div class="n8n-form-section n8n-fields-section" style="<?php echo $config['response_only'] ? 'display:none;' : ''; ?>">
                <h3><?php esc_html_e( 'Form Fields', 'n8ndash-pro' ); ?></h3>
                
                <div class="n8n-fields-list" id="fields-list-<?php echo esc_attr( $this->id ); ?>">
                    <?php if ( ! empty( $config['fields'] ) ) : ?>
                        <?php foreach ( $config['fields'] as $index => $field ) : ?>
                            <?php echo $this->render_field_config( $field, $index ); ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="n8n-no-fields"><?php esc_html_e( 'No fields added yet.', 'n8ndash-pro' ); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="n8n-add-field-wrapper">
                    <select id="add-field-type-<?php echo esc_attr( $this->id ); ?>" class="n8n-field-type-select">
                        <option value="text"><?php esc_html_e( 'Text Input', 'n8ndash-pro' ); ?></option>
                        <option value="email"><?php esc_html_e( 'Email', 'n8ndash-pro' ); ?></option>
                        <option value="number"><?php esc_html_e( 'Number', 'n8ndash-pro' ); ?></option>
                        <option value="textarea"><?php esc_html_e( 'Textarea', 'n8ndash-pro' ); ?></option>
                        <option value="select"><?php esc_html_e( 'Dropdown', 'n8ndash-pro' ); ?></option>
                        <option value="checkbox"><?php esc_html_e( 'Checkbox', 'n8ndash-pro' ); ?></option>
                        <option value="radio"><?php esc_html_e( 'Radio Buttons', 'n8ndash-pro' ); ?></option>
                        <option value="file"><?php esc_html_e( 'File Upload', 'n8ndash-pro' ); ?></option>
                        <option value="date"><?php esc_html_e( 'Date', 'n8ndash-pro' ); ?></option>
                        <option value="time"><?php esc_html_e( 'Time', 'n8ndash-pro' ); ?></option>
                        <option value="color"><?php esc_html_e( 'Color Picker', 'n8ndash-pro' ); ?></option>
                        <option value="range"><?php esc_html_e( 'Range Slider', 'n8ndash-pro' ); ?></option>
                    </select>
                    <button type="button" class="button n8n-add-field-button">
                        <span class="dashicons dashicons-plus"></span>
                        <?php esc_html_e( 'Add Field', 'n8ndash-pro' ); ?>
                    </button>
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
     * Render field configuration row
     *
     * @since    1.0.0
     * @param    array    $field    Field data
     * @param    int      $index    Field index
     * @return   string             Field config HTML
     */
    private function render_field_config( $field, $index ) {
        $field = wp_parse_args( $field, array(
            'id'    => uniqid( 'field_' ),
            'type'  => 'text',
            'name'  => '',
            'label' => '',
            'required' => false,
        ) );

        ob_start();
        ?>
        <div class="n8n-field-config" data-field-index="<?php echo esc_attr( $index ); ?>">
            <div class="n8n-field-config-header">
                <span class="n8n-field-type"><?php echo esc_html( ucfirst( $field['type'] ) ); ?></span>
                <span class="n8n-field-name"><?php echo esc_html( $field['name'] ?: __( 'Unnamed field', 'n8ndash-pro' ) ); ?></span>
                <button type="button" class="button-link n8n-remove-field">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
            <div class="n8n-field-config-body">
                <input type="hidden" name="config[fields][<?php echo $index; ?>][id]" value="<?php echo esc_attr( $field['id'] ); ?>" />
                <input type="hidden" name="config[fields][<?php echo $index; ?>][type]" value="<?php echo esc_attr( $field['type'] ); ?>" />
                
                <div class="n8n-field-row">
                    <label><?php esc_html_e( 'Field Name', 'n8ndash-pro' ); ?></label>
                    <input type="text" 
                           name="config[fields][<?php echo $index; ?>][name]" 
                           value="<?php echo esc_attr( $field['name'] ); ?>" 
                           class="regular-text" 
                           placeholder="field_name" />
                </div>
                
                <div class="n8n-field-row">
                    <label><?php esc_html_e( 'Label', 'n8ndash-pro' ); ?></label>
                    <input type="text" 
                           name="config[fields][<?php echo $index; ?>][label]" 
                           value="<?php echo esc_attr( $field['label'] ); ?>" 
                           class="regular-text" />
                </div>
                
                <?php if ( in_array( $field['type'], array( 'text', 'email', 'textarea', 'number' ) ) ) : ?>
                    <div class="n8n-field-row">
                        <label><?php esc_html_e( 'Placeholder', 'n8ndash-pro' ); ?></label>
                        <input type="text" 
                               name="config[fields][<?php echo $index; ?>][placeholder]" 
                               value="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>" 
                               class="regular-text" />
                    </div>
                <?php endif; ?>
                
                <?php if ( in_array( $field['type'], array( 'select', 'radio' ) ) ) : ?>
                    <div class="n8n-field-row">
                        <label><?php esc_html_e( 'Options (one per line)', 'n8ndash-pro' ); ?></label>
                        <textarea name="config[fields][<?php echo $index; ?>][options]" 
                                  rows="3" 
                                  class="regular-text"><?php echo esc_textarea( implode( "\n", $field['options'] ?? array() ) ); ?></textarea>
                    </div>
                <?php endif; ?>
                
                <div class="n8n-field-row">
                    <label>
                        <input type="checkbox" 
                               name="config[fields][<?php echo $index; ?>][required]" 
                               value="1" 
                               <?php checked( $field['required'] ); ?> />
                        <?php esc_html_e( 'Required field', 'n8ndash-pro' ); ?>
                    </label>
                </div>
            </div>
        </div>
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
        
        if ( $errors->has_errors() ) {
            return $errors;
        }
        
        // Sanitize and return
        $clean = array(
            'title'         => sanitize_text_field( $config['title'] ),
            'description'   => wp_kses_post( $config['description'] ?? '' ),
            'icon'          => sanitize_text_field( $config['icon'] ?? $this->get_default_icon() ),
            'button_text'   => sanitize_text_field( $config['button_text'] ?? __( 'Submit', 'n8ndash-pro' ) ),
            'success_message' => sanitize_text_field( $config['success_message'] ?? __( 'Form submitted successfully!', 'n8ndash-pro' ) ),
            'response_only' => ! empty( $config['response_only'] ),
            'show_response' => ! empty( $config['show_response'] ),
            'timeout'       => intval( $config['timeout'] ?? 30 ),
            'fields'        => array(),
            'webhook'       => array(
                'url' => sanitize_text_field( $config['webhook']['url'] ?? '' ),
                'method' => sanitize_text_field( $config['webhook']['method'] ?? 'POST' ),
                'headers' => array(),
                'body' => array(),
            ),
        );
        
        // Validate fields
        if ( ! empty( $config['fields'] ) && is_array( $config['fields'] ) ) {
            foreach ( $config['fields'] as $field ) {
                if ( empty( $field['name'] ) || empty( $field['type'] ) ) {
                    continue;
                }
                
                $clean_field = array(
                    'id'          => sanitize_key( $field['id'] ?? uniqid( 'field_' ) ),
                    'type'        => sanitize_key( $field['type'] ),
                    'name'        => sanitize_key( $field['name'] ),
                    'label'       => sanitize_text_field( $field['label'] ?? '' ),
                    'placeholder' => sanitize_text_field( $field['placeholder'] ?? '' ),
                    'required'    => ! empty( $field['required'] ),
                    'help'        => sanitize_text_field( $field['help'] ?? '' ),
                    'default'     => sanitize_text_field( $field['default'] ?? '' ),
                );
                
                // Type-specific sanitization
                switch ( $field['type'] ) {
                    case 'number':
                    case 'range':
                        $clean_field['min'] = is_numeric( $field['min'] ?? '' ) ? floatval( $field['min'] ) : '';
                        $clean_field['max'] = is_numeric( $field['max'] ?? '' ) ? floatval( $field['max'] ) : '';
                        $clean_field['step'] = is_numeric( $field['step'] ?? '' ) ? floatval( $field['step'] ) : '';
                        break;
                        
                    case 'textarea':
                        $clean_field['rows'] = intval( $field['rows'] ?? 4 );
                        break;
                        
                    case 'select':
                    case 'radio':
                        $options = array();
                        if ( ! empty( $field['options'] ) ) {
                            if ( is_string( $field['options'] ) ) {
                                $field['options'] = explode( "\n", $field['options'] );
                            }
                            foreach ( $field['options'] as $option ) {
                                $option = trim( $option );
                                if ( ! empty( $option ) ) {
                                    $options[] = sanitize_text_field( $option );
                                }
                            }
                        }
                        $clean_field['options'] = $options;
                        break;
                        
                    case 'file':
                        $clean_field['accept'] = sanitize_text_field( $field['accept'] ?? '' );
                        break;
                        
                    case 'date':
                        $clean_field['min'] = sanitize_text_field( $field['min'] ?? '' );
                        $clean_field['max'] = sanitize_text_field( $field['max'] ?? '' );
                        break;
                        
                    case 'text':
                    case 'email':
                    case 'url':
                    case 'tel':
                        $clean_field['pattern'] = sanitize_text_field( $field['pattern'] ?? '' );
                        break;
                }
                
                $clean['fields'][] = $clean_field;
            }
        }

        // Validate webhook
        if ( ! empty( $config['webhook']['url'] ) ) {
            $clean['webhook']['url'] = esc_url_raw( $config['webhook']['url'] );
            $clean['webhook']['method'] = in_array( strtoupper( $config['webhook']['method'] ?? 'POST' ), array( 'GET', 'POST', 'PUT', 'DELETE' ) ) ? strtoupper( $config['webhook']['method'] ) : 'POST';
            
            if ( ! empty( $config['webhook']['headers'] ) ) {
                $clean['webhook']['headers'] = array();
                foreach ( $config['webhook']['headers'] as $header ) {
                    if ( is_array( $header ) && isset( $header['key'] ) && isset( $header['value'] ) ) {
                        $clean['webhook']['headers'][] = array(
                            'key' => sanitize_text_field( $header['key'] ),
                            'value' => sanitize_text_field( $header['value'] )
                        );
                    }
                }
            }

            if ( ! empty( $config['webhook']['body'] ) ) {
                $clean['webhook']['body'] = json_decode( $config['webhook']['body'], true );
                if ( ! is_array( $clean['webhook']['body'] ) ) {
                    $clean['webhook']['body'] = array();
                }
            }
        }
        
        return $clean;
    }

    /**
     * Process webhook response
     *
     * @since    1.0.0
     * @param    array    $response    Webhook response data
     * @return   array                 Processed data for display
     */
    public function process_webhook_response( $response ) {
        if ( ! is_array( $response ) ) {
            return array(
                'error' => __( 'Invalid response format', 'n8ndash-pro' ),
            );
        }
        
        // Check for success/error indicators
        if ( isset( $response['error'] ) ) {
            return array(
                'error' => $response['error'],
            );
        }
        
        if ( isset( $response['success'] ) && $response['success'] === false ) {
            return array(
                'error' => $response['message'] ?? __( 'Request failed', 'n8ndash-pro' ),
            );
        }
        
        // Process the response based on configuration
        $config = wp_parse_args( $this->config, $this->get_default_config() );
        
        $processed = array(
            'success' => true,
            'message' => $config['success_message'],
            'data'    => $response,
        );
        
        // If response contains specific message, use it
        if ( isset( $response['message'] ) ) {
            $processed['message'] = $response['message'];
        }
        
        // If response contains HTML content
        if ( isset( $response['html'] ) ) {
            $processed['html'] = wp_kses_post( $response['html'] );
        }
        
        // If response contains redirect URL
        if ( isset( $response['redirect'] ) ) {
            $processed['redirect'] = esc_url_raw( $response['redirect'] );
        }
        
        return $processed;
    }

    /**
     * Handle AJAX form submission
     *
     * @since    1.0.0
     */
    public function handle_ajax_submit() {
        // Verify nonce
        if ( ! check_ajax_referer( 'n8ndash_custom_widget', 'n8ndash_custom_nonce', false ) ) {
            wp_send_json_error( array(
                'message' => __( 'Security check failed', 'n8ndash-pro' ),
            ) );
        }
        
        // Get widget ID
        $widget_id = isset( $_POST['widget_id'] ) ? intval( $_POST['widget_id'] ) : 0;
        if ( ! $widget_id ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid widget ID', 'n8ndash-pro' ),
            ) );
        }
        
        // Load widget configuration
        $widget_data = N8nDash_DB::get_widget( $widget_id );
        if ( ! $widget_data ) {
            wp_send_json_error( array(
                'message' => __( 'Widget not found', 'n8ndash-pro' ),
            ) );
        }
        
        $this->id = $widget_id;
        $this->config = json_decode( $widget_data->config, true );
        $this->webhook_config = json_decode( $widget_data->webhook_config, true );
        
        // Prepare form data
        $form_data = array();
        $config = wp_parse_args( $this->config, $this->get_default_config() );
        
        // Collect and validate form fields
        foreach ( $config['fields'] as $field ) {
            $field_name = $field['name'];
            $field_value = isset( $_POST[ $field_name ] ) ? $_POST[ $field_name ] : '';
            
            // Validate required fields
            if ( $field['required'] && empty( $field_value ) ) {
                wp_send_json_error( array(
                    'message' => sprintf(
                        __( 'Field "%s" is required', 'n8ndash-pro' ),
                        $field['label'] ?: $field_name
                    ),
                ) );
            }
            
            // Sanitize based on field type
            switch ( $field['type'] ) {
                case 'email':
                    $field_value = sanitize_email( $field_value );
                    if ( $field['required'] && ! is_email( $field_value ) ) {
                        wp_send_json_error( array(
                            'message' => sprintf(
                                __( 'Please enter a valid email address for "%s"', 'n8ndash-pro' ),
                                $field['label'] ?: $field_name
                            ),
                        ) );
                    }
                    break;
                    
                case 'url':
                    $field_value = esc_url_raw( $field_value );
                    break;
                    
                case 'number':
                case 'range':
                    $field_value = floatval( $field_value );
                    break;
                    
                case 'checkbox':
                    $field_value = ! empty( $field_value ) ? '1' : '0';
                    break;
                    
                case 'textarea':
                    $field_value = sanitize_textarea_field( $field_value ?? '' );
                    break;
                    
                default:
                    $field_value = sanitize_text_field( $field_value );
                    break;
            }
            
            $form_data[ $field_name ] = $field_value;
        }
        
        // Add metadata
        $form_data['_widget_id'] = $widget_id;
        $form_data['_timestamp'] = current_time( 'mysql' );
        $form_data['_user_id'] = get_current_user_id();
        $form_data['_ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Prepare webhook body
        $webhook_body = $form_data;
        
        // If custom body is configured, merge with form data
        if ( ! empty( $this->webhook_config['body'] ) ) {
            $custom_body = json_decode( $this->webhook_config['body'], true );
            if ( is_array( $custom_body ) ) {
                $webhook_body = array_merge( $custom_body, $form_data );
            }
        }
        
        // Make webhook request
        try {
            $response = $this->make_webhook_request( $webhook_body );
            
            if ( is_wp_error( $response ) ) {
                wp_send_json_error( array(
                    'message' => $response->get_error_message(),
                ) );
            }
            
            // Process response
            $processed = $this->process_webhook_response( $response );
            
            if ( isset( $processed['error'] ) ) {
                wp_send_json_error( array(
                    'message' => $processed['error'],
                ) );
            }
            
            // Log submission if debug mode is enabled
            if ( get_option( 'n8ndash_settings' )['enable_debug_mode'] ?? false ) {
                error_log( sprintf(
                    '[n8nDash] Custom widget submission - Widget ID: %d, Data: %s, Response: %s',
                    $widget_id,
                    json_encode( $form_data ),
                    json_encode( $processed )
                ) );
            }
            
            wp_send_json_success( $processed );
            
        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => sprintf(
                    __( 'Error processing request: %s', 'n8ndash-pro' ),
                    $e->getMessage()
                ),
            ) );
        }
    }

    /**
     * Enqueue widget assets
     *
     * @since    1.0.0
     */
    public function enqueue_assets() {
        parent::enqueue_assets();
        
        // Add custom widget specific styles
        wp_add_inline_style( 'n8ndash-widgets', $this->get_custom_styles() );
        
        // Add custom widget specific scripts
        wp_add_inline_script( 'n8ndash-widgets', $this->get_custom_scripts(), 'after' );
    }

    /**
     * Get custom styles for the widget
     *
     * @since    1.0.0
     * @return   string    CSS styles
     */
    private function get_custom_styles() {
        return '
        .n8n-custom-widget {
            padding: 20px;
        }
        
        .n8n-custom-widget__description {
            margin-bottom: 20px;
            color: #666;
        }
        
        .n8n-form-field {
            margin-bottom: 15px;
        }
        
        .n8n-field-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .n8n-field-label .required {
            color: #dc3232;
        }
        
        .n8n-field-input,
        .n8n-field-textarea,
        .n8n-field-select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .n8n-field-textarea {
            resize: vertical;
        }
        
        .n8n-field-checkbox-label,
        .n8n-field-radio-label {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            cursor: pointer;
        }
        
        .n8n-field-checkbox,
        .n8n-field-radio {
            margin-right: 8px;
        }
        
        .n8n-field-help {
            margin-top: 5px;
            font-size: 13px;
            color: #666;
        }
        
        .n8n-form-actions {
            margin-top: 20px;
        }
        
        .n8n-submit-button {
            position: relative;
            min-width: 120px;
        }
        
        .n8n-button-spinner {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .n8n-custom-response {
            margin-top: 20px;
            padding: 15px;
            background: #f0f8ff;
            border: 1px solid #b8daff;
            border-radius: 4px;
        }
        
        .n8n-custom-response.error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .n8n-custom-response.success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        ';
    }

    /**
     * Get custom scripts for the widget
     *
     * @since    1.0.0
     * @return   string    JavaScript code
     */
    private function get_custom_scripts() {
        return '
        // Custom widget scripts are now handled by n8ndash-public.js
        // This prevents conflicts and ensures consistent behavior
        ';
    }
}