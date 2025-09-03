<?php
/**
 * Admin dashboard editor page
 *
 * This file displays the dashboard editor interface.
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/admin/partials
 * @author     Amit Anand Niraj <n8ndash@gmail.com>
 */

// Get dashboard ID from query parameter
$dashboard_id = isset( $_GET['dashboard_id'] ) ? intval( $_GET['dashboard_id'] ) : 0;
$is_new = empty( $dashboard_id );

// Load dashboard data if editing
$dashboard = null;
$widgets = array();
if ( ! $is_new ) {
    $dashboard = N8nDash_DB::get_dashboard( $dashboard_id );
    
    // Check permissions - Allow both full edit and limited own edit capabilities
    if ( ! $dashboard ) {
        wp_die( esc_html__( 'Dashboard not found.', 'n8ndash-pro' ) );
    }
    
    // If user has limited capabilities, check dashboard ownership
    if ( current_user_can( 'n8ndash_edit_own_dashboards' ) && ! current_user_can( 'n8ndash_edit_dashboards' ) ) {
        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
            wp_die( esc_html__( 'You can only edit your own dashboards.', 'n8ndash-pro' ) );
        }
    } else {
        // Users with full edit_dashboards capability
        if ( ! N8nDash_DB::user_can_access_dashboard( $dashboard_id, 'edit' ) ) {
            wp_die( esc_html__( 'You do not have permission to edit this dashboard.', 'n8ndash-pro' ) );
        }
    }
    
    $widgets = N8nDash_DB::get_dashboard_widgets( $dashboard_id );
}

// Page title
$page_title = $is_new ? __( 'Create New Dashboard', 'n8ndash-pro' ) : __( 'Edit Dashboard', 'n8ndash-pro' );

?>

<div class="wrap n8ndash-admin-wrap">
    <h1 class="wp-heading-inline">
        <?php echo esc_html( $page_title ); ?>
    </h1>
    
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=n8ndash' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Back to Dashboards', 'n8ndash-pro' ); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <div class="n8ndash-editor-container">
        <!-- Dashboard Settings Panel -->
        <div class="n8ndash-editor-sidebar">
            <div class="n8ndash-panel">
                <h2 class="n8ndash-panel__title"><?php esc_html_e( 'Dashboard Settings', 'n8ndash-pro' ); ?></h2>
                
                <form id="n8ndash-dashboard-form">
                    <input type="hidden" id="dashboard-id" value="<?php echo esc_attr( $dashboard_id ); ?>">
                    
                    <div class="n8ndash-field">
                        <label for="dashboard-title"><?php esc_html_e( 'Title', 'n8ndash-pro' ); ?></label>
                        <input type="text" 
                               id="dashboard-title" 
                               class="regular-text" 
                               value="<?php echo ( $dashboard && isset($dashboard->title) ) ? esc_attr( $dashboard->title ) : ''; ?>"
                               required>
                    </div>
                    
                    <div class="n8ndash-field">
                        <label for="dashboard-description"><?php esc_html_e( 'Description', 'n8ndash-pro' ); ?></label>
                        <textarea id="dashboard-description" 
                                  class="large-text" 
                                  rows="3"><?php echo ( $dashboard && isset($dashboard->description) ) ? esc_textarea( $dashboard->description ) : ''; ?></textarea>
                    </div>
                    
                    <div class="n8ndash-field">
                        <label for="dashboard-status"><?php esc_html_e( 'Status', 'n8ndash-pro' ); ?></label>
                        <select id="dashboard-status">
                            <option value="active" <?php selected( $dashboard && isset($dashboard->status) && $dashboard->status === 'active' ); ?>>
                                <?php esc_html_e( 'Active', 'n8ndash-pro' ); ?>
                            </option>
                            <option value="inactive" <?php selected( $dashboard && isset($dashboard->status) && $dashboard->status === 'inactive' ); ?>>
                                <?php esc_html_e( 'Inactive', 'n8ndash-pro' ); ?>
                            </option>
                        </select>
                    </div>
                    
                    <div class="n8ndash-field">
                        <label>
                            <input type="checkbox" 
                                   id="dashboard-public" 
                                   <?php checked( $dashboard && !empty($dashboard->settings['is_public']) ); ?>>
                            <?php esc_html_e( 'Make this dashboard public', 'n8ndash-pro' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Public dashboards can be viewed by anyone with the link.', 'n8ndash-pro' ); ?>
                        </p>
                    </div>
                    
                    <?php if ( $dashboard && !empty($dashboard->settings['is_public']) ): ?>
                    <div class="n8ndash-field n8ndash-public-link-field">
                        <label><?php esc_html_e( 'Public Dashboard Link', 'n8ndash-pro' ); ?></label>
                        <div class="n8ndash-public-link">
                            <input type="text" 
                                   id="dashboard-public-url" 
                                   class="regular-text" 
                                   readonly 
                                   value="<?php echo esc_url( N8nDash_Frontend::get_public_url( $dashboard ) ); ?>" />
                            <button type="button" class="button" onclick="copyToClipboard('dashboard-public-url')">
                                <span class="dashicons dashicons-clipboard"></span>
                                <?php esc_html_e( 'Copy Link', 'n8ndash-pro' ); ?>
                            </button>
                        </div>
                        <p class="description">
                            <?php esc_html_e( 'Share this link with anyone to give them access to view this dashboard.', 'n8ndash-pro' ); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="n8ndash-field">
                        <button type="submit" class="button button-primary">
                            <?php echo $is_new ? esc_html__( 'Create Dashboard', 'n8ndash-pro' ) : esc_html__( 'Save Changes', 'n8ndash-pro' ); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Widget Library -->
            <div class="n8ndash-panel">
                <h2 class="n8ndash-panel__title"><?php esc_html_e( 'Add Widget', 'n8ndash-pro' ); ?></h2>
                
                <div class="n8ndash-widget-library">
                    <button class="n8ndash-widget-type" data-widget-type="data">
                        <span class="dashicons dashicons-chart-line"></span>
                        <span class="n8ndash-widget-type__label"><?php esc_html_e( 'Data Widget', 'n8ndash-pro' ); ?></span>
                        <span class="n8ndash-widget-type__desc"><?php esc_html_e( 'Display KPIs and lists', 'n8ndash-pro' ); ?></span>
                    </button>
                    
                    <button class="n8ndash-widget-type" data-widget-type="chart">
                        <span class="dashicons dashicons-chart-area"></span>
                        <span class="n8ndash-widget-type__label"><?php esc_html_e( 'Chart Widget', 'n8ndash-pro' ); ?></span>
                        <span class="n8ndash-widget-type__desc"><?php esc_html_e( 'Visualize data with charts', 'n8ndash-pro' ); ?></span>
                    </button>
                    
                    <button class="n8ndash-widget-type" data-widget-type="custom">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <span class="n8ndash-widget-type__label"><?php esc_html_e( 'Custom Widget', 'n8ndash-pro' ); ?></span>
                        <span class="n8ndash-widget-type__desc"><?php esc_html_e( 'Forms and interactions', 'n8ndash-pro' ); ?></span>
                    </button>
                </div>
            </div>
            
            <!-- Dashboard Actions -->
            <?php if ( ! $is_new ) : ?>
            <div class="n8ndash-panel">
                <h2 class="n8ndash-panel__title"><?php esc_html_e( 'Actions', 'n8ndash-pro' ); ?></h2>
                
                <div class="n8ndash-actions">
                    <button id="n8ndash-save-layout" class="button button-primary">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e( 'Save Layout', 'n8ndash-pro' ); ?>
                    </button>
                    
                    <button id="n8ndash-preview-dashboard" class="button">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php esc_html_e( 'Preview', 'n8ndash-pro' ); ?>
                    </button>
                    
                    <button id="n8ndash-export-dashboard" class="button">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e( 'Export', 'n8ndash-pro' ); ?>
                    </button>
                    
                    <button id="n8ndash-duplicate-dashboard" class="button">
                        <span class="dashicons dashicons-admin-page"></span>
                        <?php esc_html_e( 'Duplicate', 'n8ndash-pro' ); ?>
                    </button>
                    
                    <button id="n8ndash-delete-dashboard" class="button button-link-delete">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e( 'Delete', 'n8ndash-pro' ); ?>
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Dashboard Canvas -->
        <div class="n8ndash-editor-main">
            <div class="n8ndash-editor-header">
                <h2 id="n8ndash-dashboard-preview-title">
                    <?php echo ( $dashboard && isset($dashboard->title) ) ? esc_html( $dashboard->title ) : esc_html__( 'New Dashboard', 'n8ndash-pro' ); ?>
                </h2>
                <div class="n8ndash-editor-tools">
                    <button id="n8ndash-grid-toggle" class="button button-small" title="<?php esc_attr_e( 'Toggle Grid', 'n8ndash-pro' ); ?>">
                        <span class="dashicons dashicons-grid-view"></span>
                    </button>
                    <button id="n8ndash-fullscreen-toggle" class="button button-small" title="<?php esc_attr_e( 'Fullscreen', 'n8ndash-pro' ); ?>">
                        <span class="dashicons dashicons-fullscreen-alt"></span>
                    </button>
                </div>
            </div>
            
            <div id="n8ndash-dashboard-canvas" class="n8ndash-dashboard-canvas">
                <?php if ( empty( $widgets ) ) : ?>
                    <div class="n8ndash-canvas-empty">
                        <p><?php esc_html_e( 'Drag widgets from the sidebar to start building your dashboard.', 'n8ndash-pro' ); ?></p>
                    </div>
                <?php else : ?>
                    <?php foreach ( $widgets as $widget ) : ?>
                        <?php
                        // Extract position data
                        $position = is_array( $widget->position ) ? $widget->position : array();
                        $left = isset( $position['x'] ) ? $position['x'] : 50;
                        $top = isset( $position['y'] ) ? $position['y'] : 50;
                        $width = isset( $position['width'] ) ? $position['width'] : 300;
                        $height = isset( $position['height'] ) ? $position['height'] : 200;
                        ?>
                        <div class="n8ndash-widget"
                             data-widget-id="<?php echo esc_attr( $widget->id ); ?>"
                             data-widget-type="<?php echo esc_attr( $widget->widget_type ); ?>"
                             style="left: <?php echo esc_attr( $left ); ?>px;
                                    top: <?php echo esc_attr( $top ); ?>px;
                                    width: <?php echo esc_attr( $width ); ?>px;
                                    height: <?php echo esc_attr( $height ); ?>px;">
                            <div class="n8ndash-widget__header">
                                <h3 class="n8ndash-widget__title"><?php echo esc_html( $widget->title ); ?></h3>
                                <div class="n8ndash-widget__actions">
                                    <?php
                                    $type_label = ( $widget->widget_type === 'custom' ) ? 'App' : ( $widget->widget_type === 'chart' ? 'Data' : 'Data' );
                                    ?>
                                    <span class="badge-main"><?php echo esc_html( $type_label ); ?></span>
                                    <button type="button" class="n8n-widget__action n8n-widget__refresh n8ndash-widget-refresh" title="<?php esc_attr_e( 'Refresh Widget Data', 'n8ndash-pro' ); ?>" data-action="refresh">
                                        <span class="dashicons dashicons-update"></span>
                                    </button>
                                    <button type="button" class="n8n-widget__action n8n-widget__settings n8ndash-widget-edit" data-widget-id="<?php echo esc_attr( $widget->id ); ?>" title="<?php esc_attr_e( 'Edit Widget', 'n8ndash-pro' ); ?>" data-action="settings">
                                        <span class="dashicons dashicons-admin-generic"></span>
                                    </button>
                                    <?php if ( ! current_user_can( 'subscriber' ) ) : ?>
                                    <button type="button" class="n8n-widget__action n8n-widget__delete n8ndash-widget-delete" title="<?php esc_attr_e( 'Delete Widget', 'n8ndash-pro' ); ?>" data-action="delete">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                    <?php endif; ?>
                                    <span class="n8n-widget__drag-handle" title="<?php esc_attr_e( 'Drag to move', 'n8ndash-pro' ); ?>">
                                        <span class="dashicons dashicons-move"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="n8ndash-widget__body">
                                <?php
                                // Create widget instance and render actual content
                                $widget_types = array(
                                    'data' => 'N8nDash_Data_Widget',
                                    'chart' => 'N8nDash_Chart_Widget',
                                    'custom' => 'N8nDash_Custom_Widget',
                                );
                                
                                if ( isset( $widget_types[ $widget->widget_type ] ) && class_exists( $widget_types[ $widget->widget_type ] ) ) {
                                    $widget_class = $widget_types[ $widget->widget_type ];
                                    $widget_instance = new $widget_class( (array) $widget );
                                    
                                    // Widget preview without duplicate header
                                    echo '<div class="n8ndash-widget-preview" data-widget-type="' . esc_attr( $widget->widget_type ) . '">';
                                    echo '<div class="n8ndash-widget-preview-body">';
                                    echo '<div class="n8ndash-widget-content-wrapper">';
                                    echo $widget_instance->render();
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                } else {
                                    // Fallback placeholder without duplicate header
                                    echo '<div class="n8ndash-widget-preview n8ndash-widget-preview-empty" data-widget-type="' . esc_attr( $widget->widget_type ) . '">';
                                    echo '<div class="n8ndash-widget-preview-body">';
                                    echo '<div class="n8ndash-widget-preview-empty">';
                                    switch ( $widget->widget_type ) {
                                        case 'data':
                                            echo '<span class="dashicons dashicons-chart-line"></span>';
                                            echo '<p>' . esc_html__( 'Data Widget - Configure to display KPI metrics or lists', 'n8ndash-pro' ) . '</p>';
                                            break;
                                        case 'chart':
                                            echo '<span class="dashicons dashicons-chart-area"></span>';
                                            echo '<p>' . esc_html__( 'Chart Widget - Configure to display charts and graphs', 'n8ndash-pro' ) . '</p>';
                                            break;
                                        case 'custom':
                                            echo '<span class="dashicons dashicons-admin-generic"></span>';
                                            echo '<p>' . esc_html__( 'Custom Widget - Configure to display custom content', 'n8ndash-pro' ) . '</p>';
                                            break;
                                        default:
                                            echo '<span class="dashicons dashicons-admin-generic"></span>';
                                            echo '<p>' . esc_html__( 'Unknown Widget Type', 'n8ndash-pro' ) . '</p>';
                                            break;
                                    }
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Widget Configuration Modal -->
<div id="n8ndash-widget-modal" class="n8ndash-modal" style="display:none;">
    <div class="n8ndash-modal__content n8ndash-modal__content--medium">
        <div class="n8ndash-modal__header">
            <h2 class="n8ndash-modal__title"><?php esc_html_e( 'Configure Widget', 'n8ndash-pro' ); ?></h2>
            <button class="n8ndash-modal__close" aria-label="<?php esc_attr_e( 'Close', 'n8ndash-pro' ); ?>">
                <span class="dashicons dashicons-no"></span>
            </button>
        </div>
        <div class="n8ndash-modal__body">
            <form id="n8ndash-widget-form">
                <input type="hidden" id="widget-id" value="">
                <input type="hidden" id="widget-type" value="">
                
                <!-- Common fields -->
                <div class="n8ndash-field">
                    <label for="widget-title"><?php esc_html_e( 'Widget Title', 'n8ndash-pro' ); ?></label>
                    <input type="text" id="widget-title" name="config[title]" class="regular-text" required>
                </div>
                
                <!-- Webhook Configuration -->
                <fieldset class="n8ndash-fieldset">
                    <legend><?php esc_html_e( 'Webhook Configuration', 'n8ndash-pro' ); ?></legend>
                    
                    <div class="n8ndash-field">
                        <label for="webhook-url"><?php esc_html_e( 'Webhook URL', 'n8ndash-pro' ); ?></label>
                        <input type="url" id="webhook-url" name="webhook[url]" class="large-text" required>
                        <p class="description"><?php esc_html_e( 'The n8n webhook URL to fetch data from', 'n8ndash-pro' ); ?></p>
                    </div>
                    
                    <div class="n8ndash-field">
                        <label for="webhook-method"><?php esc_html_e( 'HTTP Method', 'n8ndash-pro' ); ?></label>
                        <select id="webhook-method" name="webhook[method]">
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                        </select>
                    </div>
                    
                    <div class="n8ndash-field">
                        <label for="webhook-headers"><?php esc_html_e( 'Headers (JSON)', 'n8ndash-pro' ); ?></label>
                        <textarea id="webhook-headers" name="webhook[headers]" class="large-text code" rows="3" placeholder='{"Authorization": "Bearer token"}'></textarea>
                    </div>
                    
                    <div class="n8ndash-field">
                        <label for="webhook-body"><?php esc_html_e( 'Request Body (JSON)', 'n8ndash-pro' ); ?></label>
                        <textarea id="webhook-body" name="webhook[body]" class="large-text code" rows="3" placeholder='{"key": "value"}'></textarea>
                    </div>
                    
                    <div class="n8ndash-field">
                        <label for="refresh-interval"><?php esc_html_e( 'Refresh Interval (seconds)', 'n8ndash-pro' ); ?></label>
                        <input type="number" id="refresh-interval" name="config[refresh_interval]" min="0" value="300">
                        <p class="description"><?php esc_html_e( 'Set to 0 to disable auto-refresh', 'n8ndash-pro' ); ?></p>
                    </div>
                </fieldset>
                
                <!-- Type-specific fields will be inserted here -->
                <div id="n8ndash-widget-specific-fields"></div>
                
                <!-- Custom Widget Fields Management (Hidden by default) -->
                <div id="n8ndash-custom-widget-fields" style="display: none;">
                    <fieldset class="n8ndash-fieldset">
                        <legend><?php esc_html_e( 'Form Fields Configuration', 'n8ndash-pro' ); ?></legend>
                        
                        <div class="n8ndash-field">
                            <label for="add-field-type"><?php esc_html_e( 'Add New Field', 'n8ndash-pro' ); ?></label>
                            <div class="n8ndash-field-row">
                                <select id="add-field-type" class="n8n-field-type-select">
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
                                </select>
                                <button type="button" id="add-field-btn" class="button button-secondary">
                                    <?php esc_html_e( 'Add Field', 'n8ndash-pro' ); ?>
                                </button>
                            </div>
                        </div>
                        
                        <div class="n8ndash-field">
                            <label><?php esc_html_e( 'Form Fields', 'n8ndash-pro' ); ?></label>
                            <div id="field-list" class="n8n-fields-list">
                                <p class="n8n-no-fields"><?php esc_html_e( 'No fields added yet. Click "Add Field" to start building your form.', 'n8ndash-pro' ); ?></p>
                            </div>
                        </div>
                        
                        <!-- Hidden input to store fields data -->
                        <input type="hidden" id="fields-data" name="config[fields]" value="">
                    </fieldset>
                </div>
                
                <div class="n8ndash-modal__footer">
                    <button type="button" class="button n8ndash-modal-cancel"><?php esc_html_e( 'Cancel', 'n8ndash-pro' ); ?></button>
                    <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Widget', 'n8ndash-pro' ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.n8ndash-editor-container {
    display: flex;
    gap: 20px;
    margin-top: 20px;
    height: calc(100vh - 150px);
}

.n8ndash-editor-sidebar {
    width: 300px;
    flex-shrink: 0;
    overflow-y: auto;
}

.n8ndash-editor-main {
    flex: 1;
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.n8ndash-panel {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}

.n8ndash-panel__title {
    margin: 0 0 15px;
    font-size: 14px;
    font-weight: 600;
}

.n8ndash-field {
    margin-bottom: 15px;
}

.n8ndash-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.n8ndash-field .description {
    margin-top: 5px;
    color: #646970;
    font-size: 13px;
}

.n8ndash-widget-library {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.n8ndash-widget-type {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f0f0f1;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
}

.n8ndash-widget-type:hover {
    background: #e0e0e0;
    border-color: #8c8f94;
}

.n8ndash-widget-type .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    color: #2271b1;
}

.n8ndash-widget-type__label {
    font-weight: 600;
    display: block;
}

.n8ndash-widget-type__desc {
    font-size: 12px;
    color: #646970;
    display: block;
}

.n8ndash-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.n8ndash-actions .button {
    display: flex;
    align-items: center;
    gap: 5px;
}

.n8ndash-actions .button .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

#n8ndash-save-layout {
    background: #0073aa;
    border-color: #0073aa;
    color: white;
}

#n8ndash-save-layout:hover {
    background: #005a87;
    border-color: #005a87;
}

#n8ndash-save-layout:disabled {
    background: #ccc;
    border-color: #ccc;
    cursor: not-allowed;
}

.n8ndash-editor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #dcdcde;
}

.n8ndash-editor-header h2 {
    margin: 0;
    font-size: 18px;
}

.n8ndash-editor-tools {
    display: flex;
    gap: 5px;
}

.n8ndash-dashboard-canvas {
    flex: 1;
    position: relative;
    background: #f6f7f7;
    overflow: auto;
}

.n8ndash-dashboard-canvas.show-grid {
    background-image: 
        linear-gradient(rgba(0, 0, 0, 0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
    background-size: 20px 20px;
}

.n8ndash-canvas-empty {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: #646970;
}

.n8ndash-widget {
    position: absolute;
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    min-width: 200px;
    min-height: 150px;
}

.n8ndash-widget__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    border-bottom: 1px solid #dcdcde;
    cursor: move;
}

.n8ndash-widget__title {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
}

.n8ndash-widget__actions {
    display: flex;
    gap: 5px;
}

.n8ndash-widget__actions button {
    background: none;
    border: none;
    cursor: pointer;
    padding: 2px;
    color: #646970;
}

.n8ndash-widget__actions button:hover {
    color: #135e96;
}

.n8ndash-widget__body {
    padding: 15px;
    height: calc(100% - 45px);
}

.n8ndash-widget__placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #8c8f94;
}

.n8ndash-widget__placeholder .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    margin-bottom: 10px;
}

.n8ndash-fieldset {
    border: 1px solid #dcdcde;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.n8ndash-fieldset legend {
    font-weight: 600;
    padding: 0 5px;
}

.n8ndash-modal__content--medium {
    max-width: 600px;
}

.n8ndash-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #dcdcde;
}

/* Fullscreen mode */
.n8ndash-fullscreen .n8ndash-editor-main {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 100000;
    margin: 0;
    border-radius: 0;
}
</style>