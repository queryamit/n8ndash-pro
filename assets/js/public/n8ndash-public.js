
// n8nDash Pro: Chart.js dark theme defaults
(function(){
  if (!window.Chart) return;
  try{
    Chart.defaults.color = '#e6edf6';
    Chart.defaults.borderColor = 'rgba(255,255,255,.09)';
    Chart.defaults.scales = Chart.defaults.scales || {};
    Chart.defaults.scales.linear = Chart.defaults.scales.linear || {};
    Chart.defaults.scales.category = Chart.defaults.scales.category || {};
    Chart.defaults.scales.linear.grid = { color: 'rgba(255,255,255,.08)' };
    Chart.defaults.scales.category.grid = { color: 'rgba(255,255,255,.08)' };
    Chart.defaults.scales.linear.ticks = { color: '#e6edf6' };
    Chart.defaults.scales.category.ticks = { color: '#e6edf6' };
    if (Chart.defaults.plugins && Chart.defaults.plugins.legend && Chart.defaults.plugins.legend.labels) {
      Chart.defaults.plugins.legend.labels.color = '#e6edf6';
    }
  }catch(e){ /* Chart theme init failed */ }
})()

// n8nDash Pro: x-axis label formatter to avoid overlap and support weekdays/numbers/dates
function n8nFormatXAxisTick(value, i, ticks, format){
  try{
    if(format === 'weekday'){
      const names = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
      const n = Number(value);
      if(!Number.isNaN(n)) return names[((n%7)+7)%7];
      const d = new Date(value);
      if(!isNaN(d)) return names[d.getDay()];
    } else if(format === 'date'){
      const d = new Date(value);
      if(!isNaN(d)) return d.toLocaleDateString(undefined,{month:'short',day:'numeric'});
    }
  }catch{}
  return String(value);
}

;

/**
 * n8nDash Public JavaScript
 * 
 * Handles frontend widget functionality, chart rendering, and user interactions
 * Based on the original n8nDash implementation
 * 
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Global n8nDash object
    window.N8nDash = window.N8nDash || {};

    /**
     * Utility functions
     */
    const Utils = {
        // Escape HTML
        esc: function(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(match) {
                const escapeMap = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                };
                return escapeMap[match];
            });
        },

        // Get value by path from object
        getByPath: function(obj, path) {
            if (!path || !obj) return undefined;
            return path.split('.').reduce(function(current, key) {
                // Handle array notation like items[0]
                const arrayMatch = key.match(/^(.+)\[(\d+)\]$/);
                if (arrayMatch) {
                    const arrayKey = arrayMatch[1];
                    const index = parseInt(arrayMatch[2]);
                    return (current && current[arrayKey] && current[arrayKey][index]) ? current[arrayKey][index] : undefined;
                }
                return (current && current[key] !== undefined) ? current[key] : undefined;
            }, obj);
        },

        // Generate unique ID
        uid: function() {
            return 'id' + Math.random().toString(36).substr(2, 9);
        },

        // Show toast notification
        toast: function(message, type) {
            type = type || 'info';
            const toastClass = type === 'success' ? 'alert-success' : 
                              type === 'error' ? 'alert-danger' : 'alert-info';
            
            const toast = $('<div class="alert ' + toastClass + ' n8ndash-toast" style="position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;">')
                .text(message)
                .appendTo('body');
            
            setTimeout(function() {
                toast.fadeOut(function() {
                    toast.remove();
                });
            }, 4000);
        }
    };

    /**
     * Widget Manager
     */
    const WidgetManager = {
        widgets: {},
        charts: {},

        /**
         * Initialize all widgets on the page
         */
        init: function() {
            // N8nDash: Initializing widgets
            
            // Count widgets by type
            const chartWidgets = $('.n8n-chart-widget').length;
            const dataWidgets = $('.n8n-data-widget').length;
            const customWidgets = $('.n8n-custom-widget').length;
            const allWidgets = $('.n8n-widget').length;
            
            // N8nDash: Found widgets
            
            // Initialize existing widgets
            $('.n8n-widget').each(function() {
                WidgetManager.initWidget($(this));
            });

            // Initialize chart widgets specifically
            $('.n8n-chart-widget').each(function() {
                // N8nDash: Initializing chart widget element
                WidgetManager.initChartWidget($(this));
            });

            // Initialize data widgets
            $('.n8n-data-widget').each(function() {
                WidgetManager.initDataWidget($(this));
            });

            // Initialize custom widgets
            $('.n8n-custom-widget').each(function() {
                WidgetManager.initCustomWidget($(this));
            });

            // N8nDash: Widget initialization complete
        },

        /**
         * Initialize a single widget
         */
        initWidget: function($widget) {
            const widgetId = $widget.data('widget-id');
            const widgetType = $widget.data('widget-type');
            
            if (!widgetId) {
                return;
            }

            // N8nDash: Initializing widget

            // Store widget reference
            this.widgets[widgetId] = $widget;

            // Bind refresh button
            $widget.find('.n8n-widget__refresh').on('click', function(e) {
                e.preventDefault();
                WidgetManager.refreshWidget(widgetId);
            });

            // Bind settings button
            $widget.find('.n8n-widget__settings').on('click', function(e) {
                e.preventDefault();
                WidgetManager.openWidgetSettings(widgetId);
            });
        },

        /**
         * Initialize chart widget
         */
        initChartWidget: function($chartWidget) {
            let chartId = $chartWidget.data('chart-id');
            const widgetId = $chartWidget.data('widget-id');
            
            // If no chart-id, generate one from widget-id
            if (!chartId) {
                chartId = 'chart-' + widgetId;
                $chartWidget.attr('data-chart-id', chartId);
                
                // Also update canvas id if it exists
                const $canvas = $chartWidget.find('canvas');
                if ($canvas.length > 0 && !$canvas.attr('id')) {
                    $canvas.attr('id', chartId);
                }
            }

                            // N8nDash: Initializing chart widget

            // Get chart configuration from JSON script
            const $configScript = $chartWidget.find('.n8n-chart-config');
                            // N8nDash: Chart config script found
            
            if ($configScript.length === 0) {
                // Create a default chart configuration if none exists
                // N8nDash: Creating default chart config
                this.createDefaultChart(chartId);
                return;
            }

            try {
                const configText = $configScript.text();
                // N8nDash: Chart config text
                const config = JSON.parse(configText);
                                  // N8nDash: Parsed chart config
                this.createChart(chartId, config);
            } catch (error) {
                this.createDefaultChart(chartId);
            }
        },

        /**
         * Initialize data widget
         */
        initDataWidget: function($dataWidget) {
            const widgetId = $dataWidget.closest('.n8n-widget').data('widget-id');
            // N8nDash: Initializing data widget
            
            // Data widgets are mostly static, but we can add refresh functionality
            // The refresh is handled by the main widget refresh button
        },

        /**
         * Initialize custom widget
         */
        initCustomWidget: function($customWidget) {
            const widgetId = $customWidget.data('widget-id');
            // N8nDash: Initializing custom widget

            // Additional protection: ensure form cannot submit to WordPress
            const $form = $customWidget.find('.n8n-custom-form');
            if ($form.length > 0) {
                // Remove any action attribute that might cause submission
                $form.removeAttr('action');
                $form.attr('method', 'post');
                
                // Add immediate protection
                $form.on('submit', function(e) {
                    // Form submission intercepted in main JS
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                });
                
                // Form protection added for widget
            }

            // Bind form submission for our custom handling
            $customWidget.find('.n8n-custom-form').off('submit').on('submit', function(e) {
                // Form submission event triggered for widget
                e.preventDefault();
                                  // Default prevented, calling submitCustomForm
                WidgetManager.submitCustomForm($(this), widgetId);
            });

            // Bind range sliders
            $customWidget.find('.n8n-field-range').on('input', function() {
                $(this).next('.n8n-field-range-output').text($(this).val());
            });
        },

        /**
         * Create Chart.js chart
         */
        createChart: function(chartId, config) {
            if (typeof Chart === 'undefined') {
                return;
            }

            const canvas = document.getElementById(chartId);
            if (!canvas) {
                return;
            }

            // Destroy existing chart if it exists
            if (this.charts[chartId]) {
                this.charts[chartId].destroy();
            }

            try {
                const ctx = canvas.getContext('2d');
                this.charts[chartId] = new Chart(ctx, config);
            } catch (error) {
                // Chart creation failed
            }
        },

        /**
         * Create a default chart for testing
         */
        createDefaultChart: function(chartId) {
            if (typeof Chart === 'undefined') {
                return;
            }

            const canvas = document.getElementById(chartId);
            if (!canvas) {
                return;
            }

            // Destroy existing chart if it exists
            if (this.charts[chartId]) {
                this.charts[chartId].destroy();
            }

            try {
                const ctx = canvas.getContext('2d');
                this.charts[chartId] = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Demo Data',
                            data: [12, 19, 3, 5, 2, 3],
                            borderColor: 'rgba(14, 165, 233, 1)',
                            backgroundColor: 'rgba(14, 165, 233, 0.1)',
                            tension: 0.35,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    color: '#666'
                                }
                            },
                            y: {
                                grid: {
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    color: '#666'
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
            } catch (error) {
                // Default chart creation failed
            }
        },

        /**
         * Refresh widget data
         */
        refreshWidget: function(widgetId) {
            const $widget = this.widgets[widgetId];
            if (!$widget) {
                return;
            }



            const $refreshBtn = $widget.find('.n8n-widget__refresh');
            const $statusText = $widget.find('.n8n-widget__status-text');
            
            // Show loading state
            $refreshBtn.prop('disabled', true);
            $statusText.text(n8ndash_public.strings.loading || 'Loading...');
            
            // Add spinner to button
            const originalHtml = $refreshBtn.html();
            $refreshBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span>');

            // Make AJAX request
            $.ajax({
                url: n8ndash_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'n8ndash_public_refresh_widget',
                    widget_id: widgetId,
                    nonce: n8ndash_public.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WidgetManager.updateWidgetData(widgetId, response.data);
                        $statusText.text('Updated ' + WidgetManager.formatTime(response.data.timestamp));
                        Utils.toast('Widget refreshed successfully', 'success');
                    } else {
                        $statusText.text('Error: ' + (response.data.message || 'Unknown error'));
                        Utils.toast('Failed to refresh widget: ' + (response.data.message || 'Unknown error'), 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $statusText.text('Network error');
                    Utils.toast('Network error: ' + error, 'error');
                },
                complete: function() {
                    // Restore button state
                    $refreshBtn.prop('disabled', false).html(originalHtml);
                }
            });
        },

        /**
         * Update widget with new data
         */
        updateWidgetData: function(widgetId, data) {
            const $widget = this.widgets[widgetId];
            const widgetType = $widget.data('widget-type');



            switch (widgetType) {
                case 'chart':
                    this.updateChartWidget(widgetId, data);
                    break;
                case 'data':
                    this.updateDataWidget(widgetId, data);
                    break;
                case 'custom':
                    this.updateCustomWidget(widgetId, data);
                    break;
            }
        },

        /**
         * Update chart widget with new data
         */
        updateChartWidget: function(widgetId, data) {
            const $widget = this.widgets[widgetId];
            const chartId = $widget.find('.n8n-chart-widget').data('chart-id');
            const chart = this.charts[chartId];

            if (!chart) {
                return;
            }

            // Update chart data
            if (data.labels) {
                chart.data.labels = data.labels;
            }
            if (data.data && chart.data.datasets[0]) {
                chart.data.datasets[0].data = data.data;
            }
            if (data.yMax && chart.options.scales && chart.options.scales.y) {
                chart.options.scales.y.suggestedMax = data.yMax;
            }

            chart.update();
        },

        /**
         * Update data widget with new data
         */
        updateDataWidget: function(widgetId, data) {
            const $widget = this.widgets[widgetId];
            const $dataWidget = $widget.find('.n8n-data-widget');
            const mode = $dataWidget.data('mode');

            if (mode === 'kpi') {
                // Update KPI values
                if (data.value1 !== undefined) {
                    $widget.find('#value1-' + widgetId).text(data.value1);
                }
                if (data.value2 !== undefined) {
                    const $value2 = $widget.find('#value2-' + widgetId);
                    $value2.text(data.value2);
                    
                    // Update delta styling
                    $value2.removeClass('n8n-kpi__delta--up n8n-kpi__delta--down');
                    if (String(data.value2).startsWith('-')) {
                        $value2.addClass('n8n-kpi__delta--down');
                    } else if (data.value2 && data.value2 !== '0') {
                        $value2.addClass('n8n-kpi__delta--up');
                    }
                }
                if (data.value3_url) {
                    const $value1 = $widget.find('#value1-' + widgetId);
                    if (!$value1.parent().is('a')) {
                        $value1.wrap('<a href="' + Utils.esc(data.value3_url) + '" target="_blank" style="text-decoration:none;color:inherit;"></a>');
                    }
                }
            } else if (mode === 'list') {
                // Update list items
                const $list = $widget.find('#list-' + widgetId);
                if (data.items && data.items.length > 0) {
                    const listHtml = data.items.map(function(item) {
                        return '<li class="n8n-list__item"><a href="' + Utils.esc(item.url || '#') + '" target="_blank" rel="noopener noreferrer">' + Utils.esc(item.label || 'Item') + '</a></li>';
                    }).join('');
                    $list.html(listHtml);
                } else {
                    $list.html('<li class="n8n-list__item n8n-list__item--empty">No items returned.</li>');
                }
            }
        },

        /**
         * Clean up Chart.js instance for a specific widget
         * Prevents memory leaks when widgets are removed
         */
        cleanupChart: function(widgetId) {
            const $widget = this.widgets[widgetId];
            if (!$widget) {
                return;
            }

            const chartId = $widget.find('.n8n-chart-widget').data('chart-id');
            if (chartId && this.charts[chartId]) {
                try {
                    this.charts[chartId].destroy();
                    delete this.charts[chartId];
                    // Chart.js instance cleaned up for widget ' + widgetId
                } catch (error) {
                    console.warn('N8nDash: Error cleaning up chart for widget ' + widgetId, error);
                }
            }
        },

        /**
         * Clean up all Chart.js instances
         * Call this when page is unloaded or dashboard is changed
         */
        cleanupAllCharts: function() {
            Object.keys(this.charts).forEach(chartId => {
                try {
                    if (this.charts[chartId]) {
                        this.charts[chartId].destroy();
                    }
                } catch (error) {
                    console.warn('N8nDash: Error cleaning up chart ' + chartId, error);
                }
            });
            
            // Clear charts object
            this.charts = {};
            // All Chart.js instances cleaned up
        },

        /**
         * Update custom widget with response
         */
        updateCustomWidget: function(widgetId, data) {
            const $widget = this.widgets[widgetId];
            const $response = $widget.find('#n8n-response-' + widgetId);
            
            if (data.html) {
                $response.find('.n8n-response-content').html(data.html);
            } else if (data.message) {
                $response.find('.n8n-response-content').text(data.message);
            }
            
            $response.removeClass('error success').addClass('success').show();
        },

        /**
         * Submit custom widget form with smart content-type detection
         */
        submitCustomForm: function($form, widgetId) {
                    // submitCustomForm called for widget
            
            const $submitBtn = $form.find('.n8n-submit-button');
            const $response = $('#n8n-response-' + widgetId);
            
            // Show loading state
            $submitBtn.prop('disabled', true);
            $submitBtn.find('.n8n-button-text').hide();
            $submitBtn.find('.n8n-button-spinner').show();
            
            // Clear previous response
            $response.hide().removeClass('error success');
            
            // Smart content-type detection
            const formData = new FormData($form[0]);
            const hasFiles = this.detectFileUploads(formData);
            const contentType = hasFiles ? 'multipart/form-data' : 'application/json';
            

            
            // Prepare request data
            let requestData;
            let requestHeaders = {};
            
            if (hasFiles) {
                // Use FormData for file uploads
                requestData = formData;
                // Don't set Content-Type header - let browser set it with boundary
            } else {
                // Convert FormData to JSON for text-only data
                const jsonData = {};
                for (let [key, value] of formData.entries()) {
                    jsonData[key] = value;
                }
                requestData = JSON.stringify(jsonData);
                requestHeaders['Content-Type'] = 'application/json';
            }
            
            // Add action and nonce for WordPress AJAX
            const nonceValue = $form.find('[name="nonce"]').val();
            // Nonce field found
            
            if (requestData instanceof FormData) {
                requestData.append('action', 'n8ndash_custom_widget_submit');
                requestData.append('nonce', nonceValue);
                // FormData prepared with action and nonce
            } else {
                const jsonData = JSON.parse(requestData);
                jsonData.action = 'n8ndash_custom_widget_submit';
                jsonData.nonce = nonceValue;
                requestData = JSON.stringify(jsonData);
                // JSON data prepared with action and nonce
            }
            
            // Final request data prepared
            
            // Get webhook configuration from the widget
            const $widget = $form.closest('.n8n-custom-widget');
            const currentWidgetId = $widget.data('widget-id');
            const webhookConfig = window.n8nWebhookConfigs && window.n8nWebhookConfigs[currentWidgetId];
            
            if (!webhookConfig || !webhookConfig.url) {
                WidgetManager.displayEnhancedResponse($response, 'Webhook not configured. Please configure the widget first.', 'error');
                $response.addClass('error').fadeIn();
                
                // Restore button state
                $submitBtn.prop('disabled', false);
                $submitBtn.find('.n8n-button-text').show();
                $submitBtn.find('.n8n-button-spinner').hide();
                return;
            }
            
            // Using webhook config
            
            // Prepare request options for direct webhook call
            const requestOptions = {
                method: webhookConfig.method || 'POST',
                mode: 'cors',
                headers: {}
            };
            
            // Add custom headers
            if (webhookConfig.headers && Array.isArray(webhookConfig.headers)) {
                webhookConfig.headers.forEach(header => {
                    if (header.key && header.value) {
                        requestOptions.headers[header.key] = header.value;
                    }
                });
            }
            
            let requestUrl = webhookConfig.url;
            
            if (webhookConfig.method === 'GET') {
                // For GET requests, add form data as query parameters
                const params = new URLSearchParams();
                if (requestData instanceof FormData) {
                    for (const [key, value] of requestData.entries()) {
                        if (!(value instanceof File)) {
                            params.append(key, String(value));
                        }
                    }
                } else {
                    // If it's JSON string, parse it and add to params
                    try {
                        const jsonData = JSON.parse(requestData);
                        for (const [key, value] of Object.entries(jsonData)) {
                            params.append(key, String(value));
                        }
                    } catch (e) {
                        // Error parsing JSON for GET request
                    }
                }
                requestUrl += (requestUrl.includes('?') ? '&' : '?') + params.toString();
            } else {
                // For POST requests, handle body
                if (hasFiles) {
                    // Use FormData for files
                    requestOptions.body = requestData;
                } else {
                    // requestData is already JSON string, use it directly
                    requestOptions.headers['Content-Type'] = 'application/json';
                    requestOptions.body = requestData;
                }
            }
            
            // Making direct webhook call
            
            // Make direct webhook call using fetch
            fetch(requestUrl, requestOptions)
                .then(response => {
                    const contentType = response.headers.get('content-type') || '';
                    // Webhook response received
                    
                    if (response.ok) {
                        // Handle different response types
                        if (contentType.includes('application/json')) {
                            return response.json().then(data => {
                                // JSON response received
                                WidgetManager.displayEnhancedResponse($response, data);
                                $response.addClass('success').fadeIn();
                            });
                        } else if (contentType.startsWith('text/')) {
                            return response.text().then(text => {
                                // Text response received
                                WidgetManager.displayEnhancedResponse($response, text);
                                $response.addClass('success').fadeIn();
                            });
                        } else {
                            return response.blob().then(blob => {
                                // Binary response received
                                const url = URL.createObjectURL(blob);
                                const html = `Received <b>${contentType}</b> (${blob.size.toLocaleString()} bytes). <a href="${url}" download="response">Download</a>`;
                                WidgetManager.displayEnhancedResponse($response, html);
                                $response.addClass('success').fadeIn();
                            });
                        }
                    } else {
                        throw new Error(`Webhook returned ${response.status}`);
                    }
                })
                .catch(error => {
                    WidgetManager.displayEnhancedResponse($response, 'Webhook call failed: ' + error.message, 'error');
                    $response.addClass('error').fadeIn();
                })
                .finally(() => {
                    // Restore button state
                    $submitBtn.prop('disabled', false);
                    $submitBtn.find('.n8n-button-text').show();
                    $submitBtn.find('.n8n-button-spinner').hide();
                });
        },
        
        /**
         * Detect if form has file uploads
         */
        detectFileUploads: function(formData) {
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    return true;
                }
            }
            return false;
        },
        
        /**
         * Display enhanced webhook response
         */
        displayEnhancedResponse: function($response, data, type = 'success') {
            const $content = $response.find('.n8n-response-content');
            let html = '';
            
            if (type === 'error') {
                // Error response
                html = `<div class="n8n-response-error">${this.escapeHtml(data)}</div>`;
            } else if (typeof data === 'object') {
                // Object response - check for specific fields
                if (data.html) {
                    html = data.html;
                } else if (data.download_url) {
                    html = `<div class="n8n-response-download">
                        <p>File processed successfully!</p>
                        <a href="${data.download_url}" class="button button-primary" download>Download File</a>
                    </div>`;
                } else if (data.message) {
                    html = `<div class="n8n-response-message">${this.escapeHtml(data.message)}</div>`;
                } else {
                    // JSON response
                    html = `<pre class="n8n-response-json">${JSON.stringify(data, null, 2)}</pre>`;
                }
            } else if (typeof data === 'string') {
                // String response
                html = `<div class="n8n-response-text">${this.escapeHtml(data)}</div>`;
            } else {
                // Fallback
                html = `<div class="n8n-response-fallback">Response received</div>`;
            }
            
            $content.html(html);
        },
        
        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Open widget settings (placeholder)
         */
        openWidgetSettings: function(widgetId) {
            Utils.toast('Widget settings not available in public view', 'info');
        },

        /**
         * Format timestamp for display
         */
        formatTime: function(timestamp) {
            if (!timestamp) return 'just now';
            
            const now = Math.floor(Date.now() / 1000);
            const diff = now - timestamp;
            
            if (diff < 60) return 'just now';
            if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
            return Math.floor(diff / 86400) + ' days ago';
        }
    };

    /**
     * Theme Manager
     */
    const ThemeManager = {
        init: function() {
            // Apply theme from dashboard data attribute
            $('.n8ndash-dashboard').each(function() {
                const theme = $(this).data('theme');
                if (theme) {
                    $('body').addClass('n8ndash-theme-' + theme);
                }
            });
        }
    };

    /**
     * Initialize widget interactions (drag and resize)
     */
    function initWidgetInteractions() {
        // Only initialize if interact.js is available
        if (typeof interact === 'undefined') {
            return;
        }

        // Initialize drag and drop for widgets
        interact('.n8n-widget')
            .draggable({
                inertia: true,
                modifiers: [
                    interact.modifiers.restrictRect({
                        restriction: 'parent',
                        endOnly: true
                    })
                ],
                autoScroll: true,
                listeners: {
                    move: function(event) {
                        var target = event.target;
                        var x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                        var y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                        target.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
                        target.setAttribute('data-x', x);
                        target.setAttribute('data-y', y);
                    },
                    end: function(event) {
                        var target = event.target;
                        var widgetId = target.getAttribute('data-widget-id');
                        if (widgetId) {
                            saveWidgetPosition(widgetId, target);
                        }
                    }
                }
            })
            .resizable({
                edges: { left: true, right: true, bottom: true, top: true },
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
                    },
                    end: function(event) {
                        var target = event.target;
                        var widgetId = target.getAttribute('data-widget-id');
                        if (widgetId) {
                            saveWidgetPosition(widgetId, target);
                        }
                    }
                },
                modifiers: [
                    interact.modifiers.restrictEdges({
                        outer: 'parent',
                        endOnly: true
                    }),
                    interact.modifiers.restrictSize({
                        min: { width: 200, height: 100 }
                    })
                ],
                inertia: true
            });
    }

    /**
     * Save widget position to server
     */
    function saveWidgetPosition(widgetId, element) {
        var x = parseFloat(element.getAttribute('data-x')) || 0;
        var y = parseFloat(element.getAttribute('data-y')) || 0;
        var width = parseInt(element.style.width) || 300;
        var height = parseInt(element.style.height) || 200;

        // Send position update to server
        $.post(n8ndash_public.ajax_url, {
            action: 'n8ndash_save_widget_position',
            widget_id: widgetId,
            x: x,
            y: y,
            width: width,
            height: height,
            nonce: n8ndash_public.nonce
        }, function(response) {
            if (response.success) {
                // Widget position saved
            } else {
                // Failed to save widget position
            }
        }).fail(function(xhr, status, error) {
            // Error saving widget position
        });
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        // Initialize theme
        ThemeManager.init();
        
        // Initialize widgets
        WidgetManager.init();
        
        // Initialize Lucide icons if available
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }

        // Initialize widget interactions
        initWidgetInteractions();
    });

    // Add page unload cleanup to prevent memory leaks
    $(window).on('beforeunload', function() {
        WidgetManager.cleanupAllCharts();
    });
    
    // Expose public API (maintain backward compatibility)
    window.N8nDash.WidgetManager = WidgetManager;
    window.N8nDash.Utils = Utils;
    
    // Add compatibility for diagnostic tests
    window.N8nDashWidgetManager = WidgetManager;

})(jQuery);