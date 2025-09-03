<?php
/**
 * Fired during plugin deactivation
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    N8nDash_Pro
 * @subpackage N8nDash_Pro/includes
 * @author     Amit Anand Niraj <n8ndash@gmail.com>
 */
class N8nDash_Deactivator {

    /**
     * Plugin deactivation handler.
     *
     * Clean up scheduled events and perform any other cleanup tasks
     * required when the plugin is deactivated.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear scheduled events
        self::clear_scheduled_events();
        
        // Clear transients
        self::clear_transients();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Clear all scheduled events.
     *
     * @since    1.0.0
     */
    private static function clear_scheduled_events() {
        // Clear any scheduled cron events if they exist
        if ( wp_next_scheduled( 'n8ndash_cleanup_event' ) ) {
            wp_unschedule_event( wp_next_scheduled( 'n8ndash_cleanup_event' ), 'n8ndash_cleanup_event' );
        }
        
        if ( wp_next_scheduled( 'n8ndash_cache_cleanup' ) ) {
            wp_unschedule_event( wp_next_scheduled( 'n8ndash_cache_cleanup' ), 'n8ndash_cache_cleanup' );
        }
    }

    /**
     * Clear all plugin transients.
     *
     * @since    1.0.0
     */
    private static function clear_transients() {
        global $wpdb;
        
        // Delete all transients with our prefix
        $wpdb->query( 
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_n8ndash_%' 
             OR option_name LIKE '_transient_timeout_n8ndash_%'"
        );
    }
}