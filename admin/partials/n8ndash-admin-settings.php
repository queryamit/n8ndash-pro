<?php
/**
 * Admin settings page
 *
 * This file displays the plugin settings interface.
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/admin/partials
 * @author     Amit Anand Niraj <n8ndash@gmail.com>
 */

// Get current settings
$settings = get_option( 'n8ndash_settings', array() );

// Default values - only keep working settings
$defaults = array(
    'enable_public_dashboards' => false,
);

$settings = wp_parse_args( $settings, $defaults );

?>

<div class="wrap n8ndash-admin-wrap">
    <h1><?php esc_html_e( 'n8nDash Settings', 'n8ndash-pro' ); ?></h1>
    
    <?php if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true' ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'Settings saved successfully.', 'n8ndash-pro' ); ?></p>
        </div>
    <?php endif; ?>
    
    <form id="n8ndash-settings-form" method="post" action="">
        <?php wp_nonce_field( 'n8ndash_save_settings', 'n8ndash_settings_nonce' ); ?>
        
        <div class="n8ndash-settings-container">
            <!-- General Settings -->
            <div class="n8ndash-settings-section">
                <h2 class="n8ndash-settings-section__title">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e( 'General Settings', 'n8ndash-pro' ); ?>
                </h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enable_public_dashboards">
                                <?php esc_html_e( 'Public Dashboards', 'n8ndash-pro' ); ?>
                            </label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       id="enable_public_dashboards" 
                                       name="n8ndash_settings[enable_public_dashboards]" 
                                       value="1" 
                                       <?php checked( $settings['enable_public_dashboards'] ); ?>>
                                <?php esc_html_e( 'Allow users to create public dashboards', 'n8ndash-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'When enabled, users can make their dashboards accessible via a public URL.', 'n8ndash-pro' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Uninstall Options -->
            <div class="n8ndash-settings-section">
                <h2 class="n8ndash-settings-section__title">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e( 'Uninstall Options', 'n8ndash-pro' ); ?>
                </h2>
                
                <div class="n8ndash-uninstall-options">
                    <div class="n8ndash-uninstall-option">
                        <h4><?php esc_html_e( 'Data Management', 'n8ndash-pro' ); ?></h4>
                        <p><?php esc_html_e( 'Choose what happens to your data when you deactivate or remove the plugin:', 'n8ndash-pro' ); ?></p>
                        
                        <div class="n8ndash-uninstall-choices">
                            <label class="n8ndash-uninstall-choice">
                                <input type="radio" name="n8ndash_uninstall_mode" value="keep" checked />
                                <span class="choice-title"><?php esc_html_e( 'Keep All Data', 'n8ndash-pro' ); ?></span>
                                <span class="choice-description"><?php esc_html_e( 'Keep all dashboards, widgets, and settings. Plugin can be reactivated with all data intact.', 'n8ndash-pro' ); ?></span>
                            </label>
                            
                            <label class="n8ndash-uninstall-choice">
                                <input type="radio" name="n8ndash_uninstall_mode" value="clean" />
                                <span class="choice-title"><?php esc_html_e( 'Clean Data Only', 'n8ndash-pro' ); ?></span>
                                <span class="choice-description"><?php esc_html_e( 'Remove dashboards and widgets but keep plugin settings and user capabilities.', 'n8ndash-pro' ); ?></span>
                            </label>
                            
                            <label class="n8ndash-uninstall-choice">
                                <input type="radio" name="n8ndash_uninstall_mode" value="remove" />
                                <span class="choice-title"><?php esc_html_e( 'Remove Everything', 'n8ndash-pro' ); ?></span>
                                <span class="choice-description"><?php esc_html_e( 'Remove all plugin data, settings, capabilities, and created pages. This action cannot be undone.', 'n8ndash-pro' ); ?></span>
                            </label>
                        </div>
                        
                        <div class="n8ndash-uninstall-actions">
                            <button type="button" id="execute-uninstall" class="button button-primary">
                                <span class="dashicons dashicons-trash"></span>
                                <?php esc_html_e( 'Execute Uninstall', 'n8ndash-pro' ); ?>
                            </button>
                        </div>
                        
                        <div class="n8ndash-uninstall-notice">
                            <p><strong><?php esc_html_e( 'Note:', 'n8ndash-pro' ); ?></strong> 
                            <?php esc_html_e( 'To backup your dashboards before uninstalling, please use the', 'n8ndash-pro' ); ?> 
                            <a href="<?php echo admin_url('admin.php?page=n8ndash-import-export'); ?>"><?php esc_html_e( 'Import/Export tab', 'n8ndash-pro' ); ?></a> 
                            <?php esc_html_e( 'to export your data. To restore data, use the Import All Dashboards feature in the same tab.', 'n8ndash-pro' ); ?></p>
                        </div>
                        
                        <div id="uninstall-confirmation" class="n8ndash-uninstall-confirmation" style="display: none;">
                            <div class="n8ndash-confirmation-content">
                                <h4><?php esc_html_e( 'Final Confirmation', 'n8ndash-pro' ); ?></h4>
                                <p id="confirmation-message"></p>
                                <div class="n8ndash-confirmation-actions">
                                    <button type="button" id="confirm-uninstall" class="button button-primary">
                                        <?php esc_html_e( 'Yes, Proceed', 'n8ndash-pro' ); ?>
                                    </button>
                                    <button type="button" id="cancel-uninstall" class="button button-secondary">
                                        <?php esc_html_e( 'Cancel', 'n8ndash-pro' ); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <button type="submit" class="button button-primary">
                <?php esc_html_e( 'Save Settings', 'n8ndash-pro' ); ?>
            </button>
        </p>
    </form>
</div>

<style>
.n8ndash-settings-container {
    max-width: 800px;
    margin-top: 20px;
}

.n8ndash-settings-section {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    margin-bottom: 20px;
    padding: 20px;
}

.n8ndash-settings-section__title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #dcdcde;
    font-size: 18px;
    font-weight: 600;
}

.n8ndash-settings-section__title .dashicons {
    color: #2271b1;
}

/* Uninstall Options Styles */
.n8ndash-uninstall-options {
    margin-top: 20px;
}

.n8ndash-uninstall-option h4 {
    margin: 0 0 15px;
    color: #1d2327;
    font-size: 16px;
}

.n8ndash-uninstall-option p {
    margin: 0 0 20px;
    color: #646970;
}

.n8ndash-uninstall-choices {
    margin-bottom: 25px;
}

.n8ndash-uninstall-choice {
    display: block;
    margin-bottom: 15px;
    padding: 15px;
    border: 1px solid #dcdcde;
    border-radius: 4px;
    background: #f9f9f9;
    cursor: pointer;
    transition: all 0.2s ease;
}

.n8ndash-uninstall-choice:hover {
    border-color: #2271b1;
    background: #f0f6fc;
}

.n8ndash-uninstall-choice input[type="radio"] {
    margin-right: 10px;
    vertical-align: top;
    margin-top: 2px;
}

.choice-title {
    display: block;
    font-weight: 600;
    color: #1d2327;
    margin-bottom: 5px;
}

.choice-description {
    display: block;
    color: #646970;
    font-size: 13px;
    line-height: 1.4;
}

.n8ndash-uninstall-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #dcdcde;
}

.n8ndash-uninstall-actions .button {
    margin-right: 10px;
}

.n8ndash-uninstall-actions .dashicons {
    margin-right: 5px;
    vertical-align: middle;
}

.n8ndash-uninstall-confirmation {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.n8ndash-confirmation-content {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    max-width: 500px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.n8ndash-confirmation-content h4 {
    margin: 0 0 15px;
    color: #d63638;
    font-size: 18px;
}

.n8ndash-confirmation-content p {
    margin: 0 0 25px;
    color: #646970;
    line-height: 1.5;
}

.n8ndash-confirmation-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.n8ndash-confirmation-actions .button {
    min-width: 100px;
}

.form-table th {
    width: 200px;
}

.n8ndash-uninstall-notice {
    margin-top: 15px;
    padding: 15px;
    background: #f0f8ff;
    border: 1px solid #b8daff;
    border-radius: 4px;
    border-left: 4px solid #0073aa;
}

.n8ndash-uninstall-notice p {
    margin: 0;
    color: #1d2327;
}

.n8ndash-uninstall-notice a {
    color: #0073aa;
    text-decoration: none;
    font-weight: 600;
}

.n8ndash-uninstall-notice a:hover {
    text-decoration: underline;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Save settings via AJAX
    $('#n8ndash-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submit = $form.find('.button-primary');
        
        $submit.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'n8ndash-pro' ) ); ?>');
        
        $.post(ajaxurl, {
            action: 'n8ndash_save_settings',
            settings: $form.serialize(),
            nonce: $('#n8ndash_settings_nonce').val()
        }, function(response) {
            if (response.success) {
                // Reload page to show success message
                window.location.href = window.location.href + '&settings-updated=true';
            } else {
                alert(response.data.message || '<?php echo esc_js( __( 'Failed to save settings', 'n8ndash-pro' ) ); ?>');
                $submit.prop('disabled', false).text('<?php echo esc_js( __( 'Save Settings', 'n8ndash-pro' ) ); ?>');
            }
        });
    });
    
    // Uninstall Options
    $('#execute-uninstall').on('click', function() {
        var uninstallMode = $('input[name="n8ndash_uninstall_mode"]:checked').val();
        var confirmationMessage = '';
        
        switch(uninstallMode) {
            case 'keep':
                alert('<?php echo esc_js( __( 'No action needed. Data will be kept when plugin is deactivated.', 'n8ndash-pro' ) ); ?>');
                return;
                
            case 'clean':
                confirmationMessage = '<?php echo esc_js( __( 'This will remove all dashboards and widgets but keep plugin settings and user capabilities. Are you sure you want to proceed?', 'n8ndash-pro' ) ); ?>';
                break;
                
            case 'remove':
                confirmationMessage = '<?php echo esc_js( __( 'WARNING: This will remove ALL plugin data including dashboards, widgets, settings, capabilities, and created pages. This action cannot be undone. Are you absolutely sure you want to proceed?', 'n8ndash-pro' ) ); ?>';
                break;
        }
        
        $('#confirmation-message').text(confirmationMessage);
        $('#uninstall-confirmation').show();
    });
    
    $('#confirm-uninstall').on('click', function() {
        var uninstallMode = $('input[name="n8ndash_uninstall_mode"]:checked').val();
        var $button = $(this);
        
        $button.prop('disabled', true).text('<?php echo esc_js( __( 'Processing...', 'n8ndash-pro' ) ); ?>');
        
        $.post(ajaxurl, {
            action: 'n8ndash_execute_uninstall',
            mode: uninstallMode,
            nonce: '<?php echo wp_create_nonce( 'n8ndash_uninstall' ); ?>'
        }, function(response) {
            if (response.success) {
                alert(response.data.message);
                if (uninstallMode === 'remove') {
                    // Redirect to plugins page after complete removal
                    window.location.href = '<?php echo admin_url( 'plugins.php' ); ?>';
                } else {
                    // Reload page to show updated status
                    window.location.reload();
                }
            } else {
                alert(response.data.message || '<?php echo esc_js( __( 'Uninstall failed', 'n8ndash-pro' ) ); ?>');
            }
            $button.prop('disabled', false).text('<?php echo esc_js( __( 'Yes, Proceed', 'n8ndash-pro' ) ); ?>');
            $('#uninstall-confirmation').hide();
        });
    });
    
    $('#cancel-uninstall').on('click', function() {
        $('#uninstall-confirmation').hide();
    });
});
</script>