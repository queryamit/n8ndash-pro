<?php
/**
 * Admin widget library page
 *
 * This file displays the widget library interface.
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/admin/partials
 * @author     Amit Anand Niraj <n8ndash@gmail.com>
 */

// Security check
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap n8ndash-admin-wrap">
    <h1 class="wp-heading-inline">
        <?php esc_html_e( 'Widget Library', 'n8ndash-pro' ); ?>
    </h1>
    
    <hr class="wp-header-end">
    
    <div class="n8ndash-widget-library-page">
        <div class="n8ndash-widget-types">
            <div class="n8ndash-widget-type-card">
                <div class="n8ndash-widget-type-card__icon">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
                <h3><?php esc_html_e( 'Data Widget', 'n8ndash-pro' ); ?></h3>
                <p><?php esc_html_e( 'Display key performance indicators, metrics, and data lists from your n8n workflows.', 'n8ndash-pro' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Single value display', 'n8ndash-pro' ); ?></li>
                    <li><?php esc_html_e( 'Data tables', 'n8ndash-pro' ); ?></li>
                    <li><?php esc_html_e( 'Progress indicators', 'n8ndash-pro' ); ?></li>
                    <li><?php esc_html_e( 'Real-time updates', 'n8ndash-pro' ); ?></li>
                </ul>
            </div>
            
            <div class="n8ndash-widget-type-card">
                <div class="n8ndash-widget-type-card__icon">
                    <span class="dashicons dashicons-chart-area"></span>
                </div>
                <h3><?php esc_html_e( 'Chart Widget', 'n8ndash-pro' ); ?></h3>
                <p><?php esc_html_e( 'Visualize your data with beautiful, interactive charts powered by Chart.js.', 'n8ndash-pro' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Line charts', 'n8ndash-pro' ); ?></li>
                    <li><?php esc_html_e( 'Bar charts', 'n8ndash-pro' ); ?></li>
                    <li><?php esc_html_e( 'Pie & Doughnut charts', 'n8ndash-pro' ); ?></li>
                    <li><?php esc_html_e( 'Area charts', 'n8ndash-pro' ); ?></li>
                </ul>
            </div>
            
            <div class="n8ndash-widget-type-card">
                <div class="n8ndash-widget-type-card__icon">
                    <span class="dashicons dashicons-admin-generic"></span>
                </div>
                <h3><?php esc_html_e( 'Custom Widget', 'n8ndash-pro' ); ?></h3>
                <p><?php esc_html_e( 'Create interactive forms and custom interfaces that integrate with your n8n workflows.', 'n8ndash-pro' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Custom forms', 'n8ndash-pro' ); ?></li>
                    <li><?php esc_html_e( 'Action buttons', 'n8ndash-pro' ); ?></li>
                    <li><?php esc_html_e( 'File uploads', 'n8ndash-pro' ); ?></li>
                    <li><?php esc_html_e( 'Custom HTML', 'n8ndash-pro' ); ?></li>
                </ul>
            </div>
        </div>
        
        <div class="n8ndash-widget-info">
            <h2><?php esc_html_e( 'How to Use Widgets', 'n8ndash-pro' ); ?></h2>
            
            <div class="n8ndash-steps">
                <div class="n8ndash-step">
                    <span class="n8ndash-step__number">1</span>
                    <div class="n8ndash-step__content">
                        <h4><?php esc_html_e( 'Create a Dashboard', 'n8ndash-pro' ); ?></h4>
                        <p><?php esc_html_e( 'Start by creating a new dashboard or editing an existing one.', 'n8ndash-pro' ); ?></p>
                    </div>
                </div>
                
                <div class="n8ndash-step">
                    <span class="n8ndash-step__number">2</span>
                    <div class="n8ndash-step__content">
                        <h4><?php esc_html_e( 'Add Widgets', 'n8ndash-pro' ); ?></h4>
                        <p><?php esc_html_e( 'Click on any widget type in the sidebar to add it to your dashboard.', 'n8ndash-pro' ); ?></p>
                    </div>
                </div>
                
                <div class="n8ndash-step">
                    <span class="n8ndash-step__number">3</span>
                    <div class="n8ndash-step__content">
                        <h4><?php esc_html_e( 'Configure Webhook', 'n8ndash-pro' ); ?></h4>
                        <p><?php esc_html_e( 'Connect your widget to an n8n webhook URL to fetch or send data.', 'n8ndash-pro' ); ?></p>
                    </div>
                </div>
                
                <div class="n8ndash-step">
                    <span class="n8ndash-step__number">4</span>
                    <div class="n8ndash-step__content">
                        <h4><?php esc_html_e( 'Customize & Arrange', 'n8ndash-pro' ); ?></h4>
                        <p><?php esc_html_e( 'Drag widgets to rearrange them and resize as needed.', 'n8ndash-pro' ); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="n8ndash-widget-examples">
            <h2><?php esc_html_e( 'Widget Examples', 'n8ndash-pro' ); ?></h2>
            
            <div class="n8ndash-examples-grid">
                <div class="n8ndash-example">
                    <h4><?php esc_html_e( 'Sales Dashboard', 'n8ndash-pro' ); ?></h4>
                    <p><?php esc_html_e( 'Track revenue, orders, and customer metrics in real-time.', 'n8ndash-pro' ); ?></p>
                </div>
                
                <div class="n8ndash-example">
                    <h4><?php esc_html_e( 'Server Monitoring', 'n8ndash-pro' ); ?></h4>
                    <p><?php esc_html_e( 'Monitor server health, uptime, and resource usage.', 'n8ndash-pro' ); ?></p>
                </div>
                
                <div class="n8ndash-example">
                    <h4><?php esc_html_e( 'Marketing Analytics', 'n8ndash-pro' ); ?></h4>
                    <p><?php esc_html_e( 'Visualize campaign performance and conversion rates.', 'n8ndash-pro' ); ?></p>
                </div>
                
                <div class="n8ndash-example">
                    <h4><?php esc_html_e( 'Support Tickets', 'n8ndash-pro' ); ?></h4>
                    <p><?php esc_html_e( 'Display and manage support ticket statistics.', 'n8ndash-pro' ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.n8ndash-widget-library-page {
    max-width: 1200px;
    margin: 20px 0;
}

.n8ndash-widget-types {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.n8ndash-widget-type-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
}

.n8ndash-widget-type-card__icon {
    font-size: 48px;
    line-height: 1;
    color: #2271b1;
    margin-bottom: 15px;
}

.n8ndash-widget-type-card__icon .dashicons {
    width: 48px;
    height: 48px;
    font-size: 48px;
}

.n8ndash-widget-type-card h3 {
    margin: 0 0 10px;
    font-size: 18px;
}

.n8ndash-widget-type-card p {
    color: #646970;
    margin-bottom: 15px;
}

.n8ndash-widget-type-card ul {
    margin: 0;
    padding-left: 20px;
}

.n8ndash-widget-type-card li {
    margin-bottom: 5px;
    color: #50575e;
}

.n8ndash-widget-info {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 30px;
    margin-bottom: 40px;
}

.n8ndash-widget-info h2 {
    margin-top: 0;
}

.n8ndash-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 20px;
}

.n8ndash-step {
    display: flex;
    gap: 15px;
}

.n8ndash-step__number {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: #2271b1;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
}

.n8ndash-step__content h4 {
    margin: 0 0 5px;
    font-size: 16px;
}

.n8ndash-step__content p {
    margin: 0;
    color: #646970;
}

.n8ndash-widget-examples {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 30px;
}

.n8ndash-widget-examples h2 {
    margin-top: 0;
}

.n8ndash-examples-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.n8ndash-example {
    padding: 20px;
    background: #f6f7f7;
    border-radius: 4px;
}

.n8ndash-example h4 {
    margin: 0 0 10px;
    color: #1d2327;
}

.n8ndash-example p {
    margin: 0;
    color: #646970;
}
</style>