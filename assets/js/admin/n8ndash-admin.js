/**
 * N8nDash Pro Admin JavaScript
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/admin
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize drag and drop for existing widgets
        initializeDragAndDrop();
        
        // Sync widget preview sizes
        syncWidgetPreviewSizes();
        
        // Update preview title when typing
        $('#dashboard-title').on('input', function() {
            var title = $(this).val() || 'New Dashboard';
            $('#n8ndash-dashboard-preview-title').text(title);
        });
        
        // Dashboard form submission
        $('#n8ndash-dashboard-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            var originalText = $submitBtn.text();
            
            // Disable submit button and show loading state
            $submitBtn.prop('disabled', true).text('Saving...');
            
            // Gather form data
            var dashboardId = $('#dashboard-id').val();
            var isNew = !dashboardId || dashboardId === '0';
            
            var data = {
                title: $('#dashboard-title').val(),
                description: $('#dashboard-description').val(),
                status: $('#dashboard-status').val(),
                settings: {
                    is_public: $('#dashboard-public').is(':checked')
                }
            };
            
            // Determine endpoint and method
            var endpoint = n8ndash_admin.api_url + 'dashboards';
            var method = 'POST';
            
            if (!isNew) {
                endpoint += '/' + dashboardId;
                method = 'PUT';
                data.id = dashboardId;
            }
            
            // Dashboard save initiated
            
            // Make API request
            $.ajax({
                url: endpoint,
                method: method,
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: {
                    'X-WP-Nonce': n8ndash_admin.api_nonce
                },
                beforeSend: function(xhr) {
                    // Add additional headers for compatibility
                    xhr.setRequestHeader('X-WP-Nonce', n8ndash_admin.api_nonce);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                },
                success: function(response) {
                    // Dashboard saved successfully
                    
                    // Show success message
                    showNotice('Dashboard saved successfully!', 'success');
                    
                    // Update preview title
                    $('#n8ndash-dashboard-preview-title').text(data.title);
                    
                    // If it was a new dashboard, update the URL and dashboard ID
                    if (isNew && response.id) {
                        $('#dashboard-id').val(response.id);
                        
                        // Update browser URL without reloading
                        var newUrl = window.location.href.split('?')[0] + '?page=n8ndash-edit&dashboard_id=' + response.id;
                        window.history.replaceState({}, '', newUrl);
                        
                        // Show the actions panel
                        $('.n8ndash-panel:has(#n8ndash-preview-dashboard)').show();
                    }
                    
                    // Re-enable submit button
                    $submitBtn.prop('disabled', false).text(originalText);
                },
                error: function(xhr, status, error) {
                    
                    var errorMsg = 'Failed to save dashboard.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += ' ' + xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMsg += ' REST API route not found. Please deactivate and reactivate the plugin.';
                    } else if (xhr.status === 0) {
                        errorMsg += ' Network error or CORS issue.';
                    }
                    
                    // If REST API fails with 404, try fallback AJAX method silently
                    if (xhr.status === 404) {
                        // Using fallback save method
                        fallbackSaveDashboard(data, isNew, $submitBtn, originalText);
                    } else {
                        // Only show error for non-404 errors
                        showNotice(errorMsg, 'error');
                        
                        // Re-enable submit button
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                }
            });
        });
        
        // Widget type buttons
        $('.n8ndash-widget-type').on('click', function() {
            var widgetType = $(this).data('widget-type');
            openWidgetModal(widgetType);
        });
        
        // Modal close buttons
        $('.n8ndash-modal__close, .n8ndash-modal-cancel').on('click', function() {
            closeModal();
        });
        
        // Widget form submission
        $('#n8ndash-widget-form').on('submit', function(e) {
            e.preventDefault();
            
            // For custom widgets, ensure fields data is updated before saving
            var widgetType = $('#widget-type').val();
            if (widgetType === 'custom') {
                // Update fields data for custom widget
                if (typeof window.updateFieldsHiddenInput === 'function') {
                    window.updateFieldsHiddenInput();
                }
            }
            
            saveWidget();
        });
        
        // Dashboard actions
        $('#n8ndash-preview-dashboard').on('click', function() {
            var dashboardId = $('#dashboard-id').val();
            // Use admin preview page for proper dashboard rendering
            var previewUrl = n8ndash_admin.admin_url + 'admin.php?page=n8ndash-preview&dashboard_id=' + dashboardId;
            window.open(previewUrl, '_blank');
        });
        
        // Save Layout button handler
        $('#n8ndash-save-layout').on('click', function() {
            saveAllWidgetPositions();
        });
        
        $('#n8ndash-export-dashboard').on('click', function() {
            var dashboardId = $('#dashboard-id').val();
            window.location.href = n8ndash_admin.api_url + 'export/dashboard/' + dashboardId + '?_wpnonce=' + n8ndash_admin.api_nonce;
        });
        
        $('#n8ndash-duplicate-dashboard').on('click', function() {
            if (confirm('Are you sure you want to duplicate this dashboard? This will create a copy with all widgets.')) {
                duplicateDashboard();
            }
        });
        
        $('#n8ndash-delete-dashboard').on('click', function() {
            if (confirm('Are you sure you want to delete this dashboard? This action cannot be undone.')) {
                deleteDashboard();
            }
        });
        
        // Grid toggle
        $('#n8ndash-grid-toggle').on('click', function() {
            $('#n8ndash-dashboard-canvas').toggleClass('show-grid');
        });
        
        // Fullscreen toggle
        $('#n8ndash-fullscreen-toggle').on('click', function() {
            $('body').toggleClass('n8ndash-fullscreen');
        });
        
        // Widget edit button handler - handle both admin dashboard and widget wrapper classes
        $(document).on('click', '.n8ndash-widget-edit, .n8n-widget__settings', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $widget = $(this).closest('.n8ndash-widget, .n8n-widget');
            var widgetId = $widget.data('widget-id') || $(this).data('widget-id');
            var widgetType = $widget.data('widget-type');
            
            // Edit widget clicked
            
            // Load widget data and open modal
            loadWidgetForEdit(widgetId, widgetType);
        });
        
        // Widget delete button handler
        $(document).on('click', '.n8ndash-widget-delete', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Check if user is subscriber (disable delete for subscribers)
            if (typeof n8ndash_admin !== 'undefined' && n8ndash_admin.userRole === 'subscriber') {
                alert('Subscribers cannot delete widgets. Please contact an administrator.');
                return;
            }
            
            var $widget = $(this).closest('.n8ndash-widget, .n8n-widget');
            var widgetId = $widget.data('widget-id');
            var widgetTitle = $widget.find('.n8ndash-widget__title, .n8n-widget__title').text();
            
            // Delete widget clicked
            
            if (confirm('Are you sure you want to delete the widget "' + widgetTitle + '"? This action cannot be undone.')) {
                // User confirmed deletion
                deleteWidget(widgetId, $widget);
            } else {
                // User cancelled deletion
            }
        });
        
        // Widget refresh button handler
        // Use a namespaced handler and ensure we don't bind duplicates
        $(document).off('click.n8dashRefresh').on('click.n8dashRefresh', '.n8ndash-widget-refresh', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $widget = $(this).closest('.n8ndash-widget, .n8n-widget');
            var widgetId = $widget.data('widget-id');
            var $button = $(this);
            
            // Refresh widget clicked
            
            // Add loading state
            if ($button.prop('disabled')) { return; }
            $button.prop('disabled', true);
            $button.find('.dashicons').addClass('dashicons-update-alt').removeClass('dashicons-update');
            $button.find('.dashicons').css('animation', 'spin 1s linear infinite');
            
            // Call refresh widget function
            refreshWidget(widgetId, $widget, $button);
        });
        
        // Widget type selection handler
        $(document).on('click', '.n8ndash-widget-type', function(e) {
            e.preventDefault();
            
            var widgetType = $(this).data('widget-type');
            // Widget type selected
            
            // Open widget configuration modal
            openWidgetModal(widgetType);
        });
        
        // Chart type change handler for dynamic placeholder updates
        $(document).on('change', '.n8n-chart-type-select', function() {
            var chartType = $(this).val();
            var $form = $(this).closest('form');
            
            // Update placeholders based on chart type (matching n8nDash plugin)
            var labelsPlaceholder, dataPlaceholder;
            
            switch(chartType) {
                case 'line':
                    labelsPlaceholder = 'xLabels';
                    dataPlaceholder = 'series[0].data';
                    break;
                case 'bar':
                    labelsPlaceholder = 'labels';
                    dataPlaceholder = 'data';
                    break;
                case 'pie':
                    labelsPlaceholder = 'labels';
                    dataPlaceholder = 'values';
                    break;
                default:
                    labelsPlaceholder = 'labels';
                    dataPlaceholder = 'data';
            }
            
            // Update labels path placeholder
            $form.find('input[name="config[labels_path]"]').attr('placeholder', labelsPlaceholder);
            
            // Update data path placeholder
            $form.find('input[name="config[data_path]"]').attr('placeholder', dataPlaceholder);
            
            // Show/hide dataset label field (not needed for pie charts)
            var $datasetLabelGroup = $form.find('.n8n-chart-dataset-label');
            if (chartType === 'pie') {
                $datasetLabelGroup.hide();
            } else {
                $datasetLabelGroup.show();
            }
            
            // Show/hide grid option (not needed for pie charts)
            var $gridOption = $form.find('.n8n-chart-grid-option');
            if (chartType === 'pie') {
                $gridOption.hide();
            } else {
                $gridOption.show();
            }
            
            // Show/hide Y-max option (not needed for pie charts)
            var $ymaxGroup = $form.find('.n8n-chart-ymax');
            if (chartType === 'pie') {
                $ymaxGroup.hide();
            } else {
                $ymaxGroup.show();
            }
            
                            // Chart type changed
        });
        
        // Data widget mode change handler for dynamic field visibility
        $(document).on('change', '.n8n-widget-mode-select', function() {
            var mode = $(this).val();
            var $form = $(this).closest('form');
            
                            // Data widget mode changed
            
            // Show/hide mode-specific data mapping fields
            $form.find('.n8n-data-mapping').hide();
            $form.find('.n8n-data-mapping[data-mode="' + mode + '"]').show();
            
            // Show/hide mode-specific demo data fields
            $form.find('.n8n-demo-data').hide();
            $form.find('.n8n-demo-data[data-mode="' + mode + '"]').show();
            
                            // Data widget fields updated
        });
    });
    
    /**
     * Show admin notice
     */
    function showNotice(message, type) {
        // Remove existing notices
        $('.n8ndash-notice').remove();
        
        var noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
        var notice = $('<div class="notice ' + noticeClass + ' n8ndash-notice is-dismissible"><p>' + message + '</p></div>');
        
        // Insert after the page title
        $('.wp-heading-inline').after(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    /**
     * Open widget configuration modal
     */
    function openWidgetModal(widgetType, widgetData) {
        // Opening widget modal
        
        $('#widget-type').val(widgetType);
        $('#widget-id').val(widgetData ? widgetData.id : '');
        
        // Reset form
        $('#n8ndash-widget-form')[0].reset();
        
        // Clear widget-specific fields
        $('#n8ndash-widget-specific-fields').empty();
        
        // Load widget-specific configuration form
        loadWidgetConfigForm(widgetType, widgetData);
        
        // Show/hide custom widget fields interface
        if (widgetType === 'custom') {
            // Custom widget detected, showing fields interface
            $('#n8ndash-custom-widget-fields').show();
            // Initialize custom widget field management
            if (typeof window.initCustomWidgetFields === 'function') {
                // initCustomWidgetFields function found, calling it
                window.initCustomWidgetFields();
            } else {
                // initCustomWidgetFields function not found
            }
        } else {
            $('#n8ndash-custom-widget-fields').hide();
        }
        
        // Populate form if editing
        if (widgetData) {
            // Populating form with widget data
            
            // Populate basic fields - avoid conflict with config title
            $('#widget-title').val(widgetData.title || ''); // Set the widget title field
            $('#webhook-url').val(widgetData.webhook_url);
            $('#webhook-method').val(widgetData.webhook_method);
            $('#webhook-headers').val(widgetData.webhook_headers);
            $('#webhook-body').val(widgetData.webhook_body);
            $('#refresh-interval').val(widgetData.refresh_interval);
            
            // Populate widget-specific configuration fields
            if (widgetData.config) {
                // Populating config fields
                
                // Wait for the form to be loaded, then populate
                setTimeout(function() {
                    // Populate all config fields dynamically with multiple selector strategies
                    Object.keys(widgetData.config).forEach(function(key) {
                        var value = widgetData.config[key];
                        
                        // Try multiple field selector patterns
                        var selectors = [
                            '[name="config[' + key + ']"]',
                            '#widget-' + key.replace(/_/g, '-'),
                            '#widget-config-' + key.replace(/_/g, '-'),
                            '[id*="' + key + '"]'
                        ];
                        
                        var $field = null;
                        for (var i = 0; i < selectors.length; i++) {
                            $field = $(selectors[i]);
                            if ($field.length > 0) {
                                // Field found for config key
                                break;
                            }
                        }
                        
                        if ($field && $field.length > 0) {
                            // Setting field value
                            
                            if ($field.is(':checkbox')) {
                                $field.prop('checked', !!value);
                            } else if ($field.is('select')) {
                                $field.val(value).trigger('change');
                            } else if (Array.isArray(value)) {
                                // Handle arrays (like demo_labels, demo_data)
                                $field.val(value.join(', '));
                            } else {
                                $field.val(value);
                            }
                            // Field value set successfully
                        } else {
                            // Field not found for config key
                        }
                    });
                    
                    // Handle special mode-dependent field visibility
                    if (widgetData.config.mode) {
                        var mode = widgetData.config.mode;
                        // Setting widget mode
                        $('#widget-mode').val(mode).trigger('change');
                        
                        // Show/hide mode-specific fields
                        $('.n8ndash-data-mapping').hide();
                        $('.n8ndash-data-mapping[data-mode="' + mode + '"]').show();
                        $('.n8ndash-demo-data').hide();
                        $('.n8ndash-demo-data[data-mode="' + mode + '"]').show();
                    }
                    
                    if (widgetData.config.chart_type) {
                        var chartType = widgetData.config.chart_type;
                        // Setting chart type
                        $('#widget-chart-type').val(chartType).trigger('change');
                        
                        // Show/hide chart-specific fields
                        if (chartType === 'pie') {
                            $('.n8ndash-chart-grid-option').hide();
                            $('.n8ndash-chart-dataset-label').hide();
                            $('.n8ndash-chart-ymax').hide();
                        } else {
                            $('.n8ndash-chart-grid-option').show();
                            $('.n8ndash-chart-dataset-label').show();
                            $('.n8ndash-chart-ymax').show();
                        }
                    }
                    
                    // Handle custom widget fields population
                    if (widgetType === 'custom' && widgetData.config.fields) {
                        // Populating custom widget fields
                        // Set the hidden fields data input
                        $('#fields-data').val(JSON.stringify(widgetData.config.fields));
                        // Trigger the custom widget field management to load existing fields
                        if (typeof window.loadExistingFields === 'function') {
                            setTimeout(function() {
                                window.loadExistingFields();
                            }, 200);
                        }
                    }
                }, 100); // Small delay to ensure form is rendered
            }
        }
        
        // Show modal
        $('#n8ndash-widget-modal').show();
    }
    
    /**
     * Load widget-specific configuration form
     */
    function loadWidgetConfigForm(widgetType, widgetData) {
        // Loading config form for widget type
        
        // For now, add basic type-specific fields based on widget type
        var specificFields = '';
        
        switch(widgetType) {
            case 'data':
                specificFields = `
                    <fieldset class="n8ndash-fieldset">
                        <legend>Data Widget Settings</legend>
                        <div class="n8ndash-field">
                            <label for="widget-subtitle">Subtitle</label>
                            <input type="text" id="widget-subtitle" name="config[subtitle]" class="regular-text" placeholder="Updated just now">
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-mode">Display Mode</label>
                            <select id="widget-mode" name="config[mode]">
                                <option value="kpi">KPI (Key Performance Indicator)</option>
                                <option value="list">Link List</option>
                            </select>
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-value1-path">Main Value Path</label>
                            <input type="text" id="widget-value1-path" name="config[value1Path]" class="regular-text" placeholder="value1">
                            <p class="description">JSON path to the main value (e.g., "data.revenue" or "stats.total")</p>
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-value2-path">Delta/Change Path</label>
                            <input type="text" id="widget-value2-path" name="config[value2Path]" class="regular-text" placeholder="value2">
                            <p class="description">JSON path to the change value (e.g., "data.change" or "stats.delta")</p>
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-value3-url-path">Link URL Path (Optional)</label>
                            <input type="text" id="widget-value3-url-path" name="config[value3UrlPath]" class="regular-text" placeholder="value3Url">
                            <p class="description">JSON path to make the main value clickable</p>
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-list-path">List Array Path</label>
                            <input type="text" id="widget-list-path" name="config[listPath]" class="regular-text" placeholder="items">
                            <p class="description">JSON path to the array of items (e.g., "data.items" or "results")</p>
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-item-label-path">Item Label Path</label>
                            <input type="text" id="widget-item-label-path" name="config[itemLabelPath]" class="regular-text" placeholder="title">
                            <p class="description">Path within each item for the display text</p>
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-item-url-path">Item URL Path</label>
                            <input type="text" id="widget-item-url-path" name="config[itemUrlPath]" class="regular-text" placeholder="url">
                            <p class="description">Path within each item for the link URL</p>
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-demo-value1">Demo Main Value</label>
                            <input type="text" id="widget-demo-value1" name="config[demo_value1]" class="regular-text" value="$0">
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-demo-value2">Demo Delta Value</label>
                            <input type="text" id="widget-demo-value2" name="config[demo_value2]" class="regular-text" value="+0%">
                        </div>
                        <div class="n8ndash-field">
                            <label>
                                <input type="checkbox" id="widget-show-last-updated" name="config[show_last_updated]" value="1" checked>
                                Show last updated timestamp
                            </label>
                        </div>
                        
                        <!-- Webhook configuration is handled by the admin dashboard modal -->
                        <!-- Removed duplicate webhook section to avoid duplication -->
                    </fieldset>
                `;
                break;
                
            case 'chart':
                specificFields = `
                    <fieldset class="n8ndash-fieldset">
                        <legend>Chart Widget Settings</legend>
                        <div class="n8ndash-field">
                            <label for="widget-subtitle">Subtitle</label>
                            <input type="text" id="widget-subtitle" name="config[subtitle]" class="regular-text" placeholder="">
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-chart-type">Chart Type</label>
                            <select id="widget-chart-type" name="config[chart_type]" class="n8n-chart-type-select">
                                <option value="line">Line Chart</option>
                                <option value="bar">Bar Chart</option>
                                <option value="pie">Pie Chart</option>
                            </select>
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-labels-path">Labels Path</label>
                            <input type="text" id="widget-labels-path" name="config[labels_path]" class="regular-text" placeholder="xLabels">
                            <p class="description">JSON path to the array of labels (e.g., "xLabels" for line, "labels" for bar/pie)</p>
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-data-path">Data Path</label>
                            <input type="text" id="widget-data-path" name="config[data_path]" class="regular-text" placeholder="series[0].data">
                            <p class="description">JSON path to the data array (e.g., "series[0].data" for line, "data" for bar, "values" for pie)</p>
                        </div>
                        <div class="n8ndash-field n8n-chart-dataset-label">
                            <label for="widget-dataset-label">Dataset Label</label>
                            <input type="text" id="widget-dataset-label" name="config[dataset_label]" class="regular-text" value="Series">
                        </div>
                        <div class="n8ndash-field n8n-chart-ymax">
                            <label for="widget-y-max-path">Y-Axis Max Path (Optional)</label>
                            <input type="text" id="widget-y-max-path" name="config[y_max_path]" class="regular-text" placeholder="yMax">
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-demo-labels">Demo Labels (comma-separated)</label>
                            <input type="text" id="widget-demo-labels" name="config[demo_labels]" class="large-text" value="Jan, Feb, Mar, Apr, May, Jun">
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-demo-data">Demo Data (comma-separated numbers)</label>
                            <input type="text" id="widget-demo-data" name="config[demo_data]" class="large-text" value="65, 59, 80, 81, 56, 55">
                        </div>
                        <div class="n8ndash-field">
                            <label>
                                <input type="checkbox" id="widget-show-legend" name="config[show_legend]" value="1" checked>
                                Show legend
                            </label>
                        </div>
                        <div class="n8ndash-field n8n-chart-grid-option">
                            <label>
                                <input type="checkbox" id="widget-show-grid" name="config[show_grid]" value="1" checked>
                                Show grid lines
                            </label>
                        </div>
                        
                        <!-- Webhook configuration is handled by the admin dashboard modal -->
                        <!-- Removed duplicate webhook section to avoid duplication -->
                    </fieldset>
                `;
                break;
                
            case 'custom':
                specificFields = `
                    <fieldset class="n8ndash-fieldset">
                        <legend>Custom Widget Settings</legend>
                        <div class="n8ndash-field">
                            <label for="widget-description">Description</label>
                            <textarea id="widget-description" name="config[description]" class="large-text" rows="3" placeholder="Widget description or instructions"></textarea>
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-button-text">Button Text</label>
                            <input type="text" id="widget-button-text" name="config[button_text]" class="regular-text" value="Submit">
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-success-message">Success Message</label>
                            <input type="text" id="widget-success-message" name="config[success_message]" class="regular-text" placeholder="Action completed successfully!">
                        </div>
                        <div class="n8ndash-field">
                            <label for="widget-button-color">Button Color</label>
                            <select id="widget-button-color" name="config[button_color]">
                                <option value="primary">Primary (Blue)</option>
                                <option value="success">Success (Green)</option>
                                <option value="warning">Warning (Orange)</option>
                                <option value="danger">Danger (Red)</option>
                                <option value="info">Info (Light Blue)</option>
                            </select>
                        </div>
                        <div class="n8ndash-field">
                            <label>
                                <input type="checkbox" id="widget-response-only" name="config[response_only]" value="1">
                                Response only (no form fields)
                            </label>
                        </div>
                        <div class="n8ndash-field">
                            <label>
                                <input type="checkbox" id="widget-show-response" name="config[show_response]" value="1" checked>
                                Show response data
                            </label>
                        </div>
                        <div class="n8ndash-field">
                            <label>
                                <input type="checkbox" id="widget-confirm-action" name="config[confirm_action]" value="1">
                                Require confirmation before action
                            </label>
                        </div>
                    </fieldset>
                `;
                break;
        }
        
        $('#n8ndash-widget-specific-fields').html(specificFields);
        
        // Populate widget-specific fields if editing
        if (widgetData && widgetData.config) {
            // This would need to be implemented based on actual data structure
            // Widget config data loaded
        }
    }
    
    /**
     * Close modal
     */
    function closeModal() {
        // Reset custom widget field management if it was initialized
        if (typeof window.resetCustomWidgetFields === 'function') {
            window.resetCustomWidgetFields();
        }
        
        $('#n8ndash-widget-modal').hide();
    }
    
    /**
     * Save widget
     */
    function saveWidget() {
        var dashboardId = $('#dashboard-id').val();
        var widgetId = $('#widget-id').val();
        var isNew = !widgetId;
        var widgetType = $('#widget-type').val();
        
        // Saving widget
        
        // Parse headers and body safely
        var headers = {};
        var body = {};
        
        try {
            var headersVal = $('#webhook-headers').val();
            if (headersVal && headersVal.trim()) {
                headers = JSON.parse(headersVal);
            }
        } catch (e) {
            showNotice('Invalid JSON in headers field', 'error');
            return;
        }
        
        try {
            var bodyVal = $('#webhook-body').val();
            if (bodyVal && bodyVal.trim()) {
                body = JSON.parse(bodyVal);
            }
        } catch (e) {
            showNotice('Invalid JSON in request body field', 'error');
            return;
        }
        
        // Collect all form data including widget-specific fields
        var formData = $('#n8ndash-widget-form').serializeArray();
        var config = {
            refresh_interval: $('#refresh-interval').val(),
            title: $('#widget-title').val() || 'Untitled Widget' // Get title from widget-title field
        };
        
        // Process form data to build config object
        formData.forEach(function(field) {
            if (field.name.startsWith('config[') && field.name.endsWith(']')) {
                var key = field.name.slice(7, -1); // Extract key from config[key]
                config[key] = field.value;
            }
        });
        
        // Handle custom widget fields specifically
        if (widgetType === 'custom') {
            var fieldsData = $('#fields-data').val();
            // Raw fields data from hidden input
            
            if (fieldsData) {
                try {
                    var fields = JSON.parse(fieldsData);
                    config.fields = fields;
                    // Custom widget fields added to config
                    
                    // Debug: Check each field's required property
                    fields.forEach(function(field, index) {
                        // Field required property checked
                    });
                } catch (e) {
                    config.fields = [];
                }
            } else {
                config.fields = [];
                // No custom fields data found, setting empty array
            }
        }
        
        // Config collected
        
        var data = {
            dashboard_id: dashboardId,
            widget_type: widgetType,
            title: $('#widget-title').val() || 'Untitled Widget',
            webhook: {
                url: $('#webhook-url').val(),
                method: $('#webhook-method').val(),
                headers: headers,
                body: body
            },
            config: config
        };
        
        // Widget data prepared for saving
        
        var endpoint = n8ndash_admin.api_url + 'widgets';
        var method = 'POST';
        
        if (!isNew) {
            endpoint += '/' + widgetId;
            method = 'PUT';
            // Ensure backend receives identifying fields for update
            data.id = widgetId;
        }
        
        $.ajax({
            url: endpoint,
            method: method,
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'X-WP-Nonce': n8ndash_admin.api_nonce
            },
            success: function(response) {
                                    // Widget saved successfully
                showNotice('Widget saved successfully!', 'success');
                closeModal();
                try {
                    if (response && response.id) {
                        // Append or update the widget without full reload
                        // Fallback to reload if we cannot render live
                        setTimeout(function(){ window.location.reload(); }, 200);
                        return;
                    }
                } catch(e) {}
                window.location.reload();
            },
            error: function(xhr) {
                // If REST API fails with 404, try fallback AJAX method
                if (xhr.status === 404) {
                    fallbackSaveWidget(data, isNew);
                } else {
                    var errorMsg = 'Failed to save widget.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += ' ' + xhr.responseJSON.message;
                    }
                    showNotice(errorMsg, 'error');
                }
            }
        });
    }
    
    /**
     * Delete dashboard
     */
    function deleteDashboard() {
        var dashboardId = $('#dashboard-id').val();
        
        $.ajax({
            url: n8ndash_admin.api_url + 'dashboards/' + dashboardId,
            method: 'DELETE',
            headers: {
                'X-WP-Nonce': n8ndash_admin.api_nonce
            },
            success: function() {
                showNotice('Dashboard deleted successfully!', 'success');
                
                // Redirect to dashboard list
                setTimeout(function() {
                    window.location.href = n8ndash_admin.admin_url + 'admin.php?page=n8ndash';
                }, 1000);
            },
            error: function(xhr) {
                var errorMsg = 'Failed to delete dashboard.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg += ' ' + xhr.responseJSON.message;
                }
                showNotice(errorMsg, 'error');
            }
        });
    }
    
    /**
     * Duplicate dashboard
     */
    function duplicateDashboard() {
        var dashboardId = $('#dashboard-id').val();
        var dashboardTitle = $('#dashboard-title').val();
        var dashboardDescription = $('#dashboard-description').val();
        var dashboardStatus = $('#dashboard-status').val();
        var dashboardPublic = $('#dashboard-public').is(':checked');

        var data = {
            title: dashboardTitle + ' (Copy)',
            description: dashboardDescription,
            status: dashboardStatus,
            settings: {
                is_public: dashboardPublic
            }
        };

        $.ajax({
            url: n8ndash_admin.api_url + 'dashboards/' + dashboardId + '/duplicate',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'X-WP-Nonce': n8ndash_admin.api_nonce
            },
            success: function(response) {
                showNotice('Dashboard duplicated successfully!', 'success');
                location.reload(); // Reload to show the new dashboard in the list
            },
            error: function(xhr) {
                var errorMsg = 'Failed to duplicate dashboard.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg += ' ' + xhr.responseJSON.message;
                }
                showNotice(errorMsg, 'error');
            }
        });
    }

    /**
     * Fallback save method using traditional AJAX
     */
    function fallbackSaveDashboard(data, isNew, $submitBtn, originalText) {
        var ajaxData = {
            action: 'n8ndash_save_dashboard_fallback',
            nonce: n8ndash_admin.nonce,
            dashboard_data: data,
            is_new: isNew
        };
        
        if (!isNew) {
            ajaxData.dashboard_id = $('#dashboard-id').val();
        }
        
        $.post(n8ndash_admin.ajax_url, ajaxData, function(response) {
            if (response.success) {
                showNotice('Dashboard saved successfully!', 'success');
                
                // Update preview title
                $('#n8ndash-dashboard-preview-title').text(data.title);
                
                // If it was a new dashboard, update the URL and dashboard ID
                if (isNew && response.data.dashboard_id) {
                    $('#dashboard-id').val(response.data.dashboard_id);
                    
                    // Update browser URL without reloading
                    var newUrl = window.location.href.split('?')[0] + '?page=n8ndash-edit&dashboard_id=' + response.data.dashboard_id;
                    window.history.replaceState({}, '', newUrl);
                    
                    // Show the actions panel
                    $('.n8ndash-panel:has(#n8ndash-preview-dashboard)').show();
                }
            } else {
                showNotice('Failed to save dashboard: ' + (response.data || 'Unknown error'), 'error');
            }
            
            // Re-enable submit button
            $submitBtn.prop('disabled', false).text(originalText);
        }).fail(function() {
            showNotice('Failed to save dashboard. Please try again.', 'error');
            $submitBtn.prop('disabled', false).text(originalText);
        });
    }
    
    /**
     * Fallback save method for widgets using traditional AJAX
     */
    function fallbackSaveWidget(data, isNew) {
        // Using fallback save method
        
        var ajaxData = {
            action: 'n8ndash_save_widget',
            nonce: n8ndash_admin.nonce,
            widget_data: data,
            is_new: isNew
        };
        
        if (!isNew) {
            ajaxData.widget_id = $('#widget-id').val();
        }
        
        $.post(n8ndash_admin.ajax_url, ajaxData, function(response) {
                                // Fallback save successful
            
            if (response.success) {
                showNotice('Widget saved successfully!', 'success');
                closeModal();
                
                // Reload page to show new widget
                location.reload();
            } else {
                showNotice('Failed to save widget: ' + (response.data.message || 'Unknown error'), 'error');
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            showNotice('Failed to save widget. Please try again.', 'error');
        });
    }
    
    /**
     * Load widget data for editing
     */
    function loadWidgetForEdit(widgetId, widgetType) {
        // Loading widget for edit
        
        // Make API request to get widget data
        $.ajax({
            url: n8ndash_admin.api_url + 'widgets/' + widgetId,
            method: 'GET',
            headers: {
                'X-WP-Nonce': n8ndash_admin.api_nonce
            },
            beforeSend: function(xhr) {
                // Request sent with headers
            },
            success: function(response) {
                // Widget data loaded successfully
                
                // Transform the response data to match expected format
                var widgetData = {
                    id: response.id || widgetId,
                    title: response.title,
                    webhook_url: response.webhook && response.webhook.url ? response.webhook.url : '',
                    webhook_method: response.webhook && response.webhook.method ? response.webhook.method : 'GET',
                    webhook_headers: response.webhook && response.webhook.headers ? JSON.stringify(response.webhook.headers, null, 2) : '{}',
                    webhook_body: response.webhook && response.webhook.body ? JSON.stringify(response.webhook.body, null, 2) : '{}',
                    refresh_interval: response.config && response.config.refresh_interval ? response.config.refresh_interval : 300,
                    config: response.config || {}
                };
                
                // Widget data transformed
                
                // Open modal with widget data
                openWidgetModal(widgetType, widgetData);
            },
            error: function(xhr, textStatus, errorThrown) {
                var errorMsg = 'Failed to load widget data';
                var errorDetails = '';
                
                // Try to extract meaningful error message
                try {
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorDetails = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            errorDetails = xhr.responseJSON.data.message;
                        } else if (xhr.responseJSON.code) {
                            errorDetails = 'Error code: ' + xhr.responseJSON.code;
                        } else {
                            errorDetails = 'Server returned: ' + JSON.stringify(xhr.responseJSON);
                        }
                    } else if (xhr.responseText) {
                        try {
                            var parsedResponse = JSON.parse(xhr.responseText);
                            if (parsedResponse.message) {
                                errorDetails = parsedResponse.message;
                            } else if (parsedResponse.data && parsedResponse.data.message) {
                                errorDetails = parsedResponse.data.message;
                            } else {
                                errorDetails = xhr.responseText.substring(0, 200);
                            }
                        } catch (parseError) {
                            errorDetails = xhr.responseText.substring(0, 200);
                        }
                    } else if (textStatus && textStatus !== 'error') {
                        errorDetails = textStatus;
                    } else if (errorThrown) {
                        errorDetails = errorThrown;
                    } else {
                        errorDetails = 'HTTP ' + xhr.status + ' - ' + xhr.statusText;
                    }
                } catch (e) {
                    errorDetails = 'Unable to process error response';
                }
                
                var fullErrorMsg = errorMsg + (errorDetails ? ': ' + errorDetails : '');
                
                if (xhr.status === 404) {
                    loadWidgetForEditFallback(widgetId, widgetType);
                } else {
                    showNotice(fullErrorMsg, 'error');
                }
            }
        });
    }
    
    /**
     * Fallback method to load widget data using AJAX
     */
    function loadWidgetForEditFallback(widgetId, widgetType) {
        // Using fallback method to load widget
        
        $.post(n8ndash_admin.ajax_url, {
            action: 'n8ndash_get_widget',
            widget_id: widgetId,
            nonce: n8ndash_admin.nonce
        }, function(response) {
            if (response.success && response.data) {
                // Widget data loaded via fallback
                
                // Transform the response data
                var widgetData = {
                    id: response.data.id || widgetId,
                    title: response.data.title,
                    webhook_url: response.data.webhook_url || '',
                    webhook_method: response.data.webhook_method || 'GET',
                    webhook_headers: response.data.webhook_headers || '{}',
                    webhook_body: response.data.webhook_body || '{}',
                    refresh_interval: response.data.refresh_interval || 300,
                    config: response.data.config || {}
                };
                
                // Open modal with widget data
                openWidgetModal(widgetType, widgetData);
            } else {
                var errorMsg = 'Failed to load widget data';
                if (response.data) {
                    if (typeof response.data === 'string') {
                        errorMsg += ': ' + response.data;
                    } else if (response.data.message) {
                        errorMsg += ': ' + response.data.message;
                    } else {
                        errorMsg += ': ' + JSON.stringify(response.data);
                    }
                } else {
                    errorMsg += ': Unknown error';
                }
                // Fallback load error
                showNotice(errorMsg, 'error');
            }
        }).fail(function() {
            showNotice('Failed to load widget data. Please try again.', 'error');
        });
    }
    
    /**
     * Delete widget
     */
    function deleteWidget(widgetId, $widget) {
        // Deleting widget
        
        // Disable the delete button to prevent double-clicks
        $widget.find('.n8ndash-widget-delete').prop('disabled', true);
        
        // Add loading state to widget
        $widget.addClass('n8ndash-widget-deleting').css('opacity', '0.5');
        
        $.ajax({
            url: n8ndash_admin.api_url + 'widgets/' + widgetId,
            method: 'DELETE',
            headers: {
                'X-WP-Nonce': n8ndash_admin.api_nonce
            },
            beforeSend: function(xhr) {
                // Sending DELETE request
            },
            success: function(response) {
                // Widget deleted successfully
                
                showNotice('Widget deleted successfully!', 'success');
                
                // Remove widget from canvas with animation
                $widget.fadeOut(300, function() {
                    // Clean up Interact.js instances before removing widget
                    cleanupWidgetInteractions(widgetId);
                    
                    $(this).remove();
                    
                    // Check if canvas is empty and show empty state
                    if ($('#n8ndash-dashboard-canvas .n8ndash-widget').length === 0) {
                        $('#n8ndash-dashboard-canvas').html('<div class="n8ndash-canvas-empty"><p>Drag widgets from the sidebar to start building your dashboard.</p></div>');
                    }
                });
            },
            error: function(xhr, textStatus, errorThrown) {
                // Re-enable the button and remove loading state
                $widget.find('.n8ndash-widget-delete').prop('disabled', false);
                $widget.removeClass('n8ndash-widget-deleting').css('opacity', '1');
                
                if (xhr.status === 404) {
                    // Try fallback method
                    deleteWidgetFallback(widgetId, $widget);
                } else {
                    var errorMsg = 'Failed to delete widget.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += ' ' + xhr.responseJSON.message;
                    }
                    showNotice(errorMsg, 'error');
                }
            }
        });
    }
    
    /**
     * Fallback method to delete widget using AJAX
     */
    function deleteWidgetFallback(widgetId, $widget) {
        // Using fallback method to delete widget
        // Using AJAX fallback
        
        var ajaxData = {
            action: 'n8ndash_delete_widget',
            widget_id: widgetId,
            nonce: n8ndash_admin.nonce
        };
        
                        // Fallback AJAX data prepared
        
        $.post(n8ndash_admin.ajax_url, ajaxData, function(response) {
                            // Fallback response received
            
            if (response.success) {
                // Widget deleted via fallback
                
                showNotice('Widget deleted successfully!', 'success');
                
                // Remove widget from canvas with animation
                $widget.fadeOut(300, function() {
                    // Clean up Interact.js instances before removing widget
                    cleanupWidgetInteractions(widgetId);
                    
                    $(this).remove();
                    
                    // Check if canvas is empty and show empty state
                    if ($('#n8ndash-dashboard-canvas .n8ndash-widget').length === 0) {
                        $('#n8ndash-dashboard-canvas').html('<div class="n8ndash-canvas-empty"><p>Drag widgets from the sidebar to start building your dashboard.</p></div>');
                    }
                });
            } else {
                // Re-enable the button and remove loading state
                $widget.find('.n8ndash-widget-delete').prop('disabled', false);
                $widget.removeClass('n8ndash-widget-deleting').css('opacity', '1');
                
                var errorMsg = 'Failed to delete widget: ' + (response.data && response.data.message ? response.data.message : response.data || 'Unknown error');
                showNotice(errorMsg, 'error');
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            // Re-enable the button and remove loading state
            $widget.find('.n8ndash-widget-delete').prop('disabled', false);
            $widget.removeClass('n8ndash-widget-deleting').css('opacity', '1');
            
            showNotice('Failed to delete widget. Please try again.', 'error');
        });
    }
    
    /**
     * Ensure widget preview content is properly sized
     */
    function syncWidgetPreviewSizes() {
        $('.n8ndash-widget').each(function() {
            var $widget = $(this);
            var $preview = $widget.find('.n8ndash-widget-preview');
            var $contentWrapper = $preview.find('.n8ndash-widget-content-wrapper');
            
            if ($preview.length) {
                $preview.css({
                    'width': '100%',
                    'height': '100%',
                    'margin': '0',
                    'padding': '0'
                });
                
                if ($contentWrapper.length) {
                    // Apply complete CSS properties including critical flexbox layout
                    $contentWrapper.css({
                        'width': '100%',
                        'height': '100%',
                        'min-height': '0',
                        'overflow': 'hidden',
                        'box-sizing': 'border-box',
                        'display': 'flex',
                        'flex-direction': 'column',
                        'align-items': 'center',
                        'justify-content': 'center'
                    });
                }
            }
        });
    }
    
    /**
     * Initialize drag and drop functionality
     */
    function initializeDragAndDrop() {
        // Initializing drag and drop functionality
        
        // Check if Interact.js is available
        if (typeof interact === 'undefined') {
                            // Interact.js not loaded, drag and drop disabled
            return;
        }
        
        // Remove any existing interact instances to avoid conflicts
        interact('.n8n-widget').unset();
        
        // Initialize draggable and resizable for existing widgets
        interact('.n8n-widget, .n8ndash-widget')
            .draggable({
                // Only allow dragging from the header
                allowFrom: '.n8n-widget__header, .n8ndash-widget__header',
                // Do not start drag from interactive controls
                ignoreFrom: '.n8n-widget__actions, .n8ndash-widget__actions, button, a, .n8n-widget__refresh, .n8ndash-widget-refresh',
                // Enable inertia
                inertia: true,
                // Restrict to parent container
                modifiers: [
                    interact.modifiers.restrictRect({
                        restriction: '#n8ndash-dashboard-canvas',
                        endOnly: true
                    }),
                    // Snap to grid
                    interact.modifiers.snap({
                        targets: [
                            interact.snappers.grid({ x: 16, y: 16 })
                        ],
                        range: Infinity,
                        relativePoints: [{ x: 0, y: 0 }]
                    })
                ],
                // Auto-scroll when dragging near edges
                autoScroll: true,
                listeners: {
                    start: function(event) {
                        // Drag started
                        var target = event.target;
                        
                        // Add dragging class for visual feedback
                        target.classList.add('n8ndash-widget-dragging');
                        
                        // Increase z-index to bring to front
                        target.style.zIndex = '1000';
                    },
                    move: function(event) {
                        var target = event.target;
                        var x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                        var y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
                        
                        // Update the element's style
                        target.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
                        
                        // Update the position attributes
                        target.setAttribute('data-x', x);
                        target.setAttribute('data-y', y);
                    },
                    end: function(event) {
                        // Drag ended
                        var target = event.target;
                        
                        // Remove dragging class
                        target.classList.remove('n8ndash-widget-dragging');
                        
                        // Reset z-index
                        target.style.zIndex = '';
                        
                        // Get the transform values that were applied during drag
                        var transformX = parseFloat(target.getAttribute('data-x')) || 0;
                        var transformY = parseFloat(target.getAttribute('data-y')) || 0;
                        
                        // Get the original CSS position values
                        var originalLeft = parseFloat(target.style.left) || 0;
                        var originalTop = parseFloat(target.style.top) || 0;
                        
                        // Calculate the final absolute position
                        var finalX = originalLeft + transformX;
                        var finalY = originalTop + transformY;
                        
                        // Update the CSS position to reflect the final position
                        target.style.left = finalX + 'px';
                        target.style.top = finalY + 'px';
                        
                        // Reset the transform and data attributes
                        target.style.transform = '';
                        target.removeAttribute('data-x');
                        target.removeAttribute('data-y');
                        
                        // Get final dimensions
                        var rect = target.getBoundingClientRect();
                        
                        // Update widget position in database
                        var widgetId = target.getAttribute('data-widget-id');
                        if (widgetId) {
                            // Saving widget position after drag
                            saveWidgetPosition(widgetId, finalX, finalY, rect.width, rect.height);
                        }
                    }
                }
            })
            .resizable({
                // Resize from right and bottom edges
                edges: { right: true, bottom: true },
                // Restrict to minimum size
                modifiers: [
                    interact.modifiers.restrictSize({
                        min: { width: 200, height: 150 }
                    }),
                    // Snap to grid
                    interact.modifiers.snap({
                        targets: [
                            interact.snappers.grid({ x: 16, y: 16 })
                        ],
                        range: Infinity,
                        relativePoints: [{ x: 0, y: 0 }]
                    })
                ],
                listeners: {
                    move: function(event) {
                        var target = event.target;
                        var x = (parseFloat(target.getAttribute('data-x')) || 0);
                        var y = (parseFloat(target.getAttribute('data-y')) || 0);
                        
                        // Update the element's style
                        target.style.width = event.rect.width + 'px';
                        target.style.height = event.rect.height + 'px';
                        
                        // Translate when resizing from top or left edges
                        x += event.deltaRect.left;
                        y += event.deltaRect.top;
                        
                        target.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
                        
                        target.setAttribute('data-x', x);
                        target.setAttribute('data-y', y);
                        
                        // Sync internal preview content with container size
                        var previewContent = target.querySelector('.n8ndash-widget-preview');
                        if (previewContent) {
                            previewContent.style.width = '100%';
                            previewContent.style.height = '100%';
                            
                            // Ensure content wrapper also syncs with complete flexbox properties
                            var contentWrapper = previewContent.querySelector('.n8ndash-widget-content-wrapper');
                            if (contentWrapper) {
                                contentWrapper.style.width = '100%';
                                contentWrapper.style.height = '100%';
                                contentWrapper.style.minHeight = '0';
                                contentWrapper.style.overflow = 'hidden';
                                contentWrapper.style.boxSizing = 'border-box';
                                contentWrapper.style.display = 'flex';
                                contentWrapper.style.flexDirection = 'column';
                                contentWrapper.style.alignItems = 'center';
                                contentWrapper.style.justifyContent = 'center';
                            }
                        }
                        
                        // Add resizing class for visual feedback
                        target.classList.add('n8ndash-widget-resizing');
                    },
                    end: function(event) {
                        // Resize ended
                        var target = event.target;
                        
                        // Remove resizing class
                        target.classList.remove('n8ndash-widget-resizing');
                        
                        // Get the actual absolute position from the widget's style
                        var finalX = parseFloat(target.style.left) || 0;
                        var finalY = parseFloat(target.style.top) || 0;
                        
                        // Get final dimensions
                        var rect = target.getBoundingClientRect();
                        
                        // Update widget position and size in database
                        var widgetId = target.getAttribute('data-widget-id');
                        if (widgetId) {
                            // Saving widget position after resize
                            saveWidgetPosition(widgetId, finalX, finalY, rect.width, rect.height);
                        }
                        
                        // Final sync of internal content with complete flexbox properties
                        var previewContent = target.querySelector('.n8ndash-widget-preview');
                        if (previewContent) {
                            previewContent.style.width = '100%';
                            previewContent.style.height = '100%';
                            
                            var contentWrapper = previewContent.querySelector('.n8ndash-widget-content-wrapper');
                            if (contentWrapper) {
                                contentWrapper.style.width = '100%';
                                contentWrapper.style.height = '100%';
                                contentWrapper.style.minHeight = '0';
                                contentWrapper.style.overflow = 'hidden';
                                contentWrapper.style.boxSizing = 'border-box';
                                contentWrapper.style.display = 'flex';
                                contentWrapper.style.flexDirection = 'column';
                                contentWrapper.style.alignItems = 'center';
                                contentWrapper.style.justifyContent = 'center';
                            }
                        }
                    }
                }
            });
            
        // Drag and drop initialized
    }
    
    /**
     * Clean up Interact.js instances for a specific widget
     * Prevents memory leaks when widgets are removed
     */
    function cleanupWidgetInteractions(widgetId) {
        if (typeof interact !== 'undefined') {
            // Remove specific widget from interact
            var widgetSelector = '#n8n-widget-' + widgetId + ', #n8ndash-widget-' + widgetId;
            interact(widgetSelector).unset();
            
            // Log cleanup for debugging
            // Interact.js cleanup completed for widget ' + widgetId
        }
    }
    
    /**
     * Clean up all Interact.js instances
     * Call this when page is unloaded or dashboard is changed
     */
    function cleanupAllWidgetInteractions() {
        if (typeof interact !== 'undefined') {
            // Remove all widget interactions
            interact('.n8n-widget, .n8ndash-widget').unset();
            
            // Log cleanup for debugging
            // All Interact.js instances cleaned up
        }
    }
    
    /**
     * Save widget position to database
     */
    function saveWidgetPosition(widgetId, x, y, width, height) {
        // Saving widget position
        
        var data = {
            action: 'n8ndash_save_widget_position',
            widget_id: widgetId,
            x: Math.round(x),
            y: Math.round(y),
            width: Math.round(width),
            height: Math.round(height),
            nonce: n8ndash_admin.nonce
        };
        

        
        $.post(n8ndash_admin.ajax_url, data, function(response) {
            if (response.success) {
                // Widget position saved successfully
            } else {
                // Failed to save widget position
            }
        }).fail(function(xhr, status, error) {
            // AJAX error saving widget position
        });
    }
    
    /**
     * Get next available position for new widget
     */
    function getNextWidgetPosition() {
        var canvas = $('#n8ndash-dashboard-canvas');
        var widgets = canvas.find('.n8ndash-widget');
        
        // Default position
        var position = { x: 20, y: 20, width: 300, height: 200 };
        
        if (widgets.length === 0) {
            return position;
        }
        
        // Find the rightmost widget in the top row
        var maxX = 0;
        var topRowY = 20;
        var maxWidth = 300;
        
        widgets.each(function() {
            var $widget = $(this);
            var rect = this.getBoundingClientRect();
            var canvasRect = canvas[0].getBoundingClientRect();
            
            var widgetX = rect.left - canvasRect.left;
            var widgetY = rect.top - canvasRect.top;
            var widgetWidth = rect.width;
            
            // If widget is in the top row (within 50px of top)
            if (Math.abs(widgetY - topRowY) < 50) {
                var rightEdge = widgetX + widgetWidth;
                if (rightEdge > maxX) {
                    maxX = rightEdge;
                    maxWidth = widgetWidth;
                }
            }
        });
        
        // Position new widget to the right with some spacing
        position.x = maxX + 20;
        position.y = topRowY;
        
        // If it would go off screen, start a new row
        var canvasWidth = canvas.width();
        if (position.x + position.width > canvasWidth - 20) {
            position.x = 20;
            position.y = topRowY + 220; // Height + spacing
        }
        
        return position;
    }
    
    /**
     * Add widget to canvas with proper positioning
     */
    function addWidgetToCanvas(widgetHtml) {
        var canvas = $('#n8ndash-dashboard-canvas');
        
        // Remove empty state if present
        canvas.find('.n8ndash-canvas-empty').remove();
        
        // Get next position
        var position = getNextWidgetPosition();
        
        // Create widget element
        var $widget = $(widgetHtml);
        
        // Set initial position
        $widget.css({
            position: 'absolute',
            left: position.x + 'px',
            top: position.y + 'px',
            width: position.width + 'px',
            height: position.height + 'px'
        });
        
        // Add to canvas
        canvas.append($widget);
        
        // Initialize drag and drop for the new widget
        if (typeof interact !== 'undefined') {
            // Re-initialize drag and drop to include new widget
            setTimeout(function() {
                initializeDragAndDrop();
            }, 100);
        }
        
        // Ensure the new widget has proper flexbox layout applied
        setTimeout(function() {
            var $preview = $widget.find('.n8ndash-widget-preview');
            var $contentWrapper = $preview.find('.n8ndash-widget-content-wrapper');
            
            if ($contentWrapper.length) {
                $contentWrapper.css({
                    'width': '100%',
                    'height': '100%',
                    'min-height': '0',
                    'overflow': 'hidden',
                    'box-sizing': 'border-box',
                    'display': 'flex',
                    'flex-direction': 'column',
                    'align-items': 'center',
                    'justify-content': 'center'
                });
            }
        }, 150);
        
        // Save initial position to database
        var widgetId = $widget.data('widget-id');
        if (widgetId) {
            saveWidgetPosition(widgetId, position.x, position.y, position.width, position.height);
        }
        
        // Widget added to canvas
        
        return $widget;
    }
    
    /**
     * Refresh widget data
     */
    function refreshWidget(widgetId, $widget, $button) {
        // Refreshing widget
        var $container = ($widget && $widget.length) ? ($widget.hasClass('n8n-widget') ? $widget.closest('.n8ndash-widget') : $widget) : null;
        
        // Make API request to refresh widget
        $.ajax({
            url: n8ndash_admin.api_url + 'widgets/' + widgetId + '/refresh',
            method: 'POST',
            headers: {
                'X-WP-Nonce': n8ndash_admin.api_nonce
            },
            success: function(response) {
                // Widget refreshed successfully
                
                var payload = (response && response.data) ? response.data : response;
                // Update widget content if server returned HTML
                if (payload && payload.html) {
                    if ($container && $container.length) {
                        $container.find('.n8ndash-widget__body').html(payload.html);
                    }
                } else if (payload) {
                    var WM = (window.N8nDash && window.N8nDash.WidgetManager) || window.N8nDashWidgetManager || window.WidgetManager;
                    if (WM) { try { WM.updateWidgetData(widgetId, payload); } catch(e){ console.warn('[n8nDash] updateWidgetData failed', e); } }
                }
                
                // Show success message
                showNotice('Widget data refreshed successfully!', 'success');
                
                // Reset button state
                resetRefreshButton($button);
            },
            error: function(xhr, textStatus, errorThrown) {
                if (xhr.status === 404) {
                    refreshWidgetFallback(widgetId, $widget, $button);
                } else {
                    var errorMsg = 'Failed to refresh widget data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += ' ' + xhr.responseJSON.message;
                    }
                    showNotice(errorMsg, 'error');
                    resetRefreshButton($button);
                }
            }
        });
    }
    
    /**
     * Fallback method to refresh widget using AJAX
     */
    function refreshWidgetFallback(widgetId, $widget, $button) {
        // Using fallback method to refresh widget
        
        $.post(n8ndash_admin.ajax_url, {
            action: 'n8ndash_refresh_widget',
            widget_id: widgetId,
            nonce: n8ndash_admin.nonce
        }, function(response) {
            if (response.success) {
                // Widget refreshed via fallback
                
                // Update widget content if response contains data
                var payload = (response && response.data) ? response.data : response;
                if (payload && payload.html) {
                    if ($container && $container.length) {
                        $container.find('.n8ndash-widget__body').html(payload.html);
                    }
                } else if (payload) {
                    var WM = (window.N8nDash && window.N8nDash.WidgetManager) || window.N8nDashWidgetManager || window.WidgetManager;
                    if (WM) { try { WM.updateWidgetData(widgetId, payload); } catch(e){} }
                }
                
                showNotice('Widget data refreshed successfully!', 'success');
            } else {
                var errorMsg = 'Failed to refresh widget data';
                if (response.data && response.data.message) {
                    errorMsg += ': ' + response.data.message;
                } else if (response.data) {
                    errorMsg += ': ' + response.data;
                }
                showNotice(errorMsg, 'error');
            }
            
            resetRefreshButton($button);
        }).fail(function(jqXHR, textStatus, errorThrown) {
            showNotice('Failed to refresh widget data. Please try again.', 'error');
            resetRefreshButton($button);
        });
    }
    
    /**
     * Reset refresh button state
     */
    function resetRefreshButton($button) {
        if (!$button || !$button.length) { return; }
        $button.prop('disabled', false);
        $button.find('.dashicons').removeClass('dashicons-update-alt').addClass('dashicons-update');
        $button.find('.dashicons').css('animation', '');
    }

    /**
     * Save all widget positions
     */
    function saveAllWidgetPositions() {
        // Save Layout function called
        
        var $button = $('#n8ndash-save-layout');
        var originalText = $button.html();
        
        // Button found
        
        $button.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Saving...');
        
        var canvas = $('#n8ndash-dashboard-canvas');
        var widgets = [];
        
        // Canvas found
        
        // Collect all widget positions
        canvas.find('.n8ndash-widget').each(function(index) {
            var $widget = $(this);
            var widgetId = $widget.data('widget-id');
            
                            // Processing widget
            
            var x = parseInt($widget.css('left')) || 0;
            var y = parseInt($widget.css('top')) || 0;
            var width = parseInt($widget.css('width')) || 300;
            var height = parseInt($widget.css('height')) || 200;
            
                            // Widget position collected
            
            var position = {
                widget_id: widgetId,
                x: x,
                y: y,
                width: width,
                height: height
            };
            
            widgets.push(position);
        });
        
        // Total widgets found
        
        if (widgets.length === 0) {
            // No widgets found to save
            $button.prop('disabled', false).html(originalText);
            showNotice('No widgets found to save!', 'error');
            return;
        }
        
        // Sending AJAX request
        
        // Send to server
        $.post(n8ndash_admin.ajax_url, {
            action: 'n8ndash_save_all_widget_positions',
            dashboard_id: $('#dashboard-id').val(),
            widgets: widgets,
            nonce: n8ndash_admin.nonce
        }, function(response) {
            // AJAX response received
            
            if (response.success) {
                // Save successful
                showNotice('Layout saved successfully!', 'success');
                $button.html('<span class="dashicons dashicons-yes"></span> Saved!');
                setTimeout(function() {
                    $button.prop('disabled', false).html(originalText);
                }, 2000);
            } else {
                // Save failed
                showNotice('Failed to save layout: ' + (response.data.message || 'Unknown error'), 'error');
                $button.prop('disabled', false).html(originalText);
            }
        }).fail(function(xhr, status, error) {
            showNotice('Network error saving layout', 'error');
            $button.prop('disabled', false).html(originalText);
        });
    }

    /**
     * Show notification message
     */
    function showNotice(message, type) {
        // Remove existing notices
        $('.n8ndash-notice').remove();
        
        var noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
        var notice = $('<div class="notice ' + noticeClass + ' n8ndash-notice is-dismissible"><p>' + message + '</p></div>');
        
        // Insert after the page title
        $('.wp-heading-inline').after(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }


    // Expose core actions globally for legacy/binder code outside this module
    try {
        window.n8dash = window.n8dash || {};
        if (typeof refreshWidget === 'function') { window.refreshWidget = refreshWidget; window.n8dash.refreshWidget = refreshWidget; }
        if (typeof deleteWidget === 'function')  { window.deleteWidget  = deleteWidget;  window.n8dash.deleteWidget  = deleteWidget; }
        if (typeof deleteWidgetFallback === 'function') { window.deleteWidgetFallback = deleteWidgetFallback; }
        if (typeof saveWidget === 'function')    { window.saveWidget    = saveWidget;    window.n8dash.saveWidget    = saveWidget; }
        if (typeof openWidgetModal === 'function') { window.openWidgetModal = openWidgetModal; }
        if (typeof loadWidgetForEdit === 'function') { window.loadWidgetForEdit = loadWidgetForEdit; }
        if (typeof refreshWidgetFallback === 'function') { window.refreshWidgetFallback = refreshWidgetFallback; }
        if (typeof showNotice === 'function')    { window.n8dash.showNotice = showNotice; }
        if (typeof saveAllWidgetPositions === 'function') { window.saveAllWidgetPositions = saveAllWidgetPositions; }
        if (typeof syncWidgetPreviewSizes === 'function') { window.syncWidgetPreviewSizes = syncWidgetPreviewSizes; }
    } catch (e) { /* Export failed */ }

    // ==========================================================================
    // Custom Widget Field Management Functions
    // ==========================================================================
    
    /**
     * Initialize custom widget field management
     */
    function initCustomWidgetFields() {
        // Initializing custom widget field management
        
        // Check if already initialized to prevent duplicates
        if ($('#field-list').data('initialized')) {
            // Field management already initialized, skipping
            return;
        }
        
        // Check if required elements exist
        if ($('#add-field-btn').length === 0) {
            // Add Field button not found
            return;
        }
        
        if ($('#field-list').length === 0) {
            // Field list container not found
            return;
        }
        
        // Required elements found, setting up event handlers
        
        // Mark as initialized
        $('#field-list').data('initialized', true);
        
        // Remove existing event handlers to prevent duplicates
        $('#add-field-btn').off('click.n8nCustomWidget');
        
        // Add field button
        $('#add-field-btn').on('click.n8nCustomWidget', function() {
            // Add Field button clicked
            addCustomField();
        });
        
        // Field type change handler
        $(document).off('change.n8nCustomWidget', '.field-type-select');
        $(document).on('change.n8nCustomWidget', '.field-type-select', function() {
            var $field = $(this).closest('.n8n-field-item');
            var fieldType = $(this).val();
            updateFieldOptions($field, fieldType);
        });
        
        // Remove field button
        $(document).off('click.n8nCustomWidget', '.remove-field-btn');
        $(document).on('click.n8nCustomWidget', '.remove-field-btn', function() {
            var $field = $(this).closest('.n8n-field-item');
            removeCustomField($field);
        });
        
        // Field input handlers
        $(document).off('input.n8nCustomWidget', '.field-name-input');
        $(document).on('input.n8nCustomWidget', '.field-name-input', function() {
            var $field = $(this).closest('.n8n-field-item');
            updateFieldId($field);
        });
        
        // Add event listeners to all field inputs
        addFieldInputListeners();
        
        // Load existing fields if editing
        loadExistingFields();
        
        // Custom widget field management initialized successfully
    }
    
    /**
     * Add a new custom field
     */
    function addCustomField() {
        var fieldType = $('#add-field-type').val();
        var fieldId = 'field_' + Date.now();
        var fieldName = fieldType + '_' + ($('.n8n-field-item').length + 1);
        
        var fieldHtml = createFieldHtml(fieldId, fieldType, fieldName);
        $('#field-list').append(fieldHtml);
        
        // Update field options based on type
        var $newField = $('#field-list .n8n-field-item').last();
        updateFieldOptions($newField, fieldType);
        
        // Update hidden input
        updateFieldsHiddenInput();
        
        // Hide "no fields" message
        $('.n8n-no-fields').hide();
    }
    
    /**
     * Create HTML for a field item
     */
    function createFieldHtml(fieldId, fieldType, fieldName) {
        var optionsHtml = '';
        if (fieldType === 'select' || fieldType === 'radio') {
            optionsHtml = '<div class="field-options-group"><label>Options (comma-separated):</label><input type="text" class="field-options-input regular-text" placeholder="option1, option2, option3"></div>';
        }
        
        var acceptHtml = '';
        if (fieldType === 'file') {
            acceptHtml = '<div class="field-accept-group"><label>Accept file types:</label><input type="text" class="field-accept-input regular-text" placeholder=".pdf,.doc,.txt"></div>';
        }
        
        var numberAttrs = '';
        if (fieldType === 'number') {
            numberAttrs = '<div class="field-number-attrs"><label>Min:</label><input type="number" class="field-min-input small-text" style="width:80px; margin-right:10px;"><label>Max:</label><input type="number" class="field-max-input small-text" style="width:80px; margin-right:10px;"><label>Step:</label><input type="number" class="field-step-input small-text" style="width:80px;"></div>';
        }
        
        var html = '<div class="n8n-field-item" data-field-id="' + fieldId + '">' +
            '<div class="field-header">' +
                '<h4 class="field-title">' + fieldType.charAt(0).toUpperCase() + fieldType.slice(1) + ' Field</h4>' +
                '<div class="field-actions">' +
                    '<span class="field-type-badge">' + fieldType + '</span>' +
                    '<button type="button" class="remove-field-btn">Remove</button>' +
                '</div>' +
            '</div>' +
            '<div class="field-form-group">' +
                '<div class="field-form-group">' +
                    '<label>Field Name:</label>' +
                    '<input type="text" class="field-name-input regular-text" value="' + fieldName + '" placeholder="field_name">' +
                '</div>' +
                '<div class="field-form-group">' +
                    '<label>Field Label:</label>' +
                    '<input type="text" class="field-label-input regular-text" value="' + fieldName.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); }) + '" placeholder="Field Label">' +
                '</div>' +
            '</div>' +
            '<div class="field-form-group">' +
                '<div class="field-form-group">' +
                    '<label>Placeholder:</label>' +
                    '<input type="text" class="field-placeholder-input regular-text" placeholder="Enter ' + fieldType + '...">' +
                '</div>' +
                '<div class="field-form-group">' +
                    '<label>Required:</label>' +
                    '<select class="field-required-select">' +
                        '<option value="0" selected>No</option>' +
                        '<option value="1">Yes</option>' +
                    '</select>' +
                '</div>' +
            '</div>' +
            optionsHtml +
            acceptHtml +
            numberAttrs +
            '<input type="hidden" class="field-type-input" value="' + fieldType + '">' +
            '</div>';
        
        return html;
    }
    
    /**
     * Update field options based on field type
     */
    function updateFieldOptions($field, fieldType) {
        // Hide all type-specific option groups
        $field.find('.field-options-group, .field-accept-group, .field-number-attrs').hide();
        
        // Show relevant options based on type
        if (fieldType === 'select' || fieldType === 'radio') {
            $field.find('.field-options-group').show();
        }
        
        if (fieldType === 'file') {
            $field.find('.field-accept-group').show();
        }
        
        if (fieldType === 'number') {
            $field.find('.field-number-attrs').show();
        }
    }
    
    /**
     * Remove a custom field
     */
    function removeCustomField($field) {
        $field.remove();
        updateFieldsHiddenInput();
        
        // Show "no fields" message if no fields remain
        if ($('.n8n-field-item').length === 0) {
            $('.n8n-no-fields').show();
        }
    }
    
    /**
     * Update field ID based on name input
     */
    function updateFieldId($field) {
        var fieldName = $field.find('.field-name-input').val();
        if (fieldName) {
            $field.attr('data-field-id', fieldName);
        }
    }
    
    /**
     * Add event listeners to field inputs
     */
    function addFieldInputListeners() {
        $(document).off('input.n8nCustomWidget', '.field-name-input, .field-label-input, .field-placeholder-input');
        $(document).on('input.n8nCustomWidget', '.field-name-input, .field-label-input, .field-placeholder-input', function() {
            updateFieldsHiddenInput();
        });
        
        $(document).off('change.n8nCustomWidget', '.field-required-select');
        $(document).on('change.n8nCustomWidget', '.field-required-select', function() {
            updateFieldsHiddenInput();
        });
    }
    
    /**
     * Load existing fields for custom widget editing
     */
    function loadExistingFields() {
        // Loading existing fields
        
        // Clear existing fields first to prevent duplication
        $('#field-list').empty();
        
        // Show "no fields" message by default
        $('.n8n-no-fields').show();
        
        var fieldsData = $('#fields-data').val();
        if (fieldsData) {
            try {
                var fields = JSON.parse(fieldsData);
                if (Array.isArray(fields) && fields.length > 0) {
                    // Found existing fields
                    
                    $('.n8n-no-fields').hide();
                    fields.forEach(function(field, index) {
                        // Loading field
                        
                        // Create field HTML and populate with existing data
                        var fieldHtml = createFieldHtml(field.id || 'field_' + Date.now(), field.type || 'text', field.name || '');
                        $('#field-list').append(fieldHtml);
                        
                        var $newField = $('#field-list .n8n-field-item').last();
                        
                        // Populate field data
                        $newField.find('.field-name-input').val(field.name || '');
                        $newField.find('.field-label-input').val(field.label || '');
                        $newField.find('.field-placeholder-input').val(field.placeholder || '');
                        // Handle required field with proper default
                        var requiredValue = '0'; // Default to 'No'
                        if (field.required === true || field.required === '1') {
                            requiredValue = '1';
                        } else if (field.required === false || field.required === '0') {
                            requiredValue = '0';
                        }
                        $newField.find('.field-required-select').val(requiredValue);
                        
                        // Update field type and show relevant options
                        $newField.find('.field-type-input').val(field.type || 'text');
                        updateFieldOptions($newField, field.type || 'text');
                        
                        // Populate type-specific options
                        if (field.type === 'select' || field.type === 'radio') {
                            $newField.find('.field-options-input').val(field.options || '');
                        }
                        
                        if (field.type === 'file') {
                            $newField.find('.field-accept-input').val(field.accept || '');
                        }
                        
                        if (field.type === 'number') {
                            $newField.find('.field-min-input').val(field.min || '');
                            $newField.find('.field-max-input').val(field.max || '');
                            $newField.find('.field-step-input').val(field.step || '');
                        }
                    });
                    
                    // Successfully loaded fields
                } else {
                    // No fields found in data or invalid format
                }
            } catch (e) {
                // Error parsing existing fields
                // Show error message
                $('#field-list').html('<div class="n8n-field-error">Error loading fields: ' + e.message + '</div>');
            }
        } else {
            // No fields data found
        }
    }
    
    /**
     * Update the hidden fields data input
     */
    function updateFieldsHiddenInput() {
        var fields = [];
        // updateFieldsHiddenInput called
        
        $('.n8n-field-item').each(function(index) {
            var $field = $(this);
            var requiredValue = $field.find('.field-required-select').val();
            var fieldName = $field.find('.field-name-input').val();
            var fieldLabel = $field.find('.field-label-input').val();
            var fieldType = $field.find('.field-type-input').val();
            
            // Field data collected
            
            var fieldData = {
                id: $field.attr('data-field-id'),
                type: fieldType,
                name: fieldName,
                label: fieldLabel,
                placeholder: $field.find('.field-placeholder-input').val(),
                required: requiredValue === '1'
            };
            
            // Add type-specific options
            if (fieldData.type === 'select' || fieldData.type === 'radio') {
                fieldData.options = $field.find('.field-options-input').val();
            }
            
            if (fieldData.type === 'file') {
                fieldData.accept = $field.find('.field-accept-input').val();
            }
            
            if (fieldData.type === 'number') {
                fieldData.min = $field.find('.field-min-input').val();
                fieldData.max = $field.find('.field-max-input').val();
                fieldData.step = $field.find('.field-step-input').val();
            }
            
            fields.push(fieldData);
        });
        
        var fieldsJson = JSON.stringify(fields);
        // Final fields data prepared
        $('#fields-data').val(fieldsJson);
    }
    
    /**
     * Get fields data for form submission
     */
    function getFieldsData() {
        var fieldsData = $('#fields-data').val();
        if (fieldsData) {
            try {
                return JSON.parse(fieldsData);
            } catch (e) {
                // Error parsing fields data
                return [];
            }
        }
        return [];
    }
    
    // Expose custom widget functions globally
    window.initCustomWidgetFields = initCustomWidgetFields;
    window.addCustomField = addCustomField;
    window.createFieldHtml = createFieldHtml;
    window.updateFieldOptions = updateFieldOptions;
    window.removeCustomField = removeCustomField;
    window.updateFieldId = updateFieldId;
    window.addFieldInputListeners = addFieldInputListeners;
    window.loadExistingFields = loadExistingFields;
    window.updateFieldsHiddenInput = updateFieldsHiddenInput;
    window.getFieldsData = getFieldsData;
    
    // Custom widget field management functions exposed globally

    /**
     * Reset custom widget field management state
     */
    function resetCustomWidgetFields() {
        // Resetting custom widget field management
        
        // Remove initialization flag
        $('#field-list').removeData('initialized');
        
        // Clear field list
        $('#field-list').empty();
        
        // Show "no fields" message
        $('.n8n-no-fields').show();
        
        // Clear hidden fields data
        $('#fields-data').val('');
        
        // Custom widget field management reset successfully
    }
    
    // Expose functions globally
    window.resetCustomWidgetFields = resetCustomWidgetFields;
    
    // Copy to clipboard function for public dashboard links
    window.copyToClipboard = function(elementId) {
        var element = document.getElementById(elementId);
        if (element) {
            element.select();
            element.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand('copy');
            
            // Show feedback
            var $button = $(element).next('button');
            var originalText = $button.html();
            $button.html('<span class="dashicons dashicons-yes"></span> Copied!');
            setTimeout(function() {
                $button.html(originalText);
            }, 2000);
        }
    };

    // Add page unload cleanup to prevent memory leaks
    $(window).on('beforeunload', function() {
        cleanupAllWidgetInteractions();
    });
    
    // Add cleanup when dashboard is changed
    $(document).on('click', '.n8ndash-view-dashboard, .n8ndash-edit-dashboard', function() {
        cleanupAllWidgetInteractions();
    });
    
})(jQuery);


// n8nDash Pro: bind actions on dark header
jQuery(document).on('click', '.n8n-widget__action[data-action="refresh"], .n8n-widget__refresh', function(e){
    e.preventDefault();
    var $w = jQuery(this).closest('.n8n-widget');
    var id = $w.data('widget-id') || $w.attr('data-widget-id');
    if(!id){ console.warn('[n8nDash] refresh: missing widget id'); return; }
    if (jQuery(this).prop('disabled')) return; refreshWidget(id, $w, jQuery(this));
});
jQuery(document).on('click', '.n8n-widget__action[data-action="delete"], .n8n-widget__delete', function(e){
    e.preventDefault();
    var $w = jQuery(this).closest('.n8n-widget');
    var id = $w.data('widget-id') || $w.attr('data-widget-id');
    if(!id){ console.warn('[n8nDash] delete: missing widget id'); return; }
    if(!confirm('Delete this widget?')) return;
    deleteWidget(id, $w);
});
jQuery(document).on('click', '.n8n-widget__action[data-action="settings"], .n8n-widget__settings', function(e){
    e.preventDefault();
    var $w = jQuery(this).closest('.n8n-widget');
    var id = $w.data('widget-id') || $w.attr('data-widget-id');
    if(!id){ console.warn('[n8nDash] settings: missing widget id'); return; }
    openWidgetModal(id, $w);
});



// Dashboard preview button handler
jQuery(document).on('click', '.n8ndash-view-dashboard', function(e) {
    e.preventDefault();
    var $btn = $(this);
    var dashboardId = $btn.data('dashboard-id');
    
    if (!dashboardId) {
        console.error('[N8nDash] Missing dashboard ID for preview');
        return;
    }
    
    // Open preview in new tab/window
    var previewUrl = n8ndash_admin.admin_url + 'admin.php?page=n8ndash-preview&dashboard_id=' + dashboardId;
    window.open(previewUrl, '_blank');
});

// Unified delete dashboard handler (REST -> AJAX fallback)
if (typeof jQuery !== 'undefined') {
  jQuery(function($){
    $(document).off('click.n8dashDelete', '#n8ndash-delete-dashboard, .n8ndash-dashboard-delete, [data-action="delete-dashboard"]');
    $(document).on('click.n8dashDelete', '#n8ndash-delete-dashboard, .n8ndash-dashboard-delete, [data-action="delete-dashboard"]', function(e){
      e.preventDefault();
      if (!confirm('Delete this dashboard?')) return;
      var $btn = $(this);
      var id = $btn.data('dashboardId') || $btn.data('dashboard-id') || $btn.data('id') || $btn.attr('data-id') ||
               jQuery('#dashboard-id').val() ||
               (new URLSearchParams(window.location.search)).get('dashboard_id') ||
               (new URLSearchParams(window.location.search)).get('id');
      if (!id) { alert('Missing dashboard ID'); return; }

      // Try REST first
      var base = (window.n8dash_admin && (n8dash_admin.api_url || n8dash_admin.rest_url)) || (window.n8dash_admin && n8dash_admin.api_root) || '';
      var nonce = window.n8dash_admin && (n8dash_admin.api_nonce || n8dash_admin.nonce);
      jQuery.ajax({
        url: base ? (base + 'dashboards/' + id) : '',
        method: 'DELETE',
        headers: nonce ? { 'X-WP-Nonce': nonce } : {},
      }).done(function(){
        window.location = (n8dash_admin && n8dash_admin.admin_url ? n8dash_admin.admin_url : window.location.origin + '/wp-admin/') + 'admin.php?page=n8ndash';
      }).fail(function(){
        // Fallback: admin-ajax
        jQuery.post(n8dash_admin.ajax_url, {
          action: 'n8ndash_delete_dashboard',
          nonce: n8dash_admin.nonce,
          dashboard_id: id
        }).done(function(resp){
          if (resp && resp.success){
            window.location = (n8dash_admin && n8dash_admin.admin_url ? n8dash_admin.admin_url : window.location.origin + '/wp-admin/') + 'admin.php?page=n8ndash';
          } else {
            alert('Delete failed: ' + (resp && (resp.data && (resp.data.message || resp.data)) || 'Unknown error'));
          }
        }).fail(function(xhr){
          alert('Delete failed (AJAX): ' + xhr.status);
        });
      });
    });
  });
}
