<?php
/**
 * Admin dashboard preview page - Full Window Mode
 *
 * This template renders the exact same admin dashboard view but in full-window mode
 * without the sidebar and add widgets menu, maintaining the same look and functionality.
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/admin/partials
 * @author     Amit Anand Niraj <n8ndash@gmail.com>
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get dashboard data
$dashboard_id = isset( $_GET['dashboard_id'] ) ? intval( $_GET['dashboard_id'] ) : 0;
$dashboard = N8nDash_DB::get_dashboard( $dashboard_id );

if ( ! $dashboard ) {
    wp_die( __( 'Dashboard not found.', 'n8ndash-pro' ) );
}

// Get dashboard widgets
$widgets = N8nDash_DB::get_dashboard_widgets( $dashboard_id );

// Enqueue admin styles and scripts - Same as admin dashboard
wp_enqueue_style( 'n8ndash-admin' );
wp_enqueue_script( 'n8ndash-admin' );

?>

<div class="wrap n8ndash-admin-wrap n8ndash-preview-full-window">
    <!-- Compact Header - Same as admin dashboard but minimal -->
    <div class="n8ndash-preview-header-compact">
        <div class="n8ndash-preview-header-left">
            <h1 class="wp-heading-inline">
                <?php echo esc_html( $dashboard->title ); ?>
                <span class="n8ndash-preview-badge"><?php esc_html_e( 'Preview Mode', 'n8ndash-pro' ); ?></span>
            </h1>
            <?php if ( ! empty( $dashboard->description ) ) : ?>
                <p class="n8ndash-preview-description"><?php echo esc_html( $dashboard->description ); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="n8ndash-preview-header-right">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=n8ndash-edit&dashboard_id=' . $dashboard_id ) ); ?>" 
               class="button button-primary">
                <span class="dashicons dashicons-edit"></span>
                <?php esc_html_e( 'Edit Dashboard', 'n8ndash-pro' ); ?>
            </a>
            
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=n8ndash' ) ); ?>" 
               class="button">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php esc_html_e( 'Back to Dashboards', 'n8ndash-pro' ); ?>
            </a>
        </div>
    </div>

    <hr class="wp-header-end">

    <!-- Full Window Dashboard Canvas - Same as admin dashboard -->
    <div class="n8ndash-preview-dashboard-full">
        <?php if ( empty( $widgets ) ) : ?>
            <div class="n8ndash-preview-empty">
                <div class="n8ndash-preview-empty-icon">
                    <span class="dashicons dashicons-chart-area"></span>
                </div>
                <h3><?php esc_html_e( 'No Widgets Yet', 'n8ndash-pro' ); ?></h3>
                <p><?php esc_html_e( 'This dashboard doesn\'t have any widgets configured yet.', 'n8ndash-pro' ); ?></p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=n8ndash-edit&dashboard_id=' . $dashboard_id ) ); ?>" 
                   class="button button-primary">
                    <?php esc_html_e( 'Add Widgets', 'n8ndash-pro' ); ?>
                </a>
            </div>
        <?php else : ?>
            <!-- Use the EXACT same structure as admin dashboard -->
            <div id="n8ndash-dashboard-canvas" class="n8ndash-dashboard-canvas">
                <?php foreach ( $widgets as $widget ) : ?>
                    <?php
                    // Extract position data - Same as admin dashboard
                    $position = is_array( $widget->position ) ? $widget->position : array();
                    $left = isset( $position['x'] ) ? $position['x'] : 50;
                    $top = isset( $position['y'] ) ? $position['y'] : 50;
                    $width = isset( $position['width'] ) ? $position['width'] : 300;
                    $height = isset( $position['height'] ) ? $position['height'] : 200;
                    ?>
                    <!-- EXACT same widget HTML structure as admin dashboard -->
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
                            // Create widget instance and render actual content - Same as admin dashboard
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
            </div>
        <?php endif; ?>
    </div>
</div>












