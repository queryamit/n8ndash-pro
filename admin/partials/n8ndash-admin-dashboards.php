<?php
/**
 * Admin dashboards listing page
 *
 * This file displays the list of all dashboards in the admin area.
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/admin/partials
 * @author     Amit Anand Niraj <n8ndash@gmail.com>
 */

// Get current user dashboards
$user_id = get_current_user_id();
$dashboards = N8nDash_DB::get_user_dashboards( $user_id, array(
    'limit' => 50,
    'orderby' => 'updated_at',
    'order' => 'DESC',
) );

?>

<div class="wrap n8ndash-admin-wrap">
    <h1 class="wp-heading-inline">
        <?php esc_html_e( 'n8nDash Dashboards', 'n8ndash-pro' ); ?>
    </h1>
    
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=n8ndash-new' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New', 'n8ndash-pro' ); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <?php if ( empty( $dashboards ) ) : ?>
        
        <div class="n8ndash-empty-state">
            <div class="n8ndash-empty-state__icon">
                <span class="dashicons dashicons-dashboard"></span>
            </div>
            <h2><?php esc_html_e( 'No dashboards yet', 'n8ndash-pro' ); ?></h2>
            <p><?php esc_html_e( 'Create your first dashboard to start monitoring your n8n workflows.', 'n8ndash-pro' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=n8ndash-new' ) ); ?>" class="button button-primary button-hero">
                <?php esc_html_e( 'Create Your First Dashboard', 'n8ndash-pro' ); ?>
            </a>
        </div>
        
    <?php else : ?>
        
        <div class="n8ndash-dashboards-grid">
            <?php foreach ( $dashboards as $dashboard ) : ?>
                <div class="n8ndash-dashboard-card">
                    <div class="n8ndash-dashboard-card__header">
                        <h3 class="n8ndash-dashboard-card__title">
                            <?php 
                            $preview_url = add_query_arg( 
                                array( 
                                    'page' => 'n8ndash-preview',
                                    'dashboard_id' => $dashboard->id
                                ), 
                                admin_url( 'admin.php' ) 
                            );
                            ?>
                            <a href="<?php echo esc_url( $preview_url ); ?>" target="_blank">
                                <?php echo esc_html( $dashboard->title ); ?>
                            </a>
                        </h3>
                        <div class="n8ndash-dashboard-card__actions">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=n8ndash-edit&dashboard_id=' . $dashboard->id ) ); ?>" 
                               class="button button-small"
                               title="<?php esc_attr_e( 'Edit Dashboard', 'n8ndash-pro' ); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </a>
                            <button class="button button-small n8ndash-view-dashboard" 
                                    data-dashboard-id="<?php echo esc_attr( $dashboard->id ); ?>"
                                    title="<?php esc_attr_e( 'Preview Dashboard', 'n8ndash-pro' ); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            <button class="button button-small n8ndash-delete-dashboard" 
                                    data-dashboard-id="<?php echo esc_attr( $dashboard->id ); ?>"
                                    data-dashboard-title="<?php echo esc_attr( $dashboard->title ); ?>"
                                    title="<?php esc_attr_e( 'Delete Dashboard', 'n8ndash-pro' ); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    
                    <?php if ( $dashboard->description ) : ?>
                        <div class="n8ndash-dashboard-card__description">
                            <?php echo esc_html( $dashboard->description ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="n8ndash-dashboard-card__meta">
                        <span class="n8ndash-dashboard-card__widgets">
                            <?php
                            $widget_count = count( N8nDash_DB::get_dashboard_widgets( $dashboard->id ) );
                            printf(
                                esc_html( _n( '%d widget', '%d widgets', $widget_count, 'n8ndash-pro' ) ),
                                $widget_count
                            );
                            ?>
                        </span>
                        <span class="n8ndash-dashboard-card__updated">
                            <?php
                            printf(
                                esc_html__( 'Updated %s', 'n8ndash-pro' ),
                                human_time_diff( strtotime( $dashboard->updated_at ), current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'n8ndash-pro' )
                            );
                            ?>
                        </span>
                        <span class="n8ndash-dashboard-card__owner">
                            <span class="dashicons dashicons-admin-users" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px;"></span>
                            <?php
                            $owner_user = get_userdata( $dashboard->user_id );
                            if ( $owner_user ) {
                                printf(
                                    esc_html__( 'Created by %s', 'n8ndash-pro' ),
                                    esc_html( $owner_user->display_name )
                                );
                            } else {
                                esc_html_e( 'Created by Unknown User', 'n8ndash-pro' );
                            }
                            ?>
                        </span>
                    </div>
                    
                                         <div class="n8ndash-dashboard-card__status-container">
                         <?php if ( $dashboard->status === 'active' ) : ?>
                             <span class="n8ndash-status n8ndash-status--active">
                                 <?php esc_html_e( 'Active', 'n8ndash-pro' ); ?>
                             </span>
                         <?php else : ?>
                             <span class="n8ndash-status n8ndash-status--inactive">
                                 <?php esc_html_e( 'Inactive', 'n8ndash-pro' ); ?>
                             </span>
                         <?php endif; ?>
                         
                         <?php 
                         // Add public/private status indicator
                         $settings = $dashboard->settings ?: array();
                         if ( is_string( $settings ) ) {
                             $settings = json_decode( $settings, true ) ?: array();
                         }
                         $is_public = ! empty( $settings['is_public'] );
                         ?>
                         <?php if ( $is_public ) : ?>
                             <span class="n8ndash-status n8ndash-status--public">
                                 <span class="dashicons dashicons-share"></span>
                                 <?php esc_html_e( 'Public', 'n8ndash-pro' ); ?>
                             </span>
                             <button class="n8ndash-copy-link" 
                                     data-public-url="<?php echo esc_url( N8nDash_Frontend::get_public_url( $dashboard ) ); ?>"
                                     title="<?php esc_attr_e( 'Copy Public Link', 'n8ndash-pro' ); ?>">
                                 <span class="dashicons dashicons-clipboard"></span>
                             </button>
                         <?php else : ?>
                             <span class="n8ndash-status n8ndash-status--private">
                                 <span class="dashicons dashicons-lock"></span>
                                 <?php esc_html_e( 'Private', 'n8ndash-pro' ); ?>
                             </span>
                         <?php endif; ?>
                     </div>
                </div>
            <?php endforeach; ?>
        </div>
        
    <?php endif; ?>
</div>



<style>
.n8ndash-admin-wrap {
    margin-top: 20px;
}

.n8ndash-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    margin-top: 20px;
}

.n8ndash-empty-state__icon {
    font-size: 48px;
    color: #dcdcde;
    margin-bottom: 20px;
}

.n8ndash-empty-state h2 {
    font-size: 24px;
    margin: 0 0 10px;
}

.n8ndash-empty-state p {
    color: #646970;
    font-size: 14px;
    margin: 0 0 20px;
}

.n8ndash-dashboards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.n8ndash-dashboard-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
    position: relative;
    transition: box-shadow 0.2s;
}

.n8ndash-dashboard-card:hover {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.n8ndash-dashboard-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.n8ndash-dashboard-card__title {
    margin: 0;
    font-size: 18px;
}

.n8ndash-dashboard-card__title a {
    text-decoration: none;
    color: #1d2327;
}

.n8ndash-dashboard-card__title a:hover {
    color: #2271b1;
}

.n8ndash-dashboard-card__actions {
    display: flex;
    gap: 5px;
}

.n8ndash-dashboard-card__actions .button {
    padding: 0 8px;
    min-height: 26px;
    line-height: 24px;
}

.n8ndash-dashboard-card__actions .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.n8ndash-dashboard-card__description {
    color: #646970;
    font-size: 14px;
    margin-bottom: 15px;
}

.n8ndash-dashboard-card__meta {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: #787c82;
    margin-bottom: 10px;
    flex-wrap: wrap;
    gap: 10px;
}

.n8ndash-dashboard-card__owner {
    color: #2271b1;
    font-weight: 500;
    display: flex;
    align-items: center;
    background: #f0f7ff;
    padding: 4px 8px;
    border-radius: 3px;
    border: 1px solid #d1ecf1;
}

.n8ndash-dashboard-card__status-container {
    position: absolute;
    bottom: 10px;
    right: 10px;
    display: flex;
    gap: 8px;
    align-items: center;
}

.n8ndash-status {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
}

.n8ndash-status--active {
    background: #d7f7c2;
    color: #0a4b33;
}

.n8ndash-status--inactive {
    background: #f0f0f1;
    color: #646970;
}

.n8ndash-status--public {
    background: #d1ecf1;
    color: #0c5460;
}

.n8ndash-status--private {
    background: #f8d7da;
    color: #721c24;
}

.n8ndash-copy-link {
    background: none;
    border: none;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 3px;
    color: #2271b1;
    transition: all 0.2s;
}

.n8ndash-copy-link:hover {
    background: #f0f7ff;
    color: #135e96;
}

.n8ndash-copy-link .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}


</style>

<script>
jQuery(document).ready(function($) {
    // Preview dashboard
    $('.n8ndash-view-dashboard').on('click', function() {
        var dashboardId = $(this).data('dashboard-id');
        // Use admin preview page for proper dashboard rendering
        var previewUrl = '<?php 
            $js_preview_url = add_query_arg( 
                array( 'page' => 'n8ndash-preview' ), 
                admin_url( 'admin.php' ) 
            );
            echo esc_url( $js_preview_url );
        ?>' + '&dashboard_id=' + dashboardId;
        window.open(previewUrl, '_blank');
    });
    

    
    // Copy public link
    $('.n8ndash-copy-link').on('click', function() {
        var publicUrl = $(this).data('public-url');
        var $button = $(this);
        
        // Copy to clipboard
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(publicUrl).then(function() {
                // Show success feedback
                $button.find('.dashicons').removeClass('dashicons-clipboard').addClass('dashicons-yes');
                $button.attr('title', '<?php echo esc_js( __( 'Link copied!', 'n8ndash-pro' ) ); ?>');
                
                // Reset after 2 seconds
                setTimeout(function() {
                    $button.find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-clipboard');
                    $button.attr('title', '<?php echo esc_js( __( 'Copy Public Link', 'n8ndash-pro' ) ); ?>');
                }, 2000);
            });
        } else {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = publicUrl;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            // Show success feedback
            $button.find('.dashicons').removeClass('dashicons-clipboard').addClass('dashicons-yes');
            $button.attr('title', '<?php echo esc_js( __( 'Link copied!', 'n8ndash-pro' ) ); ?>');
            
            // Reset after 2 seconds
            setTimeout(function() {
                $button.find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-clipboard');
                $button.attr('title', '<?php echo esc_js( __( 'Copy Public Link', 'n8ndash-pro' ) ); ?>');
            }, 2000);
        }
    });
    
    // Delete dashboard
    $('.n8ndash-delete-dashboard').on('click', function() {
        var dashboardId = $(this).data('dashboard-id');
        var dashboardTitle = $(this).data('dashboard-title');
        
        if (confirm('<?php echo esc_js( __( 'Are you sure you want to delete this dashboard?', 'n8ndash-pro' ) ); ?>\n\n' + dashboardTitle)) {
            var $button = $(this);
            $button.prop('disabled', true);
            
            $.post(ajaxurl, {
                action: 'n8ndash_delete_dashboard',
                dashboard_id: dashboardId,
                nonce: '<?php echo wp_create_nonce( 'n8ndash_admin_nonce' ); ?>'
            }, function(response) {
                if (response.success) {
                    $button.closest('.n8ndash-dashboard-card').fadeOut(function() {
                        $(this).remove();
                        
                        // Check if no dashboards left
                        if ($('.n8ndash-dashboard-card').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert(response.data.message || '<?php echo esc_js( __( 'Failed to delete dashboard', 'n8ndash-pro' ) ); ?>');
                    $button.prop('disabled', false);
                }
            });
        }
    });
});
</script>