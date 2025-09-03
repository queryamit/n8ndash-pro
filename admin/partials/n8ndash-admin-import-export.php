<?php
/**
 * Admin import/export page
 *
 * This file displays the import/export interface.
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

// FIX: Don't execute database queries when file is included - only when displayed
// $dashboards = N8nDash_DB::get_user_dashboards();
?>

<div class="wrap n8ndash-admin-wrap">
    <h1 class="wp-heading-inline">
        <?php esc_html_e( 'Import/Export Dashboards', 'n8ndash-pro' ); ?>
    </h1>
    
    <hr class="wp-header-end">
    
    <div class="n8ndash-import-export-page">
        <!-- Quick Actions Container -->
        <div class="n8ndash-section n8ndash-quick-actions">
            <h2><?php esc_html_e( 'Quick Actions', 'n8ndash-pro' ); ?></h2>
            <p><?php esc_html_e( 'Quickly export or import all dashboards at once.', 'n8ndash-pro' ); ?></p>
            
            <div class="n8ndash-quick-actions-grid">
                <div class="n8ndash-quick-action">
                    <button type="button" id="n8ndash-export-all" class="button button-primary">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e( 'Export All Dashboards', 'n8ndash-pro' ); ?>
                    </button>
                    <p class="description"><?php esc_html_e( 'Quick export for all dashboards.', 'n8ndash-pro' ); ?></p>
                </div>
                
                <div class="n8ndash-quick-action">
                    <button type="button" id="n8ndash-import-all-dashboards" class="button button-primary">
                        <span class="dashicons dashicons-upload"></span>
                        <?php esc_html_e( 'Import All Dashboards', 'n8ndash-pro' ); ?>
                    </button>
                    <p class="description"><?php esc_html_e( 'Quick import for files containing multiple dashboards.', 'n8ndash-pro' ); ?></p>
                </div>
            </div>
        </div>
        
        <div class="n8ndash-import-export-grid">
            <!-- Export Section -->
            <div class="n8ndash-section n8ndash-export-section">
                <h2><?php esc_html_e( 'Export Dashboards', 'n8ndash-pro' ); ?></h2>
                <p><?php esc_html_e( 'Export your dashboards to share with others or create backups.', 'n8ndash-pro' ); ?></p>
                
                <!-- Individual Dashboard Export -->
                <div class="n8ndash-individual-export">
                    <h3><?php esc_html_e( 'Export Individual Dashboard', 'n8ndash-pro' ); ?></h3>
                    <div id="n8ndash-export-form-container">
                        <p><?php esc_html_e( 'Loading dashboards...', 'n8ndash-pro' ); ?></p>
                    </div>
                </div>
                
                <div id="n8ndash-export-result" style="display:none;">
                    <h3><?php esc_html_e( 'Export Data', 'n8ndash-pro' ); ?></h3>
                    <p><?php esc_html_e( 'Copy the JSON data below or download as a file:', 'n8ndash-pro' ); ?></p>
                    <textarea id="n8ndash-export-data" class="large-text code" rows="10" readonly></textarea>
                    <p>
                        <button type="button" id="n8ndash-copy-export" class="button">
                            <span class="dashicons dashicons-clipboard"></span>
                            <?php esc_html_e( 'Copy to Clipboard', 'n8ndash-pro' ); ?>
                        </button>
                        <button type="button" id="n8ndash-download-export" class="button">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Download File', 'n8ndash-pro' ); ?>
                        </button>
                    </p>
                </div>
            </div>
            
            <!-- Import Section -->
            <div class="n8ndash-section n8ndash-import-section">
                <h2><?php esc_html_e( 'Import Dashboards', 'n8ndash-pro' ); ?></h2>
                <p><?php esc_html_e( 'Import dashboards from JSON files or paste the export data.', 'n8ndash-pro' ); ?></p>
                
                <!-- Individual Dashboard Import -->
                <div class="n8ndash-individual-import">
                    <h3><?php esc_html_e( 'Import Individual Dashboard', 'n8ndash-pro' ); ?></h3>
                    <form id="n8ndash-import-form" method="post">
                        <?php wp_nonce_field( 'n8ndash_import', 'n8ndash_import_nonce' ); ?>
                        
                        <div class="n8ndash-form-group">
                            <label><?php esc_html_e( 'Import Method', 'n8ndash-pro' ); ?></label>
                            <div class="n8ndash-radio-group">
                                <label>
                                    <input type="radio" name="import_method" value="file" checked>
                                    <?php esc_html_e( 'Upload JSON file', 'n8ndash-pro' ); ?>
                                </label>
                                <label>
                                    <input type="radio" name="import_method" value="paste">
                                    <?php esc_html_e( 'Paste JSON data', 'n8ndash-pro' ); ?>
                                </label>
                            </div>
                        </div>
                        
                        <div id="n8ndash-import-file" class="n8ndash-import-method">
                            <div class="n8ndash-form-group">
                                <label for="import-file"><?php esc_html_e( 'Select File', 'n8ndash-pro' ); ?></label>
                                <input type="file" id="import-file" name="import_file" accept=".json" required>
                                <p class="description"><?php esc_html_e( 'Select a JSON file exported from n8nDash.', 'n8ndash-pro' ); ?></p>
                            </div>
                        </div>
                        
                        <div id="n8ndash-import-paste" class="n8ndash-import-method" style="display:none;">
                            <div class="n8ndash-form-group">
                                <label for="import-data"><?php esc_html_e( 'JSON Data', 'n8ndash-pro' ); ?></label>
                                <textarea id="import-data" name="import_data" class="large-text code" rows="10" placeholder='{"version":"1.0.0","dashboard":{...}}'></textarea>
                            </div>
                        </div>
                        
                        <div class="n8ndash-form-group">
                            <label>
                                <input type="checkbox" name="overwrite_existing" value="1">
                                <?php esc_html_e( 'Overwrite if dashboard with same title exists', 'n8ndash-pro' ); ?>
                            </label>
                        </div>
                        
                        <button type="button" id="n8ndash-import-button" class="button button-primary">
                            <span class="dashicons dashicons-upload"></span>
                            <?php esc_html_e( 'Import Dashboard', 'n8ndash-pro' ); ?>
                        </button>
                    </form>
                    
                    <div id="n8ndash-import-result" class="notice" style="display:none;">
                        <p></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="n8ndash-section n8ndash-instructions">
            <h2><?php esc_html_e( 'Import/Export Instructions', 'n8ndash-pro' ); ?></h2>
            
            <div class="n8ndash-instructions-grid">
                <div class="n8ndash-instruction">
                    <h3><?php esc_html_e( 'Exporting', 'n8ndash-pro' ); ?></h3>
                    <ol>
                        <li><?php esc_html_e( 'Select the dashboard you want to export', 'n8ndash-pro' ); ?></li>
                        <li><?php esc_html_e( 'Choose what to include (widgets, settings)', 'n8ndash-pro' ); ?></li>
                        <li><?php esc_html_e( 'Click Export Dashboard', 'n8ndash-pro' ); ?></li>
                        <li><?php esc_html_e( 'Copy the JSON or download as file', 'n8ndash-pro' ); ?></li>
                    </ol>
                </div>
                
                <div class="n8ndash-instruction">
                    <h3><?php esc_html_e( 'Importing', 'n8ndash-pro' ); ?></h3>
                    <ol>
                        <li><?php esc_html_e( 'Choose import method (file or paste)', 'n8ndash-pro' ); ?></li>
                        <li><?php esc_html_e( 'Select file or paste JSON data', 'n8ndash-pro' ); ?></li>
                        <li><?php esc_html_e( 'Click Import Dashboard', 'n8ndash-pro' ); ?></li>
                        <li><?php esc_html_e( 'Dashboard will be created in your account', 'n8ndash-pro' ); ?></li>
                    </ol>
                </div>
            </div>
            
            <div class="n8ndash-notice n8ndash-notice--info">
                <p>
                    <strong><?php esc_html_e( 'Note:', 'n8ndash-pro' ); ?></strong>
                    <?php esc_html_e( 'Webhook URLs and authentication details are included in exports. Make sure to update them after importing if needed.', 'n8ndash-pro' ); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.n8ndash-import-export-page {
    max-width: 1200px;
    margin: 20px 0;
}

.n8ndash-import-export-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

@media (max-width: 782px) {
    .n8ndash-import-export-grid {
        grid-template-columns: 1fr;
    }
}

.n8ndash-section {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
}

.n8ndash-section h2 {
    margin-top: 0;
    margin-bottom: 10px;
}

.n8ndash-section > p {
    color: #646970;
    margin-bottom: 20px;
}

.n8ndash-quick-actions {
    margin-bottom: 30px;
    padding: 20px;
    background: #f0f8ff;
    border: 1px solid #b8daff;
    border-radius: 4px;
}

.n8ndash-quick-actions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.n8ndash-quick-action {
    text-align: center;
}

.n8ndash-quick-action .button {
    margin-right: 0;
    margin-bottom: 10px;
}

.n8ndash-quick-action .dashicons {
    margin-right: 5px;
    vertical-align: middle;
}

.n8ndash-quick-action .description {
    margin-top: 15px;
    color: #646970;
    font-style: italic;
}

.n8ndash-quick-export {
    margin-bottom: 25px;
    padding: 20px;
    background: #f0f8ff;
    border: 1px solid #b8daff;
    border-radius: 4px;
}

.n8ndash-quick-export .button {
    margin-right: 10px;
    margin-bottom: 10px;
}

.n8ndash-quick-export .dashicons {
    margin-right: 5px;
    vertical-align: middle;
}

.n8ndash-quick-export .description {
    margin-top: 15px;
    color: #646970;
    font-style: italic;
}

.n8ndash-quick-import {
    margin-bottom: 25px;
    padding: 20px;
    background: #f0fff0;
    border: 1px solid #b8ffb8;
    border-radius: 4px;
}

.n8ndash-quick-import .button {
    margin-right: 10px;
    margin-bottom: 10px;
}

.n8ndash-quick-import .dashicons {
    margin-right: 5px;
    vertical-align: middle;
}

.n8ndash-quick-import .description {
    margin-top: 15px;
    color: #646970;
    font-style: italic;
}

.n8ndash-form-group {
    margin-bottom: 15px;
}

.n8ndash-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.n8ndash-form-group .description {
    margin-top: 5px;
    color: #646970;
    font-size: 13px;
}

.n8ndash-radio-group label {
    display: inline-block;
    margin-right: 20px;
    font-weight: normal;
}

.n8ndash-no-dashboards {
    padding: 20px;
    background: #f6f7f7;
    border-radius: 4px;
    text-align: center;
}

#n8ndash-export-result {
    margin-top: 20px;
    padding: 15px;
    background: #f0f8ff;
    border: 1px solid #b8daff;
    border-radius: 4px;
}

#n8ndash-export-data {
    font-family: Consolas, Monaco, monospace;
    font-size: 12px;
}

.n8ndash-individual-export {
    margin-bottom: 25px;
    padding: 20px;
    background: #f0f8ff;
    border: 1px solid #b8daff;
    border-radius: 4px;
}

.n8ndash-individual-export h3 {
    margin-top: 0;
    margin-bottom: 10px;
}

.n8ndash-individual-import {
    margin-bottom: 25px;
    padding: 20px;
    background: #f0fff0;
    border: 1px solid #b8ffb8;
    border-radius: 4px;
}

.n8ndash-individual-import h3 {
    margin-top: 0;
    margin-bottom: 10px;
}

.n8ndash-instructions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 20px;
}

@media (max-width: 782px) {
    .n8ndash-instructions-grid {
        grid-template-columns: 1fr;
    }
}

.n8ndash-instruction h3 {
    margin-top: 0;
    margin-bottom: 10px;
}

.n8ndash-instruction ol {
    margin: 0;
    padding-left: 20px;
}

.n8ndash-instruction li {
    margin-bottom: 5px;
}

.n8ndash-notice {
    padding: 12px;
    border-radius: 4px;
}

.n8ndash-notice--info {
    background: #e5f5ff;
    border: 1px solid #b8daff;
}

.n8ndash-notice p {
    margin: 0;
}

.button .dashicons {
    margin-right: 5px;
    vertical-align: text-bottom;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle import method
    $('input[name="import_method"]').on('change', function() {
        $('.n8ndash-import-method').hide();
        $('#n8ndash-import-' + $(this).val()).show();
    });
    
    // Load dashboards dynamically to prevent activation output
    $(document).ready(function() {
        loadDashboardsForExport();
    });
    
    function loadDashboardsForExport() {
        $.ajax({
            url: n8ndash_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'n8ndash_get_user_dashboards',
                nonce: n8ndash_admin.nonce
            },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    var dashboards = response.data;
                    var formHtml = '<form id="n8ndash-export-form" method="post">';
                    formHtml += '<input type="hidden" name="nonce" value="' + n8ndash_admin.nonce + '">';
                    
                    formHtml += '<div class="n8ndash-form-group">';
                    formHtml += '<label for="export-dashboard">' + '<?php esc_html_e( 'Select Dashboard', 'n8ndash-pro' ); ?>' + '</label>';
                    formHtml += '<select id="export-dashboard" name="dashboard_id" class="regular-text" required>';
                    formHtml += '<option value=""><?php esc_html_e( '— Select Dashboard —', 'n8ndash-pro' ); ?></option>';
                    
                    dashboards.forEach(function(dashboard) {
                        formHtml += '<option value="' + dashboard.id + '">' + dashboard.title + '</option>';
                    });
                    
                    formHtml += '</select>';
                    formHtml += '</div>';
                    
                    formHtml += '<div class="n8ndash-form-group">';
                    formHtml += '<label><input type="checkbox" name="include_widgets" value="1" checked> <?php esc_html_e( 'Include all widgets', 'n8ndash-pro' ); ?></label>';
                    formHtml += '</div>';
                    
                    formHtml += '<div class="n8ndash-form-group">';
                    formHtml += '<label><input type="checkbox" name="include_settings" value="1" checked> <?php esc_html_e( 'Include dashboard settings', 'n8ndash-pro' ); ?></label>';
                    formHtml += '</div>';
                    
                    formHtml += '<button type="button" id="n8ndash-export-button" class="button button-primary">';
                    formHtml += '<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export Dashboard', 'n8ndash-pro' ); ?>';
                    formHtml += '</button>';
                    formHtml += '</form>';
                    
                    $('#n8ndash-export-form-container').html(formHtml);
                } else {
                    $('#n8ndash-export-form-container').html('<p class="n8ndash-no-dashboards"><?php esc_html_e( 'You have no dashboards to export.', 'n8ndash-pro' ); ?> <a href="<?php echo admin_url('admin.php?page=n8ndash-new'); ?>"><?php esc_html_e( 'Create your first dashboard', 'n8ndash-pro' ); ?></a></p>');
                }
            },
            error: function() {
                $('#n8ndash-export-form-container').html('<p class="n8ndash-error"><?php esc_html_e( 'Failed to load dashboards. Please refresh the page.', 'n8ndash-pro' ); ?></p>');
            }
        });
    }

    // Export functionality
    $(document).on('click', '#n8ndash-export-button', function() {
        var dashboardId = $('#export-dashboard').val();
        if (!dashboardId) {
            alert('<?php esc_html_e( 'Please select a dashboard to export', 'n8ndash-pro' ); ?>');
            return;
        }
        
        var $button = $(this);
        $button.prop('disabled', true).text('<?php esc_html_e( 'Exporting...', 'n8ndash-pro' ); ?>');
        
        // Try REST API first
        $.ajax({
            url: n8ndash_admin.api_url + 'export/dashboard/' + dashboardId,
            method: 'GET',
            headers: {
                'X-WP-Nonce': n8ndash_admin.api_nonce
            },
            success: function(response) {
                var exportData = JSON.stringify(response, null, 2);
                $('#n8ndash-export-data').val(exportData);
                $('#n8ndash-export-result').show();
                
                // Store for download
                $('#n8ndash-download-export').data('export', response);
                $('#n8ndash-download-export').data('filename', 'n8ndash-' + response.dashboard.title.toLowerCase().replace(/\s+/g, '-') + '.json');
            },
            error: function(xhr) {
                // REST API export failed, trying AJAX fallback
                // Fallback to AJAX method
                exportDashboardAjax(dashboardId, $button);
            },
            complete: function() {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export Dashboard', 'n8ndash-pro' ); ?>');
            }
        });
    });
    
    // AJAX fallback export method
    function exportDashboardAjax(dashboardId, $button) {
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'n8ndash_export_dashboard',
                dashboard_id: dashboardId,
                nonce: n8ndash_admin.nonce,
                include_widgets: $('#n8ndash-export-form input[name="include_widgets"]:checked').length > 0,
                include_settings: $('#n8ndash-export-form input[name="include_settings"]:checked').length > 0
            },
            success: function(response) {
                if (response.success) {
                    var exportData = JSON.stringify(response.data, null, 2);
                    $('#n8ndash-export-data').val(exportData);
                    $('#n8ndash-export-result').show();
                    
                    // Store for download
                    $('#n8ndash-download-export').data('export', response.data);
                    $('#n8ndash-download-export').data('filename', 'n8ndash-' + response.data.dashboard.title.toLowerCase().replace(/\s+/g, '-') + '.json');
                } else {
                    var errorMessage = 'Export failed';
                    if (response.data && typeof response.data === 'string') {
                        errorMessage += ': ' + response.data;
                    } else if (response.data && response.data.message) {
                        errorMessage += ': ' + response.data.message;
                    } else if (response.data) {
                        errorMessage += ': ' + JSON.stringify(response.data);
                    }
                    alert(errorMessage);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'Export failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage += ': ' + response.message;
                        }
                    } catch (e) {
                        errorMessage += ': ' + xhr.responseText;
                    }
                } else {
                    errorMessage += ': ' + error;
                }
                alert(errorMessage);
            }
        });
    }
    
    // Copy to clipboard
    $('#n8ndash-copy-export').on('click', function() {
        var $textarea = $('#n8ndash-export-data');
        $textarea.select();
        document.execCommand('copy');
        $(this).text('<?php esc_html_e( 'Copied!', 'n8ndash-pro' ); ?>');
        setTimeout(() => {
            $(this).html('<span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Copy to Clipboard', 'n8ndash-pro' ); ?>');
        }, 2000);
    });
    
    // Download export
    $('#n8ndash-download-export').on('click', function() {
        var exportData = $(this).data('export');
        var filename = $(this).data('filename');
        
        var blob = new Blob([JSON.stringify(exportData, null, 2)], {type: 'application/json'});
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
    
    // Import functionality
    $('#n8ndash-import-button').on('click', function() {
        var method = $('input[name="import_method"]:checked').val();
        var importData;
        
        if (method === 'file') {
            var file = $('#import-file')[0].files[0];
            if (!file) {
                alert('<?php esc_html_e( 'Please select a file to import', 'n8ndash-pro' ); ?>');
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    importData = JSON.parse(e.target.result);
                    performImport(importData);
                } catch (error) {
                    showImportResult('error', '<?php esc_html_e( 'Invalid JSON file', 'n8ndash-pro' ); ?>');
                }
            };
            reader.readAsText(file);
        } else {
            var jsonText = $('#import-data').val();
            if (!jsonText) {
                alert('<?php esc_html_e( 'Please paste JSON data to import', 'n8ndash-pro' ); ?>');
                return;
            }
            
            try {
                importData = JSON.parse(jsonText);
                performImport(importData);
            } catch (error) {
                showImportResult('error', '<?php esc_html_e( 'Invalid JSON data', 'n8ndash-pro' ); ?>');
            }
        }
    });
    
    function performImport(data) {
        var $button = $('#n8ndash-import-button');
        $button.prop('disabled', true).text('<?php esc_html_e( 'Importing...', 'n8ndash-pro' ); ?>');
        
        // FIX: Add debugging to see what data is being sent
                    // JavaScript - Data to import

        // FIX: Normalize various possible export shapes (REST, AJAX-wrapped, legacy)
        function normalizeImportData(raw) {
            var x = raw || {};
            // Unwrap WP AJAX/REST wrappers
            if (x && x.success === true && x.data) {
                x = x.data;
            }
            if (x && x.data && (x.data.type || x.data.dashboard || x.data.dashboards)) {
                x = x.data;
            }
            // If still missing type, try to infer
            if (!x.type) {
                if (x.dashboard && x.widgets) {
                    x = Object.assign({
                        version: '1.0.0',
                        type: 'dashboard',
                        exported: new Date().toISOString().slice(0, 19).replace('T', ' ')
                    }, x);
                } else if (x.dashboards) {
                    x = Object.assign({
                        version: '1.0.0',
                        type: 'all_dashboards',
                        exported: new Date().toISOString().slice(0, 19).replace('T', ' ')
                    }, x);
                } else if (x.title && x.widgets) {
                    // Very old legacy: dashboard fields at top-level
                    x = {
                        version: '1.0.0',
                        type: 'dashboard',
                        exported: new Date().toISOString().slice(0, 19).replace('T', ' '),
                        dashboard: {
                            title: x.title,
                            slug: x.slug || '',
                            description: x.description || '',
                            status: x.status || 'active',
                            settings: x.settings || {}
                        },
                        widgets: x.widgets
                    };
                }
            }
            return x;
        }

        data = normalizeImportData(data);
                    // JavaScript - Normalized import data
        
        // Try REST API first
        $.ajax({
            url: n8ndash_admin.api_url + 'import',
            method: 'POST',
            headers: {
                'X-WP-Nonce': n8ndash_admin.api_nonce,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(data),
            success: function(response) {
                // JavaScript - REST API success
                showImportResult('success', '<?php esc_html_e( 'Dashboard imported successfully!', 'n8ndash-pro' ); ?>');
                setTimeout(function() {
                    // FIX: Extract dashboard_id from the response structure
                    var dashboardId = response.dashboard_id || (response.data && response.data.dashboard_id);
                    window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=n8ndash-edit&dashboard_id=' ) ); ?>' + dashboardId;
                }, 2000);
            },
            error: function(xhr) {
                // JavaScript - REST API failed
                                  // REST API import failed, trying AJAX fallback
                // Fallback to AJAX method
                performImportAjax(data, $button);
            }
        });
    }
    
    // AJAX fallback import method
    function performImportAjax(data, $button) {
                    // JavaScript - AJAX fallback - Data
        
        $.ajax({
            url: n8ndash_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'n8ndash_import_data',
                data: JSON.stringify(data),
                nonce: n8ndash_admin.nonce
            },
            success: function(response) {
                // JavaScript - AJAX success
                if (response.success) {
                    showImportResult('success', '<?php esc_html_e( 'Dashboard imported successfully!', 'n8ndash-pro' ); ?>');
                    setTimeout(function() {
                        window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=n8ndash-edit&dashboard_id=' ) ); ?>' + response.data.dashboard_id;
                    }, 2000);
                } else {
                    var message = response.data?.message || '<?php esc_html_e( 'Import failed. Please try again.', 'n8ndash-pro' ); ?>';
                    // JavaScript - AJAX error message
                    showImportResult('error', message);
                }
            },
            error: function(xhr, status, error) {
                // JavaScript - AJAX error
                var message = '<?php esc_html_e( 'Import failed. Please try again.', 'n8ndash-pro' ); ?>';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showImportResult('error', message);
            },
            complete: function() {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Import Dashboard', 'n8ndash-pro' ); ?>');
            }
        });
    }
    
    function showImportResult(type, message) {
        var $result = $('#n8ndash-import-result');
        $result.removeClass('notice-success notice-error')
               .addClass('notice-' + type)
               .find('p').text(message);
        $result.show();
    }
    
    // Quick Export Buttons
    $('#n8ndash-export-all').on('click', function() {
        window.location.href = ajaxurl + '?action=n8ndash_export_all&nonce=' + n8ndash_admin.nonce;
    });
    
    // Quick Import All Dashboards Button
    $('#n8ndash-import-all-dashboards').on('click', function() {
        // Create a file input for the user to select a file
        var fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = '.json';
        fileInput.style.display = 'none';
        
        fileInput.onchange = function(e) {
            var file = e.target.files[0];
            if (!file) return;
            
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var importData = JSON.parse(e.target.result);
                    performImportAllDashboards(importData);
                } catch (error) {
                    showImportResult('error', '<?php esc_html_e( 'Invalid JSON file', 'n8ndash-pro' ); ?>');
                }
            };
            reader.readAsText(file);
        };
        
        document.body.appendChild(fileInput);
        fileInput.click();
        document.body.removeChild(fileInput);
    });
    
    function performImportAllDashboards(data) {
        var $button = $('#n8ndash-import-all-dashboards');
        $button.prop('disabled', true).text('<?php esc_html_e( 'Importing...', 'n8ndash-pro' ); ?>');
        
        // Try REST API first
        $.ajax({
            url: n8ndash_admin.api_url + 'import',
            method: 'POST',
            headers: {
                'X-WP-Nonce': n8ndash_admin.api_nonce,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(data),
            success: function(response) {
                showImportResult('success', '<?php esc_html_e( 'All dashboards imported successfully!', 'n8ndash-pro' ); ?>');
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            },
            error: function(xhr) {
                // REST API import failed, trying AJAX fallback
                performImportAllDashboardsAjax(data, $button);
            }
        });
    }
    
    // AJAX fallback for import all dashboards
    function performImportAllDashboardsAjax(data, $button) {
        $.ajax({
            url: n8ndash_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'n8ndash_import_data',
                data: JSON.stringify(data),
                nonce: n8ndash_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showImportResult('success', '<?php esc_html_e( 'All dashboards imported successfully!', 'n8ndash-pro' ); ?>');
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    var message = response.data?.message || '<?php esc_html_e( 'Import failed. Please try again.', 'n8ndash-pro' ); ?>';
                    showImportResult('error', message);
                }
            },
            error: function(xhr, status, error) {
                var message = '<?php esc_html_e( 'Import failed. Please try again.', 'n8ndash-pro' ); ?>';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showImportResult('error', message);
            },
            complete: function() {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Import All Dashboards', 'n8ndash-pro' ); ?>');
            }
        });
    }
});
</script>