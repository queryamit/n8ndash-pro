<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/includes
 * @author     Amit Anand Niraj <n8ndash@gmail.com>
 */
class N8nDash_i18n {

    /**
     * Load the plugin text domain for translation.
     * Note: WordPress.org automatically loads translations for plugins hosted on WordPress.org
     * This method is kept for compatibility but is not called automatically.
     *
     * @since    1.0.0
     * @deprecated Since WordPress 4.6, WordPress.org automatically loads translations
     */
    public function load_plugin_textdomain() {
        // WordPress.org automatically loads translations for plugins hosted on WordPress.org
        // This method is kept for compatibility but is not called automatically
        // Note: This method is not called automatically to comply with WordPress.org guidelines
    }
}