'default'     => sanitize_text_field( $field['default'] ?? '' ),
                );
                
                // Handle options for select and radio fields
                if ( in_array( $clean_field['type'], array( 'select', 'radio' ) ) ) {
                    $clean_field['options'] = array();
                    if ( ! empty( $field['options'] ) ) {
                        if ( is_string( $field['options'] ) ) {
                            $options = array_map( 'trim', explode( "\n", $field['options'] ) );
                        } else {
                            $options = (array) $field['options'];
                        }
                        foreach ( $options as $option ) {
                            if ( ! empty( $option ) ) {
                                $clean_field['options'][] = sanitize_text_field( $option );
                            }
                        }
                    }
                }
                
                // Handle field-specific attributes
                if ( $field['type'] === 'number' || $field['type'] === 'range' ) {
                    $clean_field['min'] = is_numeric( $field['min'] ?? '' ) ? $field['min'] : '';
                    $clean_field['max'] = is_numeric( $field['max'] ?? '' ) ? $field['max'] : '';
                    $clean_field['step'] = is_numeric( $field['step'] ?? '' ) ? $field['step'] : '';
                }
                
                if ( $field['type'] === 'textarea' ) {
                    $clean_field['rows'] = intval( $field['rows'] ?? 4 );
                }
                
                if ( $field['type'] === 'file' ) {
                    $clean_field['accept'] = sanitize_text_field( $field['accept'] ?? '' );
                }
                
                $clean['fields'][] = $clean_field;
            }
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
            'message' => $config['success_message'],
            'timestamp' => current_time( 'timestamp' ),
        );
        
        // If response is a string, treat it as the message
        if ( is_string( $response ) ) {
            $processed['message'] = $response;
            $processed['data'] = null;
        } else {
            // Extract message if present in response
            if ( is_array( $response ) ) {
                if ( isset( $response['message'] ) ) {
                    $processed['message'] = sanitize_text_field( $response['message'] );
                }
                if ( isset( $response['success'] ) ) {
                    $processed['success'] = (bool) $response['success'];
                }
                if ( isset( $response['error'] ) ) {
                    $processed['success'] = false;
                    $processed['message'] = sanitize_text_field( $response['error'] );
                }
            }
            
            $processed['data'] = $response;
        }
        
        return $processed;
    }
}